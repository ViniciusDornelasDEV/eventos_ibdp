<?php
namespace Associados\Model;
use Application\Model\BaseTable;

class Categoria Extends BaseTable {

    public function getCategorias($empresa = false){
        return $this->getTableGateway()->select(function($select) use ($empresa) {

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = empresa',
                    array('nome_fantasia'),
                    'left'
                );

            if($empresa){
                $select->where(array('empresa' => $empresa));
            }

            $select->order('e.nome_fantasia, tb_associado_categoria.nome');
        });
    }
}
