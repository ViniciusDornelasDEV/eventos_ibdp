<?php
namespace Competicao\Model;
use Application\Model\BaseTable;
use Zend\Db\Sql\Predicate\Expression;

class Orador Extends BaseTable {
  public function getOradoresByFaculdade($idFaculdade){
    return $this->getTableGateway()->select(function($select) use ($idFaculdade) {
        $select->where(array('faculdade' => $idFaculdade));

      $select->order('ativo DESC, nome');
    });
  }

  public function getNotas($idCompeticao, $chave = 'cn.orador_recorrente_1'){
    return $this->getTableGateway()->select(function($select) use ($idCompeticao, $chave) {
      $select->join(
          array('f' => 'tb_competicao_faculdade'),
          'f.id = faculdade',
          array('nome_faculdade' => 'nome', 'numero_faculdade' => 'numero')
      );

      $select->join(
          array('cn' => 'tb_competicao_chaveamento_nota'),
          new Expression($chave.' = tb_competicao_orador.id'),
          array(
            'chaveamento', 
            'orador_recorrente_1', 
            'nota_total_recorrente_1' => new Expression('SUM(cn.nota_total_recorrente_1)'), 
            'orador_recorrente_2', 
            'nota_total_recorrente_2' => new Expression('SUM(cn.nota_total_recorrente_2)'),
            'orador_recorrido_1', 
            'nota_total_recorrido_1' => new Expression('SUM(cn.nota_total_recorrido_1)'), 
            'orador_recorrido_2', 
            'nota_total_recorrido_2' => new Expression('SUM(cn.nota_total_recorrido_2)'), 
          )
      );

      $select->join(
          array('cc' => 'tb_competicao_chaveamento'),
          'cc.id = cn.chaveamento',
          array()
      );

      $select->where(array('cc.competicao' => $idCompeticao));
      $select->group(array('tb_competicao_orador.id', 'cn.chaveamento'));
      $select->order('f.numero, tb_competicao_orador.nome');

    });
  }

  public function getOradoresNotas($idCompeticao){
    //RECORRENTE 1
    $oradores = $this->getNotas($idCompeticao, 'cn.orador_recorrente_1');
    $preparedArray = array();
    foreach ($oradores as $orador) {
      
      if(!isset($preparedArray[$orador->id])){
        $preparedArray[$orador->id] = array(
          'id'                      =>  $orador['id'],
          'nome'                    =>  $orador['nome'],
          'email'                   =>  $orador['email'],
          'numero_faculdade'        =>  $orador['numero_faculdade'],
          'nota_total_recorrente_1' =>  0,
          'nota_total_recorrente_2' =>  0,
          'nota_total_recorrido_1'  =>  0,
          'nota_total_recorrido_2'  =>  0,
          'vezes_respondeu'         =>  0
        );
      }

      $preparedArray[$orador->id]['nota_total_recorrente_1'] += $orador['nota_total_recorrente_1'];
      $preparedArray[$orador->id]['vezes_respondeu']++;
    }

    //RECORRENTE 2
    $oradores = $this->getNotas($idCompeticao, 'cn.orador_recorrente_2');
    foreach ($oradores as $orador) {
      if(!isset($preparedArray[$orador->id])){
        $preparedArray[$orador->id] = array(
          'id'                      =>  $orador['id'],
          'nome'                    =>  $orador['nome'],
          'email'                   =>  $orador['email'],
          'numero_faculdade'        =>  $orador['numero_faculdade'],
          'nota_total_recorrente_1' =>  0,
          'nota_total_recorrente_2' =>  0,
          'nota_total_recorrido_1'  =>  0,
          'nota_total_recorrido_2'  =>  0,
          'vezes_respondeu'         =>  0
        );
      }

      $preparedArray[$orador->id]['nota_total_recorrente_2'] += $orador['nota_total_recorrente_2'];
      $preparedArray[$orador->id]['vezes_respondeu']++;
    }

    //RECORRIDO 1
    $oradores = $this->getNotas($idCompeticao, 'cn.orador_recorrido_1');
    foreach ($oradores as $orador) {
      if(!isset($preparedArray[$orador->id])){
        $preparedArray[$orador->id] = array(
          'id'                      =>  $orador['id'],
          'nome'                    =>  $orador['nome'],
          'email'                   =>  $orador['email'],
          'numero_faculdade'        =>  $orador['numero_faculdade'],
          'nota_total_recorrente_1' =>  0,
          'nota_total_recorrente_2' =>  0,
          'nota_total_recorrido_1'  =>  0,
          'nota_total_recorrido_2'  =>  0,
          'vezes_respondeu'         =>  0
        );
      }

      $preparedArray[$orador->id]['nota_total_recorrido_1'] += $orador['nota_total_recorrido_1'];
      $preparedArray[$orador->id]['vezes_respondeu']++;
    }

    //RECORRIDO 2
    $oradores = $this->getNotas($idCompeticao, 'cn.orador_recorrido_2');
    foreach ($oradores as $orador) {
      if(!isset($preparedArray[$orador->id])){
        $preparedArray[$orador->id] = array(
          'id'                      =>  $orador['id'],
          'nome'                    =>  $orador['nome'],
          'email'                   =>  $orador['email'],
          'numero_faculdade'        =>  $orador['numero_faculdade'],
          'nota_total_recorrente_1' =>  0,
          'nota_total_recorrente_2' =>  0,
          'nota_total_recorrido_1'  =>  0,
          'nota_total_recorrido_2'  =>  0,
          'vezes_respondeu'         =>  0
        );
      }

      $preparedArray[$orador->id]['nota_total_recorrido_2'] += $orador['nota_total_recorrido_2'];
      $preparedArray[$orador->id]['vezes_respondeu']++;
    }
    return $preparedArray;
  }
}
