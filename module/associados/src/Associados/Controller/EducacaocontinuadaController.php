<?php

namespace Associados\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Associados\Form\Continuada as formContinuada;


class EducacaocontinuadaController extends BaseController
{

    public function indexcontinuadaAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $serviceContinuada = $this->getServiceLocator()->get('AssociadoContinuada');
        $continuadas = $serviceContinuada->getRecords(1, 1, $fields = array('*'), 'id DESC');

        $paginator = new Paginator(new ArrayAdapter($continuadas->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'continuadas'     => $paginator
                ));
    }

    public function novocontinuadaAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formContinuada = new formContinuada('formContinuada');
        
        if ($this->getRequest()->isPost()) {
            $formContinuada->setData($this->getRequest()->getPost());
            if($formContinuada->isValid()){
                $dados = $formContinuada->getData();
                unset($dados['imagem']);
                $files = $this->getRequest()->getfiles()->toArray();
                if($files['imagem']['size'] != 0){
                    $idContinuada = $this->getServiceLocator()->get('AssociadoContinuada')->getNextInsertId('tb_associado_educacao_continuada');
                    $dados['imagem'] = $this->uploadImagem($files, $idContinuada->Auto_increment);
                }
                
                $dados['descricao'] = htmlspecialchars($dados['descricao']);
                $result = $this->getServiceLocator()->get('AssociadoContinuada')->insert($dados);
                if($result){
                    $this->flashMessenger()->addSuccessMessage('Educação continuada inserida com sucesso!');
                    return $this->redirect()->toRoute('alterarContinuadaAssociados', array('idContinuada' => $result)); 
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir educação continuada para associados!');
                    return $this->redirect()->toRoute('novoContinuadaAssociados'); 
                }
            }
        }

        return new ViewModel(array(
            'formContinuada' => $formContinuada
        ));
    }

    public function alterarcontinuadaAction()
    {
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formContinuada = new formContinuada('formContinuada');
        $continuada = $this->getServiceLocator()->get('AssociadoContinuada')->getRecord($this->params()->fromRoute('idContinuada'));
        $formContinuada->setData($continuada);

        if ($this->getRequest()->isPost()) {
            $formContinuada->setData($this->getRequest()->getPost());
            if($formContinuada->isValid()){
                $dados = $formContinuada->getData();
                unset($dados['imagem']);
                $files = $this->getRequest()->getfiles()->toArray();
                if($files['imagem']['size'] != 0){
                    $dados['imagem'] = $this->uploadImagem($files, $continuada->id);
                }

                $dados['descricao'] = htmlspecialchars($dados['descricao']);
                $result = $this->getServiceLocator()->get('AssociadoContinuada')->update($dados, array('id' => $continuada->id));
                $this->flashMessenger()->addSuccessMessage('Educação continuada alterada com sucesso!');
                return $this->redirect()->toRoute('alterarContinuadaAssociados', array('idContinuada' => $continuada->id));
            }
        }

        return new ViewModel(array(
            'formContinuada' => $formContinuada
        ));
    }

    public function educacaocontinuadaAction() {
        $this->layout('layout/cliente');
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $serviceContinuada = $this->getServiceLocator()->get('AssociadoContinuada');
        $continuadas = $serviceContinuada->getRecords('S', 'ativo', $fields = array('*'), 'id DESC');

        $paginator = new Paginator(new ArrayAdapter($continuadas->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        //associado
        $associado = $this->getServiceLocator()->get('Associado')->getAssociados(array('cliente' => $usuario['cliente']))->current();
        
        if(!empty($associado->certificado)){
            if(strtotime($associado->validade_certificado) < strtotime(date('Y-m-d')) || $associado['exibir_site'] == 'N'){
                $associado->certificado = '';
            }
        }
        $questionario = false;
        if($associado){
            $questionario = $this->getServiceLocator()->get('Questionario')->getAvaliacaoAberta($associado)->current();
        }
        
        if ($associado->adimplente != 'S') {
            die('Você não tem permissão para acessar esta página.');
        }

        return new ViewModel(array(
            'continuadas'    =>  $paginator,
            'associado'     =>  $associado,
            'questionario'  =>  $questionario
        ));
    }

    public function uploadImagem($arquivos, $idContinuada){
        $caminho = 'public/arquivos/empresa';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho = 'public/arquivos/empresa/continuadaAssociados';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/continuada-'.$idContinuada.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/continuada-'.$idContinuada.'.'.$extensao;
                }
            }
        }

        return '/arquivos/empresa/continuadaAssociados/continuada-'.$idContinuada.'.'.$extensao;
    }
}