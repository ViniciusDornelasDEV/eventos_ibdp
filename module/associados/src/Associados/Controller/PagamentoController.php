<?php

namespace Associados\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Associados\Form\Pagar as formPagar;
use Associados\Form\PesquisarPagamento as formPesquisa;
use Application\Params\Parametros as arrayParams;

class PagamentoController extends BaseController
{
    //Sandbox
    //private $urlRedir = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    //private $endPoint = 'https://api-3t.sandbox.paypal.com/nvp';

    //Produção
    private $urlRedir = 'https://www.paypal.com/cgi-bin/webscr';
    private $endPoint = 'https://api-3t.paypal.com/nvp';

    public function indexAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }
        
        $formPesquisa = new formPesquisa('formPesquisa', $this->getServiceLocator(), false, $empresa);
        $container = new Container();
        if(!isset($container->dados)){
            $container->dados = array();
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['limpar'])){
                $container->dados = array();
                $this->redirect()->toRoute('listaPagamentosAssociados');
            }else{
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $container->dados = $formPesquisa->getData();
                }
                
            }
        }

        if($empresa){
            $container->dados['empresa'] = $empresa;
        }

        $formPesquisa->setData($container->dados);
        
        //pesquisar pagamentos
        $pagamentos = $this->getServiceLocator()->get('Associado')->getPagamentos($container->dados);
        
        //paginação
        $paginator = new Paginator(new ArrayAdapter($pagamentos->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);


        return new ViewModel(array(
                    'pagamentos'     => $paginator,
                    'formPesquisa'   => $formPesquisa,
                    'empresa'        => $empresa
                ));
    }

    public function downloadcomprovanteAction(){
        $serviceComprovante = $this->getServiceLocator()->get('AssociadoPagamentoComprovante');
        $comprovante = $serviceComprovante->getRecord($this->params()->fromRoute('id'));
        $fileName = $comprovante->arquivo;
        if(!is_file($fileName)) {
            //Não foi possivel encontrar o arquivo
        }

        //se cliente, verificar se o arquivo é realmente dele!
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 2){
            $associado = $this->getServiceLocator()->get('Associado')->getAssociados(array('cliente' => $usuario['cliente']))->current();
            if($associado['id'] != $comprovante['associado']){
                $this->flashMessenger()->addWarningMessage('Arquivo nã encontrado!');
                return $this->redirect()->toRoute('anuidadesCliente');
            }
        }

        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'whatever your content type is')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;
    }

    public function pagarAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $idAssociado = $this->params()->fromRoute('idAssociado');
        $idAnuidade = $this->params()->fromRoute('idAnuidade');

        $associado = $this->getServiceLocator()->get('Associado')->getPagamento($idAssociado, $idAnuidade);
        if(!$associado || ($empresa && $associado['empresa'] != $empresa)){
            $this->flashMessenger()->addWarningMessage('Associado não encontrado!');
            return $this->redirect()->toRoute('listaPagamentosAssociados');
        }


        $formPagar = new formPagar('formPagar');
        if(!empty($associado['valor_pagamento'])){
          $associado['valor'] = $associado['valor_pagamento'];
        }

        if(empty($associado['data_baixa'])){
            $associado['data_baixa'] = date('Y-m-d H:i:s');
        }
        $formPagar->setData($associado);
        if($this->getRequest()->isPost()){
            $formPagar->setData($this->getRequest()->getPost());
            if($formPagar->isValid()){
                $servicePagamento = $this->getServiceLocator()->get('AssociadoPagamento');
                $dados = $formPagar->getData();
                if(!empty($associado->forma_pagamento)){
                    $servicePagamento->update(array('forma_pagamento' => $dados['forma_pagamento'], 'data_pagamento' => $dados['data_pagamento'], 'valor_pagamento' => $associado['valor']), array('id' => $associado->id_pagamento));
                    $this->flashMessenger()->addSuccessMessage('Pagamento alterado com sucesso!');
                    return $this->redirect()->toRoute('listaPagamentosAssociados'); 
                }else{
                    if(empty($dados['data_pagamento'])){
                        unset($dados['data_pagamento']);
                    }
                    
                    $usuario = $this->getServiceLocator()->get('session')->read();
                    $dados['associado'] = $idAssociado;
                    $dados['anuidade'] = $idAnuidade;
                    $dados['valor_pagamento'] = $associado['valor'];
                    $dados['usuario_baixa'] = $usuario['id'];

                    if($servicePagamento->insert($dados)){
                        $this->flashMessenger()->addSuccessMessage('Pagamento inserido com sucesso!');
                        return $this->redirect()->toRoute('listaPagamentosAssociados'); 
                    }
                    
                }
            }
        }

        //pesquisar se cliente fez upload de comprovante
        $comprovante = $this->getServiceLocator()
            ->get('AssociadoPagamentoComprovante')
            ->getRecordFromArray(array('anuidade' => $idAnuidade, 'associado' => $idAssociado));

        return new ViewModel(array(
            'formPagar'     => $formPagar, 
            'associado'     => $associado,
            'idAssociado'   => $idAssociado,
            'idAnuidade'    => $idAnuidade,
            'comprovante'   => $comprovante
        ));
    }

    public function cancelarpagamentoAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $idAssociado = $this->params()->fromRoute('idAssociado');
        $idAnuidade = $this->params()->fromRoute('idAnuidade');

        $associado = $this->getServiceLocator()->get('Associado')->getPagamento($idAssociado, $idAnuidade);
        if(!$associado || ($empresa && $associado['empresa'] != $empresa)){
            $this->flashMessenger()->addWarningMessage('Associado não encontrado!');
            return $this->redirect()->toRoute('listaPagamentosAssociados');
        }

        if($this->getServiceLocator()->get('AssociadoPagamento')->delete(array('id' => $associado['id_pagamento']))){
            $this->flashMessenger()->addSuccessMessage('Pagamento cancelado com sucesso!');
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao cancelar pagamento!');
        }
        return $this->redirect()->toRoute('pagarAnuidade', array('idAssociado' => $idAssociado, 'idAnuidade' => $idAnuidade));
    }

    public function carregaranuidadeAction(){   
        $params = $this->getRequest()->getPost();
        //instanciar form
        $formPesquisa = new formPesquisa('formPesquisa', $this->getServiceLocator(), $params->required);
        $anuidade = $formPesquisa->setAnuidadeByCategoria($params->categoria);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('anuidades' => $anuidade));
        return $view;
    }

        public function paypalAction(){
        $this->layout('layout/cliente');
        $idAssociado = $this->params()->fromRoute('idAssociado');
        $idAnuidade = $this->params()->fromRoute('idAnuidade');

        $sessao = new Container();
        $sessao->offsetSet('idAssociado', $idAssociado);
        $sessao->offsetSet('idAnuidade', $idAnuidade);


        $pagamento = $this->getServiceLocator()->get('Associado')->getPagamento($idAssociado, $idAnuidade);
        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($pagamento->empresa);

        if(empty($empresa->paypal_pwd) || empty($empresa->paypal_user) || empty($empresa->paypal_signature)){
            die('Não existem dados do paypal para esta empresa, por favor contate o administrador!');
        }

        $paramsGlobal = new arrayParams();
        $nvp = array(
            'LOCALECODE'                        => 'pt_BR',
            'PAYMENTREQUEST_0_PAYMENTACTION'    => 'Sale',
            'PAYMENTREQUEST_0_AMT'              => $pagamento->valor,
            'PAYMENTREQUEST_0_CURRENCYCODE'     => 'BRL', 
            'PAYMENTREQUEST_0_ITEMAMT'          => $pagamento->valor,
            'L_PAYMENTREQUEST_0_NAME0'          => $pagamento->nome_fantasia,
            'L_PAYMENTREQUEST_0_DESC0'          => 'Anuidade '.$pagamento->nome_fantasia,
            'L_PAYMENTREQUEST_0_AMT0'           => $pagamento->valor,
            'RETURNURL'                         => $paramsGlobal->getBaseUrl().'/associado/pagamento/paypal/retorno',
            'CANCELURL'                         => $paramsGlobal->getBaseUrl().'/associado/pagamento/paypal/cancelamento',
            'METHOD'                            => 'SetExpressCheckout',
            'VERSION'                           => '73.0',
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
        $idAssociado = $sessao->offsetGet('idAssociado');
        $idAnuidade = $sessao->offsetGet('idAnuidade');
        $pagamento = $this->getServiceLocator()->get('Associado')->getPagamento($idAssociado, $idAnuidade);
        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord($pagamento->empresa);

        if ( isset( $_GET[ 'token' ] ) ) {
            $token = $_GET[ 'token' ];

            $nvp = array(
                'TOKEN'     => $token,
                'METHOD'    => 'GetExpressCheckoutDetails',
                'VERSION'   => '124.0', 
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
                        //marcar como pago
                        $dadosInsert = array(
                            'associado'         =>  $idAssociado,
                            'anuidade'          =>  $idAnuidade,
                            'forma_pagamento'   =>  5,
                            'valor_pagamento'   =>  $pagamento['valor'],
                            'token_paypal'      =>  $nvp['TOKEN'],
                            'payer_id_paypal'   =>  $nvp['PAYERID']
                        );

                        $result = $this->getServiceLocator()->get('AssociadoPagamento')->insert($dadosInsert);

                        //enviar email
                        $mailer = $this->getServiceLocator()->get('mailer');
                        $mailer->mailUser(
                            $pagamento['email'], 
                            'Anuidade '.$pagamento['nome_fantasia'], 
                            'Anuidade paga com sucesso!'
                        );

                        if($result){
                            $this->flashMessenger()->addSuccessMessage('Anuidade paga com sucesso!!');
                            return $this->redirect()->toRoute('anuidadesCliente');
                        }
                    } else {
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
                $mensagem = $responseNvp['TIMESTAMP'].' - Anuidade: '.$idAnuidade.', associado: '.$idAssociado.'. Erro número: '.$responseNvp['L_ERRORCODE0'].
                ' - '.$responseNvp['L_LONGMESSAGE0'];
                parent::logSistema($mensagem, 'paypal');

                $this->flashMessenger()->addErrorMessage('Erro ao realizar pagamento, por favor tente novamente ou contate o administrador!');
                return $this->redirect()->toRoute('anuidadesCliente');
            }
        }
    }

    public function cancelamentopaypalAction(){
        $this->flashMessenger()->addWarningMessage('Pagamento cancelado pelo usuário!');
        return $this->redirect()->toRoute('anuidadesCliente');
    }

  

}

