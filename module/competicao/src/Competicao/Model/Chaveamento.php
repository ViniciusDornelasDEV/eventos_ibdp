<?php
namespace Competicao\Model;
use Application\Model\BaseTable;

class Chaveamento Extends BaseTable {
  public function getChaves($idCompeticao){
    return $this->getTableGateway()->select(function($select) use ($idCompeticao) {
      $select->join(
          array('s' => 'tb_competicao_salas'),
          's.id = tb_competicao_chaveamento.sala',
          array('nome_sala' => 'nome')
      );

      //recorrente
      $select->join(
          array('te' => 'tb_competicao_faculdade'),
          'te.id = tb_competicao_chaveamento.recorrente',
          array('nome_recorrente' => 'nome')
      );

      //recorrido
      $select->join(
          array('do' => 'tb_competicao_faculdade'),
          'do.id = tb_competicao_chaveamento.recorrido',
          array('nome_recorrido' => 'nome')
      );

      $select->where(array('tb_competicao_chaveamento.competicao' => $idCompeticao));
      $select->order('tb_competicao_chaveamento.data, tb_competicao_chaveamento.hora, tb_competicao_chaveamento.sala');
    });
  }

  public function getChavesCompletas($idCompeticao, $params){
    return $this->getTableGateway()->select(function($select) use ($idCompeticao, $params) {
      $select->join(
          array('s' => 'tb_competicao_salas'),
          's.id = tb_competicao_chaveamento.sala',
          array('nome_sala' => 'nome')
      );

      //recorrente
      $select->join(
          array('te' => 'tb_competicao_faculdade'),
          'te.id = tb_competicao_chaveamento.recorrente',
          array('nome_recorrente' => 'nome', 'numero_recorrente' => 'numero')
      );

      //recorrido
      $select->join(
          array('do' => 'tb_competicao_faculdade'),
          'do.id = tb_competicao_chaveamento.recorrido',
          array('nome_recorrido' => 'nome', 'numero_recorrido' => 'numero')
      );

      //avaliações
      $select->join(
          array('ch' => 'tb_competicao_chaveamento_nota'),
          'ch.chaveamento = tb_competicao_chaveamento.id',
          array('nota_total_recorrente_1', 'nota_total_recorrente_2', 'nota_total_recorrido_1', 'nota_total_recorrido_2'),
          'LEFT'
      );

      //avaliador
      $select->join(
          array('ca' => 'tb_competicao_avaliador'),
          'ca.id = ch.avaliador',
          array('nome_avaliador' => 'nome'),
          'LEFT'
      );

      //orador_recorrente_1
      $select->join(
          array('or1' => 'tb_competicao_orador'),
          'or1.id = ch.orador_recorrente_1',
          array('nome_recorrente_1' => 'nome'),
          'LEFT'
      );

      //orador_recorrente_2
      $select->join(
          array('or2' => 'tb_competicao_orador'),
          'or2.id = ch.orador_recorrente_2',
          array('nome_recorrente_2' => 'nome'),
          'LEFT'
      );

      //orador_recorrido_1
      $select->join(
          array('or3' => 'tb_competicao_orador'),
          'or3.id = ch.orador_recorrido_1',
          array('nome_recorrido_1' => 'nome'),
          'LEFT'
      );

      //orador_recorrido_2
      $select->join(
          array('or4' => 'tb_competicao_orador'),
          'or4.id = ch.orador_recorrido_2',
          array('nome_recorrido_2' => 'nome'),
          'LEFT'
      );
      

      $select->where(array('tb_competicao_chaveamento.competicao' => $idCompeticao));
      $select->order('tb_competicao_chaveamento.data, tb_competicao_chaveamento.hora, tb_competicao_chaveamento.sala');
      
      if(isset($params['sala']) && !empty($params['sala'])){
        $select->where(array('tb_competicao_chaveamento.sala' => $params['sala']));
      }

      if(isset($params['data']) && !empty($params['data'])){
        $select->where(array('tb_competicao_chaveamento.data' => $params['data']));
      }

      if(isset($params['hora']) && !empty($params['hora'])){
        $select->where(array('tb_competicao_chaveamento.hora' => $params['hora']));
      }

    });
  }

