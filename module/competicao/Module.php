<?php
namespace Competicao;

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
                'Avaliador' => function($sm) {
                    $tableGateway = new TableGateway('tb_competicao_avaliador', $sm->get('db_adapter_main'));
                    $updates = new Model\Avaliador($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Faculdade' => function($sm) {
                    $tableGateway = new TableGateway('tb_competicao_faculdade', $sm->get('db_adapter_main'));
                    $updates = new Model\Faculdade($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Orador' => function($sm) {
                    $tableGateway = new TableGateway('tb_competicao_orador', $sm->get('db_adapter_main'));
                    $updates = new Model\Orador($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Competicao' => function($sm) {
                    $tableGateway = new TableGateway('tb_competicao', $sm->get('db_adapter_main'));
                    $updates = new Model\Competicao($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CompeticaoSalas' => function($sm) {
                    $tableGateway = new TableGateway('tb_competicao_salas', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CompeticaoChaveamento' => function($sm) {
                    $tableGateway = new TableGateway('tb_competicao_chaveamento', $sm->get('db_adapter_main'));
                    $updates = new Model\Chaveamento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'CompeticaoNota' => function($sm) {
                    $tableGateway = new TableGateway('tb_competicao_chaveamento_nota', $sm->get('db_adapter_main'));
                    $updates = new Model\Nota($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'FaculdadeNota' => function($sm) {
                    $tableGateway = new TableGateway('tb_competicao_faculdade_nota', $sm->get('db_adapter_main'));
                    $updates = new Model\FaculdadeNota($tableGateway);
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
