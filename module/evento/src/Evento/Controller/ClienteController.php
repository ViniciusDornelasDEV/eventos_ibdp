<?php

namespace Evento\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Session\Container;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

use Associados\Form\ResponderQuestionario as formResponder;
use Evento\Form\Cliente as formAssociado;
use Evento\Form\ComprovanteAnuidade as formComprovante;
use Evento\Form\ComprovanteInscricao as formComprovanteInscricao;

use Evento\Form\PesquisaTrabalhoPublic as formPesquisaTrabalho;

class ClienteController extends BaseController
{
    public function inscricoesAction()
    {   
        $this->layout('layout/cliente');
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $inscricoes = $serviceInscricao->getInscricoesByParams(array('cliente' => $usuario['cliente']))->toArray();

        //paginação
        $paginator = new Paginator(new ArrayAdapter($inscricoes));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(40);
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

        $trabalhos = false;
        if($usuario['avaliador'] == 'S'){
            $trabalhos = $serviceInscricao->getTrabalhos(array('avaliador' => $usuario['id']))->toArray();
        }

        return new ViewModel(array(
            'inscricoes'    =>  $paginator,
            'associado'     =>  $associado,
            'questionario'  =>  $questionario,
            'trabalhos'     =>  $trabalhos
        ));
    }

    public function comprovanteinscricaoAction(){
        $this->layout('layout/cliente');

        $formArquivo = new formComprovanteInscricao('frmArquivo');
        $idInscricao = $this->params()->fromRoute('idInscricao');
        $inscricao = $this->getServiceLocator()->get('Inscricao')
            ->getInscricoesFromArray(array('tb_inscricao.id' => $idInscricao))->current();
        

        //verificar se cliente é o mesmo da inscricao
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        if($usuario['id_usuario_tipo'] == 2){
            if(!$inscricao || $inscricao['cliente'] != $usuario['cliente']){
                $this->flashMessenger()->addWarningMessage('Inscrição não encontrada!');
                return $this->redirect()->toRoute('inscricoesCliente');
            }
        }
                
        //POST
        if($this->getRequest()->isPost()){
            $formArquivo->setData($this->getRequest()->getPost());
            if($formArquivo->isValid()){
                //upload do comprovante
                $files = $this->getRequest()->getfiles()->toArray();
                $caminhoArquivo = $this->uploadComprovanteInscricao($files, $inscricao['cliente'], $inscricao['id']);
                $this->getServiceLocator()->get('Inscricao')->update(array('comprovante' => $caminhoArquivo), array('id' => $inscricao['id']));
                 $this->flashMessenger()->addSuccessMessage('Comprovante salvo com sucesso!');
                if($usuario['id_usuario_tipo'] == 2){
                    return $this->redirect()->toRoute('inscricoesCliente');
                }else{
                    return $this->redirect()->toRoute('visualizaInscricoes');
                }
            }
        }

        return new ViewModel(array(
            'formArquivo'   =>  $formArquivo,
            'inscricao'      =>  $inscricao
        )); 
    }

