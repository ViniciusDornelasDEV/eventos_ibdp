<?php
namespace Associados\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class Promocao Extends BaseTable {
    public function inserir($dados, $categoriasEvento, $categoriasAssociados, $idEvento){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            if($dados['categoria_evento'] == 'T'){
                //pesquisar todas as categorias do evento
                foreach ($categoriasEvento as $categoriaEvento) {
                    if($dados['categoria_associado'] == 'T'){
                        //percorrer categorias de associados
                        foreach ($categoriasAssociados as $categoriaAssociado) {
                            parent::insert(array('categoria_associado' => $categoriaAssociado['id'], 'categoria_evento' => $categoriaEvento['id'], 'status_associado' => $dados['status_associado'], 'desconto' => $dados['desconto']));
                        }
                    }else{
                        //apenas 1 categoria de associado
                        parent::insert(array('categoria_associado' => $dados['categoria_associado'], 'categoria_evento' => $categoriaEvento['id'], 'status_associado' => $dados['status_associado'], 'desconto' => $dados['desconto']));
                    }
                }
            }else{
                if($dados['categoria_associado'] == 'T'){
                    //percorrer categorias de associados
                    foreach ($categoriasAssociados as $categoriaAssociado) {
                        parent::insert(array('categoria_associado' => $categoriaAssociado['id'], 'categoria_evento' => $dados['categoria_evento'], 'status_associado' => $dados['status_associado'], 'desconto' => $dados['desconto']));
                    }
                }else{
                    //penas 1 categoria de associado
                    parent::insert(array('categoria_associado' => $dados['categoria_associado'], 'categoria_evento' => $dados['categoria_evento'], 'status_associado' => $dados['status_associado'], 'desconto' => $dados['desconto']));
                }
            }

            
            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        return false;
    }


    public function getPromocoesByEvento($idEvento){
        return $this->getTableGateway()->select(function($select) use ($idEvento) {

            $select->join(
                    array('ca' => 'tb_associado_categoria'),
                    'ca.id = categoria_associado',
                    array('nome_categoria_associado' => 'nome')
                );

            $select->join(
                    array('cc' => 'tb_evento_cliente_categoria'),
                    'cc.id = categoria_evento',
                    array()
                );

            $select->join(
                    array('c' => 'tb_cliente_categoria'),
                    'c.id = cc.cliente_categoria',
                    array('nome_categoria_evento' => 'nome')
                );

            $select->where(array('cc.evento' => $idEvento));

            $select->order('c.nome, ca.nome');
        });
    }
}
