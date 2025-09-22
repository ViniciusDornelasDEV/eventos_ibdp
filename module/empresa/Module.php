<?php
namespace Empresa;

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
                'Empresa' => function($sm) {
                    $tableGateway = new TableGateway('tb_empresa', $sm->get('db_adapter_main'));
                    $updates = new Model\Empresa($tableGateway);
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
