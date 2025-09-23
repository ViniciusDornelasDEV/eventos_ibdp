<?php
namespace Associados\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;

class AssociadoIpag Extends BaseTable {
    public function getPagamento($idAssociado, $idAnuidade){
        return $this->getTableGateway()->select(function($select) use ($idAssociado, $idAnuidade) {
            $select->where(array('associado' => $idAssociado, 'anuidade' => $idAnuidade));

        })->current();
    }

    public function atualizarPagamentoAssociado($callback, $pago){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $associadoIpag = parent::getRecordFromArray(array(
                'id' =>  $callback->attributes->order_id
            ));

            //inserir log em parent::update
            $dadosIpag = array(
                'tipo_pagamento'=>  $callback->attributes->method,
                'ipag_id'       =>  $callback->id,
                'descricao'     =>  $callback->attributes->status->code.' - '.
                                    $callback->attributes->status->message.': '.
                                    $callback->attributes->gateway->message,
                'data'          =>  date('Y-m-d H:i:s')
            );

            parent::update($dadosIpag, array('id' => $associadoIpag['id']));

            if ($pago['status'] == true) {
                $dadosInsert = array(
                    'associado'         =>  $associadoIpag['associado'],
                    'anuidade'          =>  $associadoIpag['anuidade'],
                    'forma_pagamento'   =>  12,
                    'valor_pagamento'   =>  $callback->attributes->amount,
                    'ipag_id'           =>  $callback->id
                );
                $servicePagamento = new TableGateway('tb_associado_pagamento', $adapter);
                $servicePagamento->insert($dadosInsert);
            }
             
            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
        }
        return false;
    }
}
