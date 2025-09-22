<?php

namespace Cliente\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

use Cliente\Form\AlterarFisica as pessoaFisicaForm;
use Cliente\Form\AlterarJuridica as pessoaJuridicaForm;
use Usuario\Form\TipoUsuario as formBase;

class ClienteController extends BaseController
{

    public function indexAction()
    {   
        
        return new ViewModel();
    }

    public function novoAction()
    { 
        return new ViewModel();
    }

    public function alterarAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }
        //pesquisar inscricao
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getInscricaoById($this->params()->fromRoute('inscricao'), $empresa);
        $serviceCliente = $this->getServiceLocator()->get('Cliente');
        $cliente = $serviceCliente->getRecord($inscricao->cliente);

        //pesquisar campos para personalizar form
        $serviceInscricaoCampos = $this->getServiceLocator()->get('InscricaoCamposEvento');
        $campos = $serviceInscricaoCampos->getCamposByEvento($inscricao->evento)->toArray();
        $campos = $this->prepararCampos($campos);
        $campos['codigo']['aparecer'] = 'N';
        if(empty($cliente->cpf)){
            //instanciar form de PJ
            $formPessoa = new pessoaFisicaForm('formPessoa', $this->getServiceLocator(), $campos);
        }else{
            //instanciar form de PF
            $formPessoa = new pessoaJuridicaForm('formPessoaJuridica', $this->getServiceLocator(), $campos);
        }

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formPessoa->setData($dados);
            if($formPessoa->isValid()){
                //alterar cliente
                $dadosUpdate = $formPessoa->getData();
                $dadosUpdate['senha'] = $dados['senha'];
                $serviceCliente->update($dadosUpdate, array('id' => $cliente->id));
                $this->flashMessenger()->addSuccessMessage('Cliente alterado com sucesso!');
                return $this->redirect()->toRoute('clienteAlterar', array('inscricao' => $inscricao->id));
            }
        }else{
            $formPessoa->setData($cliente);
        }

        return new ViewModel(array(
                                'formPessoa'    => $formPessoa,
                                'inscricao'     => $inscricao
                            ));
    }

    public function deletarempresaAction(){
    	return new ViewModel();
    }

    public function buscarclienteAction(){
        $params = $this->getRequest()->getPost();
        $cliente = $this->getServiceLocator()->get('Cliente')->getClienteByCpf($params['cpf'], 'cpf');
        if($cliente){
            $form = new formBase('frmBase');
            $cliente->data_nascimento = $form->converterData($cliente->data_nascimento);
        }
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('cliente' => $cliente));
        return $view;
    }

}

