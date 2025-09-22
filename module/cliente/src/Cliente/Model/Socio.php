<?php
namespace Cliente\Model;
use Application\Model\BaseTable;

class Socio Extends BaseTable {

    public function insertCpf($cpf, $categoria){
        $adapter = $this->tableGateway->getAdapter();

        //tratar parametros
        $cpf = $adapter->platform->quoteIdentifier($cpf);
        $categoria = $adapter->platform->quoteIdentifier($categoria);

        //montar sql
        $sql = 'INSERT INTO tb_socios (cpf, categoria) VALUES ('.$cpf.', '.$categoria.')
              ON DUPLICATE KEY UPDATE cpf = '.$cpf.';';

        //executar sql
        $sql = str_replace('`', '', $sql);

        $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        return $resultSet;
    }
}

?>