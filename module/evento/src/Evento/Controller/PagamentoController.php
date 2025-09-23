<?php

namespace Evento\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;

use Evento\Form\PlanilhaCielo as formUpload;
use Application\Params\Parametros as arrayParams;
use Associados\Form\DadosIpag as formIpag;
use Associados\Classes\Ipag as Ipag;


class PagamentoController extends BaseController
{   
    //Sandbox
    //private $urlRedir = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    //private $endPoint = 'https://api-3t.sandbox.paypal.com/nvp';

    //Produção
    private $urlRedir = 'https://www.paypal.com/cgi-bin/webscr';
    private $endPoint = 'https://api-3t.paypal.com/nvp';

    public function boletobradescoAction()
    {   
        //Pegar id da inscrição na sessão
        $codigoBoleto = $this->params()->fromRoute('codigoBoleto');

        //pesquisar inscrição
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $inscricao = $serviceInscricao->getInscricaoByBoleto($codigoBoleto);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('inscricao' => $inscricao));
        return $view;
    }

    public function cieloAction(){
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');
        //pesquisar inscrição
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $inscricao = $serviceInscricao->getInscricaoById($idInscricao);
        $valorTotal = str_replace('.', '', $inscricao->valor_total);
        $quantidadeTotal = $inscricao->quantidade_membros + 1;


        $order = array(
            "OrderNumber"       => $inscricao->id
        );
        $order['Cart'] = array();
        $order['Cart']['Discount'] = array(
            'Type'  => 'Percent',
            'Value' => 00
        );
        $order['Cart']['Items'] = array(
            "Name"          =>  $inscricao->nome_evento,
            "Description"   =>  $inscricao->nome_evento,
            "UnitPrice"     =>  $valorTotal,
            "Quantity"      =>  $quantidadeTotal,
            "Type"          =>  "Service"
        );
        $order['Shipping'] = array(
            'Type'  =>  'WithoutShipping'
        );

        if(!empty($inscricao->celular_cliente)){
            $telefone = $inscricao->celular_cliente;
        }else{
            $telefone = $inscricao->telefone_cliente;
        }
        $telefone = str_replace('(', '', $telefone);
        $telefone = str_replace(')', '', $telefone);
        $telefone = str_replace(' ', '', $telefone);
        $telefone = str_replace('-', '', $telefone);

        $order['Customer'] = array(
            "Identity"  =>  $inscricao->cpf,
            "FullName"  =>  $inscricao->nome_completo,
            "Email"     =>  $inscricao->email_cliente,
            "Phone"     =>  $telefone
        );
        $order['Options'] = array(
            'AntifraudEnabled'  =>  true,
            'ReturnUrl'         =>  'http://eventos.local/evento/inscricoes/sucesso'
        );
        

        $client = new Client('https://cieloecommerce.cielo.com.br/api/public/v1/orders');
        $client->setMethod('POST');
        $client->setOptions(array('timeout' => 30));
        $adapter = new Curl();
        $client->setAdapter($adapter);

        $client->setParameterPost($order);

        $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            )
        ));

        $response = $client->send($client->getRequest());
        $response = $response->getBody();
        
        $view = new ViewModel(array('html' => $response));
        $view->setTerminal(true);
        return $view;

    }

    public function retornocieloAction(){
        //$this->layout('layout/cielo');
        $res = false;

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();

            if(isset($dados->checkout_cielo_order_number) && isset($dados->order_number)){
                //marcar inscrição como pago!
                $serviceInscricao = $this->getServiceLocator()->get('Inscricao');

                //pesquisar inscrição
                $inscricao = $serviceInscricao->getRecord($dados->order_number);

                //comparar valores
                $valorTotal = str_replace('.', '', $inscricao->valor_total);
                if($valorTotal == $dados->amount){
                    //mudar forma de pagamento

                    $res = $serviceInscricao->update(array('inscricao_status' => 3, 
                                                    'data_hora_pagamento' => date('Y-m-d H:i:s'),
                                                    'checkout_cielo_order_number' => $dados->checkout_cielo_order_number,
                                                    'forma_pagamento' => $dados->payment_method_type
                                                ), 
                                                array('id' => $dados->order_number)
                                            );
                }
            }
        }

        $this->layout('layout/cielo');
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('sucesso' => $res));
        return $view;
    }

    public function mudarstatuscieloAction(){
        $res = false;
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados->checkout_cielo_order_number) && isset($dados->order_number)){
                //marcar inscrição como pago!
                $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
                $serviceStatusPagamento = $this->getServiceLocator()->get('StatusPagamento');

                //verificar valores
                $inscricao = $serviceInscricao->getInscricaoById($dados->order_number);
                $valorTotal = str_replace('.', '', $inscricao->valor_total);
                
                $idStatus = $serviceStatusPagamento->insert(array('inscricao' => $dados->order_number, 'status' => $dados->payment_status));
                
                $res = $serviceInscricao->update(array('status_pagamento' => $dados->payment_status, 
                                                'checkout_cielo_order_number' => $dados->checkout_cielo_order_number,
                                                'forma_pagamento' => $dados->payment_method_type
                                            ), 
                                            array('id' => $dados->order_number)
                                        );

                //enviar email para cliente
                $mailer = $this->getServiceLocator()->get('mailer');
                if($dados->payment_status == 2){
                    //Pago
                    $mailer->mailUser(
                        $inscricao->email_cliente, 
                        $inscricao->nome_evento, 
                        $this->mensagemEmail($inscricao->mensagem_pagamento, $inscricao));

                }

                if($dados->payment_status == 3 || $dados->payment_status == 5){
                    //negado ou cancelado
                    $mailer->mailUser($inscricao->email_cliente, 'Erro no pagamento', 'Não foi possível confirmar sua inscrição no evento '.
                            $inscricao->nome_evento.'.');
                }
            }
        }
        
        $this->layout('layout/cielo');
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('sucesso' => $res));
        return $view;
    }

    public function planilhacieloAction(){
        $formUpload = new formUpload('frmUpload');

        if($this->getRequest()->isPost()){
            $files = $this->getRequest()->getfiles()->toArray();
            $dados = $this->uploadImagem($files, 'public/arquivos', array());
            
            $objReader = new \PHPExcel_Reader_Excel2007();
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($dados['arquivo']);
            $objPHPExcel->setActiveSheetIndex(0);
            $inscricoes = $objPHPExcel->getActiveSheet()->toArray(null, true,true,true);
            $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
            
            $inscricoesPagas = 0;
            $mailer = $this->getServiceLocator()->get('mailer');

            foreach ($inscricoes as $inscricao) {
                if(strtolower($inscricao['G']) == 'pago'){
                    $inscricaoBase = $serviceInscricao->getInscricaoById(array('tb_inscricao.id' => $inscricao['B']));
                    if($inscricaoBase && $inscricaoBase['status_pagamento'] != 2){

                        switch ($inscricao['D']) {
                            case 'Cartão de crédito':
                                $pagamento = 1;
                                break;
                            case 'Cartão de Débito':
                                $pagamento = 4;
                                break;
                            case 'Débito Online':
                                $pagamento = 3;
                                break;
                        }
                        $serviceInscricao->update(array(
                                'inscricao_status'              => 3, 
                                'data_hora_pagamento'           => date('Y-m-d H:i:s'),
                                'checkout_cielo_order_number'   => 'Confirmado por planílha',
                                'status_pagamento'              => 2
                            ), 
                            array('id' => $inscricao['B']));
                        
                        if(!empty($inscricaoBase['email_cliente'])){
                            $mailer->mailUser(
                                $inscricaoBase['email_cliente'], 
                                $inscricaoBase->nome_evento, 
                                $this->mensagemEmail($inscricaoBase->mensagem_pagamento, $inscricaoBase)
                            );
                            
                        }

                        $inscricoesPagas++;
                        
                    }
                }    
            }
            $this->flashMessenger()->addSuccessMessage($inscricoesPagas.' inscrições alteradas!');
            return $this->redirect()->toRoute('planilhaCielo');
        }
        return new ViewModel(array(
                'formUpload'    => $formUpload
            ));
    }


    public function paypalAction(){
        $this->layout('layout/avaliacao');
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');
        
        //pesquisar inscricao
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($idInscricao);
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);
        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($evento->empresa);
        $this->layout()->evento =  $evento;

        if(empty($empresa->paypal_pwd) || empty($empresa->paypal_user) || empty($empresa->paypal_signature)){
            die('Não existem dados do paypal para esta empresa, por favor contate o administrador!');
        }

        $paramsGlobal = new arrayParams();
        $nvp = array(
            'LOCALECODE'                        => 'pt_BR',
            'PAYMENTREQUEST_0_PAYMENTACTION'    => 'SALE',
            'PAYMENTREQUEST_0_AMT'              => $inscricao->valor_total,
            'PAYMENTREQUEST_0_INVNUM'           => $idInscricao,
            'PAYMENTREQUEST_0_CURRENCYCODE'     => 'BRL', 
            'PAYMENTREQUEST_0_ITEMAMT'          => $inscricao->valor_total,
            'L_PAYMENTREQUEST_0_NAME0'          => $evento->sigla,
            'L_PAYMENTREQUEST_0_DESC0'          => $evento->nome,
            'L_PAYMENTREQUEST_0_AMT0'           => $inscricao->valor_total,
            'RETURNURL'                         => $paramsGlobal->getBaseUrl().'/evento/retorno/paypal',
            'CANCELURL'                         => $paramsGlobal->getBaseUrl().'/cancelamento/paypal',
            'METHOD'                            => 'SetExpressCheckout',
            'VERSION'                           => '108.0',
            'PWD'                               => $empresa->paypal_pwd,
            'USER'                              => $empresa->paypal_user,
            'SIGNATURE'                         => $empresa->paypal_signature, 
        );
        $curl = curl_init();

        curl_setopt( $curl , CURLOPT_URL , $this->endPoint );
        curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false );
        curl_setopt( $curl , CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt( $curl , CURLOPT_POST , 1 );
        curl_setopt( $curl , CURLOPT_POSTFIELDS , http_build_query( $nvp ) );

        $response = urldecode( curl_exec( $curl ) );

        curl_close( $curl );
        $responseNvp = array();

        
        if ( preg_match_all( '/(?<name>[^\=]+)\=(?<value>[^&]+)&?/' , $response , $matches ) ) {
            foreach ( $matches[ 'name' ] as $offset => $name ) {
                $responseNvp[ $name ] = $matches[ 'value' ][ $offset ];
            }
        }
        if ( isset( $responseNvp[ 'ACK' ] ) && $responseNvp[ 'ACK' ] == 'Success' ) {
            $paypalURL = $this->urlRedir;
            $query = array(
                'cmd'   => '_express-checkout',
                'useraction' => 'commit', 
                'token' => $responseNvp[ 'TOKEN' ]
            );
            return $this->redirect()->toUrl($paypalURL . '?' . http_build_query( $query ));
            //header( 'Location: ' . $paypalURL . '?' . http_build_query( $query ) );
        } else {
            echo $response;
        }
    }

    public function retornopaypalAction(){
        $this->layout('layout/avaliacao');
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $inscricao = $serviceInscricao->getRecord($idInscricao);
        $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($inscricao->cliente);
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);
        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($evento->empresa);
        $this->layout()->evento =  $evento;


        if ( isset( $_GET[ 'token' ] ) ) {
            $token = $_GET[ 'token' ];

            $nvp = array(
                'TOKEN'     => $token,
                'METHOD'    => 'GetExpressCheckoutDetails',
                'VERSION'   => '108.0', 
                        'PWD'       => $empresa->paypal_pwd,
                        'USER'      => $empresa->paypal_user,
                        'SIGNATURE'     => $empresa->paypal_signature 
            );

            $curl = curl_init();

            curl_setopt( $curl , CURLOPT_URL , $this->endPoint );
            curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false );
            curl_setopt( $curl , CURLOPT_RETURNTRANSFER , 1 );
            curl_setopt( $curl , CURLOPT_POST , 1 );
            curl_setopt( $curl , CURLOPT_POSTFIELDS , http_build_query( $nvp ) );

            $response = urldecode( curl_exec( $curl ) ); 
            
            $responseNvp = array();
             

            if ( preg_match_all( '/(?<name>[^\=]+)\=(?<value>[^&]+)&?/' , $response , $matches ) ) {
                foreach ( $matches[ 'name' ] as $offset => $name ) {
                    $responseNvp[ $name ] = $matches[ 'value' ][ $offset ];
                }
            }
            
            $idInscricao = $responseNvp['INVNUM'];

            if ( isset( $responseNvp[ 'TOKEN' ] ) && isset( $responseNvp[ 'ACK' ] ) ) {
                if ( $responseNvp[ 'TOKEN' ] == $token && $responseNvp[ 'ACK' ] == 'Success' ) {
                    $nvp[ 'PAYERID' ]           = $responseNvp[ 'PAYERID' ];
                    $nvp[ 'PAYMENTREQUEST_0_AMT' ]      = $responseNvp[ 'PAYMENTREQUEST_0_AMT' ];
                    $nvp[ 'PAYMENTREQUEST_0_CURRENCYCODE' ] = $responseNvp[ 'PAYMENTREQUEST_0_CURRENCYCODE' ];
                                //$nvp[ 'SUBJECT' ]                       = $responseNvp[ 'SUBJECT' ];
                    $nvp[ 'METHOD' ]            = 'DoExpressCheckoutPayment';
                    $nvp[ 'PAYMENTREQUEST_0_PAYMENTACTION' ]= 'SALE'; 
                    curl_setopt( $curl , CURLOPT_POSTFIELDS , http_build_query( $nvp ) );

                    $response = urldecode( curl_exec( $curl ) );
                    $responseNvp = array();

                    if ( preg_match_all( '/(?<name>[^\=]+)\=(?<value>[^&]+)&?/' , $response , $matches ) ) {
                        foreach ( $matches[ 'name' ] as $offset => $name ) {
                            $responseNvp[ $name ] = $matches[ 'value' ][ $offset ];
                        }
                    }
                    if ( $responseNvp[ 'PAYMENTINFO_0_PAYMENTSTATUS' ] == 'Completed' ) {
                        $sessao->offsetSet('inscricao', $idInscricao);
                        $inscricao = $serviceInscricao->getRecord($idInscricao);
                        //marcar como pago
                        $result = $serviceInscricao->pagarPaypal($nvp, $inscricao);

                        //enviar email
                        $mailer = $this->getServiceLocator()->get('mailer');
                        $mailer->mailUser(
                            $cliente['email'], 
                            $evento->nome, 
                            $this->mensagemEmail($evento->mensagem_pagamento, $cliente)
                        );

                        if($result){
                            $this->flashMessenger()->addSuccessMessage('Inscrição realizada com sucesso!');
                            return $this->redirect()->toRoute('sucesso', array('inscricao' => $idInscricao));
                        }
                    } else {
                        if($responseNvp[ 'PAYMENTINFO_0_PAYMENTSTATUS' ] == 'Pending'){
                            //mudar status para 1
                            $sessao->offsetSet('inscricao', $idInscricao);
                            $inscricao = $serviceInscricao->getRecord($idInscricao);
                            $result = $serviceInscricao->update(array('status_pagamento' => 1), array('id' => $idInscricao));

                            $this->flashMessenger()->addSuccessMessage('Inscrição realizada com sucesso, seu pagamento está pendente e será confirmado nas próximas 24 horas!');
                            return $this->redirect()->toRoute('sucesso', array('inscricao' => $idInscricao));


                        }
                        $result = false;
                    }
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
            curl_close( $curl );

            //se result for false, gravar log
            if(!$result){
                $mensagem = $responseNvp['TIMESTAMP'].' - Inscrição: '.$idInscricao.'. Erro número: '.$responseNvp['L_ERRORCODE0'].
                ' - '.$responseNvp['L_LONGMESSAGE0'];
                parent::logSistema($mensagem, 'paypal');

                //10486 - This transaction couldn't be completed. Please redirect your customer to PayPal.
                if($responseNvp['L_ERRORCODE0'] == 10486 || $responseNvp['L_ERRORCODE0'] == '10486'){
                    return new ViewModel();                  
                }

                $this->flashMessenger()->addErrorMessage('Erro ao realizar pagamento, por favor tente novamente ou contate o administrador!');
                return $this->redirect()->toRoute('atividades', array('inscricao' => $idInscricao));
            }
        }
    }

    public function cancelamentopaypalAction(){
        $this->flashMessenger()->addWarningMessage('Pagamento cancelado pelo usuário!');
        return $this->redirect()->toRoute('atividades');
    }

    public function realizarpagamentoinscricaoipagAction(){
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');
        $this->layout('layout/avaliacao');
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($idInscricao);
        $evento = $this->getServiceLocator()->get('Evento')->getRecordFromArray(array('id' => $inscricao['evento'], 'ativo' => 'S'));
        $this->layout()->evento = $evento;
        
        $formIpag = new formIpag('formIpag', $evento['parcelas']);

        if($this->getRequest()->isPost()){
            $formIpag->setData($this->getRequest()->getPost());
            if($formIpag->isValid()){
                $dadosCartao = $formIpag->getData();
                $dataVencimento = explode('/', $dadosCartao['expiry_date']);                
                $cliente = $this->getServiceLocator()->get('Cliente')->getClienteById($inscricao['cliente']);
                //verificar se já existe pagamento
                if ($inscricao['status_pagamento'] == 2) {
                    die('Pagamento já foi efetuado!');
                }

                $pagamentoInscricao = array(
                    'amount' => $inscricao['valor_total'],
                    'callback_url' => '',
                    'order_id' => 'i-'.$inscricao['id'],
                    'capture' => false,
                    'payment' => array(
                        'type' => 'card',
                        'method' => $dadosCartao['bandeira'],
                        'installments' => $dadosCartao['parcelas'],
                        'card' => array(
                            'holder' => $dadosCartao['holder'],
                            'number' => $dadosCartao['number'],
                            'expiry_month' => $dataVencimento[0],
                            'expiry_year' => $dataVencimento[1],
                            'cvv' => $dadosCartao['cvv']
                        )
                    ),
                    'customer' => array(
                        'name'      => $cliente['nome_completo'],
                        'cpf_cnpj'  => str_replace(['.', '-'], '', $cliente['cpf']),
                    )
                );

                $iPag = new Ipag($this->getServiceLocator());
                $res = $iPag->realizarPagamentoInscricao($pagamentoInscricao, $inscricao);
                parent::logSistema('REALIZAR PAGAMENTO INSCRICAO: '.json_encode($res), 'iPagInsc');
                $response = $iPag->getResponse();
                
                if ($res == true) {
                    //$this->flashMessenger()->addSuccessMessage('Pagamento registrado com sucesso, aguarde a confirmação!');
                    return $this->redirect()->toRoute('sucesso');
                } else {
                    $this->flashMessenger()->addErrorMessage($iPag->httpCodeMessage());
                    return $this->redirect()->toRoute('realizarPagamentoInscricaoIpag');
                }
                
            }
        }

        return new ViewModel(array(
            'formIpag' => $formIpag
        ));
    }

    public function realizarpagamentoinscricaoipagpixAction(){
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');
        $this->layout('layout/avaliacao');
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($idInscricao);
        $evento = $this->getServiceLocator()->get('Evento')->getRecordFromArray(array('id' => $inscricao['evento'], 'ativo' => 'S'));
        $this->layout()->evento = $evento;
        
        $cliente = $this->getServiceLocator()->get('Cliente')->getClienteById($inscricao['cliente']);

        $cliente['telefone'] = parent::formatarTelefone($cliente['telefone']);
        $cliente['celular'] = parent::formatarTelefone($cliente['celular']);

        if ($inscricao['status_pagamento'] == 2) {
            die('Pagamento já foi efetuado!');
        }

        $pagamentoInscricao = array(
            'amount' => $inscricao['valor_total'],
            'callback_url' => '',
            'order_id' => 'i-'.$inscricao['id'],
            'payment' => array(
                'type' => 'pix',
                'method' => 'pix',
                'pix_expires_in' => 60
            ),
            'customer' => array(
                'name'      => $cliente['nome_completo'],
                'cpf_cnpj'  => str_replace(['.', '-'], '', $cliente['cpf']),
            )
        );

        $iPag = new Ipag($this->getServiceLocator());
        $res = $iPag->realizarPagamentoInscricao($pagamentoInscricao, $inscricao);
        parent::logSistema('REALIZAR PAGAMENTO INSCRICAO: '.json_encode($res), 'iPagInsc');
        $response = $iPag->getResponse();
        
        if (isset($response->attributes)) {
            return $this->redirect()->toUrl($response->attributes->pix->link);
        } else {
            $this->flashMessenger()->addErrorMessage($iPag->httpCodeMessage());
            return $this->redirect()->toRoute('atividades');
        }
                
        return new ViewModel(array(
        ));
    }

    public function retornoinscricaoipagAction()
    {
        $dados = json_decode(file_get_contents('php://input'));
        parent::logSistema('URL RETORNO INSCRICAO: '.json_encode($dados), 'iPagInsc');
        $iPag = new Ipag($this->getServiceLocator());
        $iPag->capturarRetornoInscricao($dados);
        die('retorno pagamento inscricao ipag!');
    }

    public function uploadImagem($arquivos, $caminho, $dados){
        if(!file_exists($caminho)){
            mkdir($caminho);
        }
        

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                $nomeArquivoServer = 'cielo';
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$nomeArquivoServer.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/'.$nomeArquivoServer.'.'.$extensao;
                }
            }
        }

        return $dados;
    }

    public function getExtensao($name){
        $extensao = explode('.', $name);
        return $extensao[1];
    }

}
?>