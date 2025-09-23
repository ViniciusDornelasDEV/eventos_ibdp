<?php
namespace Evento;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\BaseTable;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Evento' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento', $sm->get('db_adapter_main'));
                    $updates = new Model\Evento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Inscricao' => function($sm) {
                    $tableGateway = new TableGateway('tb_inscricao', $sm->get('db_adapter_main'));
                    $updates = new Model\Inscricao($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'InscricaoStatus' => function($sm) {
                    $tableGateway = new TableGateway('tb_inscricao_status', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'QuantidadeCategoria' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_cliente_categoria', $sm->get('db_adapter_main'));
                    $updates = new Model\QuantidadeCategoria($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'ValorEvento' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_cliente_valor', $sm->get('db_adapter_main'));
                    $updates = new Model\ValorEvento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'PromocaoEvento' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_codigo_promocional', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'InscricaoCampos' => function($sm) {
                    $tableGateway = new TableGateway('tb_inscricao_campos', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'InscricaoCamposEvento' => function($sm) {
                    $tableGateway = new TableGateway('tb_inscricao_campos_evento', $sm->get('db_adapter_main'));
                    $updates = new Model\InscricaoCamposEvento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'StatusPagamento' => function($sm) {
                    $tableGateway = new TableGateway('tb_inscricao_status_pagamento', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'PagamentoStatus' => function($sm) {
                    $tableGateway = new TableGateway('tb_status_pagamento', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'MensagensEvento' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_mensagem', $sm->get('db_adapter_main'));
                    $updates = new Model\Mensagens($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'EventoOpcao' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_opcao', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'EventoOpcaoAlternativa' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_opcao_alternativa', $sm->get('db_adapter_main'));
                    $updates = new Model\EventoOpcaoAlternativa($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'EventoOpcaoAlternativaInscricao' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_opcao_alternativa_inscricao', $sm->get('db_adapter_main'));
                    $updates = new Model\EventoOpcaoAlternativaInscricao($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                
                
                'EventoTransmissao' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_transmissao', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
            ),
            'invokables' => array(
                'ImageService' => 'Imagine\Gd\Imagine',
            ),
        );
    }
}
