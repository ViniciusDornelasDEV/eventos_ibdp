<?php

namespace Associados\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Associados\Form\Beneficios as formBeneficios;
use Associados\Form\Ebook as formEbook;


class BeneficiosController extends BaseController
{

    public function indexbeneficiosAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        //pesquisar empresas
        $serviceBeneficios = $this->getServiceLocator()->get('AssociadoBeneficios');
        $beneficios = $serviceBeneficios->getRecords(1, 1, $fields = array('*'), 'id DESC');

        //paginação
        $paginator = new Paginator(new ArrayAdapter($beneficios->toArray()));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(10);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'beneficios'     => $paginator
                ));
    }

    public function novobeneficiosAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formBeneficios = new formBeneficios('formBeneficios');
        
        if ($this->getRequest()->isPost()) {
            $formBeneficios->setData($this->getRequest()->getPost());
            if($formBeneficios->isValid()){
                $dados = $formBeneficios->getData();
                unset($dados['imagem']);
                $files = $this->getRequest()->getfiles()->toArray();
                if($files['imagem']['size'] != 0){
                    $idBeneficio = $this->getServiceLocator()->get('AssociadoBeneficios')->getNextInsertId('tb_associado_clube_beneficios');
                    $dados['imagem'] = $this->uploadImagem($files, $idBeneficio->Auto_increment);
                }

                $dados['descricao'] = htmlspecialchars($dados['descricao']);
                $result = $this->getServiceLocator()->get('AssociadoBeneficios')->insert($dados);
                if($result){
                    $this->flashMessenger()->addSuccessMessage('Benefício para associados inserido com sucesso!');
                    return $this->redirect()->toRoute('alterarBeneficioAssociados', array('idBeneficio' => $result)); 
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir benefício para associados!');
                    return $this->redirect()->toRoute('categoriaAssociadosNovo'); 
                }
            }
        }

        return new ViewModel(array(
            'formBeneficios' => $formBeneficios
        ));
    }

    public function alterarbeneficiosAction()
    {
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formBeneficios = new formBeneficios('formBeneficios');
        $beneficio = $this->getServiceLocator()->get('AssociadoBeneficios')->getRecord($this->params()->fromRoute('idBeneficio'));
        $formBeneficios->setData($beneficio);

        if ($this->getRequest()->isPost()) {
            $formBeneficios->setData($this->getRequest()->getPost());
            if($formBeneficios->isValid()){
                $dados = $formBeneficios->getData();
                unset($dados['imagem']);
                $files = $this->getRequest()->getfiles()->toArray();
                if($files['imagem']['size'] != 0){
                    $dados['imagem'] = $this->uploadImagem($files, $beneficio->id);
                }
                
                $dados['descricao'] = htmlspecialchars($dados['descricao']);
                $this->getServiceLocator()->get('AssociadoBeneficios')->update($dados, array('id' => $beneficio->id));
                $this->flashMessenger()->addSuccessMessage('Benefício para associados alterado com sucesso!');
                return $this->redirect()->toRoute('alterarBeneficioAssociados', array('idBeneficio' => $beneficio->id));
            }
        }

        return new ViewModel(array(
            'formBeneficios' => $formBeneficios
        ));
    }

    public function clubebeneficiosAction()
    {
        $this->layout('layout/cliente');
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $serviceContinuada = $this->getServiceLocator()->get('AssociadoBeneficios');
        $beneficios = $serviceContinuada->getRecords('S', 'ativo', $fields = array('*'), 'id DESC');

        $paginator = new Paginator(new ArrayAdapter($beneficios->toArray()));
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
            'beneficios'    =>  $paginator,
            'associado'     =>  $associado,
            'questionario'  =>  $questionario
        ));
    }

    public function exibirbeneficioAction()
    {
        $this->layout('layout/vazio');
        $usuario = $this->getServiceLocator()->get('session')->read();
        $associado = $this->getServiceLocator()->get('Associado')->getAssociados(array('cliente' => $usuario['cliente']))->current();
        
        if ($associado->adimplente != 'S') {
            die('Você não tem permissão para acessar esta página.');
        }
        
        $beneficio = $serviceContinuada = $this->getServiceLocator()->get('AssociadoBeneficios')
            ->getRecord($this->params()->fromRoute('idBeneficio'));

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'beneficio'    =>   $beneficio
        ));
        return $view;
    }

    public function uploadImagem($arquivos, $idBeneficio){
        $caminho = 'public/arquivos/empresa';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho = 'public/arquivos/empresa/beneficiosAssociados';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/beneficio-'.$idBeneficio.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/beneficio-'.$idBeneficio.'.'.$extensao;
                }
            }
        }

        return '/arquivos/empresa/beneficiosAssociados/beneficio-'.$idBeneficio.'.'.$extensao;
    }

    public function downloadarquivoAction(){
        $tipoArquivo = $this->params()->fromRoute('tipoArquivo');
        switch ($tipoArquivo) {
            case 1:
                $beneficio = $this->getServiceLocator()->get('AssociadoBeneficios')->getRecord($this->params()->fromRoute('id'));
                $fileName = $beneficio->imagem;
                break;
            case 2:
                $continuada = $this->getServiceLocator()->get('AssociadoContinuada')->getRecord($this->params()->fromRoute('id'));
                $fileName = $continuada->imagem;
                break;
            case 3:
                $eBook = $this->getServiceLocator()->get('AssociadoEbook')->getRecord($this->params()->fromRoute('id'));
                $fileName = $eBook->arquivo;
                break;
        }
        
        if(!is_file($fileName)) {
            //Não foi possivel encontrar o arquivo
        }
        
        $fileContents = file_get_contents('public/'.$fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'whatever your content type is')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;
    }

}

