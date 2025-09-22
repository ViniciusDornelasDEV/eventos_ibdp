<?php

namespace Cliente\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Cliente\Form\CategoriaCliente as formCategoria;
use Cliente\Form\Upload as formUpload;


class CategoriaController extends BaseController
{

    public function indexAction()
    {   
        //pesquisar categorias de cliente
        $serviceCategoria = $this->getServiceLocator()->get('CategoriaCliente');
        $categorias = $serviceCategoria->fetchAll();

        //paginação
        $paginator = new Paginator(new ArrayAdapter($categorias->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'categorias'     => $paginator
                ));
    }

    public function novoAction()
    { 
        $formCategoria = new formCategoria('formCategoria');
        //caso venha um post salvar
        if($this->getRequest()->isPost()){
            //salvar e enviar para  edit
            $dados = $this->getRequest()->getPost();
            $serviceCategoria = $this->getServiceLocator()->get('CategoriaCliente');

            //validar form
            $formCategoria->setData($dados);
            if($formCategoria->isValid()){
                $result = $serviceCategoria->insert($formCategoria->getData());
                if($result){
                    //sucesso criar mensagem e redir para edit
                    $this->flashMessenger()->addSuccessMessage('Categoria de cliente inserida com sucesso!');                
                    return $this->redirect()->toRoute('categoriaClienteAlterar', array('id' => $result));
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir categoria do cliente!'); 
                }
            }

        }

        return new ViewModel(array('formCategoria' => $formCategoria));
    }

    public function alterarAction(){
        //Pesquisar categoria
        $idCategoria = $this->params()->fromRoute('id');
        $serviceCategoria = $this->getServiceLocator()->get('CategoriaCliente');
        $categoria = $serviceCategoria->getRecordFromArray(array('id' => $idCategoria));

        //Popular form
        $formCategoria = new formCategoria('frmCategoria');
        $formCategoria->setData($categoria);
        
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost()->toArray();
            $formCategoria->setData($dados);
            
            if($formCategoria->isValid()){
                $serviceCategoria->update($formCategoria->getData(), array('id'  =>  $categoria->id));
                $this->flashMessenger()->addSuccessMessage('Categoria do cliente alterada com sucesso!'); 
                return $this->redirect()->toRoute('categoriaClienteAlterar', array('id' => $categoria->id));
            }
        }

        return new ViewModel(array(
                                'formCategoria' => $formCategoria,
                                )
                            );
    }

    public function uploadsociosAction(){
        $idCategoria = $this->params()->fromRoute('idCategoria');
        
        $formUpload = new formUpload('formUpload');
        $numeroSocios = 0;
        if($this->getRequest()->isPost()){
            $files = $this->getRequest()->getfiles()->toArray();
            $dados = $this->uploadImagem($files, 'public/arquivos', array());

            $objReader = new \PHPExcel_Reader_Excel5();
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($dados['planilha']);
            $objPHPExcel->setActiveSheetIndex(0);
            $socios = $objPHPExcel->getActiveSheet()->toArray(null, true,true,true);
            $serviceSocio = $this->getServiceLocator()->get('Socio');
            foreach ($socios as $socio) {
                $cpf = $this->isCpf($socio['D']);
                if($cpf){
                    //inserir
                    $serviceSocio->insertCpf($cpf, $idCategoria);
                    $numeroSocios++;
                }
            }
        }
        return new ViewModel(array(
                'formUpload'    => $formUpload,
                'numeroSocios'  => $numeroSocios
            ));
    }

    public function uploadImagem($arquivos, $caminho, $dados){
        if(!file_exists($caminho)){
            mkdir($caminho);
        }
        
        $nomeArquivoServer = 'importarSocios';
        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$nomeArquivoServer.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/'.$nomeArquivoServer.'.'.$extensao;
                }
            }
        }

        return $dados;
    }
}

