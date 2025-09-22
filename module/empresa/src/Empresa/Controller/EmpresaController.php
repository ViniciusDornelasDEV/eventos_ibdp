<?php

namespace Empresa\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Empresa\Form\PesquisaEmpresa as pesquisaEmpresaForm;
use Empresa\Form\Empresa as empresaForm;
use Empresa\Form\Paypal as formPaypal;
use Empresa\Form\Cielo as formCielo;

class EmpresaController extends BaseController
{

    public function indexAction()
    {   
        $formPesquisa = new pesquisaEmpresaForm('formEmpresa');
        $dados = array();
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost()->toArray();
            $formPesquisa->setData($dados);
        }

        //pesquisar empresas
        $serviceEmpresa = $this->getServiceLocator()->get('Empresa');
        $empresas = $serviceEmpresa->getEmpresaByParams($dados);

        //paginação
        $paginator = new Paginator(new ArrayAdapter($empresas->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'formPesquisa' => $formPesquisa,
                    'empresas'     => $paginator
                ));
    }

    public function novoAction()
    { 
        $formEmpresa = new empresaForm('formEmpresa');
        //caso venha um post salvar
        if($this->getRequest()->isPost()){
            //salvar e enviar para  edit
            $dados = $this->getRequest()->getPost();
            $serviceEmpresa = $this->getServiceLocator()->get('Empresa');

            //validar form
            $formEmpresa->setData($dados);
            if($formEmpresa->isValid()){
                $dadosInsert = $formEmpresa->getData(); 
                $dadosInsert['dados_deposito'] = htmlspecialchars($dadosInsert['dados_deposito']);
                $dadosInsert['anuidade_deposito'] = $dados['anuidade_deposito'];
                $result = $serviceEmpresa->insert($dadosInsert);
                if($result){
                    //sucesso criar mensagem e redir para edit
                    $this->flashMessenger()->addSuccessMessage('Empresa inserida com sucesso!');                
                    return $this->redirect()->toRoute('empresaAlterar', array('id' => $result));
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir empresa!'); 
                }
            }

        }

        return new ViewModel(array('formEmpresa' => $formEmpresa));
    }

    public function alterarAction(){
        //Pesquisar empresa
        $idEmpresa = $this->params()->fromRoute('id');
        $serviceEmpresa = $this->getServiceLocator()->get('Empresa');
        $empresa = $serviceEmpresa->getRecordFromArray(array('id' => $idEmpresa));

        //Popular form
        $formEmpresa = new empresaForm('frmEmpresa');
        $formEmpresa->setData($empresa);
        
        $formPaypal = new formPaypal('frmPaypal');
        $formCielo = new formCielo('frmCielo');

        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost()->toArray();
            //empresa
            if(isset($dados['nome_fantasia'])){
                $formEmpresa->setData($dados);
                
                if($formEmpresa->isValid()){
                    $dadosInsert = $formEmpresa->getData(); 
                    $dadosInsert['dados_deposito'] = htmlspecialchars($dadosInsert['dados_deposito']);
                    $dadosInsert['anuidade_paypal'] = $dados['anuidade_paypal'];
                    $dadosInsert['anuidade_cielo'] = $dados['anuidade_cielo'];
                    $dadosInsert['anuidade_deposito'] = $dados['anuidade_deposito'];
                    
                    //validar formas de pagamento
                    if($dados['anuidade_paypal'] == 'S'){
                        if(empty($empresa->paypal_pwd) || empty($empresa->paypal_user) || empty($empresa->paypal_signature)){
                            $this->flashMessenger()->addWarningMessage('Favor configurar o PayPal antes de habilitar o pagamento!');
                            return $this->redirect()->toRoute('empresaAlterar', array('id' => $empresa->id));
                        }
                    }

                    if($dados['anuidade_cielo'] == 'S'){
                        if(empty($empresa->merchant_id)){
                            $this->flashMessenger()->addWarningMessage('Favor configurar a Cielo antes de habilitar o pagamento!');
                            return $this->redirect()->toRoute('empresaAlterar', array('id' => $empresa->id));
                        }
                    }

                    $serviceEmpresa->update($dadosInsert, array('id'  =>  $empresa->id));
                    $this->flashMessenger()->addSuccessMessage('Empresa alterada com sucesso!'); 
                    return $this->redirect()->toRoute('empresaAlterar', array('id' => $empresa->id));
                }
            }

            //paypal
            if(isset($dados['paypal_pwd'])){
                $formPaypal->setData($dados);
                
                if($formPaypal->isValid()){
                    $serviceEmpresa->update($formPaypal->getData(), array('id'  =>  $empresa->id));
                    $this->flashMessenger()->addSuccessMessage('Dados do paypal alterados com sucesso!'); 
                    return $this->redirect()->toRoute('empresaAlterar', array('id' => $empresa->id));
                }
            }

            //cielo
            if(isset($dados['merchant_id'])){
                $formCielo->setData($dados);
                
                if($formCielo->isValid()){
                    $serviceEmpresa->update($formCielo->getData(), array('id'  =>  $empresa->id));
                    $this->flashMessenger()->addSuccessMessage('Dados da cielo alterados com sucesso!'); 
                    return $this->redirect()->toRoute('empresaAlterar', array('id' => $empresa->id));
                }
            }
        }
        
        $formPaypal->setData($empresa);
        $formCielo->setData($empresa);

        return new ViewModel(array(
                                'formEmpresa' => $formEmpresa,
                                'formPaypal'  => $formPaypal,
                                'formCielo'   => $formCielo
                                )
                            );
    }

    public function deletarempresaAction(){
        $serviceEmpresa = $this->getServiceLocator()->get('Empresa');
        $idEmpresa = $this->params()->fromRoute('id');
        try {
            $serviceEmpresa->delete(array('id' => $idEmpresa));
            $this->flashMessenger()->addSuccessMessage('Empresa excluída com sucesso!');
        } catch (Exception $e) {
            $serviceEmpresa->update(array('ativo' => 'N'), array('id' => $idEmpresa));
            $this->flashMessenger()->addSuccessMessage('Existem dados relacionados a empresa, ela foi desativada!');
        }
        return $this->redirect()->toRoute('empresa');
    }

}

