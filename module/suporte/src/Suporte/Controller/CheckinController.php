<?php

namespace Suporte\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Suporte\Form\Checkin as formCheckin;

use Cliente\Form\PessoaFisica as pessoaFisicaForm;
use Cliente\Form\PessoaJuridica as pessoaJuridicaForm;

class CheckinController extends BaseController
{

    public function indexAction()
    {   
        $this->layout('layout/login');
        $siglaEvento = $this->params()->fromRoute('siglaEvento');
        $evento = $this->getServiceLocator()->get('Evento')->getRecord($siglaEvento, 'sigla');
        $formCheckin = new formCheckin('frmCertificado');

        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        if($this->getRequest()->isPost()){
            $formCheckin->setData($this->getRequest()->getPost());
            if($formCheckin->isValid()){
                $dados = $formCheckin->getData();
                $params = array('evento' => $evento->id);

                if(empty($dados['cpf'])){
                    $params['cliente'] = $dados['idCliente'];
                }else{
                    $params['cpf'] = $dados['cpf'];
                }
                $inscricao = $serviceInscricao
                    ->getInscricoesPagas($params)
                    ->current();

                
                if($inscricao){
                    //realizar checkin
                    return $this->redirect()->toRoute('confirmarPresenca', array('siglaEvento' => $siglaEvento, 'inscricao' => $inscricao->id));
                }else{
                    $this->flashMessenger()->addWarningMessage('Verificar pendência!');
                    return $this->redirect()->toRoute('buscarCheckin', array('siglaEvento' => $siglaEvento));
                }
            }
        }

        //pesquisar todos os clientes que pagaram o evento
        $inscritos = $serviceInscricao->getInscricoesPagas(array('evento' => $evento->id));

        return new ViewModel(array(
            'formCheckin'       => $formCheckin,
            'evento'            => $evento,
            'inscritos'         => $inscritos
        ));
    }

    public function confirmarpresencaAction(){
        $this->layout('layout/login');
        $siglaEvento = $this->params()->fromRoute('siglaEvento');

        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $inscricao = $serviceInscricao->getInscricaoById($this->params()->fromRoute('inscricao'));

        $serviceCliente = $this->getServiceLocator()->get('Cliente');
        $cliente = $serviceCliente->getRecord($inscricao->cliente);

        $serviceInscricaoCampos = $this->getServiceLocator()->get('InscricaoCamposEvento');
        $campos = $serviceInscricaoCampos->getCamposByEvento($inscricao->evento)->toArray();
        $campos = $this->prepararCampos($campos);
        if(empty($cliente['cnpj'])){
            $formPessoa = new pessoaFisicaForm('pessoaFisicaForm', $this->getServiceLocator(), $campos);
        }else{
            $formPessoa = new pessoaJuridicaForm('pessoaJuridicaForm', $this->getServiceLocator(), $campos);
        }

        $clienteForm = array();
        $campos['codigo']['aparecer'] = 'N';
        foreach ($campos as $campo) {
            if($campo['aparecer'] == 'S'){
                $clienteForm[$campo['nome_campo']] = $cliente[$campo['nome_campo']];
            }
        }
        $formPessoa->setData($clienteForm);

        if($this->getRequest()->isPost()){
            $formPessoa->setData($this->getRequest()->getPost());
            if($formPessoa->isValid()){
                //salvar dados
                if($serviceCliente->checkin($inscricao, $formPessoa->getData(), $cliente)){
                    $this->flashMessenger()->addSuccessMessage('Checkin realizado com sucesso!');
                    return $this->redirect()->toRoute('buscarCheckin', array('siglaEvento' => $siglaEvento));
                }
            }
        }

        return new ViewModel(array(
            'formPessoa'    =>  $formPessoa,
            'siglaEvento'   =>  $siglaEvento
        ));
    }

}

