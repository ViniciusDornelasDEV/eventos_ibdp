<?php
return array(
    'router' => array(
        'routes' => array(
            'buscarCertificado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/eventos/certificado[/:siglaEvento][/:numeroCertificado]',
                    'defaults' => array(
                        'controller' => 'Suporte\Controller\Certificado',
                        'action'     => 'index',
                    ),
                ),
            ),
            'configurarCertificado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/eventos/certificado/configurar[/:evento][/:numerocertificado][/:certificado]',
                    'constraints' => array(
                        'evento'     => '[0-9]+',
                        'certificado'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Suporte\Controller\Certificado',
                        'action'     => 'configurarcertificado',
                    ),
                ),
            ),
            'deletarConfiguracao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/eventos/certificado/configurar/deletar[/:id][/:evento]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'evento'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Suporte\Controller\Certificado',
                        'action'     => 'deletarconfiguracao',
                    ),
                ),
            ),

            'buscarCheckin' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/eventos/checkin[/:siglaEvento]',
                    'defaults' => array(
                        'controller' => 'Suporte\Controller\Checkin',
                        'action'     => 'index',
                    ),
                ),
            ),
            'confirmarPresenca' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/eventos/checkin/confirmar[/:siglaEvento][/:inscricao]',
                    'defaults' => array(
                        'controller' => 'Suporte\Controller\Checkin',
                        'action'     => 'confirmarpresenca',
                    ),
                ),
            ),





            'downloadCertificadoCategoriaAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/certificado/download[/:idCategoria]',
                    'constraints' => array(
                        'idCategoria'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Suporte\Controller\Certificadoassociados',
                        'action'     => 'download',
                    ),
                ),
            ),
            'configurarCertificadoAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/certificado/configurar[/:idCategoria][/:certificado]',
                    'constraints' => array(
                        'idCategoria'     => '[0-9]+',
                        'certificado'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Suporte\Controller\Certificadoassociados',
                        'action'     => 'configurarcertificado',
                    ),
                ),
            ),
            'deletarConfiguracaoAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/certificado/configurar/deletar[/:id][/:idCategoria]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'idCategoria'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Suporte\Controller\Certificadoassociados',
                        'action'     => 'deletarconfiguracao',
                    ),
                ),
            ),

        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Suporte\Controller\Certificado' => 'Suporte\Controller\CertificadoController',
            'Suporte\Controller\Checkin' => 'Suporte\Controller\CheckinController',
            'Suporte\Controller\Certificadoassociados' => 'Suporte\Controller\CertificadoassociadosController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);