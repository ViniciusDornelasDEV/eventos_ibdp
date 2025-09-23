<?php

namespace Competicao\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Competicao\Form\Faculdade as formFaculdade;
use Competicao\Form\PesquisarFaculdade as formPesquisa;
use Competicao\Form\Orador as formOrador;
use Competicao\Form\Importar as formImportar;

class FaculdadeController extends BaseController
{
  public function indexAction(){   
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
            $this->redirect()->toRoute('faculdades');
        }else{
            $formPesquisa->setData($dados);
            if($formPesquisa->isValid()){
                $container->dados = $formPesquisa->getData();
            }
            
        }
    }
    $formPesquisa->setData($container->dados);
    $faculdades = $this->getServiceLocator()->get('Faculdade')->getFaculdades($container->dados)->toArray();

    //paginação
    $paginator = new Paginator(new ArrayAdapter($faculdades));
    $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
    $paginator->setItemCountPerPage(40);
    $paginator->setPageRange(5);

    return new ViewModel(array(
        'formPesquisa'     => $formPesquisa,
        'faculdades'      => $paginator,
    ));
  }

  public function novoAction(){ 
    $formFaculdade = new formFaculdade('frmFaculdade');
    
    if($this->getRequest()->isPost()){
      $formFaculdade->setData($this->getRequest()->getPost());
      if($formFaculdade->isValid()){
        $idFaculdade = $this->getServiceLocator()->get('Faculdade')->insert($formFaculdade->getData());
        $this->flashmessenger()->addSuccessMessage('Faculdade inserida com sucesso!');
        return $this->redirect()->toRoute('faculdadeAlterar', array('id' => $idFaculdade));
      }
    }
    return new ViewModel(array('formFaculdade' => $formFaculdade));
  }

  public function alterarAction(){
    $formFaculdade = new formFaculdade('frmFaculdade');
    $faculdade = $this->getServiceLocator()->get('Faculdade')->getRecord($this->params()->fromRoute('id'));
    $formFaculdade->setData($faculdade);

    if($this->getRequest()->isPost()){
      $formFaculdade->setData($this->getRequest()->getPost());
      if($formFaculdade->isValid()){
        $this->getServiceLocator()->get('Faculdade')->update($formFaculdade->getData(), array('id' => $faculdade->id));
        $this->flashmessenger()->addSuccessMessage('Faculdade alterada com sucesso!');
        return $this->redirect()->toRoute('faculdadeAlterar', array('id' => $faculdade->id));
      }
    }
    return new ViewModel(array(
      'formFaculdade' => $formFaculdade,
      'faculdade'     => $faculdade
    ));
  }

  public function oradoresAction(){
    $faculdade = $this->getServiceLocator()->get('Faculdade')->getRecord($this->params()->fromRoute('idFaculdade'));
    $formOrador = new formOrador('frmOrador');
    $idOrador = $this->params()->fromRoute('id');
    $orador = false;
    if(!empty($idOrador)){
      $orador = $this->getServiceLocator()->get('Orador')->getRecord($idOrador);
      $formOrador->setData($orador);
    }

    //POST
    if($this->getRequest()->isPost()){
      $formOrador->setData($this->getRequest()->getPost());
      if($formOrador->isValid()){
      $dados = $formOrador->getData();
        if($orador){
          //update
          $this->getServiceLocator()->get('Orador')->update($dados, array('id' => $orador->id));
          $this->flashmessenger()->addSuccessMessage('Orador alterado com sucesso!');
          return $this->redirect()->toRoute('faculdadeOradores', array('idFaculdade' => $faculdade->id));
        }else{
          //insert 
          $dados['faculdade'] = $faculdade->id;
          $this->getServiceLocator()->get('Orador')->insert($dados);
          $this->flashmessenger()->addSuccessMessage('Orador inserido com sucesso!');
          return $this->redirect()->toRoute('faculdadeOradores', array('idFaculdade' => $faculdade->id));
        }
      }
    }

    $oradores = $this->getServiceLocator()->get('Orador')->getOradoresByFaculdade($faculdade->id);

    return new ViewModel(array(
      'formOrador'  => $formOrador,
      'oradores'    => $oradores,
      'faculdade'   => $faculdade
    ));
  }

  public function importarfaculdadesAction(){
    
    $formUpload = new formImportar('formUpload');
    
    if($this->getRequest()->isPost()){
        $files = $this->getRequest()->getfiles()->toArray();
        $arquivo = $this->uploadImagem($files);
        
        $objReader = new \PHPExcel_Reader_Excel5();
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($arquivo);
        $objPHPExcel->setActiveSheetIndex(0);

        $res = $this->getServiceLocator()->get('Faculdade')->importarFaculdades($objPHPExcel);
        if($res['status'] == true){
          $this->flashmessenger()->addSuccessMessage($res['faculdades'].' faculdades e '.$res['oradores'].' oradores importados!');
        }else{
          $this->flashmessenger()->addErrorMessage('Ocorreu algum erro!');
        }
        return $this->redirect()->toRoute('importarFaculdades');
    }
    return new ViewModel(array(
        'formUpload'        => $formUpload,
    ));
  }

  public function uploadImagem($arquivos){
      $caminho = 'public/arquivos/faculdades';
      if(!file_exists($caminho)){
          mkdir($caminho);
      }

      foreach ($arquivos as $nomeArquivo => $arquivo) {
          if(!empty($arquivo['tmp_name'])){
              $extensao = $this->getExtensao($arquivo['name']);
              if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/importarFaculdade.'.$extensao)){
                  $dados[$nomeArquivo] = $caminho.'/importarFaculdade.'.$extensao;
              }
          }
      }

      return $caminho.'/importarFaculdade.'.$extensao;
  }
}