    public function downloadcomprovanteAction(){
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($this->params()->fromRoute('idInscricao'));
        $fileName = strtolower($inscricao->comprovante);

        //se cliente, verificar se o arquivo é realmente dele!
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 2){
            if($usuario['id_usuario_tipo'] == 2 && $inscricao['cliente'] != $usuario['cliente']){
                $this->flashMessenger()->addWarningMessage('Arquivo não encontrado!');
                return false;
            }
        }

        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'comprovante inscricao')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;
    }

    public function anuidadesAction(){
        $this->layout('layout/cliente');
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $serviceAssociado = $this->getServiceLocator()->get('Associado');
        $anuidades = $serviceAssociado->getPagamentos(array('cliente' => $usuario['cliente']))->toArray();
        
        //paginação
        $paginator = new Paginator(new ArrayAdapter($anuidades));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(40);
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

        return new ViewModel(array(
            'anuidades'    =>  $paginator,
            'associado'     =>  $associado,
            'questionario'  =>  $questionario
        ));
    }

    public function uploadcomprovanteanuidadeAction(){

        $formArquivo = new formComprovante('frmArquivo');
        $idAnuidade = $this->params()->fromRoute('idAnuidade');
        $anuidade = $this->getServiceLocator()->get('AssociadoAnuidade')->getRecord($idAnuidade);

        //pesquisar associado
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        //se não admin admin
        if($usuario['id_usuario_tipo'] != 1){
          $this->layout('layout/cliente');
        }

        //se cliente
        if($usuario['id_usuario_tipo'] == 2){
          $associado = $this->getServiceLocator()->get('Associado')->getAssociados(array('cliente' => $usuario['cliente']))->current();
        }else{
          $associado = $this->getServiceLocator()->get('Associado')->getAssociados(array('cliente' => $this->params()->fromRoute('idCliente')))->current();
          if($usuario['id_usuario_tipo'] == 3 && $associado['empresa'] != $usuario['empresa']){
            $this->flashMessenger()->addWarningMessage('Anuidade não encontrada!');
            return $this->redirect()->toRoute('listaPagamentosAssociados');
          }
        }
        
        //pesquisar pagamento
        $serviceComprovante = $this->getServiceLocator()->get('AssociadoPagamentoComprovante');
        $comprovante = $serviceComprovante->getRecordFromArray(array('anuidade' => $idAnuidade, 'associado' => $associado['id']));
        if($comprovante){
            $formArquivo->setData(array('observacoes' => $comprovante['observacoes']));
        }
        //POST
        if($this->getRequest()->isPost()){
            $formArquivo->setData($this->getRequest()->getPost());
            if($formArquivo->isValid()){
                $dados = $formArquivo->getData();
                
                //upload do comprovante
                $files = $this->getRequest()->getfiles()->toArray();
                $caminhoArquivo = $this->uploadComprovante($files, $associado, $idAnuidade);
                if(!$comprovante){
                    $serviceComprovante->insert(array(
                        'anuidade'      =>  $idAnuidade,
                        'associado'     =>  $associado['id'],
                        'arquivo'       =>  $caminhoArquivo,
                        'observacoes'   =>  $dados['observacoes']
                    ));

                    $this->flashMessenger()->addSuccessMessage('Comprovante inserido com sucesso!');
                }else{
                  $serviceComprovante->update(array(
                      'arquivo'     =>  $caminhoArquivo,
                      'observacoes' =>  $dados['observacoes']
                  ), 
                  array(
                    'anuidade'  =>  $idAnuidade,
                    'associado' =>  $associado['id'],
                  ));
                  $this->flashMessenger()->addSuccessMessage('Comprovante alterado com sucesso!');
                }

                //se cliente
                if($usuario['id_usuario_tipo'] == 2){
                  return $this->redirect()->toRoute('anuidadesCliente');
                }else{
                  return $this->redirect()->toRoute('listaPagamentosAssociados');
                }
            }
        }

        return new ViewModel(array(
            'formArquivo'   =>  $formArquivo,
            'anuidade'      =>  $anuidade,
            'comprovante'   =>  $comprovante,
            'usuario'       =>  $usuario
        ));
    }

    public function pagaranuidadeAction(){
        $idAssociado = $this->params()->fromRoute('associado');
        $idAnuidade = $this->params()->fromRoute('anuidade');

        $pagamento = $this->getServiceLocator()->get('Associado')->getPagamento($idAssociado, $idAnuidade);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'pagamento'     => $pagamento, 
            'idAssociado'   => $idAssociado, 
            'idAnuidade'    => $idAnuidade
        ));
        return $view;
    }

    public function arquivosAction(){
        $this->layout('layout/cliente');
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        
        $serviceAssociado = $this->getServiceLocator()->get('Associado');
        $arquivos = $serviceAssociado->getArquivosAssociado(array('cliente' => $usuario['cliente']))->toArray();
        
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
            //incluir arquivos individuais aos arquivos
            $arquivosIndividuais = $this->getServiceLocator()
                ->get('AssociadoArquivoIndividual')->
                getRecords($associado['id'], 'associado', array('*'), 'descricao');
                
            foreach ($arquivosIndividuais as $arquivo) {
                $arquivos[] = array(
                    'id_arquivo'         => $arquivo['id'],
                    'nome_fantasia'     => '-',
                    'nome_categoria'    => 'Arquivo individual',
                    'descricao_arquivo' => $arquivo['descricao'],
                    'idAssociado'       => $arquivo['associado'],
                );
            }

        }

        //paginação
        $paginator = new Paginator(new ArrayAdapter($arquivos));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(40);
        $paginator->setPageRange(5);



        return new ViewModel(array(
            'arquivos'    =>  $paginator,
            'associado'     =>  $associado,
            'questionario'  =>  $questionario
        ));
    }

    public function questionariosAction(){
        $this->layout('layout/cliente');
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        $associado = $this->getServiceLocator()->get('Associado')->getRecord($usuario['cliente'], 'cliente');

        $serviceQuestionario = $this->getServiceLocator()->get('Questionario');
        $questionarios = $serviceQuestionario->getRespondidosByAssociado($associado->id)->toArray();

        //paginação
        $paginator = new Paginator(new ArrayAdapter($questionarios));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(40);
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

        return new ViewModel(array(
            'questionarios'    =>  $paginator,
            'associado'     =>  $associado,
            'questionario'  =>  $questionario
        ));
    }

    public function visualizarquestionarioAction(){
        $idQuestionario = $this->params()->fromRoute('id');
        $respostas = $this->getServiceLocator()
            ->get('QuestionarioAssociadoResponder')
            ->getRespostasQuestionario($idQuestionario)->toArray();

        if(count($respostas) == 0){
            $this->flashMessenger()->addWarningMessage('Questionário/pesquisa não encontrados!');
            return $this->redirect()->toRoute('questionariosCliente');
        }

        $questionario = $this->getServiceLocator()->get('Questionario')->getQuestoes($respostas[0]['id_questionario']);
        $formQuestionario = new formResponder('frmResponder', $questionario);

        $dadosForm = array();
        foreach ($respostas as $resposta) {
            if($resposta['tipo_questao'] == 'F'){
                //questão fechada
                $dadosForm['radio_'.$resposta['id_questao']] = $resposta['alternativa'];

            }else{
                //questão aberta
                $dadosForm['text_'.$resposta['alternativa']] = $resposta['resposta_aberta'];
            }
        }

        $formQuestionario->setData($dadosForm);
        $primeiraQuestao = $questionario[key($questionario)];

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array(
            'formQuestionario'  =>  $formQuestionario,
            'questionario'      =>  $questionario,
            'primeiraQuestao'   =>  $primeiraQuestao,
            'idQuestionario'    =>  $respostas[0]['id_questionario'],
            'dadosForm'         =>  $dadosForm
        ));
        return $view;
    }

    public function meusdadosAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $this->layout('layout/cliente');

        $formCliente = new formAssociado('formAssociado', $this->getServiceLocator(), true);
        $serviceAssociado = $this->getServiceLocator()->get('Associado');
        $cliente = $this->getServiceLocator()->get('Cliente')->getClienteById($usuario['cliente']);

        if(!$cliente){
            $this->flashMessenger()->addWarningMessage('Dados não encontrados, favor entrar em contato com o administrador!');
            return $this->redirect()->toRoute('inscricoesCliente');
        }

        $formCliente->setData($cliente);

        if($this->getRequest()->isPost()){
            $formCliente->setData($this->getRequest()->getPost());
            if($formCliente->isValid()){
                $dados = $formCliente->getData();
                unset($dados['cpf']);
                unset($dados['email']);
                $result = $this->getServiceLocator()->get('Cliente')->update($dados, array('id' => $usuario['cliente']));
                if($result){
                    $this->flashMessenger()->addSuccessMessage('Dados alterados com sucesso!');
                }else{
                    $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao alterar seus dados, por favor tente novamente!');
                }
                return $this->redirect()->toRoute('meusDadosCliente'); 
            }
        }

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

        return new ViewModel(array(
            'formCliente'   => $formCliente, 
            'associado'     => $associado,
            'questionario'  => $questionario,
            'cliente'       => $cliente
        ));
    }

    public function listarvideosAction(){
        $this->layout('layout/cliente');
        $usuario = $this->getServiceLocator()->get('session')->read();
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
        $videos = $this->getServiceLocator()->get('EventoVideo')->getVideos(true);

        return new ViewModel(array(
            'videos'        =>  $videos,
            'associado'     =>  $associado,
            'questionario'  =>  $questionario
        ));
    }

    public function visualizarvideoAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $videos = $this->getServiceLocator()->get('EventoVideo')->getVideos();
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('videos'  =>  $videos->toArray()));
        return $view;
    }

    public function uploadComprovante($arquivos, $associado, $idAnuidade){
        $caminho = 'public/arquivos/associados';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho .= '/'.$associado['id'];
        if(!file_exists($caminho)){
            mkdir($caminho);
        }        

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/anuidade_'.$idAnuidade.'.'.$extensao)){
                    return $caminho.'/anuidade_'.$idAnuidade.'.'.$extensao;
                }
            }
        }

        return '';
    }

    public function uploadComprovanteInscricao($arquivos, $cliente, $idInscricao){
        $caminho = 'public/arquivos/cliente';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho .= '/'.$idInscricao;
        if(!file_exists($caminho)){
            mkdir($caminho);
        }        

        foreach ($arquivos as $nomeArquivo => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                $extensao = $this->getExtensao($arquivo['name']);
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/inscricao_'.$idInscricao.'.'.$extensao)){
                    return $caminho.'/inscricao_'.$idInscricao.'.'.$extensao;
                }
            }
        }

        return '';
    }

    

}
?>