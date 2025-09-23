<?php

namespace Competicao\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;

use Competicao\Form\Avaliar as formAvaliar;
use Competicao\Form\PesquisarAvaliador as formPesquisa;
use Competicao\Form\Avaliador as formAvaliador;
use Competicao\Form\Importar as formImportar;

class AvaliadorController extends BaseController
{

    public function indexAction()
    {   
      $formPesquisa = new formPesquisa('formPesquisa');

      $dados = array();
      //se vier post é uma pesquisa
      $container = new Container();
      if(!isset($container->dados)){
          $container->dados = array('ativo' => 'S');
      }
      if($this->getRequest()->isPost()){
          $dados = $this->getRequest()->getPost();
          if(isset($dados['limpar'])){
              unset($container->dados);
              $this->redirect()->toRoute('avaliadoresCompeticao');
          }else{
              $formPesquisa->setData($dados);
              if($formPesquisa->isValid()){
                  $container->dados = $formPesquisa->getData();
              }
              
          }
      }
      $formPesquisa->setData($container->dados);
      $avaliadores = $this->getServiceLocator()->get('Avaliador')->getAvaliadores($container->dados)->toArray();

      //paginação
      $paginator = new Paginator(new ArrayAdapter($avaliadores));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(40);
      $paginator->setPageRange(5);

      return new ViewModel(array(
          'formPesquisa'     => $formPesquisa,
          'avaliadores'      => $paginator,
      ));
    }

    public function novoAction()
    { 
      $formAvaliador = new formAvaliador('frmAvaliador');

      if($this->getRequest()->isPost()){
        $formAvaliador->setData($this->getRequest()->getPost());
        if($formAvaliador->isValid()){
          $dados = $formAvaliador->getData();
          if(isset($dados['senha']) && !empty($dados['senha'])){
            $bcrypt = new bcrypt();
            $dados['senha'] = $bcrypt->create($dados['senha']);
          }else{
            unset($dados['senha']);
          }
          $idAvaliador = $this->getServiceLocator()->get('Avaliador')->insert($dados);
          $this->flashmessenger()->addSuccessMessage('Avaliador inserido com sucesso!');
          return $this->redirect()->toRoute('avaliadoresCompeticao');
        }
      }

      return new ViewModel(array('formAvaliador' => $formAvaliador));
    }

    public function alterarAction(){
      $formAvaliador = new formAvaliador('frmAvaliador');
      $avaliador = $this->getServiceLocator()->get('Avaliador')->getRecord($this->params()->fromRoute('id'));
      unset($avaliador['senha']);
      $formAvaliador->setData($avaliador);

      if($this->getRequest()->isPost()){
        $formAvaliador->setData($this->getRequest()->getPost());
        if($formAvaliador->isValid()){
          $dados = $formAvaliador->getData();
          if(isset($dados['senha']) && !empty($dados['senha'])){
            $bcrypt = new bcrypt();
            $dados['senha'] = $bcrypt->create($dados['senha']);
          }else{
            unset($dados['senha']);
          }
          $idAvaliador = $this->getServiceLocator()->get('Avaliador')->update($dados, array('id' => $avaliador->id));
          $this->flashmessenger()->addSuccessMessage('Avaliador alterado com sucesso!');
          return $this->redirect()->toRoute('avaliadoresCompeticao');
        }
      }

      return new ViewModel(array('formAvaliador' => $formAvaliador));
    }

    public function avaliarAction(){
      $this->layout('layout/chaveamento');
      $partida = $this->getServiceLocator()->get('CompeticaoChaveamento')->getPartida($this->params()->fromRoute('idPartida'));

      //se tem 3 avaliações não pode mais pontuar
      $avaliacoes = $this->getServiceLocator()->get('CompeticaoNota')->getRecords($partida->id, 'chaveamento')->toArray();
      if(count($avaliacoes) == 3){
        $this->flashmessenger()->addWarningMessage('Esta partida já foi avaliada!');
        return $this->redirect()->toRoute('competicaoVisualizarChaveamento', array('idCompeticao' => $partida->competicao));
      }

      //se já foi avaliado por este usuário não pode avaliar
      $usuario = $this->getServiceLocator()->get('session')->read();
      foreach ($avaliacoes as $avaliacao) {
        if($avaliacao['avaliador'] == $usuario['id']){
          $this->flashmessenger()->addWarningMessage('Esta partida já foi avaliada!');
          return $this->redirect()->toRoute('competicaoVisualizarChaveamento', array('idCompeticao' => $partida->competicao));
        }
      }

      $formValido = true;
      $formAvaliar = new formAvaliar('frmAvaliar', $this->getServiceLocator(), $partida);
      if($this->getRequest()->isPost()){
        $formAvaliar->setData($this->getRequest()->getPost());
        if($formAvaliar->isValid()){
          $dados = $formAvaliar->getData();
          $dados['chaveamento'] = $partida['id'];
          $dados['avaliador'] = $usuario['id'];
          $this->getServiceLocator()->get('CompeticaoNota')->salvarAvaliacao($dados, $avaliacoes, $partida);
          $this->flashmessenger()->addSuccessMessage('Notas salvas com sucesso!');
          return $this->redirect()->toRoute('competicaoVisualizarChaveamento', array('idCompeticao' => $partida->competicao));
        }else{
          $formValido = false;
        }
      }

      return new ViewModel(array(
        'formAvaliar'   =>  $formAvaliar,
        'partida'       =>  $partida,
        'formValido'    =>  $formValido
      ));
    }


    public function importarAction(){
      $formUpload = new formImportar('formUpload');
    
      if($this->getRequest()->isPost()){
          $files = $this->getRequest()->getfiles()->toArray();
          $arquivo = $this->uploadImagem($files);
          
          $objReader = new \PHPExcel_Reader_Excel5();
          $objReader->setReadDataOnly(true);
          $objPHPExcel = $objReader->load($arquivo);
          $objPHPExcel->setActiveSheetIndex(0);

          $res = $this->getServiceLocator()->get('Avaliador')->importarAvaliadores($objPHPExcel);
          if($res['status'] == true){
            $this->flashmessenger()->addSuccessMessage($res['avaliadores'].' avaliadores importados!');
          }else{
            $this->flashmessenger()->addErrorMessage('Ocorreu algum erro!');
          }
          return $this->redirect()->toRoute('importarAvaliadores');
      }
      return new ViewModel(array(
          'formUpload'        => $formUpload,
      ));
    }

    public function uploadImagem($arquivos){
      $caminho = 'public/arquivos/avaliadores';
      if(!file_exists($caminho)){
          mkdir($caminho);
      }

      foreach ($arquivos as $nomeArquivo => $arquivo) {
          if(!empty($arquivo['tmp_name'])){
              $extensao = $this->getExtensao($arquivo['name']);
              if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/importarAvaliador.'.$extensao)){
                  $dados[$nomeArquivo] = $caminho.'/importarAvaliador.'.$extensao;
              }
          }
      }

      return $caminho.'/importarAvaliador.'.$extensao;
  }
}

