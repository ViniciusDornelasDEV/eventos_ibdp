<?php
namespace Evento\Model;
use Application\Model\BaseTable;

class Mensagens Extends BaseTable {
    public function getMensagensByEvento($idEvento){
        return $this->getTableGateway()->select(function($select) use ($idEvento) {

            $select->join(
                    array('cc' => 'tb_evento_cliente_categoria'),
                    'cc.id = evento_cliente_categoria',
                    array(),
                    'left'
                );

            $select->join(
                    array('c' => 'tb_cliente_categoria'),
                    'c.id = cc.cliente_categoria',
                    array('nome_categoria' => 'nome'),
                    'left'
                );


            $select->where(array('tb_evento_mensagem.evento' => $idEvento));

        }); 
    }

}
