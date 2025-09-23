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

class Inscricao2Controller extends BaseController
{
    public function realizarinscricaoAction(){
        $this->layout('layout/avaliacao');

        //Recuperar id do evento
        $siglaEvento = $this->params()->fromRoute('siglaEvento');
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($siglaEvento, 'sigla');
        //passar evento para layout
        $this->layout()->evento = $evento;
        if(!$evento){
            $this->flashMessenger()->addErrorMessage('Não foi possível encontrar o evento, por favor contate o administrador!');
            return $this->redirect()->toRoute('login');
        }

        //tipo de pessoa
        $tipoPessoa = $this->params()->fromRoute('tipoPessoa');

        
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

        //Caso seja um POST
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formPessoa->setData($dados);
            if($formPessoa->isValid()){
                $dados['valor_inscricao'] = 39;
                //BUSCAR VALOR DA INSCRIÇÃO PELO EVENTO ID(se não vier nada o id foi alterado)
                $valorInscricao = $this->getServiceLocator()->get('ValorEvento')
                                        ->getValoresByEvento($evento->id, $dados['valor_inscricao'])
                                        ->current();
                
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
                        return $this->redirect()->toRoute('realizarInscricao2', array('tipoPessoa' => $tipoPessoa, 'siglaEvento' => $siglaEvento));
                    }
                }

                //procurar por CPF
                $serviceCliente = $this->getServiceLocator()->get('Cliente');
                $cliente = $serviceCliente->getRecord($dados['cpf'], 'cpf');
                $serviceCliente = $this->getServiceLocator()->get('Cliente');
                
                if($cliente){
                    //se tem cpf na base update
                    $result = $serviceCliente->update($formPessoa->getData(), array('id' => $cliente->id));
                }else{
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
                    if($resultUsuario){
                        $corpoEmail = 'Seus dados de acesso ao sistema são:<br>
                                        Login: '.$cpf.'<br>
                                        Senha: '.$cpf.'<br>';
                    }else{
                        $corpoEmail = 'Ocorreu um erro ao criar dados de acesso, por favor contate o administrador!<br>';
                    }

                }

