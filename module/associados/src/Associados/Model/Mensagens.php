<?php
namespace Associados\Model;
use Application\Model\BaseTable;

class Mensagens Extends BaseTable {
    public function getMensagens(){
        return $this->getTableGateway()->select(function($select) {

            $select->join(
                    array('c' => 'tb_associado_categoria'),
                    'c.id = categoria_associado',
                    array('nome_categoria' => 'nome')
                );

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = c.empresa',
                    array('nome_fantasia')
                );

            $select->order('data_hora_envio DESC');
        });
    }
}
