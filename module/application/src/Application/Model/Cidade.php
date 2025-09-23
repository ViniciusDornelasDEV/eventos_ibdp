<?php

namespace Application\Model;
use Application\Model\BaseTable;

class Cidade Extends BaseTable {

    public function getCidadeByParams($estado, $cidade){
        return $this->getTableGateway()->select(function($select) use ($estado, $cidade) {
            $select->where(array('estado' => $estado));
            $select->where->like('nome', '%'.$cidade.'%');
            
        })->current(); 
    }
}
