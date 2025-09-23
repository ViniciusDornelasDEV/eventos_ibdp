<?php

namespace Associados\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Associados\Form\Ebook as formEbook;


class EbooksController extends BaseController
{
    public function indexebookAction()
    {
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $serviceEbooks = $this->getServiceLocator()->get('AssociadoEbook');
        $ebooks = $serviceEbooks->getRecords(1, 1, $fields = array('*'), 'id DESC');

        $paginator = new Paginator(new ArrayAdapter($ebooks->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'ebooks'     => $paginator
                ));
    }

    public function novoebookAction()
    {
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formEbook = new formEbook('formEbook');
        
        if ($this->getRequest()->isPost()) {
            $formEbook->setData($this->getRequest()->getPost());
            if($formEbook->isValid()){
                $dados = $formEbook->getData();
                unset($dados['arquivo']);
                $files = $this->getRequest()->getfiles()->toArray();
                $idEbook = $this->getServiceLocator()->get('AssociadoEbook')->getNextInsertId('tb_associado_ebook');
                if($files['arquivo']['size'] != 0){
                    $dados['arquivo'] = $this->uploadImagem($files, $idEbook->Auto_increment, 'ebook');
                }

                if($files['imagem_capa']['size'] != 0){
                    $dados['imagem_capa'] = $this->uploadImagem($files, $idEbook->Auto_increment, 'capa');
                }

                $result = $this->getServiceLocator()->get('AssociadoEbook')->insert($dados);
                if($result){
                    $this->flashMessenger()->addSuccessMessage('Ebook para associados inserido com sucesso!');
                    return $this->redirect()->toRoute('alterarEbookAssociados', array('idEbook' => $result)); 
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir ebook para associados!');
                    return $this->redirect()->toRoute('novoEbookAssociados'); 
                }
            }
        }

        return new ViewModel(array(
            'formEbook' => $formEbook
        ));
    }

    public function alterarebookAction()
    {
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formEbook = new formEbook('formEbook');
        $ebook = $this->getServiceLocator()->get('AssociadoEbook')->getRecord($this->params()->fromRoute('idEbook'));
        $formEbook->setData($ebook);

        if ($this->getRequest()->isPost()) {
            $formEbook->setData($this->getRequest()->getPost());
            if($formEbook->isValid()){
                $dados = $formEbook->getData();
                unset($dados['arquivo']);
                $files = $this->getRequest()->getfiles()->toArray();
                $idEbook = $this->getServiceLocator()->get('AssociadoEbook')->getNextInsertId('tb_associado_ebook');
                if($files['arquivo']['size'] != 0){
                    $dados['arquivo'] = $this->uploadImagem($files, $ebook->id, 'ebook');
                }else{
                    $dados['arquivo'] = $ebook->arquivo;
                }

                if($files['imagem_capa']['size'] != 0){
                    $dados['imagem_capa'] = $this->uploadImagem($files, $ebook->id, 'capa');
                }else{
                    $dados['imagem_capa'] = $ebook->imagem_capa;
                }

                $this->getServiceLocator()->get('AssociadoEbook')->update($dados, array('id' => $ebook->id));
                $this->flashMessenger()->addSuccessMessage('Ebook para associados alterado com sucesso!');
                return $this->redirect()->toRoute('alterarEbookAssociados', array('idEbook' => $ebook->id)); 
            }
        }

        return new ViewModel(array(
            'formEbook' => $formEbook
        ));
    }

    public function ebooksAction() {
        $this->layout('layout/cliente');
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $serviceEbook = $this->getServiceLocator()->get('AssociadoEbook');
        $ebooks = $serviceEbook->getRecords('S', 'ativo', $fields = array('*'), 'id DESC');

        /*echo '<pre>';
        var_dump($ebooks->toArray());
        die();*/
        
        $paginator = new Paginator(new ArrayAdapter($ebooks->toArray()));
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
            'ebooks'        =>  $paginator,
            'associado'     =>  $associado,
            'questionario'  =>  $questionario
        ));
    }

    public function uploadImagem($arquivos, $idEbook, $complemento){
        $caminho = 'public/arquivos/empresa';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho = 'public/arquivos/empresa/ebooksAssociados';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$complemento.'-'.$idEbook.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/'.$complemento.'-'.$idEbook.'.'.$extensao;
                }
            }
        }

        return '/arquivos/empresa/ebooksAssociados/'.$complemento.'-'.$idEbook.'.'.$extensao;
    }



}

