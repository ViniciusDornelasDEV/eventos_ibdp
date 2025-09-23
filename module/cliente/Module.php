<?php
namespace Cliente;

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
                /* My Tables  */
                /*'Equipamento' => function($sm) {
                    $tableGateway = new TableGateway('tb_equipamento', $sm->get('db_adapter_main'));
                    $updates = new Model\Equipamento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },*/
                'Cliente' => function($sm) {
                    $tableGateway = new TableGateway('tb_cliente', $sm->get('db_adapter_main'));
                    $updates = new Model\Cliente($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'EstadoCivil' => function($sm) {
                    $tableGateway = new TableGateway('tb_estado_civil', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Nacionalidade' => function($sm) {
                    $tableGateway = new TableGateway('tb_nacionalidade', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'ComoFicouSabendo' => function($sm) {
                    $tableGateway = new TableGateway('tb_como_ficou_sabendo', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CategoriaCliente' => function($sm) {
                    $tableGateway = new TableGateway('tb_cliente_categoria', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Socio' => function($sm) {
                    $tableGateway = new TableGateway('tb_socios', $sm->get('db_adapter_main'));
                    $updates = new Model\Socio($tableGateway);
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
