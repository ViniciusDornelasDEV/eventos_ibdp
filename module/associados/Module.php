<?php
namespace Associados;

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
                'CategoriaAssociado' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_categoria', $sm->get('db_adapter_main'));
                    $updates = new Model\Categoria($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Associado' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado', $sm->get('db_adapter_main'));
                    $updates = new Model\Associado($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoAnuidade' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_anuidade', $sm->get('db_adapter_main'));
                    $updates = new Model\Anuidade($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoArquivo' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_arquivo', $sm->get('db_adapter_main'));
                    $updates = new Model\Arquivo($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoArquivoIndividual' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_arquivo_individual', $sm->get('db_adapter_main'));
                    $updates = new Model\ArquivoIndividual($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoPagamento' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_pagamento', $sm->get('db_adapter_main'));
                    $updates = new Model\Pagamento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoPagamentoComprovante' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_pagamento_comprovante', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'PromocaoAssociado' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_promocao', $sm->get('db_adapter_main'));
                    $updates = new Model\Promocao($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Questionario' => function($sm) {
                    $tableGateway = new TableGateway('tb_questionario', $sm->get('db_adapter_main'));
                    $updates = new Model\Questionario($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'QuestionarioQuestao' => function($sm) {
                    $tableGateway = new TableGateway('tb_questionario_questao', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'QuestionarioQuestaoAlternativa' => function($sm) {
                    $tableGateway = new TableGateway('tb_questionario_questao_alternativa', $sm->get('db_adapter_main'));
                    $updates = new Model\Alternativa($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'QuestionarioAssociado' => function($sm) {
                    $tableGateway = new TableGateway('tb_questionario_associado', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'QuestionarioAssociadoResponder' => function($sm) {
                    $tableGateway = new TableGateway('tb_questionario_alternativa_associado', $sm->get('db_adapter_main'));
                    $updates = new Model\QuestionarioResponder($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'MensagensAssociado' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_mensagem', $sm->get('db_adapter_main'));
                    $updates = new Model\Mensagens($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoIpag' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_ipag', $sm->get('db_adapter_main'));
                    $updates = new Model\AssociadoIpag($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoBeneficios' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_clube_beneficios', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoContinuada' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_educacao_continuada', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'AssociadoEbook' => function($sm) {
                    $tableGateway = new TableGateway('tb_associado_ebook', $sm->get('db_adapter_main'));
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
