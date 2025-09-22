<?php

namespace Associados\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Associados\Form\PesquisarQuestionario as formPesquisa;
use Associados\Form\Questionario as formQuestionario;
use Associados\Form\Questao as formQuestao;
use Associados\Form\QuestaoAlternativa as formAlternativa;
use Associados\Form\ResponderQuestionario as formResponder;
use Associados\Form\PesquisarRespostas as formRespostas;

class QuestionarioController extends BaseController
{

    public function indexAction()
    {   
        $formPesquisa = new formPesquisa('frmPesquisa', $this->getServiceLocator());
        $container = new Container();
        if(!isset($container->dados)){
            $container->dados = array();
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['limpar'])){
                $container->dados = array();
                $this->redirect()->toRoute('listaQuestionarioAssociado');
            }else{
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $container->dados = $formPesquisa->getData();
                }
                
            }
        }
        $formPesquisa->setData($container->dados);
        $questionarios = $this->getServiceLocator()->get('Questionario')->getQuestionarios($container->dados);
        
        //paginação
        $paginator = new Paginator(new ArrayAdapter($questionarios->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'formPesquisa'      => $formPesquisa,
                    'questionarios'     => $paginator
                ));
    }

    public function novoAction()
    { 
        $formQuestionario = new formQuestionario('frmQuestionario', $this->getServiceLocator());

        if($this->getRequest()->isPost()){
            $formQuestionario->setData($this->getRequest()->getPost());
            if($formQuestionario->isValid()){
                $dados = $formQuestionario->getData();
                $dados['descricao'] = htmlspecialchars($dados['descricao']);

                $idQuestionario = $this->getServiceLocator()->get('Questionario')->insert($dados);
                $this->flashMessenger()->addSuccessMessage('Questionário salvo com sucesso!');
                return $this->redirect()->toRoute('alterarQuestionarioAssociado', array('idQuestionario' => $idQuestionario));
            }
        }

        return new ViewModel(array('formQuestionario' => $formQuestionario));
    }

    public function alterarAction(){
        $serviceQuestionario = $this->getServiceLocator()->get('Questionario');
        $formQuestionario = new formQuestionario('frmQuestionario',  $this->getServiceLocator());

        $questionario = $serviceQuestionario->getRecord($this->params()->fromRoute('idQuestionario'));
        if(!$questionario){
            $this->flashMessenger()->addWarningMessage('Questionário não encontrado!');
            return $this->redirect()->toRoute('listaQuestionarioAssociado');
        }
        $formQuestionario->setData($questionario);

        if($this->getRequest()->isPost()){
            $formQuestionario->setData($this->getRequest()->getPost());
            if($formQuestionario->isValid()){
                $dados = $formQuestionario->getData();
                $dados['descricao'] = htmlspecialchars($dados['descricao']);
                $serviceQuestionario->update($dados, array('id' => $questionario->id));
                $this->flashMessenger()->addSuccessMessage('Questionário alterado com sucesso!');
                return $this->redirect()->toRoute('alterarQuestionarioAssociado', array('idQuestionario' => $questionario->id));
            }
        }


        //pesquisar questões e alternativas
        $questoes = $serviceQuestionario->getQuestoes($questionario->id);


        return new ViewModel(array(
            'formQuestionario'  =>  $formQuestionario,
            'questionario'      =>  $questionario,
            'questoes'          =>  $questoes
        ));
    }

    public function questaoAction(){
        $idQuestionario = $this->params()->fromRoute('idQuestionario');
        $formQuestao = new formQuestao('frmQuestao');

        $serviceQuestao = $this->getServiceLocator()->get('QuestionarioQuestao');

        $idQuestao = $this->params()->fromRoute('idQuestao');
        $questao = $serviceQuestao->getRecord($idQuestao);
        
        if($questao){
            $formQuestao->setData($questao);
        }else{
            $idQuestao = 0;
        }

        if($this->getRequest()->isPost()){
            $formQuestao->setData($this->getRequest()->getPost());
            if($formQuestao->isValid()){
                $dados = $formQuestao->getData();
                $dados['questionario'] = $idQuestionario;
            
                if($questao){
                    //update
                    $serviceQuestao->update($dados, array('id' => $questao->id));
                    $this->flashMessenger()->addSuccessMessage('Questão alterada com sucesso!');
                }else{
                    //insert
                    $serviceQuestao->insert($dados);
                    $this->flashMessenger()->addSuccessMessage('Questão inserida com sucesso!');
                }
                return $this->redirect()->toRoute('alterarQuestionarioAssociado', array('idQuestionario' => $idQuestionario));
            }
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'formQuestao'       =>  $formQuestao,
            'idQuestionario'    =>  $idQuestionario,
            'idQuestao'         =>  $idQuestao
        ));
        return $view;
    }

    public function alternativaAction(){
        $idQuestionario = $this->params()->fromRoute('idQuestionario');
        $idQuestao = $this->params()->fromRoute('idQuestao');
        $formAlternativa = new formAlternativa('frmAlternativa');

        $serviceAlternativa = $this->getServiceLocator()->get('QuestionarioQuestaoAlternativa');

        $idAlternativa = $this->params()->fromRoute('idAlternativa');
        $alternativa = $serviceAlternativa->getRecord($idAlternativa);

        if($alternativa){
            $formAlternativa->setData($alternativa);
        }else{
            $idAlternativa = 0;
        }

        if($this->getRequest()->isPost()){
            $formAlternativa->setData($this->getRequest()->getPost());
            if($formAlternativa->isValid()){
                $dados = $formAlternativa->getData();
                $dados['questao'] = $idQuestao;
            
                if($alternativa){
                    //update
                    $serviceAlternativa->update($dados, array('id' => $alternativa->id));
                    $this->flashMessenger()->addSuccessMessage('Alternativa alterada com sucesso!');
                }else{
                    //insert
                    $serviceAlternativa->insert($dados);
                    $this->flashMessenger()->addSuccessMessage('Alternativa inserida com sucesso!');
                }
                return $this->redirect()->toRoute('alterarQuestionarioAssociado', array('idQuestionario' => $idQuestionario));
            }
        }

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'formAlternativa'   =>  $formAlternativa,
            'idQuestionario'    =>  $idQuestionario,
            'idQuestao'         =>  $idQuestao,
            'idAlternativa'     =>  $idAlternativa
        ));
        return $view;
    }

    public function responderquestionarioAction(){
        $idQuestionario = $this->params()->fromRoute('idQuestionario');
        $serviceQuestionario = $this->getServiceLocator()->get('Questionario');
        $questionario = $serviceQuestionario->getQuestoes($idQuestionario);
        $formQuestionario = new formResponder('frmResponder', $questionario);

        $usuario = $this->getServiceLocator()->get('session')->read();
        $associado = $this->getServiceLocator()->get('Associado')->getAssociadoByUser($usuario['id']);

        //verificar se existe associado
        if(!$associado){
            $this->flashMessenger()->addWarningMessage('Associado não encontrado!');
            return $this->redirect()->toRoute('inscricoesCliente');
        }

        //verificar se já foi respondido
        $questionarioAberto = $serviceQuestionario->getAvaliacaoAberta($associado);
        if(!$questionarioAberto){
            $this->flashMessenger()->addWarningMessage('Questionário já respondido!');
            return $this->redirect()->toRoute('inscricoesCliente');
        }

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();

            $resposta = $this->getServiceLocator()
                ->get('QuestionarioAssociadoResponder')
                ->responderQuestionario($dados, array('associado' => $associado->id, 'questionario' => $idQuestionario));
            if($resposta){
                $this->flashMessenger()->addSuccessMessage('Questionário respondido com sucesso!');
            }else{
                $this->flashMessenger()->addErrorMessage('Ocorreu algum erro, por favor tente novamente!');
            }
            return $this->redirect()->toRoute('inscricoesCliente');
        }

        $primeiraQuestao = $questionario[key($questionario)];


        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'formQuestionario'  =>  $formQuestionario,
            'questionario'      =>  $questionario,
            'primeiraQuestao'   =>  $primeiraQuestao,
            'idQuestionario'    =>  $idQuestionario    
        ));
        return $view;
    }

    public function listarrespostasquestionarioAction(){
        $formPesquisa = new formRespostas('frmPesquisa', $this->getServiceLocator());
        $container = new Container();
        if(!isset($container->dados)){
            $container->dados = array();
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['limpar'])){
                $container->dados = array();
                $this->redirect()->toRoute('listarRespostasQuestionario');
            }else{
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $container->dados = $formPesquisa->getData();
                }
                
            }
        }

        $formPesquisa->setData($container->dados);
        $questionarios = array();
        if(isset($container->dados['questionario']) && !empty($container->dados['questionario'])){
            $questionarios = $this->getServiceLocator()->get('Associado')->getQuestionariosRespondidos($container->dados)->toArray();
        }
        //paginação
        $paginator = new Paginator(new ArrayAdapter($questionarios));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'formPesquisa'      => $formPesquisa,
                    'questionarios'     => $paginator,
                    'params'            => $container->dados
                ));
    }


}

