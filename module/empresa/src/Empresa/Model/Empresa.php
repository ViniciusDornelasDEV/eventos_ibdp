<?php
namespace Empresa\Model;
use Application\Model\BaseTable;

class Empresa Extends BaseTable {
    public function getEmpresaByParams($params = false){
        return $this->getTableGateway()->select(function($select) use ($params) {
            if($params){
                if($params['nome_fantasia']){
                    $select->where->like('nome_fantasia', '%'.$params['nome_fantasia'].'%');
                }    

                if($params['cnpj']){
                    $select->where(array('cnpj' => $params['cnpj'])); 
                }
            }
            $select->order('nome_fantasia');
        }); 
    }

}
