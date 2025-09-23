<?php
namespace Competicao\Model;
use Application\Model\BaseTable;

class Competicao Extends BaseTable {
  public function getCompeticoes($params){
    return $this->getTableGateway()->select(function($select) use ($params) {

      if(isset($params['nome']) && !empty($params['nome'])){
        $select->where->like('nome', '%'.$params['nome'].'%');
      }

      if(isset($params['ativo']) && !empty($params['ativo'])){
        $select->where(array('ativo' => $params['ativo']));
      }

      $select->order('nome');
    });
  }    
}
