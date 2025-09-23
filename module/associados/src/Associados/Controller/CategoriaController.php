<?php

namespace Associados\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Associados\Form\Categoria as formCategoria;
use Associados\Form\Anuidade as formAnuidade;
use Associados\Form\Arquivos as formArquivos;
use Associados\Form\Mensagem as formMensagem;

class CategoriaController extends BaseController
{

    public function indexAction()
    {   
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        //pesquisar empresas
        $serviceCategoria = $this->getServiceLocator()->get('CategoriaAssociado');
        $categorias = $serviceCategoria->getCategorias($empresa);

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
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formCategoria = new formCategoria('formCategoria', $this->getServiceLocator(), $empresa);
        
        if($this->getRequest()->isPost()){
            $formCategoria->setData($this->getRequest()->getPost());
            if($formCategoria->isValid()){
                $dados = $formCategoria->getData();
                if($empresa){
                    $dados['empresa'] = $empresa;
                }

                //UPLOAD DO CERTIFICADO
                unset($dados['certificado']);
                $files = $this->getRequest()->getfiles()->toArray();
                if($files['certificado']['size'] != 0){
                    if(empty($dados['validade_certificado'])){
                        $this->flashMessenger()->addWarningMessage('Informe uma data de validade para o certificado!');
                        return $this->redirect()->toRoute('categoriaAssociadosNovo'); 
                    }
                    $idCategoria = $this->getServiceLocator()->get('CategoriaAssociado')->getNextInsertId('tb_associado_categoria');
                    $dados = $this->uploadImagem($files, $dados, 'certificado'.$idCategoria->Auto_increment);
                }

                $result = $this->getServiceLocator()->get('CategoriaAssociado')->insert($dados);
                if($result){
                    $this->flashMessenger()->addSuccessMessage('Categoria de associado inserida com sucesso!');
                    return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $result)); 
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir categoria de associado!');
                    return $this->redirect()->toRoute('categoriaAssociadosNovo'); 
                }
            }
        }

        return new ViewModel(array(
            'formCategoria' => $formCategoria
        ));
    }

    public function alterarAction(){
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        $empresa = false;
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $empresa = $usuario['empresa'];
        }

        $formCategoria = new formCategoria('formCategoria', $this->getServiceLocator(), $empresa);
        $formAnuidade = new formAnuidade('formAnuidade');

        //alocar services e pesquisar dados
        $serviceCategoria = $this->getServiceLocator()->get('CategoriaAssociado');
        $categoria = $serviceCategoria->getRecord($this->params()->fromRoute('idCategoria'));
        if($empresa && $categoria['empresa'] != $empresa){
            $this->flashMessenger()->addWarningMessage('Categoria de associado não encontrada!');
            return $this->redirect()->toRoute('listaCategoriaAssociados');
        }

        $formArquivo = new formArquivos('formArquivos', $this->getServiceLocator(), $categoria->empresa);

        $serviceAnuidade = $this->getServiceLocator()->get('AssociadoAnuidade');
        $anuidade = $serviceAnuidade->getRecord($this->params()->fromRoute('idAnuidade'));
        
        $serviceArquivo = $this->getServiceLocator()->get('AssociadoArquivo');
        $arquivo = $serviceArquivo->getRecord($this->params()->fromRoute('idArquivo'));

        //POST
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['nome'])){
                //categoria
                $formCategoria->setData($dados);
                if($formCategoria->isValid()){
                    //UPLOAD DO CERTIFICADO
                    $dados = $formCategoria->getData();
                    
                    unset($dados['certificado']);
                    $files = $this->getRequest()->getfiles()->toArray();
                    if($files['certificado']['size'] != 0){
                        $extensao = $this->getExtensao($files['certificado']['name']);
                        if($extensao != 'jpg'){
                            $this->flashMessenger()->addWarningMessage('Favor inserir uma imagem .jpg para o certificado!');
                            return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $categoria->id));
                        }
                        
                        $dados = $this->uploadImagem($files, $dados, 'certificado'.$categoria->id);
                    }
                    
                    $serviceCategoria->update($dados, array('id' => $categoria->id));
                    $this->flashMessenger()->addSuccessMessage('Categoria de associado alterada com sucesso!');
                    return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $categoria->id)); 
                }
            }

            if(isset($dados['data_inicio'])){
                //anuidade
                $formAnuidade->setData($dados);
                if($formAnuidade->isValid()){
                    $dados = $formAnuidade->getData();
                    $dados['categoria'] = $categoria->id;
                    //verificar datas
                    if(!$this->validarDatas($dados)){
                        $this->flashMessenger()->addWarningMessage('Existem datas da anuidade em conflito!');
                        return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $categoria->id));  
                    }
                    if($anuidade){
                        //update
                        if($serviceAnuidade->update($dados, array('id' => $anuidade->id))){
                            $this->flashMessenger()->addSuccessMessage('Anuidade alterada com sucesso!');
                        }else{
                            $this->flashMessenger()->addErrorMessage('Falha ao alterar anuidade!');
                        }
                        return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $categoria->id, 'idAnuidade' => $anuidade->id)); 
                    }else{
                        //insert
                        $result = $serviceAnuidade->insert($dados);
                        if($result){
                            $this->flashMessenger()->addSuccessMessage('Anuidade inserida com sucesso!');
                            return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $categoria->id, 'idAnuidade' => $result)); 
                        }else{
                            $this->flashMessenger()->addErrorMessage('Falha ao inserir anuidade!');
                            return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $categoria->id));
                        }
                    }
                    return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('id' => $categoria->id)); 
                }
            }

            if(isset($dados['descricao'])){
                //arquivos
                $formArquivo->setData($dados);
                if($formArquivo->isValid()){
                    $dados = $formArquivo->getData();
                    $files = $this->getRequest()->getfiles()->toArray();
                    $idArquivo = $serviceArquivo->getNextInsertId('tb_associado_arquivo');
                    $dados = $this->uploadImagem($files, $dados, $idArquivo->Auto_increment);
                    
                    $categorias = $serviceCategoria->getRecordsFromArray(array('ativo' => 'S', 'empresa' => $categoria->empresa));
                    $result = $serviceArquivo->inserirArquivos($dados, $categorias);
                    if($result){
                        $this->flashMessenger()->addSuccessMessage('Arquivo inserido com sucesso!');
                    }else{
                        $this->flashMessenger()->addErrorMessage('Falha ao inserir arquivo!');
                    }
                    return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $categoria->id));
                }

            }
        }

        //popular forms
        $formCategoria->setData($categoria);

        if($anuidade){
            $formAnuidade->setData($anuidade);
        } 

        if($arquivo){
            $formArquivo->setData($arquivo);
        }

        //pesquisar anuidades e arquivos
        $anuidades = $serviceAnuidade->getRecordsFromArray(array('categoria' => $categoria->id), 'data_pagamento');
        $arquivos = $serviceArquivo->getRecordsFromArray(array('categoria' => $categoria->id), 'descricao');

        return new ViewModel(array(
            'formCategoria'     => $formCategoria, 
            'formAnuidade'      => $formAnuidade,
            'formArquivos'      => $formArquivo,
            'categoria'         => $categoria,
            'anuidades'         => $anuidades,
            'arquivos'          => $arquivos
        ));
    }

    public function associadosadimplentesAction(){
        //anuidades pagas, associados ficam adimplentes (todos os outros associados ficam inadimplentes)
        $idAnuidade = $this->params()->fromRoute('idAnuidade');
        $idCategoria = $this->params()->fromRoute('idCategoria');

        //pesquisar pagamentos
        $pagamentos = $this->getServiceLocator()->get('AssociadoPagamento')->getRecordsFromArray(array('anuidade' => $idAnuidade));
        if($pagamentos->count() == 0){
          $this->flashMessenger()->addWarningMessage('Não foram encontrados pagamentos para a anuidade selecionada!');
          return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $idCategoria));
        }
        $result = $this->getServiceLocator()->get('Associado')->adimplenteByAnuidade($idAnuidade, $idCategoria, $pagamentos);
        if($result){
            $this->flashMessenger()->addSuccessMessage('Adimplentes/inadimplentes atualizados com sucesso!');
        }else{
            $this->flashMessenger()->addErrorMessage('Ocorreu algum erro, por favor tente novamente!');
        }
        return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $idCategoria));
    }

    public function listarassociadossiteAction(){
        //anuidades pagas, associados ficam adimplentes (todos os outros associados ficam inadimplentes)
        $idAnuidade = $this->params()->fromRoute('idAnuidade');
        $idCategoria = $this->params()->fromRoute('idCategoria');
        $anuidade = $this->getServiceLocator()->get('AssociadoAnuidade')->getRecord($idAnuidade);
        
        //pesquisar pagamentos
        $pagamentos = $this->getServiceLocator()->get('AssociadoPagamento')->getRecordsFromArray(array('anuidade' => $idAnuidade));
        $result = $this->getServiceLocator()->get('Associado')->listarByAnuidade($anuidade, $idCategoria, $pagamentos);
        if($result){
            $this->flashMessenger()->addSuccessMessage('Associados do site atualizados com sucesso!');
        }else{
            $this->flashMessenger()->addErrorMessage('Ocorreu algum erro, por favor tente novamente!');
        }
        return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $idCategoria));
    }

    public function downloadassociadoAction(){
        $serviceArquivo = $this->getServiceLocator()->get('AssociadoArquivo');
        $arquivo = $serviceArquivo->getRecord($this->params()->fromRoute('idArquivo'));
        $fileName = $arquivo->arquivo;
        if(!is_file($fileName)) {
            //Não foi possivel encontrar o arquivo
        }
        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'whatever your content type is')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;
    }

    public function deletararquivoassociadoAction(){
        $serviceArquivo = $this->getServiceLocator()->get('AssociadoArquivo');
        $serviceArquivo->delete(array('id' => $this->params()->fromRoute('idArquivo')));
        $this->flashMessenger()->addSuccessMessage('Arquivo excluído com sucesso!');
        return $this->redirect()->toRoute('categoriaAssociadosAlterar', array('idCategoria' => $this->params()->fromRoute('idCategoria')));
    }
    
    public function mensagemassociadoAction(){
        $idEvento = $this->params()->fromRoute('id');
        $serviceMensagens = $this->getServiceLocator()->get('MensagensAssociado');

        //instanciar form de mensagem
        $formMensagem = new formMensagem('formMensagem', $this->getServiceLocator());

        $erros = array();
        $email = false;
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formMensagem->setData($dados);
            if($formMensagem->isValid()){
                $dados = $formMensagem->getData();
                if($serviceMensagens->insert($dados)){
                    
                    //PESQUISAR ASSOCIADOS DA CATEGORIA SELECIONADA
                    $associados = $this->getServiceLocator()->get('Associado')->getPagamentos($dados, true);
                    $email = array();
                    
                    if($associados->count() == 0){
                        $this->flashMessenger()->addWarningMessage('Nenhum associado encontrado!');
                        return $this->redirect()->toRoute('mensagemAssociado');
                    }
                    $email['contatos'] = array();
                    $email['assunto'] = $dados['assunto'];
                    $email['mensagem'] = html_entity_decode($dados['mensagem']);
                    $email['requests'] = array();
                    $count = 0;
                    $numRequests = 1;
                    foreach ($associados as $associado) {
                        if(!empty($associado->email)){

                            if($this->validarEmail($associado->email)){
                                $count++;
                                $email['contatos'][] = strtolower(trim($associado->email));
                                if($count == 50){
                                    $count = 0;
                                    if($numRequests == 1){
                                        $email['requests'][] = array('min' => 0, 'max' => 50);
                                    }else{
                                        $email['requests'][] = array('min' => ($numRequests-1)*50, 'max' => $numRequests*50);
                                    }
                                    $numRequests++;
                                }
                            }else{
                                $erros[$associado->cpf] = $associado->email;
                            }
                        }else{
                           $erros[$associado->cpf] = $associado->email;
                        }
                    }
                    
                    if($count > 0){
                        if($numRequests == 1){
                            $email['requests'][] = array('min' => 0, 'max' => $count);
                        }else{
                            $email['requests'][] = array('min' => ($numRequests-1)*50, 'max' => ($numRequests-1)*50+$count);
                        }
                    }
                }
            }
        }
        
        //pesquisar mensagens enviadas
        $mensagens = $serviceMensagens->getMensagens();

        return new ViewModel(array(
                        'mensagens'        => $mensagens,
                        'formMensagem'     => $formMensagem,
                        'erros'            => $erros,
                        'listaEmail'       => $email
                    ));
    }

    public function enviaremailAction(){
        $dadosPost = $this->getRequest()->getPost();
        $dados = $dadosPost['dados'];
        $range = $dados['requests'][$dadosPost['posicao']];
        $destinatarios = array();
        for ($i=$range['min']; $i < $range['max']; $i++) { 
            $destinatarios[] = $dados['contatos'][$i];
        }
        $mailer = $this->getServiceLocator()->get('mailer');
        $mailer->mailUserCco($destinatarios, $dados['assunto'], html_entity_decode($dados['mensagem']));
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('status' => true));
        return $view;
    }

    public function uploadImagem($arquivos, $dados, $idArquivo){
        $caminho = 'public/arquivos/empresa/categoriaAssociados';

        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$idArquivo.'.'.$extensao)){
                    $dados[$nomeArquivo] = $caminho.'/'.$idArquivo.'.'.$extensao;
                }
            }
        }

        return $dados;
    }

    private function validarDatas($dados){
        return true;
        $serviceAnuidade = $this->getServiceLocator()->get('AssociadoAnuidade');
        $anuidades = $serviceAnuidade->getAnuidadePeriodo($dados);

        if($anuidades->count() > 0){
            return false;
        }else{
            return true;
        }
    }

}

