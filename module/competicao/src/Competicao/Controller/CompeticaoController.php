<?php

namespace Competicao\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Competicao\Form\PesquisarCompeticao as formPesquisa;
use Competicao\Form\Competicao as formCompeticao;
use Competicao\Form\Sala as formSala;
use Competicao\Form\Chaveamento as formChaveamento;
use Competicao\Form\PesquisarChaveamento as formPesquisaChaveamento;

use Competicao\Form\Avaliar as formAvaliar;

class CompeticaoController extends BaseController
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
              $this->redirect()->toRoute('competicoes');
          }else{
              $formPesquisa->setData($dados);
              if($formPesquisa->isValid()){
                  $container->dados = $formPesquisa->getData();
              }
              
          }
      }
      $formPesquisa->setData($container->dados);
      $competicoes = $this->getServiceLocator()->get('Competicao')->getCompeticoes($container->dados)->toArray();

      //paginação
      $paginator = new Paginator(new ArrayAdapter($competicoes));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(40);
      $paginator->setPageRange(5);

      return new ViewModel(array(
        'formPesquisa'  =>  $formPesquisa,
        'competicoes'   =>  $paginator
      ));
    }

    public function novoAction()
    { 
      $formCompeticao = new formCompeticao('formCompeticao');
    
      if($this->getRequest()->isPost()){
        $formCompeticao->setData($this->getRequest()->getPost());
        if($formCompeticao->isValid()){
          $dados = $formCompeticao->getData();
          $dados['descricao'] = htmlspecialchars($dados['descricao']);
          $idCompeticao = $this->getServiceLocator()->get('Competicao')->insert($dados);
          $this->flashmessenger()->addSuccessMessage('Competição inserida com sucesso!');
          return $this->redirect()->toRoute('competicaoAlterar', array('idCompeticao' => $idCompeticao));
        }
      }

      return new ViewModel(array(
        'formCompeticao'    =>  $formCompeticao
      ));
    }

    public function alterarAction(){
      $formCompeticao = new formCompeticao('formCompeticao');
      $competicao = $this->getServiceLocator()->get('Competicao')->getRecord($this->params()->fromRoute('idCompeticao'));
      $formCompeticao->setData($competicao);

      //post
      if($this->getRequest()->isPost()){
        $formCompeticao->setData($this->getRequest()->getPost());
        if($formCompeticao->isValid()){
          $dados = $formCompeticao->getData();
          $dados['descricao'] = htmlspecialchars($dados['descricao']);
          $this->getServiceLocator()->get('Competicao')->update($dados, array('id' => $competicao->id));
          $this->flashmessenger()->addSuccessMessage('Competição alterada com sucesso!');
          return $this->redirect()->toRoute('competicaoAlterar', array('idCompeticao' => $competicao->id));
        }
      }

      return new ViewModel(array(
        'formCompeticao'    =>  $formCompeticao,
        'competicao'        =>  $competicao
      ));
    }

    public function salasAction(){
      $idCompeticao = $this->params()->fromRoute('idCompeticao');
      $idSala = $this->params()->fromRoute('idSala');
      $formSala = new formSala('frmSala');
      $sala = false;
      if($idSala){
        $sala = $this->getServiceLocator()->get('CompeticaoSalas')->getRecord($idSala);
        $formSala->setData($sala);
      }

      if($this->getRequest()->isPost()){
        $formSala->setData($this->getRequest()->getPost());
        if($formSala->isValid()){
          $dados = $formSala->getData();
          $dados['competicao'] = $idCompeticao;
          if($sala){
            //UPDATE
            $this->getServiceLocator()->get('CompeticaoSalas')->update($dados, array('id' => $sala->id));
            $this->flashmessenger()->addSuccessMessage('Sala alterada com sucesso!');
            return $this->redirect()->toRoute('competicaoSalas', array('idCompeticao' => $idCompeticao));
          }else{
            //INSERT
            $this->getServiceLocator()->get('CompeticaoSalas')->insert($dados);
            $this->flashmessenger()->addSuccessMessage('Sala cadastada com sucesso!');
            return $this->redirect()->toRoute('competicaoSalas', array('idCompeticao' => $idCompeticao));
          }
        }
      }
      
      $salas = $this->getServiceLocator()
        ->get('CompeticaoSalas')
        ->getRecords($idCompeticao, 'competicao', array('*'), 'nome');
      $competicao = $this->getServiceLocator()->get('Competicao')->getRecord($idCompeticao);

      return new ViewModel(array(
        'formSala'      =>  $formSala,
        'salas'         =>  $salas,
        'competicao'    =>  $competicao
      ));
    }

    public function deletarsalasAction(){
      $idCompeticao = $this->params()->fromRoute('idCompeticao');
      $res = $this->getServiceLocator()->get('CompeticaoSalas')->delete(array('id' => $this->params()->fromRoute('idSala')));
      if($res){
        $this->flashmessenger()->addSuccessMessage('Sala excluída com sucesso!');
      }else{
        $this->flashmessenger()->addErrorMessage('Erro ao excluir sala!');
      }
      return $this->redirect()->toRoute('competicaoSalas', array('idCompeticao' => $idCompeticao));
    }


    public function chaveamentoAction(){

      $idCompeticao = $this->params()->fromRoute('idCompeticao');
      $formChaveamento = new formChaveamento('frmChaveamento', $this->getServiceLocator(), $idCompeticao);
      $idChave = $this->params()->fromRoute('idChave');
      $chave = false;
      if($idChave){
        $chave = $this->getServiceLocator()->get('CompeticaoChaveamento')->getRecord($idChave);
        $formChaveamento->setData($chave);
      }

      //POST
      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        $formChaveamento->setData($dados);
        if($formChaveamento->isValid()){
          $dados = $formChaveamento->getData();
          if($chave){
            //UPDATE
            $this->getServiceLocator()->get('CompeticaoChaveamento')->update($dados, array('id' => $chave->id));
            $this->flashmessenger()->addSuccessMessage('Rodada alterada com sucesso!');
            return $this->redirect()->toRoute('competicaoChaveamento', array('idCompeticao' => $idCompeticao));
          }else{
            //INSERT
            $dados['competicao'] = $idCompeticao;
            $this->getServiceLocator()->get('CompeticaoChaveamento')->insert($dados);
            $this->flashmessenger()->addSuccessMessage('Rodada inserida com sucesso!');
            return $this->redirect()->toRoute('competicaoChaveamento', array('idCompeticao' => $idCompeticao));
          }
        }
      }

      $rodadas = $this->getServiceLocator()->get('CompeticaoChaveamento')->getChaves($idCompeticao);
      $competicao = $this->getServiceLocator()->get('Competicao')->getRecord($idCompeticao);

      return new ViewModel(array(
        'formChaveamento' =>  $formChaveamento,
        'rodadas'         =>  $rodadas,
        'competicao'      =>  $competicao   
      ));
    }

    public function deletarrodadaAction(){
      $idCompeticao = $this->params()->fromRoute('idCompeticao');
      $idChave = $this->params()->fromRoute('idChave');
      
      $this->getServiceLocator()->get('CompeticaoChaveamento')->delete(array('id' => $idChave));
      $this->flashmessenger()->addSuccessMessage('Rodada excluída com sucesso!');
      return $this->redirect()->toRoute('competicaoChaveamento', array('idCompeticao' => $idCompeticao));
    }

    public function classificacaoAction(){
      $this->layout('layout/chaveamento');
      $faculdades = $this->getServiceLocator()->get('FaculdadeNota')->getClassificacao($this->params()->fromRoute('idCompeticao'));
      $competicao = $this->getServiceLocator()->get('Competicao')->getRecord($this->params()->fromRoute('idCompeticao'));
      
      return new ViewModel(array(
        'faculdades'   => $faculdades,
        'competicao'   => $competicao
      ));
    }

    public function visualizarchaveamentoAction(){
      $this->layout('layout/chaveamento');
      $competicao = $this->getServiceLocator()->get('Competicao')->getRecord($this->params()->fromRoute('idCompeticao'));
      $rodadas = $this->getServiceLocator()->get('CompeticaoChaveamento')->getRodadas($competicao->id);
      $formPesquisa = new formPesquisaChaveamento('frmChaveamento', $this->getServiceLocator(), $competicao->id, $rodadas);

      $dados = array();
      //se vier post é uma pesquisa
      $container = new Container();
      if(!isset($container->dados)){
          $container->dados = array();
      }
      if($this->getRequest()->isPost()){
          $dados = $this->getRequest()->getPost();
          if(isset($dados['limpar'])){
              unset($container->dados);
              $this->redirect()->toRoute('competicaoVisualizarChaveamento', array('idCompeticao' => $competicao->id));
          }else{
              $formPesquisa->setData($dados);
              if($formPesquisa->isValid()){
                  $container->dados = $formPesquisa->getData();
              }
              
          }
      }
      $formPesquisa->setData($container->dados);
      if(count($container->dados) > 0){
        $rodadas = $this->getServiceLocator()->get('CompeticaoChaveamento')->getRodadas($competicao->id, $container->dados);
      }
      
      $usuario = $this->getServiceLocator()->get('session')->read();
      
      return new ViewModel(array(
        'formPesquisa'  =>  $formPesquisa,
        'rodadas'       =>  $rodadas,
        'competicao'    =>  $competicao,
        'usuario'       =>  $usuario
      ));
    } 

    public function visualizaravaliacoesAction(){

      $partida = $this->getServiceLocator()->get('CompeticaoChaveamento')->getPartida($this->params()->fromRoute('idChave'));
      $notas = $this->getServiceLocator()->get('CompeticaoNota')->getNotas($partida->id)->toArray();
      if(count($notas) == 0){
        $this->flashmessenger()->addWarningMessage('Nenhuma avaliação encontrada!');
        return $this->redirect()->toRoute('competicaoChaveamento', array('idCompeticao' => $partida->competicao, 'idChave' => $partida->id));
      }
      $formAvaliar = new formAvaliar('frmAvaliar', $this->getServiceLocator(), $partida);
      $numAvaliacao = $this->params()->fromRoute('avaliacao');
      if(!$numAvaliacao){
        $numAvaliacao = 0;
      }
      
      $formAvaliar->setData($notas[$numAvaliacao]);

      return new ViewModel(array(
        'formAvaliar'   =>  $formAvaliar,
        'notas'         =>  $notas,
        'partida'       =>  $partida,
        'numAvaliacao' =>  $numAvaliacao
      ));
    }

    public function deletaravaliacaoAction(){
      $idCompeticao = $this->params()->fromRoute('idCompeticao');
      $idNota = $this->params()->fromRoute('idNota');
      $partida = $this->getServiceLocator()->get('CompeticaoChaveamento')->getRecord($this->params()->fromRoute('idChave'));

      //deletar
      $res = $this->getServiceLocator()->get('CompeticaoNota')->deletar($idNota, $partida);
      if($res == true){
        $this->flashmessenger()->addSuccessMessage('Avaliação excluída com sucesso!');
      }else{
        $this->flashmessenger()->addErrorMessage('Ocorreu algum erro, por favor tente novamente!');
      }

      return $this->redirect()->toRoute('competicaoChaveamento', array('idCompeticao' => $partida->competicao));
    }

    public function exportarcompeticaoAction(){
      $rodadas = $this->getServiceLocator()->get('CompeticaoChaveamento')->getRodadas($this->params()->fromRoute('idCompeticao'));
      
      //gerar relatório
      $objPHPExcel = new \PHPExcel();

      $objPHPExcel->getProperties()->setCreator("Time Sistemas");
      $objPHPExcel->getProperties()->setTitle("Competição");
      $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de eventos.");
      $objPHPExcel->getActiveSheet()->setTitle('Competição');
      $objPHPExcel->setActiveSheetIndex(0);

      $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Painel');
      $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Dia/Hora');
      $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Dia');
      $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Horário');
      $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Sala');
      $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Apelante');
      $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Apelado');
      $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Avaliador 1');
      $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Avaliador 2');
      $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Avaliador 3');
      $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'Orador Apelante 1');
      $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'OC1A1');
      $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'OC1A2');
      $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'OC1A3');
      $objPHPExcel->getActiveSheet()->SetCellValue('O1', 'Orador Apelante 2');
      $objPHPExcel->getActiveSheet()->SetCellValue('P1', 'OC2A1');
      $objPHPExcel->getActiveSheet()->SetCellValue('Q1', 'OC2A2');
      $objPHPExcel->getActiveSheet()->SetCellValue('R1', 'OC2A3');
      $objPHPExcel->getActiveSheet()->SetCellValue('S1', 'Orador Apelado 1');
      $objPHPExcel->getActiveSheet()->SetCellValue('T1', 'OR1A1');
      $objPHPExcel->getActiveSheet()->SetCellValue('U1', 'OR1A2');
      $objPHPExcel->getActiveSheet()->SetCellValue('V1', 'OR1A3');
      $objPHPExcel->getActiveSheet()->SetCellValue('W1', 'Orador Apelado 2');
      $objPHPExcel->getActiveSheet()->SetCellValue('X1', 'OR2A1');
      $objPHPExcel->getActiveSheet()->SetCellValue('Y1', 'OR2A2');
      $objPHPExcel->getActiveSheet()->SetCellValue('Z1', 'OR2A3');
      
      $numLinha = 1;
          
      foreach ($rodadas as $rodada) {
        foreach ($rodada['partidas'] as $partida) {
          $painel = $numLinha;
          $numLinha++;
          $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numLinha, $painel);
          $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numLinha, $painel);
          $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numLinha, $rodada['data']);
          $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numLinha, $rodada['hora']);
          $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numLinha, $partida['nome_sala']);
          $objPHPExcel->getActiveSheet()->SetCellValue('F'.$numLinha, $partida['numero_recorrente']);
          $objPHPExcel->getActiveSheet()->SetCellValue('G'.$numLinha, $partida['numero_recorrido']);

          if(isset($partida['avaliacoes'][0]['nome_avaliador'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$numLinha, $partida['avaliacoes'][0]['nome_avaliador']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][1]['nome_avaliador'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$numLinha, $partida['avaliacoes'][1]['nome_avaliador']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][2]['nome_avaliador'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$numLinha, $partida['avaliacoes'][2]['nome_avaliador']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$numLinha, '-');
          }

          //RECORRENTE 1
          $objPHPExcel->getActiveSheet()->SetCellValue('K'.$numLinha, $partida['orador_recorrente_1']);

          if(isset($partida['avaliacoes'][0]['nota_total_recorrente_1'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$numLinha, $partida['avaliacoes'][0]['nota_total_recorrente_1']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][1]['nota_total_recorrente_1'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$numLinha, $partida['avaliacoes'][1]['nota_total_recorrente_1']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][2]['nota_total_recorrente_1'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$numLinha, $partida['avaliacoes'][2]['nota_total_recorrente_1']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$numLinha, '-');
          }

          //RECORRENTE 2
          $objPHPExcel->getActiveSheet()->SetCellValue('O'.$numLinha, $partida['orador_recorrente_2']);
          if(isset($partida['avaliacoes'][0]['nota_total_recorrente_2'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('P'.$numLinha, $partida['avaliacoes'][0]['nota_total_recorrente_2']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('P'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][1]['nota_total_recorrente_2'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$numLinha, $partida['avaliacoes'][1]['nota_total_recorrente_2']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][2]['nota_total_recorrente_2'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('R'.$numLinha, $partida['avaliacoes'][2]['nota_total_recorrente_2']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('R'.$numLinha, '-');
          }

          //RECORRIDO_1
          $objPHPExcel->getActiveSheet()->SetCellValue('S'.$numLinha, $partida['orador_recorrido_1']);
          if(isset($partida['avaliacoes'][0]['nota_total_recorrido_1'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('T'.$numLinha, $partida['avaliacoes'][0]['nota_total_recorrido_1']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('T'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][1]['nota_total_recorrido_1'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('U'.$numLinha, $partida['avaliacoes'][1]['nota_total_recorrido_1']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('U'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][2]['nota_total_recorrido_1'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('V'.$numLinha, $partida['avaliacoes'][2]['nota_total_recorrido_1']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('V'.$numLinha, '-');
          }

          //RECORRIDO_2
          $objPHPExcel->getActiveSheet()->SetCellValue('W'.$numLinha, $partida['orador_recorrido_2']);
          if(isset($partida['avaliacoes'][0]['nota_total_recorrido_2'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('X'.$numLinha, $partida['avaliacoes'][0]['nota_total_recorrido_2']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('X'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][1]['nota_total_recorrido_2'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$numLinha, $partida['avaliacoes'][1]['nota_total_recorrido_2']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$numLinha, '-');
          }

          if(isset($partida['avaliacoes'][2]['nota_total_recorrido_2'])){
            $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$numLinha, $partida['avaliacoes'][2]['nota_total_recorrido_2']);
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$numLinha, '-');
          }
        }
      }

      //gerar e redir p download
      $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
      $diretorio = 'public/relatorios';
      if(!file_exists($diretorio)){
          mkdir($diretorio);
      }

      $fileName = $diretorio.'/competicao.xlsx';
      $objWriter->save($fileName);
      
      //enviar para download
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

    public function exportarcompetidoresAction(){
      $objPHPExcel = new \PHPExcel();

      $objPHPExcel->getProperties()->setCreator("Time Sistemas");
      $objPHPExcel->getProperties()->setTitle("Oradores da competição");
      $objPHPExcel->getProperties()->setDescription("Relatório gerado pelo sistema de eventos.");
      $objPHPExcel->getActiveSheet()->setTitle('Oradores da competição');
      $objPHPExcel->setActiveSheetIndex(0);

      $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Equipe');
      $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Nome completo');
      $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Email');
      $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Telefone');
      $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Vezes que falou');
      $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Soma requerente 1');
      $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Soma requerente 2');
      $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Soma requerida 1');
      $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Soma requerida 2');
      $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'Pontuação média');
      
      $oradores = $this->getServiceLocator()->get('Orador')->getOradoresNotas($this->params()->fromRoute('idCompeticao'));
      $numLinha = 1;
      foreach ($oradores as $orador) {
        $numLinha++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$numLinha, $orador['numero_faculdade']);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$numLinha, $orador['nome']);
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$numLinha, $orador['email']);
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$numLinha, '-');
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$numLinha, $orador['vezes_respondeu']);
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$numLinha, $orador['nota_total_recorrente_1']);
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$numLinha, $orador['nota_total_recorrente_2']);
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$numLinha, $orador['nota_total_recorrido_1']);
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$numLinha, $orador['nota_total_recorrido_2']);
        $notaTotal = ($orador['nota_total_recorrente_1']+$orador['nota_total_recorrente_2']+$orador['nota_total_recorrido_1']+$orador['nota_total_recorrido_2'])/$orador['vezes_respondeu'];
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$numLinha, $notaTotal);
      }

      $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
      $diretorio = 'public/relatorios';
      if(!file_exists($diretorio)){
          mkdir($diretorio);
      }

      $fileName = $diretorio.'/oradoresCompeticao.xlsx';
      $objWriter->save($fileName);
      
      //enviar para download
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

