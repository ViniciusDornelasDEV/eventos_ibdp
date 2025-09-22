<?php

namespace Associados\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Associados\Form\GraficoQuestao as formQuestao;
use Associados\Form\GraficoQuestionario as formQuestionario;

class GraficosController extends BaseController
{
    public function pizzaquestaoAction(){
        //Gráfico pizza por questão, mostra a quantidade de alternativas respondidas...
    	$formQuestao = new formQuestao('frmQuestao', $this->getServiceLocator());

    	$avaliacoes = false;
    	$questao = false;
    	if($this->getRequest()->isPost()){
    		$formQuestao->setData($this->getRequest()->getPost());
    		if($formQuestao->isValid()){
    			$dados = $formQuestao->getData();
    			$avaliacoes = $this->getServiceLocator()->get('QuestionarioQuestaoAlternativa')
    				->getRespostasByQuestao($dados['questao'])->toArray();
    			$questao = $this->getServiceLocator()->get('QuestionarioQuestao')->getRecord($dados['questao']);
    			
    		}
    	}

    	return new ViewModel(array(
    		'formQuestao'	=>	$formQuestao,
    		'avaliacoes'	=>	$avaliacoes,
    		'questao'		=>	$questao
    	));
    }

    public function barrasquestionarioAction(){
        //Gráfico de barras todas as questões (ver no RH)
    	$formQuestionario = new formQuestionario('frmQuestionario', $this->getServiceLocator());

        $respostasQuestoes = array();
        if($this->getRequest()->isPost()){
            $formQuestionario->setData($this->getRequest()->getPost());
            if($formQuestionario->isValid()){
                $dados = $formQuestionario->getData();

                $questoes = $this->getServiceLocator()
                    ->get('QuestionarioQuestao')
                    ->getRecordsFromArray(array('questionario' => $dados['questionario']), 'ordem')->toArray();


                foreach ($questoes as $key => $questao) {
                    $respostasQuestoes[$key] = array();
                    $respostasQuestoes[$key]['questao'] = $this->getServiceLocator()
                        ->get('QuestionarioQuestao')->getRecord($questao['id']);
                    
                    $respostasQuestoes[$key]['respostas'] = $this->getServiceLocator()->get('QuestionarioQuestaoAlternativa')
                    ->getRespostasByQuestao($questao['id'])->toArray();

                }
                
            }
        }
        
        return new ViewModel(array(
            'formQuestionario'   =>  $formQuestionario,
            'respostasQuestoes'  =>  $respostasQuestoes,
        ));
    }

    public function respondidosAction(){
        //Gráfico de respondidos/não respondidos, listar quem não respondeu
        $formQuestionario = new formQuestionario('frmQuestionario', $this->getServiceLocator());
        
        $respondidas = false;
        $emAberto = false;
        if($this->getRequest()->isPost()){
            $formQuestionario->setData($this->getRequest()->getPost());
            if($formQuestionario->isValid()){
                $dados = $formQuestionario->getData();
                $respondidas = $this->getServiceLocator()->get('Questionario')->getRespondidasByQuestionario($dados['questionario']);
                $emAberto = $this->getServiceLocator()->get('Associado')->getQuestionariosAberto($dados['empresa'], $dados['questionario']);
                
            }
        }

        return new ViewModel(array(
            'formQuestionario'      =>  $formQuestionario,
            'respondidas'           =>  $respondidas,
            'emAberto'              =>  $emAberto
        ));
    }

    public function questionariosbyempresaAction(){
    	$params = $this->getRequest()->getPost();
    	//instanciar form
    	$formQuestao = new formQuestao('formQuestao', $this->getServiceLocator());
        $questionario = $formQuestao->setQuestionariosByEmpresa($params->empresa);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('questionario' => $questionario));
        return $view;
    }

    public function questoesbyquestionarioAction(){
    	$params = $this->getRequest()->getPost();
    	//instanciar form
    	$formQuestao = new formQuestao('formQuestao', $this->getServiceLocator());
        $questoes = $formQuestao->setQuestoesByQuestionario($params->questionario);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('questoes' => $questoes));
        return $view;
    }

}

