<?php
namespace Associados\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class Arquivo Extends BaseTable {

    public function inserirArquivos($dados, $categorias){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            if($dados['categoria'] == 'T'){
                //todas as categorias
                foreach ($categorias as $categoria) {
                    parent::insert(array('categoria' => $categoria->id, 'descricao' => $dados['descricao'], 'arquivo' => $dados['arquivo'], 'status_associado' => $dados['status_associado']));
                }
            }else{
                //apenas uma categoria
                parent::insert($dados);
            }
             
            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
        }
        return false;
    }
}
