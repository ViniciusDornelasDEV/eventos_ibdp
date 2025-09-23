<?php
namespace Associados\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class Anuidade Extends BaseTable {

    public function getAnuidadePeriodo($dados){
        $adapter = $this->tableGateway->getAdapter();
        $sql = 'SELECT * 
        FROM tb_associado_anuidade 
        WHERE categoria = '.$dados['categoria'].' AND (data_inicio BETWEEN "'.$dados['data_inicio'].'" AND "'.$dados['data_fim'].'" OR 
            data_fim BETWEEN "'.$dados['data_inicio'].'" AND "'.$dados['data_fim'].'");';

        $sql = str_replace('`', '', $sql);
        
        $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
}
