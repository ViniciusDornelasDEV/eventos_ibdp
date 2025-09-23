<?php
namespace Evento\Model;
use Application\Model\BaseTable;

class QuantidadeCategoria Extends BaseTable {
    public function getQuantidadeInscricoesCategoriaByEvento($idEvento){
        return $this->getTableGateway()->select(function($select) use ($idEvento) {

            $select->join(
                    array('c' => 'tb_cliente_categoria'),
                    'c.id = cliente_categoria',
                    array('nome_categoria' => 'nome', 'id_categoria' => 'id'),
                    'inner'
                );


            $select->where(array('evento' => $idEvento));
            $select->order('c.nome');
        }); 
    }

}
