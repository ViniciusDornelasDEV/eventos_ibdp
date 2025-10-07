<?php
namespace Evento\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class EventoVideo Extends BaseTable {

    public function getVideos($group = false){
        return $this->getTableGateway()->select(function($select) use ($group) {
            $select->columns(array('id_video' => 'id', 'descricao_video' => 'descricao', 'link_video'));
            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = tb_evento_video.evento',
                    array('id_evento' => 'id','sigla', 'nome_evento' => 'nome', 'certificado_1', 'enviar_trabalho'),
                    'inner'
                );

            if ($group) {
              $select->group('e.id');
            }

            $select->order('e.id DESC, tb_evento_video.id DESC');

        });
    }
}
