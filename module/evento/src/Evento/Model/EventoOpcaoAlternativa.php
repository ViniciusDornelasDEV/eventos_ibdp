<?php
namespace Evento\Model;
use Application\Model\BaseTable;

class EventoOpcaoAlternativa Extends BaseTable {

    public function getQuestoesAlternativas($idEvento){
        return $this->getTableGateway()->select(function($select) use ($idEvento) {

            $select->join(
                    array('e' => 'tb_evento_opcao'),
                    'e.id = evento_opcao',
                    array('titulo', 'data'),
                    'inner'
                );

            $select->order('data, evento_opcao');
            $select->where(array('e.evento' => $idEvento));
        });
    }
}
