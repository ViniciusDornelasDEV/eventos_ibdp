<?php
namespace Trabalhos;

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
                'InscricaoTrabalhoCategoria' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_trabalho_categoria', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'InscricaoTrabalho' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_trabalho', $sm->get('db_adapter_main'));
                    $updates = new Model\Trabalho($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'InscricaoTrabalhoIntegrante' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_trabalho_integrante', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'InscricaoTrabalhoPDF' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_trabalho_pdf', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'EventoTrabalhoAvaliador' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_trabalho_avaliador', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'EventoTrabalhoPoster' => function($sm) {
                    $tableGateway = new TableGateway('tb_evento_trabalho_poster', $sm->get('db_adapter_main'));
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
