<?php

namespace Trabalhos\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Trabalhos\Form\CategoriaTrabalho as formCategoria;
use Trabalhos\Form\VisualizarTrabalho as formVisualizarTrabalho;
use Trabalhos\Form\PesquisaTrabalho as formPesquisaTrabalho;

use Trabalhos\Form\EnviarTrabalho as formTrabalho;
use Trabalhos\Form\EnviarPoster as formPoster;

class TrabalhoController extends BaseController
{

    public function categoriastrabalhoAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $idEvento = $this->params()->fromRoute('idEvento');
        if(!empty($usuario['empresa'])){
            $this->layout('layout/empresa');
            
            //verificar se evento é da empresa
            $evento = $this->getServiceLocator()->get('Evento')->getRecord($idEvento);
            if($evento && $evento['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Evento não encontrado!');
                return $this->redirect()->toRoute('evento');
            }

        }

        $categorias = $this->getServiceLocator()->get('InscricaoTrabalhoCategoria')->getRecords($idEvento, 'evento', array('*'), 'categoria');
        
        return new ViewModel(array(
            'categorias' => $categorias,
            'idEvento'   => $idEvento
        ));
    }

    public function novacategoriatrabalhoAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $idEvento = $this->params()->fromRoute('idEvento');
        if(!empty($usuario['empresa'])){
            $this->layout('layout/empresa');

            //verificar se evento é da empresa
            $evento = $this->getServiceLocator()->get('Evento')->getRecord($idEvento);
            if($evento && $evento['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Evento não encontrado!');
                return $this->redirect()->toRoute('evento');
            }
        }

        $formCategoria = new formCategoria('frmCategoria');

        if($this->getRequest()->isPost()){
            $formCategoria->setData($this->getRequest()->getPost());
            if($formCategoria->isValid()){
                $dados = $formCategoria->getData();
                $dados['evento'] = $idEvento;
                $this->getServiceLocator()->get('InscricaoTrabalhoCategoria')->insert($dados);
                $this->flashMessenger()->addSuccessMessage('Categoria inserida com sucesso!');
                return $this->redirect()->toRoute('categoriasTrabalho', array('idEvento' => $idEvento));
            }
        }

        return new ViewModel(array(
            'formCategoria' => $formCategoria,
            'idEvento'      => $idEvento
        ));

    }

    public function deletarcategoriatrabalhoAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();
        $categoria = $this->getServiceLocator()->get('InscricaoTrabalhoCategoria')->getRecord($this->params()->fromRoute('idCategoria'));
        if(!empty($usuario['empresa'])){
            $this->layout('layout/empresa');

            //verificar se evento é da empresa
            $evento = $this->getServiceLocator()->get('Evento')->getRecord($categoria->evento);
            if($evento && $evento['empresa'] != $usuario['empresa']){
                $this->flashMessenger()->addWarningMessage('Evento não encontrado!');
                return $this->redirect()->toRoute('evento');
            }
        }

        $res = $this->getServiceLocator()->get('InscricaoTrabalhoCategoria')->delete(array('id' => $categoria['id']));
        if($res){
            $this->flashMessenger()->addSuccessMessage('Categoria excluída com sucesso!');
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao excluir a categoria, por favor tente novamente!');
        }
        return $this->redirect()->toRoute('categoriasTrabalho', array('idEvento' => $categoria['evento']));
    }


    public function visualizartrabalhoAction(){
        $idInscricao = $this->params()->fromRoute('idInscricao');
        $usuario = $this->getServiceLocator()->get('session')->read();
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getInscricaoById($idInscricao, $usuario['empresa']);
        if(!empty($usuario['empresa'])){
            $container->dados['empresa'] = $usuario['empresa'];
            $this->layout('layout/empresa');
            
            //verificar se trabalho é da empresa
            if(!$inscricao){
                $this->flashMessenger()->addWarningMessage('Trabalho não encontrado!');
                return $this->redirect()->toRoute('listarTrabalhos');
            }
        }

        $trabalho = $this->getServiceLocator()->get('Inscricao')->getTrabalhos(array(), $idInscricao)->current();
        $avaliacoes = $this->getServiceLocator()->get('EventoTrabalhoAvaliador')
            ->getRecordsFromArray(array('avaliacao' => $trabalho['id_trabalho']))
            ->toArray();

        //se veio post, aprovar o trabalho
        if($this->getRequest()->isPost()){
            if($trabalho->trabalho_aprovado == 'N'){
                $this->getServiceLocator()->get('InscricaoTrabalho')
                    ->update(array('aprovado' => 'S'), array('inscricao' => $idInscricao));
                
                $mailer = $this->getServiceLocator()->get('mailer');
                $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($inscricao->cliente);
                if (!empty($inscricao->email_cliente)) {
                    $evento = $this->getServiceLocator()->get('Evento')->getEventoById($inscricao->evento);
                    $res = $mailer->mailUser(
                        $inscricao->email_cliente, 
                        $trabalho['titulo_trabalho'], 
                        parent::mensagemEmail($evento->mensagem_trabalho, $cliente)
                    );
                }
            }else{
                $this->getServiceLocator()->get('InscricaoTrabalho')
                    ->update(array('aprovado' => 'N'), array('inscricao' => $idInscricao));
            }
            $this->flashMessenger()->addSuccessMessage('Status alterado com sucesso!');
            return $this->redirect()->toRoute('listarTrabalhos');
        }

        $integrantesTrabalho = $this->getServiceLocator()->get('InscricaoTrabalhoIntegrante')->getRecords($trabalho->id_trabalho, 'trabalho');
        $coautores = '';
        foreach ($integrantesTrabalho as $integrante) {
            $coautores .= empty($integrante->cpf) 
                ? $integrante->nome . PHP_EOL 
                : $integrante->cpf . ' - ' . $integrante->nome . PHP_EOL;
        }
        $coautores = htmlspecialchars($coautores, ENT_QUOTES, 'UTF-8');

        $trabalho->autores_trabalho = $coautores;
        $formTrabalho = new formVisualizarTrabalho('frmTrabalho');
        $formTrabalho->setData($trabalho);

        return new ViewModel(array(
            'formTrabalho'  =>  $formTrabalho,
            'trabalho'      =>  $trabalho,
            'idInscricao'   =>  $idInscricao,
            'avaliacoes'    =>  $avaliacoes
        ));
    }
    public function listartrabalhosAction(){
        $usuario = $this->getServiceLocator()->get('session')->read();        
        $formPesquisa = new formPesquisaTrabalho('fromPesquisa', $this->getServiceLocator(), $usuario['empresa']);

        $dados = array();
        $container = new Container();
        if(!isset($container->dados)){
            $container->dados = array();
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['limpar'])){
                unset($container->dados);
                $this->redirect()->toRoute('listarTrabalhos');
            }else{
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $container->dados = $formPesquisa->getData();
                }
                
            }
        }
        $formPesquisa->setData($container->dados);

        
        if(!empty($usuario['empresa'])){
            $container->dados['empresa'] = $usuario['empresa'];
            $this->layout('layout/empresa');
        }

        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        
        $trabalhos = $serviceInscricao->getTrabalhos($container->dados)->toArray();

        //paginação
        $paginator = new Paginator(new ArrayAdapter($trabalhos));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(40);
        $paginator->setPageRange(5);
        
        return new ViewModel(array(
                    'formPesquisa'     => $formPesquisa,
                    'trabalhos'        => $paginator,
                    'empresa'          => $usuario['empresa'],
                    'clientes'         => $trabalhos,
                    'dadosPesquisa'    => $dados
                ));
    }

    public function deletartrabalhoAction(){
        $idInscricao = $this->params()->fromRoute('idInscricao');
        $usuario = $this->getServiceLocator()->get('session')->read();
        if(!empty($usuario['empresa'])){
            //verificar se trabalho é da empresa
            $inscricao = $serviceInscricao->getInscricaoById($idInscricao, $usuario['empresa']);
            if(!$inscricao){
                $this->flashMessenger()->addWarningMessage('Trabalho não encontrado!');
                return $this->redirect()->toRoute('listarTrabalhos');
            }
        }

        //detelar o trabalho
        $res = $this->getServiceLocator()->get('InscricaoTrabalho')->deletarTrabalho($idInscricao);
        if($res == true){
            $this->flashMessenger()->addSuccessMessage('Trabalho excluído com sucesso!');
            return $this->redirect()->toRoute('listarTrabalhos');
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao excluir trabalho, por favor tente novamente!');
            return $this->redirect()->toRoute('visualizarTrabalho', array('idInscricao' => $idInscricao));
        }
        return new ViewModel(array());
    }







    public function enviartrabalhoAction(){
        $this->layout('layout/cliente');
        $usuario = $this->getServiceLocator()->get('session')->read();

        //verificar se inscrição é realmente do inscrito
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($this->params()->fromRoute('idInscricao'));
        if($inscricao['cliente'] != $usuario['cliente']){
            $this->flashMessenger()->addWarningMessage('Inscrição não encontrada!');
            return $this->redirect()->toRoute('inscricoesCliente');
        }
        $formTrabalho = new formTrabalho('frmTrabalho', $this->getServiceLocator(), $inscricao['evento']);

        if($inscricao['status_pagamento'] != 2 && $inscricao['status_pagamento'] != 8 && $inscricao['status_pagamento'] != 9){
            $this->flashMessenger()->addWarningMessage('Apenas inscrições com pagamento confirmado podem enviar trabalhos!');
            return $this->redirect()->toRoute('inscricoesCliente');
        }
        
        $trabalho = $this->getServiceLocator()->get('InscricaoTrabalho')->getRecord($this->params()->fromRoute('idInscricao'), 'inscricao');        
        if($trabalho){
            $this->flashMessenger()->addWarningMessage('Um trabalho já foi enviado, favor entrar em contato com o administrador!');
            return $this->redirect()->toRoute('inscricoesCliente');
        }
        $erroPDF = false;
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost()->toArray();
            $formTrabalho->setData($dados);

            if($formTrabalho->isValid()){
                $files = $this->getRequest()->getfiles()->toArray();
                foreach ($files as $file) {
                    if(!empty($file['name']) && $file['type'] != 'application/pdf'){
                        $erroPDF = true;
                        break;
                    }
                }
                
                if($erroPDF == false){
                    $files = $this->uploadPdfTrabalhos($files, $this->params()->fromRoute('idInscricao'));
                    $dadosInsert = $formTrabalho->getData();
                    $dadosInsert['pode_postar'] = $dados['pode_postar'];
                    $res = $this->getServiceLocator()
                        ->get('InscricaoTrabalho')
                        ->salvarTrabalho($dadosInsert, $files, $this->params()->fromRoute('idInscricao'));

                    if($res == true){
                        $this->flashMessenger()->addSuccessMessage('Trabalho salvo com sucesso!');
                        return $this->redirect()->toRoute('inscricoesCliente');
                    }
                }

            }
        }else{
            $formTrabalho->setData(array(
                'pode_postar' => 'S'
            ));
        }

        //pesquisar associado
        $associado = $this->getServiceLocator()->get('Associado')->getAssociados(array('cliente' => $usuario['cliente']))->current();
        $questionario = false;
        if($associado){
            $questionario = $this->getServiceLocator()->get('Questionario')->getAvaliacaoAberta($associado)->current();
        }

        return new ViewModel(array(
            'formTrabalho' => $formTrabalho,
            'associado'    => $associado,
            'questionario' => $questionario,
            'erroPDF'      => $erroPDF
        ));
    }

    public function vincularposterAction(){
        $this->layout('layout/cliente');
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getRecord($this->params()->fromRoute('idInscricao'));
        $trabalho = $this->getServiceLocator()->get('InscricaoTrabalho')->getRecord($this->params()->fromRoute('idInscricao'), 'inscricao');
        $formPoster = new formPoster('frmPoster', $this->getServiceLocator(), $inscricao['evento']);

        $erroPDF = false;
        if($this->getRequest()->isPost()){
            $formPoster->setData($this->getRequest()->getPost());
            if($formPoster->isValid()){
                $file = $this->getRequest()->getfiles()->toArray();
                
                if($file['poster']['type'] != 'application/pdf'){
                    $erroPDF = true;
                }
                
                if($erroPDF == false){
                    //fazer upload e mandar para model salvar em transaction
                    $file = $this->uploadPoster($file, $this->params()->fromRoute('idInscricao'), 'poster_');

                    $res = $this->getServiceLocator()
                        ->get('InscricaoTrabalho')
                        ->update(array('poster' => $file['arquivo']), array('id' => $trabalho['id']));

                    if($res == true){
                        $this->flashMessenger()->addSuccessMessage('Pôster salvo com sucesso!');
                        return $this->redirect()->toRoute('inscricoesCliente');
                    }
                }

            }
        }

        $formPoster->setData($trabalho);
        return new ViewModel(array(
            'erroPDF'      => $erroPDF,
            'formPoster'   => $formPoster,
            'trabalho'     => $trabalho
        ));

    }

    public function listartrabalhospublicAction(){
        $this->layout('layout/vazio');
        $formPesquisa = new formPesquisaTrabalho('fromPesquisa', $this->getServiceLocator(), NULL);

        //se vier post é uma pesquisa
        $container = new Container();
        //unset($container->dados);
        if(!isset($container->dados)){
            $container->dados = array();
        }
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            if(isset($dados['limpar'])){
                unset($container->dados);
                $this->redirect()->toRoute('listarTrabalhosPublic');
            }else{
                $formPesquisa->setData($dados);
                if($formPesquisa->isValid()){
                    $container->dados = $formPesquisa->getData();
                }
                
            }
        }
        $formPesquisa->setData($container->dados);
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');        
        $container->dados['visualizar_trabalhos'] = 'S';
        $container->dados['aprovado'] = 'S';
        $trabalhos = $serviceInscricao->getTrabalhos($container->dados)->toArray();
        $paginator = new Paginator(new ArrayAdapter($trabalhos));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(40);
        $paginator->setPageRange(5);
        return new ViewModel(array(
                    'formPesquisa'     => $formPesquisa,
                    'trabalhos'        => $paginator,
                    'clientes'         => $trabalhos
                ));
    }

    public function visualizartrabalhospublicAction(){
        $this->layout('layout/vazio');
        $idInscricao = $this->params()->fromRoute('idInscricao');
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getInscricaoById($idInscricao);
        $trabalho = $this->getServiceLocator()->get('Inscricao')->getTrabalhos(array(), $idInscricao)->current();

        $formTrabalho = new formVisualizarTrabalho('frmTrabalho');        
        $formTrabalho->setData($trabalho);
        
        return new ViewModel(array(
            'formTrabalho'  =>  $formTrabalho,
            'trabalho'      =>  $trabalho,
            'idInscricao'   =>  $idInscricao,
            'inscricao'     =>  $inscricao
        ));
    }


    public function uploadPdfTrabalhos($arquivos, $idInscricao){
        
        $caminho = 'public/arquivos/cliente';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho .= '/'.$idInscricao;
        if(!file_exists($caminho)){
            mkdir($caminho);
        }       

        $caminho .= '/trabalho';
        if(!file_exists($caminho)){
            mkdir($caminho);
        } 

        $uploadedFiles = array();
        foreach ($arquivos as $key => $arquivo) {
            if(!empty($arquivo['tmp_name'])){
                if(move_uploaded_file($arquivo['tmp_name'], $caminho.'/'.$key.'-'.$arquivo['name'])){
                    $uploadedFiles[] = array('arquivo' => $caminho.'/'.$key.'-'.$arquivo['name'], 'nome' => $arquivo['name']);
                }
            }
        }

        return $uploadedFiles;
    }

    public function uploadPoster($arquivo, $idInscricao){
        $caminho = 'public/arquivos/cliente';
        if(!file_exists($caminho)){
            mkdir($caminho);
        }

        $caminho .= '/'.$idInscricao;
        if(!file_exists($caminho)){
            mkdir($caminho);
        }       

        $caminho .= '/trabalho';
        if(!file_exists($caminho)){
            mkdir($caminho);
        } 

        $uploadedFiles = array();
        if(!empty($arquivo['poster']['tmp_name'])){
            if(move_uploaded_file($arquivo['poster']['tmp_name'], $caminho.'/'.'poster_'.$arquivo['poster']['name'])){
                $uploadedFiles = array('arquivo' => $caminho.'/'.'poster_'.$arquivo['poster']['name'], 'nome' => 'poster_'.$arquivo['poster']['name']);
            }
        }
        

        return $uploadedFiles;
    }


    public function downloadarquivotrabalhoAction(){        
        $trabalho = $this->getServiceLocator()->get('InscricaoTrabalho')->getRecord($this->params()->fromRoute('idArquivo'));
        $fileName = $trabalho[$this->params()->fromRoute('nomeCampo')];
        
        if(!is_file($fileName)) {
            //Não foi possivel encontrar o arquivo
        }
        $fileContents = file_get_contents($fileName);

        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();

        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'application/pdf')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="'.$fileName.'"')
            ->addHeaderLine('Content-Length', filesize($fileName));
        return $this->response;
    }

    public function recalculartrabalhosAction() {
        $container = new Container();
        if (empty($container->dados['evento'])) {
            $this->flashMessenger()->addWarningMessage('Selecione um evento!');
            return $this->redirect()->toRoute('listarTrabalhos');
        }
        $trabalhos = $this->getServiceLocator()->get('Inscricao')->getTrabalhos($container->dados)->toArray();
        foreach ($trabalhos as $trabalho) {
            $avaliacoes = $this->getServiceLocator()
                ->get('EventoTrabalhoAvaliador')
                ->getRecordsFromArray(array('avaliacao' => $trabalho['id_trabalho']))
                ->toArray();
            $medianas = parent::gerarMediasAvaliacoes($avaliacoes);
            $this->getServiceLocator()->get('InscricaoTrabalho')
                ->update(
                    array(
                        'pontuacao' => $medianas['total']
                    ), 
                    array(
                        'inscricao' => $trabalho['id_inscricao'],
                    )
                );
        }
        $this->flashMessenger()->addSuccessMessage('Avaliações recalculadas com sucesso!');
        return $this->redirect()->toRoute('listarTrabalhos');
    }

    public function exportartrabalhosAction() {
        $container = new Container();
        $trabalhos = $this->getServiceLocator()->get('Inscricao')->getTrabalhos($container->dados)->toArray();

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Time Sistemas");
        $objPHPExcel->getProperties()->setTitle("Trabalhos");
        $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de eventos.");
        $objPHPExcel->getActiveSheet()->setTitle('Trabalhos');
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Evento');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Nome inscrito');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'CPF');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Categoria');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Título');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Afiliação');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Descritor1');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Descritor2');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Descritor3');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Coautores');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Apoio financeiro');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'Resumo');
        
        $numLinha = 1;
        foreach ($trabalhos as $trabalho) {
            $numLinha++;
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numLinha, $trabalho['nome_evento']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numLinha, $trabalho['nome_completo']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numLinha, $trabalho['cpf']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numLinha, $trabalho['nome_categoria']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numLinha, $trabalho['titulo_trabalho']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$numLinha, $trabalho['afiliacao']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$numLinha, $trabalho['descritor1']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$numLinha, $trabalho['descritor2']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$numLinha, $trabalho['descritor3']);

            $integrantesTrabalho = $this->getServiceLocator()->get('InscricaoTrabalhoIntegrante')->getRecords($trabalho['id_trabalho'], 'trabalho');
            $coautores = '';
            foreach ($integrantesTrabalho as $integrante) {
                $coautores .= empty($integrante->cpf) 
                    ? $integrante->nome . ' | ' 
                    : $integrante->cpf . ' - ' . $integrante->nome . ' | ';
            }
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$numLinha, $coautores);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$numLinha, $trabalho['apoio_financeiro']);
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$numLinha, $trabalho['resumo_trabalho']);
        }

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $diretorio = 'public/relatorios';
        if(!file_exists($diretorio)){
            mkdir($diretorio);
        }
        $fileName = $diretorio.'/trabalhos.xlsx';
        $objWriter->save($fileName);

        $fileContents = file_get_contents($fileName);
        $response = $this->getResponse();
        $response->setContent($fileContents);

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($fileContents));
        return $this->response;
    }

}

