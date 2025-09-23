<?php

namespace Evento\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Crypt\Password\Bcrypt;


use Evento\Form\PesquisaInscricao as pesquisaInscricaoForm;
use Cliente\Form\PessoaFisica as pessoaFisicaForm;
use Cliente\Form\PessoaJuridica as pessoaJuridicaForm;

use Evento\Form\MudarCategoria as formMudarCategoria;
use Evento\Form\PagarInscricao as formPagar;
use Evento\Form\Promocao as formPromocao;
use Evento\Form\CadastrarSenha as formSenha;



use Application\Params\Parametros;

class InscricaoController extends BaseController
{
    //Gerenciar inscrições e visualizar inscrições
    public function visualizarinscricoesAction()
    {   
        
        //pegar usuário logado
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        //instanciar form de inscrição
        $formPesquisa = new pesquisaInscricaoForm('fromInscricao', $this->getServiceLocator(), $usuario['empresa']);

        $dados = array();

        //se vier post é uma pesquisa
        $container = new Container();
        //unset($container->dados);
        if(!isset($container->dados)){
            $container->dados = array();
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['limpar'])){
                unset($container->dados);
                $this->redirect()->toRoute('visualizaInscricoes');
            }else{
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $container->dados = $formPesquisa->getData();
                }
                
            }
        }
        $formPesquisa->setData($container->dados);

        
        if(!empty($usuario['empresa'])){
            $container->dados['empresa'] = $usuario['empresa'];
            $this->layout('layout/empresa');
        }

        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');

        $inscricoes = $serviceInscricao->getInscricoesByParams($container->dados)->toArray();

        //paginação
        $paginator = new Paginator(new ArrayAdapter($inscricoes));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(40);
        $paginator->setPageRange(5);

        $params = $container->dados;
        unset($params['nome']);
        $clientes = $serviceInscricao->getInscricoesByParams($params)->toArray();

        return new ViewModel(array(
                    'formPesquisa'     => $formPesquisa,
                    'inscricoes'       => $paginator,
                    'empresa'          => $usuario['empresa'],
                    'clientes'         => $clientes
                ));
    }

    public function gerenciarinscricaoAction(){
        $idInscricao = $this->params()->fromRoute('id');

        //pesquisar inscricao
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
        }

        if($usuario['id_usuario_tipo'] == 2){
            $this->layout('layout/cliente');
        }

        $inscricao = $serviceInscricao->getInscricaoById($idInscricao, $usuario['empresa'], $usuario['cliente']);
        if(!$inscricao){
            $this->flashMessenger()->addWarningMessage('Inscrição não encontrada!');
            return $this->redirect()->toRoute('visualizaInscricoes');
        }


        //caso tenha passado o prazo do valor, atualizar inscrição para o proximo valor ativo
        if($inscricao->status_pagamento != 2){
            $valor = $this->getServiceLocator()->get('ValorEvento')->getValoresByEventoAndDate(
                $inscricao->evento,
                date('Y-m-d'),
                $inscricao->id_categoria
            )->current();

            if(($valor) && $inscricao->valor_bruto != $valor->valor_inscricao){
                $valorTotal = $valor->valor_inscricao;
                if($inscricao->valor_bruto != $inscricao->valor_total){
                    //calcular desconto
                    $promocao = $this->getServiceLocator()->get('PromocaoEvento')
                        ->getRecord($inscricao->codigo_desconto, 'codigo_promocional');
                    if($promocao){
                        $valorDesconto = ($valor->valor_inscricao*$promocao->desconto)/100;
                        $valorTotal = $valor->valor_inscricao - $valorDesconto;
                    }
                }
                $serviceInscricao->update(
                        array('valor_bruto' => $valor->valor_inscricao, 'valor_total' => $valorTotal),
                        array('id' => $inscricao->id)
                    );
                $inscricao = $serviceInscricao->getInscricaoById($idInscricao, $usuario['empresa'], $usuario['cliente']);
            }
        }


        //criar sessão, caso o usuário queira ir novamente para páfina da cielo
        $sessao = new Container();
        $sessao->offsetSet('inscricao', $inscricao->id);
        
        $evento = $this->getServiceLocator()->get('Evento')->getEventoById($inscricao->evento);

        $desconto = false;
        if(!empty($evento->codigo_desconto)){
            $desconto = $this->getServiceLocator()->get('PromocaoEvento')->getRecord($evento->codigo_desconto, 'codigo_promocional');
        }

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['pagar_pagseguro']) && empty($usuario['cliente'])){
                //mudar status de inscrição para pago
                $idStatus = $this->getServiceLocator()
                                ->get('StatusPagamento')
                                ->insert(array('inscricao' => $idInscricao, 'status' => 8));
                    
                    $res = $serviceInscricao->update(array(
                                                    'status_pagamento' => 8,
                                                    'inscricao_status' => 3
                                                ), 
                                                array('id' => $idInscricao)
                                            );

                    //enviar email para cliente
                    $mailer = $this->getServiceLocator()->get('mailer');
                    //if($res){
                        //Pago
                        $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($inscricao->cliente);
                        $mailer->mailUser(
                                $inscricao->email_cliente, 
                                $evento->nome, 
                                $this->mensagemEmail($evento->mensagem_pagamento, $cliente
                            ));

                        $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));
                    //}
            }
        }

        return new ViewModel(array(
                                'inscricao'     => $inscricao,
                                'evento'        => $evento,
                                'desconto'      => $desconto,
                                'tipoUsuario'   => $usuario['id_usuario_tipo'],
                                'cliente'       => $usuario['cliente']
                            ));
    }

    public function statuspagamentoAction(){
        $idInscricao = $this->params()->fromRoute('id');
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $inscricao = $serviceInscricao->getRecord($idInscricao);
        if(!$inscricao){
            $this->flashMessenger()->addWarningMessage('Inscrição não encontrada!');
            return $this->redirect()->toRoute('visualizaInscricoes');
        }

        $serviceInscricao->mudarStatusPagamento($inscricao);
        $this->flashMessenger()->addSuccessMessage('Status de pagamento alterado com sucesso!');
        return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));
    }

    public function inserircodigopromocionalAction(){
        $formPromocao = new formPromocao('frmPromocao');
        $idInscricao = $this->params()->fromRoute('inscricao');

        $inscricao = $this->getServiceLocator()->get('Inscricao')->getInscricaoById($idInscricao);
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 3){
            //verificar se inscrição é da mesma empresa 
            $this->layout('layout/empresa');
            if($inscricao['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Inscrição não encontrada!');
                return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));
            }
        }

        if($this->getRequest()->isPost()){
            $formPromocao->setData($this->getRequest()->getPost());
            if($formPromocao->isValid()){
                $dados = $formPromocao->getData();
                if($inscricao['codigo_desconto'] == $dados['codigo']){
                    $this->flashMessenger()->addWarningMessage('Este código já foi aplicado para esta inscrição!');
                    return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));    
                }
                if($this->getServiceLocator()->get('Inscricao')->concederDesconto($inscricao, $dados['codigo'])){
                    $this->flashMessenger()->addSuccessMessage('Código promocional inserido com sucesso!');
                }else{
                    $this->flashMessenger()->addErrorMessage('Não foi possível vincular o código promocional, por favor verifique seu código!');
                }
                return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));

               
            }
        }
        return new ViewModel(array('formPromocao' => $formPromocao, 'inscricao' => $inscricao));
    }

    public function pagarinscricaoAction(){
        $idInscricao = $this->params()->fromRoute('inscricao');

        $inscricao = $this->getServiceLocator()->get('Inscricao')->getInscricaoById($idInscricao);
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);
        $formPagar = new formPagar('frmPagar', $this->getServiceLocator(), $inscricao);        
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 3){
            //verificar se inscrição é da mesma empresa 
            $this->layout('layout/empresa');
            if($inscricao['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Inscrição não encontrada!');
                return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));
            }
        }

        if($this->getRequest()->isPost()){
            $formPagar->setData($this->getRequest()->getPost());
            if($formPagar->isValid()){
                $dados = $formPagar->getData();
                $dados['status_pagamento'] = 2;
                $dados['data_hora_pagamento'] = date('Y-m-d H:i:s');

                if(!empty($dados['categoria'])){
                    $valor = $this->getServiceLocator()->get('ValorEvento')->getRecord($dados['categoria']);
                    $dados['valor_bruto'] = $valor->valor_inscricao;
                    $dados['valor_total'] = $valor->valor_inscricao;
                }
                unset($dados['categoria']);

                //ValorEvento
                $this->getServiceLocator()->get('Inscricao')->update($dados, array('id' => $idInscricao));

                //enviar email
                $mailer = $this->getServiceLocator()->get('mailer');
                $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($inscricao->cliente);
                $mailer->mailUser(
                                $inscricao->email_cliente, 
                                $evento->nome, 
                                $this->mensagemEmail($evento->mensagem_pagamento, $cliente
                            ));
                $this->flashMessenger()->addSuccessMessage('Inscrição paga com sucesso!');
                return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));
            }
        }
        return new ViewModel(array('formPagar' => $formPagar, 'inscricao' => $inscricao));
    }

    public function realizarinscricaoAction(){
        $this->layout('layout/avaliacao');
        //Recuperar id do evento
        $siglaEvento = $this->params()->fromRoute('siglaEvento');
        $evento = $this->getServiceLocator()->get('Evento')->getRecordFromArray(array('sigla' => $siglaEvento, 'ativo' => 'S'));
        //passar evento para layout
        $this->layout()->evento = $evento;
        if(!$evento){
            $this->flashMessenger()->addErrorMessage('Não foi possível encontrar o evento, por favor contate o administrador!');
            return $this->redirect()->toRoute('login');
        }

        //pesquisar valores para a data corrente do evento
        $serviceValor = $this->serviceLocator->get('ValorEvento');
        $data = date('Y-m-d');
        $valores = $serviceValor->getValoresByEventoAndDate($evento->id, $data)->toArray();

        if(count($valores) < 1){
            echo '<script>alert("Inscrições não estão abertas para o evento, contate o administrador!");</script>';
            $this->redirect()->toUrl($evento->site_evento);
        }

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            return $this->redirect()->toRoute('meusDadosInscricao', 
                        array(
                            'tipoPessoa'        => 'F', 
                            'siglaEvento'       => $evento->sigla, 
                            'valorInscricao'    => $dados['valor_inscricao']
                        ));
        }
        
        return new ViewModel(array(
                        'valores'           => $valores,
                        'evento'            => $evento
                        ));
    }


    public function meusdadosAction(){
        $this->layout('layout/avaliacao');
        $tipoPessoa = $this->params()->fromRoute('tipoPessoa');
        $valorInscricaoParam = $this->params()->fromRoute('valorInscricao');
        //PESQUISAR EVENTO
        $siglaEvento = $this->params()->fromRoute('siglaEvento');
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($siglaEvento, 'sigla');
        //passar evento para layout
        $this->layout()->evento = $evento;
        if(!$evento){
            $this->flashMessenger()->addErrorMessage('Não foi possível encontrar o evento, por favor contate o administrador!');
            return $this->redirect()->toRoute('login');
        }

        //pesquisar campos que podem ser inseridos
        $serviceInscricaoCampos = $this->getServiceLocator()->get('InscricaoCamposEvento');
        $campos = $serviceInscricaoCampos->getCamposByEvento($evento->id)->toArray();
        $campos = $this->prepararCampos($campos);

        //Pessoa física
        if($tipoPessoa == 'F'){
            $formPessoa = new pessoaFisicaForm('pessoaFisicaForm', $this->getServiceLocator(), $campos);
        }else{
            $formPessoa = new pessoaJuridicaForm('pessoaJuridicaForm', $this->getServiceLocator(), $campos);
        }

        //Inicializar variavel de email
        $corpoEmail = '';

        if(!$tipoPessoa){
            $tipoPessoa = 'F';
        }

        $reCaptchaValid = true;
        $credenciais = Parametros::getCredenciaisReCaptcha();
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $dados['valor_inscricao'] = $valorInscricaoParam;
            //salvar dados
            $formPessoa->setData($dados);
            if($formPessoa->isValid()){
                if($_POST['g-recaptcha-response']){
                    $resposta = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$credenciais['private'].'&response='.$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
                    $resposta = json_decode($resposta);
                    if(!$resposta->success){
                        die('Ocorreu algum problema, por favor tente novamente!');
                    }
                    $valorInscricao = $this->getServiceLocator()->get('ValorEvento')
                                            ->getValoresByEvento($evento->id, $dados['valor_inscricao'])
                                            ->current();
                    if(!$valorInscricao){
                        $this->flashMessenger()->addWarningMessage('Categoria de inscrição não encontrada!');
                        return $this->redirect()->toRoute('realizarInscricao', array('siglaEvento' => $evento->sigla));
                    }
                    //pesquisar se existe CPF para a categoria selecionada
                    $serviceSocio = $this->getServiceLocator()->get('Socio');
                    $categorias = $serviceSocio->getRecords($valorInscricao['evento_cliente_categoria'], 'categoria');
                    if($categorias->count() > 1){
                        //se existir validar se cpf esta na lista
                        $cpf = $dados['cpf'];
                        $cpf = str_replace('.', '', $cpf);
                        $cpf = str_replace('-', '', $cpf);
                        $socio = $serviceSocio->getRecordFromArray(array('cpf' => $cpf, 'categoria' => $valorInscricao['evento_cliente_categoria']));
                        if(!$socio){
                            $this->flashMessenger()->addErrorMessage('Seu CPF não está na lista de associados, por favor contate o administrador ou selecione outra categoria!');
                            return $this->redirect()->toRoute('meusDadosInscricao', array('tipoPessoa' => $tipoPessoa, 'siglaEvento' => $siglaEvento));
                        }
                    }

                    //procurar por CPF
                    $serviceCliente = $this->getServiceLocator()->get('Cliente');
                    $cliente = $serviceCliente->getRecord($dados['cpf'], 'cpf');
                    $serviceCliente = $this->getServiceLocator()->get('Cliente');
                    
                    //VERIFICAR SE CATEGORIA É APENAS DE ASSOCIADO
                    if($valorInscricao['apenas_associados'] == 'S'){
                      $associado = $this->getServiceLocator()->get('Associado')->getAssociadoByCpf($dados['cpf']);
                      
                      if($associado == false || $associado['ativo'] == 'N'){
                        $paramsGlobal = new Parametros();
                        $this->flashMessenger()->addErrorMessage($paramsGlobal->getMensagemInscricaoAssociado());
                            return $this->redirect()->toRoute('meusDadosInscricao', array('tipoPessoa' => $tipoPessoa, 'siglaEvento' => $siglaEvento));
                      }
                    }
                    

                    $novoCliente = false;
                    if($cliente){
                        //se tem cpf na base update
                        $result = $serviceCliente->update($formPessoa->getData(), array('id' => $cliente->id));
                    }else{
                        //pesquisar se existe usuário
                        $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($dados['cpf'], 'login');
                        if($usuario){
                          $this->flashMessenger()->addErrorMessage('Já existe um usuário cadastrado para o cpf '.$dados['cpf'].', por favor entre em contato com o administrador!');
                            return $this->redirect()->toRoute('meusDadosInscricao', array('tipoPessoa' => $tipoPessoa, 'siglaEvento' => $siglaEvento));
                        }
                        //se não tem cpf insert
                        $novoCliente = true;
                        $result = $serviceCliente->insert($formPessoa->getData());
                        $cliente = $serviceCliente->getRecord($result);

                        //criar um usuário na base para este cliente
                        $cpf = $dados['cpf'];
                        $dadosUsuario = array(
                                            'nome'              => $dados['nome_completo'],
                                            'login'             => $cpf,
                                            'id_usuario_tipo'   => 2,
                                            'cliente'           => $cliente->id,
                                            'ativo'             => 'S'
                                        );

                        $bcrypt = new Bcrypt();
                        $dadosUsuario['senha'] = 'N';
                        $resultUsuario = $this->getServiceLocator()->get('Usuario')->insert($dadosUsuario);
                    }

                    //se existir inscrição deste cliente no evento não deixar se inscrever nevamente
                    $inscricao = $this->existeInscricao($evento->id, $cliente->id); 
                    if($inscricao){
                        if($inscricao->inscricao_status == 3){
                            $this->flashMessenger()->addWarningMessage('Já existe uma inscrição para este CPF, para alterar sua inscrição 
                                realize login com os dados enviados por email!');
                            return $this->redirect()->toRoute('login');
                        }else{
                            $this->getServiceLocator()->get('EventoOpcaoAlternativaInscricao')->delete(array('inscricao' => $inscricao->id));
                            $this->getServiceLocator()->get('Inscricao')->delete(array('evento' => $evento->id, 'cliente' => $cliente->id));
                        }
                        $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($cliente->id, 'cliente');
                        if($usuario['senha'] == 'N'){
                            //cadastrar senha
                            $novoCliente = true;
                        }
                    }
                
                    if($cliente){
                        //Validações e alterações no banco no model de inscrição (não da para usar transaction na action)
                        $inscricao = $this->getServiceLocator()->get('Inscricao');
                        $clienteCategoria = $this->getServiceLocator()->get('QuantidadeCategoria')->getRecordFromArray(array('evento' => $valorInscricao->evento, 'cliente_categoria' => $valorInscricao->evento_cliente_categoria));

                        //pesquisar promocao associado
                        $promocaoAssociado = $this->getServiceLocator()->get('Associado')->getpromocao($clienteCategoria->id, $dados['cpf']);
                        $retorno = $inscricao->realizarInscricao($dados, $cliente, $valorInscricao, $clienteCategoria, $promocaoAssociado);
                        $idInscricao = $retorno['id'];
                        if($idInscricao){
                            if(isset($dados['email']) && !empty($dados['email'])){
                                //enviar email para usuário
                                $mailer = $this->getServiceLocator()->get('mailer');
                                if($novoCliente){
                                    $mailer->mailUser($dados['email'], $evento->nome, $this->mensagemEmail($evento->mensagem_cadastro_novo, $cliente));

                                }else{
                                    $mailer->mailUser($dados['email'], $evento->nome, $this->mensagemEmail($evento->mensagem_cadastro_antigo, $cliente));
                                }
                            }

                            //guardar dados na sessão para utilizar na interface de atividades
                            $sessao = new Container();
                            $sessao->offsetSet('inscricao', $idInscricao);
                            if($novoCliente){
                                //cadastrar senha
                                return $this->redirect()->toRoute('cadastrarSenhaInscrito');
                            }else{
                                return $this->redirect()->toRoute('atividades');
                            }
                        }else{
                            $this->flashMessenger()->addWarningMessage('Ocorreu algum erro ao realizar a inscrição, por favor verifique seus dados!');
                            return $this->redirect()->toRoute('meusDadosInscricao', array('tipoPessoa' => $tipoPessoa, 'siglaEvento' => $siglaEvento));
                        }
                        

                    }else{
                        //CASO NÃO INSERIU NEM ATUALIZOU CLIENTE
                         $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao inserir/alterar participante!');
                         return $this->redirect()->toRoute('meusDadosInscricao', array('tipoPessoa' => $tipoPessoa, 'siglaEvento' => $siglaEvento));
                    }
                }else{
                    $reCaptchaValid = false;
                }
            }
        }

        return new ViewModel(array(
                'valor_inscricao' => $valorInscricaoParam,
                'tipoPessoa'      => $tipoPessoa,
                'formPessoa'      => $formPessoa,
                'evento'          => $evento,
                'reCaptchaValid'  => $reCaptchaValid,
                'credenciais'     => $credenciais
            ));
    }

    public function cadastrarsenhaAction(){
        $this->layout('layout/avaliacao');
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');

        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($idInscricao);
        $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($inscricao->cliente);
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);
        $this->layout()->evento = $evento;

        $formSenha = new formSenha('frmSenha');
        $formSenha->setData(array('login' => $cliente->cpf));

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formSenha->setData($dados);
            if($formSenha->isValid()){
                $bcrypt = new Bcrypt();
                $senha = $bcrypt->create($dados['senha']);
                if($this->getServiceLocator()->get('Usuario')->update(array('senha' => $senha), array('cliente' => $cliente->id))){
                    $this->flashMessenger()->addSuccessMessage('Senha cadastrada com sucesso!');
                    return $this->redirect()->toRoute('atividades');
                }
            }
        }

        return new ViewModel(array('formSenha' => $formSenha));
    }

    public function atividadesAction(){
        $this->layout('layout/avaliacao');

        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');

        if(!$idInscricao){
            $this->flashMessenger()->addWarningMessage('Sua sessão expirou, favor logar-se com os dados enviados por email!');
            return $this->redirect()->toRoute('login');
        }
        //pesquisar inscricao
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($idInscricao);
        $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($inscricao->cliente);
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);
        $this->layout()->evento =  $evento;

        //pesquisar opções do evento
        $opcoes = $this->getServiceLocator()
                    ->get('EventoOpcaoAlternativa')
                    ->getQuestoesAlternativas($inscricao->evento);

        
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $serviceAlternativaInscricao = $this->getServiceLocator()->get('EventoOpcaoAlternativaInscricao');

            //salvar dados
            if($serviceAlternativaInscricao->inserirAtividades($inscricao->id, $dados->toArray())){
                //$this->flashMessenger()->addSuccessMessage('Atividades selecionadas com sucesso!');
                if(isset($dados['cielo'])){
                    $this->getServiceLocator()->get('Inscricao')->update(array( 
                            'inscricao_status' => 3,
                            ), 
                        array('id' => $idInscricao));
                    return $this->redirect()->toRoute('cielo');
                }else{
                    if(isset($dados['confirmar'])){
                        //voucher de 100%
                        $this->getServiceLocator()->get('Inscricao')->update(array(
                            'status_pagamento' => 2, 
                            'inscricao_status' => 3,
                            'forma_pagamento'  => 7
                            ), 
                        array('id' => $idInscricao));
                        return $this->redirect()->toRoute('sucesso');
                    }else{
                        if(isset($dados['transferencia'])){
                            $this->getServiceLocator()->get('Inscricao')->update(array(
                            'inscricao_status' => 3,
                            'forma_pagamento'  => 8
                            ), 
                            array('id' => $idInscricao));

                            $mailer = $this->getServiceLocator()->get('mailer');
                            $mailer->mailUser(
                                $cliente['email'], 
                                $evento['nome'],
                                $this->mensagemEmail($evento->mensagem_transferencia, $cliente)
                            );
                    
                                return $this->redirect()->toRoute('sucesso');
                        }else{
                            if(isset($dados['cartao'])){
                                $this->getServiceLocator()->get('Inscricao')->update(array( 
                                    'inscricao_status' => 3,
                                    ), 
                                array('id' => $idInscricao));
                                return $this->redirect()->toRoute('realizarPagamentoInscricaoIpag');  
                            }else{
                                if (isset($dados['pix'])) {
                                    $this->getServiceLocator()->get('Inscricao')->update(array( 
                                        'inscricao_status' => 3,
                                        ), 
                                    array('id' => $idInscricao));
                                    return $this->redirect()->toRoute('realizarPagamentoInscricaoIpagPix'); 
                                }
                                //REDIRECIONAR PARA PAYPAL
                                $this->getServiceLocator()->get('Inscricao')->update(array( 
                                    'inscricao_status' => 3,
                                    ), 
                                array('id' => $idInscricao));
                                return $this->redirect()->toRoute('paypal');    
                            }
                            
                        }
                    }
                }
            }else{
                $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao salvar as atividades, por favor tente novamente!');
                return $this->redirect()->toRoute('atividades');
            }
        }
        
        return new ViewModel(array(
                'opcoes'    =>  $opcoes,
                'evento'    =>  $evento,
                'inscricao' =>  $inscricao
            ));
    }

    public function sucessoAction(){
        $this->layout('layout/avaliacao');
        $dados = $this->getRequest()->getPost();
        
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');

        if(!$idInscricao){
            $this->flashMessenger()->addWarningMessage('Sua sessão expirou, favor logar-se com os dados enviados por email!');
            return $this->redirect()->toRoute('login');
        }
        //pesquisar inscricao
        $inscricao = $serviceInscricao->getInscricaoById($idInscricao);

        //pesquisar evento
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);

        $this->layout()->evento = $evento;
        return new ViewModel(array('inscricao' => $inscricao, 'evento' => $evento));
    }

    public function inscricaomembrotimeAction(){
        $this->layout('layout/avaliacao');

        //Recuperar codigo do lider
        $codigoLider = $this->params()->fromRoute('codigolider');
        
        //pesquisar inscrição do líder
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $inscricaoLider = $serviceInscricao->getRecord($codigoLider, 'codigo_lider');
        
        if(!$inscricaoLider){
            $this->flashMessenger()->addWarningMessage('Não foi possível encontrar um líder, por favor verifique o link!');
            return $this->redirect()->toRoute('login');
        }
        if($inscricaoLider->quantidade_membros <= $inscricaoLider->quantidade_utilizada){
            $this->flashMessenger()->addWarningMessage('Não existem mais vagas disponíveis para este link!');
            return $this->redirect()->toRoute('login');
        }

        //VERIFICAR SE LIDER JÁ PAGOU A INSCRIÇÃO
        if($inscricaoLider->numero_pagamento_boleto == 'N' && $inscricaoLider->checkout_cielo_order_number == 'N'){
            $this->flashMessenger()->addWarningMessage('Aguardando confirmação de pagamento para inscrição!');
            return $this->redirect()->toRoute('login');
        }

        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricaoLider->evento);
        $this->layout()->evento = $evento;
        //instanciar form de pessoa física
        $serviceInscricaoCampos = $this->getServiceLocator()->get('InscricaoCamposEvento');
        $campos = $serviceInscricaoCampos->getCamposByEvento($inscricaoLider->evento)->toArray();
        $campos = $this->prepararCampos($campos);

        //Pessoa física
        $formPessoa = new pessoaFisicaForm('pessoaFisicaForm', $this->getServiceLocator(), $campos);
        
        //remover campo de código promocional
        $formPessoa->remove('codigo');
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formPessoa->setData($dados);
            if($formPessoa->isValid()){
                //pesquisar se já existe cliente na base
                $serviceCliente = $this->getServiceLocator()->get('Cliente');
                $cliente = $serviceCliente->getRecord($dados['cpf'], 'cpf');
                $serviceCliente = $this->getServiceLocator()->get('Cliente');
                
                $novoCliente = false;
                if($cliente){
                    //se tem cpf na base update
                    $result = $serviceCliente->update($formPessoa->getData(), array('id' => $cliente->id));
                }else{
                    $novoCliente = true;
                    //se não tem cpf insert
                    $result = $serviceCliente->insert($formPessoa->getData());
                    $cliente = $serviceCliente->getRecord($result);

                    //criar um usuário na base para este cliente
                    $cpf = $this->eliminaMascaras($dados['cpf']);
                    $dadosUsuario = array(
                                        'nome'              => $dados['nome_completo'],
                                        'login'             => $cpf,
                                        'id_usuario_tipo'   => 2,
                                        'cliente'           => $cliente->id,
                                        'ativo'             => 'S'
                                    );

                    $bcrypt = new Bcrypt();
                    $dadosUsuario['senha'] = $bcrypt->create($cpf);
                    $resultUsuario = $this->getServiceLocator()->get('Usuario')->insert($dadosUsuario);
                }

                //se existir inscrição deste cliente no evento não deixar se inscrever nevamente
                if($this->existeInscricao($evento->id, $cliente->id)){
                    $this->flashMessenger()->addWarningMessage('Já existe uma inscrição para este CPF, para alterar sua inscrição 
                            realize login com os dados enviados por email!');
                    return $this->redirect()->toRoute('login');
                }

                //inserir inscrição
                if($cliente){
                    $dadosInscricao = array(
                            'cliente'               => $cliente->id,
                            'evento'                => $evento->id,
                            'inscricao_status'      => 3,
                            'como_ficou_sabendo'    => $dados->como_ficou_sabendo,
                            'id_inscricao_lider'    => $inscricaoLider->id,
                            'cliente_categoria'     => $inscricaoLider->cliente_categoria
                        );

                    $quantidadeMatriculas = $inscricaoLider->quantidade_utilizada + 1;
                    if($serviceInscricao->update(array('quantidade_utilizada' => $quantidadeMatriculas), array('id' => $inscricaoLider->id))){
                        if($serviceInscricao->insert($dadosInscricao)){
                            if(isset($dados['email']) && !empty($dados['email'])){
                                //enviar email para usuário
                                $mailer = $this->getServiceLocator()->get('mailer');
                                if($novoCliente){
                                    if(!$mailer->mailUser($dados['email'], $evento->nome, $this->mensagemEmail($evento->mensagem_cadastro_novo, $cliente))){
                                    $this->flashMessenger()->addWarningMessage('Erro ao enviar email com dados de acesso, por favor contate o administrador!');
                                }
                                }else{
                                    if(!$mailer->mailUser($dados['email'], $evento->nome, $this->mensagemEmail($evento->mensagem_cadastro_antigo, $cliente))){
                                        $this->flashMessenger()->addWarningMessage('Erro ao enviar email com dados de acesso, por favor contate o administrador!');
                                    }
                                    
                                }
                            }
                            $this->flashMessenger()->addSuccessMessage('Inscrição de membro realizada com sucesso!');
                            return $this->redirect()->toRoute('sucesso');
                        }
                    }else{
                        $this->addErrorMessage('Erro ao realizar inscrição, por favor contate o administrator!');
                        return $this->redirect()->toRoute('inscricaoMembroTime', array('codigolider' => $codigoLider));
                    }
                }
            }
        }

        return new ViewModel(array(
                'formPessoa' => $formPessoa,
                'evento'     => $evento
            ));
    }

    public function deletarinscricaoAction(){

        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($this->params()->fromRoute('id'));
        if($this->getServiceLocator()->get('Inscricao')->deletar($inscricao)){
            $this->flashMessenger()->addSuccessMessage('Inscrição excluída com sucesso!');
            return $this->redirect()->toRoute('visualizaInscricoes');
        }else{
            $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao excluir inscrição, por favor tente novamente!');
            return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $inscricao['id']));
        }

    }

    private function existeInscricao($idEvento, $idCliente){
        $inscricao = $this->getServiceLocator()->get('Inscricao')
                                ->getRecordFromArray(array('evento' => $idEvento, 'cliente' => $idCliente));

        if($inscricao && $inscricao->count() > 0){
            return $inscricao;
        }

        return false;
    }

    public function mudarcategoriaAction(){
        $idInscricao = $this->params()->fromRoute('inscricao');
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $inscricao = $serviceInscricao->getInscricaoById($idInscricao);

        $usuario = $this->getServiceLocator()->get('session')->read();

        if($usuario['id_usuario_tipo'] == 3){
            //verificar se inscrição é da mesma empresa 
            $this->layout('layout/empresa');
            if($inscricao['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Inscrição não encontrada!');
                return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));
            }
        }
        
        //popular form
        $formCategoria = new formMudarCategoria('formCategoria', $this->getServiceLocator(), $inscricao['evento']);

        $formCategoria->setData(array('cliente_categoria' => $inscricao['cliente_categoria']));

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(!empty($dados['cliente_categoria'])){
                
                $valor = $this->getServiceLocator()->get('ValorEvento')->getValoresByEventoAndDate(
                    $inscricao->evento,
                    date('Y-m-d'),
                    $dados['cliente_categoria']
                )->current();

                $valorTotal = $valor->valor_inscricao;
                if($inscricao->valor_bruto != $inscricao->valor_total){
                    //calcular desconto
                    $promocao = $this->getServiceLocator()->get('PromocaoEvento')->getRecord($inscricao->codigo_desconto, 'codigo_promocional');
                    if($promocao){
                        $valorDesconto = ($valor->valor_inscricao*$promocao->desconto)/100;
                        $valorTotal = $valor->valor_inscricao - $valorDesconto;
                    }
                }
                
                $serviceInscricao->update(array(
                    'cliente_categoria' => $valor['id_evento_categoria'], 
                    'valor_bruto'       => $valor['valor_inscricao'],
                    'valor_total'       => $valorTotal
                    ), array('id' => $idInscricao));
                $this->flashMessenger()->addSuccessMessage('Categoria alterada com sucesso!');
                return $this->redirect()->toRoute('gerenciarInscricao', array('id' => $idInscricao));
            }
        }
        return new ViewModel(array(
                'formCategoria'     =>  $formCategoria,
                'inscricao'         =>  $inscricao
            ));
    }

}