                //se existir inscrição deste cliente no evento não deixar se inscrever nevamente
                if($this->existeInscricao($evento->id, $cliente->id)){
                    $this->flashMessenger()->addWarningMessage('Já existe uma inscrição para este CPF, para alterar sua inscrição 
                            realize login com os dados enviados por email!');
                    return $this->redirect()->toRoute('login');
                }
                
                if($cliente){
                    //Validações e alterações no banco no model de inscrição (não da para usar transaction na action)
                    $inscricao = $this->getServiceLocator()->get('Inscricao');
                    $retorno = $inscricao->realizarInscricao($dados, $cliente, $valorInscricao);
                    $idInscricao = $retorno['id'];
                    if($idInscricao){
                        if(isset($dados['email']) && !empty($dados['email'])){
                            //gerar email de inscrição
                            $corpoEmail .= 'Seu cadastro no evento '.$evento->sigla.' - '.$evento->nome.' foi recebido, para efetivar a inscrição efetue o pagamento!<br>';
                            if(isset($retorno['codigo_lider'])){
                                $corpoEmail .= '<br><a href="http://eventostime.com.br/evento/inscricoes/time/'.$retorno['codigo_lider'].'">
                                                        Link</a> 
                                                    para inscrição dos membros de seu time: 
                                                    http://eventostime.com.br/evento/inscricoes/time/'.$retorno['codigo_lider'].'<br>';
                            }
                            $corpoEmail .= $evento->site_evento;
                            $corpoEmail .= '<br>Para mais detalhes acesse seu <a href="http://eventostime.com.br/">painel</a>
                                             administrativo: http://eventostime.com.br/';

                            //enviar email para usuário
                            $mailer = $this->getServiceLocator()->get('mailer');
                            $mailer->mailUser($dados['email'], $evento->nome, $corpoEmail);
                        }

                        //guardar dados na sessão para utilizar na interface de atividades
                        $sessao = new Container();
                        $sessao->offsetSet('inscricao', $idInscricao);
                        
                        return $this->redirect()->toRoute('atividades2');
                    }else{
                        $this->flashMessenger()->addWarningMessage('Ocorreu algum erro ao realizar a inscrição, por favor verifique seus dados!');
                        return $this->redirect()->toRoute('realizarInscricao2', array('tipoPessoa' => $tipoPessoa, 'siglaEvento' => $siglaEvento));
                    }
                    

                }else{
                    //CASO NÃO INSERIU NEM ATUALIZOU CLIENTE
                     $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao inserir/alterar participante!');
                     return $this->redirect()->toRoute('realizarInscricao2', array('tipoPessoa' => $tipoPessoa, 'siglaEvento' => $siglaEvento));
                }
            }
        }

        //pesquisar valores para a data corrente do evento
        $serviceValor = $this->serviceLocator->get('ValorEvento');
        $data = date('Y-m-d');
        $valores = $serviceValor->getValoresByEventoAndDate($evento->id, $data)->toArray();
        if(count($valores) < 1){
            echo '<script>alert("Inscrições não estão abertas para o evento, contate o administrador!");</script>';
            $this->redirect()->toUrl($evento->site_evento);
        }
        return new ViewModel(array(
                        'formPessoa'        => $formPessoa,
                        'tipoPessoa'        => $tipoPessoa,
                        'valores'           => $valores,
                        'evento'            => $evento

                        ));
    }

    public function atividadesAction(){
        $this->layout('layout/avaliacao');

        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');

        //pesquisar inscricao
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($idInscricao);
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
            if($serviceAlternativaInscricao->inserirAtividades($inscricao->id, $dados)){
                $this->flashMessenger()->addSuccessMessage('Atividades selecionadas com sucesso!');
                return $this->redirect()->toRoute('pagamento2');
            }else{
                $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao salvar as atividades, por favor tente novamente!');
                return $this->redirect()->toRoute('atividades2');
            }
            

        }
        return new ViewModel(array(
                'opcoes'    =>  $opcoes,
                'evento'    =>  $evento
            ));
    }

    public function pagamentoAction(){
        $this->layout('layout/avaliacao');
        
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        
        //pesquisar inscricao
        $inscricao = $serviceInscricao->getInscricaoById($idInscricao);

        $this->layout()->evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            //alterar valor_bruto de inscrição
            $idValor = $this->definirValor($dados['code']);
            $valorEvento = $this->getServiceLocator()->get('ValorEvento')->getRecord($idValor); 
            $serviceInscricao->update(array('valor_bruto' => $valorEvento->valor_inscricao), array('id' => $idInscricao));
            $url = 'https://pagseguro.uol.com.br/checkout/v2/payment.html?code='.$dados['code'];
            return $this->redirect()->toUrl($url);
        }
         
        return new ViewModel(array(
                'inscricao'     =>  $inscricao
            ));
    }

    private function definirValor($code){
        switch ($code) {
            case 'AF2DC2DCDEDEF5C8848EEFA15D56F3F1':
                return 39;
                break;
            case '517992576161602114041F8BBB6DC4C4':
                return 40;
                break;
            case 'FE809D498484B1D2240CFF8F91AD4916':
                return 41;
                break;
            case '47D235CE5757FFADD4398F91FF5DA9B9':
                return 42;
                break;
            case 'CBC68BE2EAEA63BAA4590F8526F396E9':
                return 45;
                break;
            case '6FBBF0EECACAF0755439FF8481FF60C6':
                return 44;
                break;
        }
    }

    public function sucessoAction(){
        $this->layout('layout/avaliacao');
        
        $dados = $this->getRequest()->getPost();
        
        $sessao = new Container();
        $idInscricao = $sessao->offsetGet('inscricao');
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');

        if(!$idInscricao){
            $this->flashMessenger()->addWarningMessage('Sua sessão expirou!');
            return $this->redirect()->toRoute('login');
        }
        //pesquisar inscricao
        $inscricao = $serviceInscricao->getInscricaoById($idInscricao);

        //pesquisar evento
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($inscricao->evento);
        $this->layout()->evento = $evento;
        return new ViewModel(array('inscricao' => $inscricao, 'evento' => $evento));
    }

    private function existeInscricao($idEvento, $idCliente){
        $inscricao = $this->getServiceLocator()->get('Inscricao')
                                ->getRecordFromArray(array('evento' => $idEvento, 'cliente' => $idCliente));

        if($inscricao && $inscricao->count() > 0){
            return true;
        }

        return false;
    }

}

