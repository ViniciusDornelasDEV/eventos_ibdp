<?php
namespace Competicao\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class Nota Extends BaseTable {
  public function salvarAvaliacao($dados, $avaliacoes, $partida){
    $adapter = $this->getTableGateway()->getAdapter();
    $connection = $adapter->getDriver()->getConnection();
    $connection->beginTransaction();

    try {
      $numAvaliacoes = count($avaliacoes)+1;
      $idAvaliacao = parent::insert($dados);



      if($numAvaliacoes == 3){
        //somar notas e update chaveamento e notaFaculdade
        $notaRecorrente = ($dados['nota_lei_recorrente_1']+$dados['nota_lei_recorrente_2'])/2;
        $notaRecorrente += ($dados['nota_fatos_recorrente_1']+$dados['nota_fatos_recorrente_2'])/2;
        $notaRecorrente += ($dados['nota_postura_recorrente_1']+$dados['nota_postura_recorrente_2'])/2;
        $notaRecorrente += ($dados['nota_resposta_recorrente_1']+$dados['nota_resposta_recorrente_2'])/2;

        $notaRecorrido = ($dados['nota_lei_recorrido_1']+$dados['nota_lei_recorrido_2'])/2;
        $notaRecorrido += ($dados['nota_fatos_recorrido_1']+$dados['nota_fatos_recorrido_2'])/2;
        $notaRecorrido += ($dados['nota_postura_recorrido_1']+$dados['nota_postura_recorrido_2'])/2;
        $notaRecorrido += ($dados['nota_resposta_recorrido_1']+$dados['nota_resposta_recorrido_2'])/2;

        foreach ($avaliacoes as $avaliacao) {
          $notaRecorrente += ($avaliacao['nota_lei_recorrente_1']+$avaliacao['nota_lei_recorrente_2'])/2;
          $notaRecorrente += ($avaliacao['nota_fatos_recorrente_1']+$avaliacao['nota_fatos_recorrente_2'])/2;
          $notaRecorrente += ($avaliacao['nota_postura_recorrente_1']+$avaliacao['nota_postura_recorrente_2'])/2;
          $notaRecorrente += ($avaliacao['nota_resposta_recorrente_1']+$avaliacao['nota_resposta_recorrente_2'])/2;

          $notaRecorrido += ($avaliacao['nota_lei_recorrido_1']+$avaliacao['nota_lei_recorrido_2'])/2;
          $notaRecorrido += ($avaliacao['nota_fatos_recorrido_1']+$avaliacao['nota_fatos_recorrido_2'])/2;
          $notaRecorrido += ($avaliacao['nota_postura_recorrido_1']+$avaliacao['nota_postura_recorrido_2'])/2;
          $notaRecorrido += ($avaliacao['nota_resposta_recorrido_1']+$avaliacao['nota_resposta_recorrido_2'])/2;
        }

        $notaRecorrente = $notaRecorrente/3;
        $notaRecorrido = $notaRecorrido/3;
        //update nota das faculdades
        $serviceChaveamento = new TableGateway('tb_competicao_chaveamento', $adapter);
        $serviceChaveamento->update(array('nota_recorrente' => $notaRecorrente, 'nota_recorrido' => $notaRecorrido), array('id' => $dados['chaveamento']));

        //update nota total da faculdade na competicao
        $serviceFaculdade = new TableGateway('tb_competicao_faculdade_nota', $adapter);
        $totalRecorrente = $serviceFaculdade->select(array(
          'competicao' => $partida->competicao, 
          'faculdade' => $partida->recorrente
        ))->current();
        if($totalRecorrente){
          $totalRecorrente->nota = $totalRecorrente->nota+$notaRecorrente;
          $serviceFaculdade->update(array('nota' => $totalRecorrente->nota), array('id' => $totalRecorrente->id));
        }else{
          $serviceFaculdade->insert(array(
            'faculdade' => $partida->recorrente, 
            'competicao' => $partida->competicao,
            'nota'      =>  $notaRecorrente
          ));
        }

        $totalRecorrido = $serviceFaculdade->select(array(
          'competicao' => $partida->competicao, 
          'faculdade' => $partida->recorrido
        ))->current();
        if($totalRecorrido){
          $totalRecorrido->nota = $totalRecorrido->nota+$notaRecorrido;
          $serviceFaculdade->update(array('nota' => $totalRecorrido->nota), array('id' => $totalRecorrido->id));
        }else{
          $serviceFaculdade->insert(array(
            'faculdade' => $partida->recorrido, 
            'competicao' => $partida->competicao,
            'nota'      =>  $notaRecorrido
          ));
        }
      }
      
      $connection->commit();
      return true;
    } catch (Exception $e) {
      $connection->rollback();
      return false;
    }
    $connection->rollback();
    return false;
  }

  public function getNotas($idPartida){
    return $this->getTableGateway()->select(function($select) use ($idPartida) {
      $select->join(
          array('a' => 'tb_competicao_avaliador'),
          'a.id = tb_competicao_chaveamento_nota.avaliador',
          array('nome_avaliador' => 'nome')
      );

      $select->where(array('tb_competicao_chaveamento_nota.chaveamento' => $idPartida));
    });
  }

  public function deletar($idNota, $partida){
    $adapter = $this->getTableGateway()->getAdapter();
    $connection = $adapter->getDriver()->getConnection();
    $connection->beginTransaction();
    
    try {
      $notas = $this->getNotas($partida->id);
      parent::delete(array('id' => $idNota));
      if($notas->count() == 3){
        //atualizar a pontuação da faculdade recorrente
        $serviceFaculdade = new TableGateway('tb_competicao_faculdade_nota', $adapter);
        $recorrente = $serviceFaculdade->select(array('competicao' => $partida['competicao'], 'faculdade' => $partida['recorrente']))->current();
        $notaRecorrente = $recorrente->nota-$partida['nota_recorrente'];
        $serviceFaculdade->update(array('nota' => $notaRecorrente), array('id' => $recorrente->id));

        //atualizar a pontuação da faculdade recorrida
        $recorrido = $serviceFaculdade->select(array('competicao' => $partida['competicao'], 'faculdade' => $partida['recorrido']))->current();
        $notaRecorrido = $recorrido->nota-$partida['nota_recorrido'];
        $serviceFaculdade->update(array('nota' => $notaRecorrido), array('id' => $recorrido->id));
      }    
      
      $connection->commit();
      return true;
    } catch (Exception $e) {
      $connection->rollback();
      return false;
    }
    $connection->rollback();
    return false;
  }

}