  public function getRodadas($idCompeticao, $params = array()){
    $rodadas = $this->getChavesCompletas($idCompeticao, $params);
    $preparedArray = array();
    $data = false;
    $hora = false;
    $numRodada = 0;
    $idRodada = false;
    foreach ($rodadas as $rodada) {
      //NOVA RODADA?
      if($data != $rodada['data'] || $hora != $rodada['hora']){
        $numRodada++;
        $idPartida = false;
        $data = $rodada['data'];
        $hora = $rodada['hora'];
        $preparedArray[$numRodada] = array(
          'data'      =>  $rodada['data'],
          'hora'      =>  $rodada['hora']
        );
        $preparedArray[$numRodada]['partidas'] = array();
      }

      //PARTIDA
      if($idRodada != $rodada['id']){
        $idRodada = $rodada['id'];
        $preparedArray[$numRodada]['partidas'][$idRodada] = array(
          'id_partida'          =>  $rodada['id'],
          'nome_sala'           =>  $rodada['nome_sala'],
          'id_recorrente'       =>  $rodada['recorrente'],
          'numero_recorrente'   =>  $rodada['numero_recorrente'],
          'nome_recorrente'     =>  $rodada['nome_recorrente'],
          'nota_recorrente'     =>  $rodada['nota_recorrente'],
          'id_recorrido'        =>  $rodada['recorrido'],
          'numero_recorrido'    =>  $rodada['numero_recorrido'],
          'nome_recorrido'      =>  $rodada['nome_recorrido'],
          'nota_recorrido'      =>  $rodada['nota_recorrido'],
          'orador_recorrente_1' =>  $rodada['nome_recorrente_1'],
          'orador_recorrente_2' =>  $rodada['nome_recorrente_2'],
          'orador_recorrido_1'  =>  $rodada['nome_recorrido_1'],
          'orador_recorrido_2'  =>  $rodada['nome_recorrido_2']
        );  
        $preparedArray[$numRodada]['partidas'][$idRodada]['avaliacoes'] = array();
      }
      $preparedArray[$numRodada]['partidas'][$idRodada]['avaliacoes'][] = array(
        'nome_avaliador'           =>  $rodada['nome_avaliador'],
        'nota_total_recorrente_1'  =>  $rodada['nota_total_recorrente_1'],
        'nota_total_recorrente_2'  =>  $rodada['nota_total_recorrente_2'],
        'nota_total_recorrido_1'   =>  $rodada['nota_total_recorrido_1'],
        'nota_total_recorrido_2'   =>  $rodada['nota_total_recorrido_2'],
      );
    }

    return $preparedArray;
  }

  public function getPartida($idPartida){
    return $this->getTableGateway()->select(function($select) use ($idPartida) {
      $select->join(
          array('c' => 'tb_competicao'),
          'c.id = tb_competicao_chaveamento.competicao',
          array('nome_competicao' => 'nome')
      );

      $select->join(
          array('s' => 'tb_competicao_salas'),
          's.id = tb_competicao_chaveamento.sala',
          array('nome_sala' => 'nome')
      );

      //recorrente
      $select->join(
          array('te' => 'tb_competicao_faculdade'),
          'te.id = tb_competicao_chaveamento.recorrente',
          array('nome_recorrente' => 'nome')
      );

      //recorrido
      $select->join(
          array('do' => 'tb_competicao_faculdade'),
          'do.id = tb_competicao_chaveamento.recorrido',
          array('nome_recorrido' => 'nome')
      );

      $select->where(array('tb_competicao_chaveamento.id' => $idPartida));
      $select->order('tb_competicao_chaveamento.data, tb_competicao_chaveamento.hora, tb_competicao_chaveamento.sala');
    })->current();
  }

}
