<?php
namespace Competicao\Model;
use Application\Model\BaseTable;

class FaculdadeNota Extends BaseTable {
  public function getClassificacao($idCompeticao){
    return $this->getTableGateway()->select(function($select) use ($idCompeticao) {
      
      $select->join(
          array('f' => 'tb_competicao_faculdade'),
          'f.id = faculdade',
          array('nome_faculdade' => 'nome')
      );

      $select->where(array('competicao' => $idCompeticao));
      $select->order('nota DESC');
    });
  }

}
