<?php
return array(
    'router' => array(
        'routes' => array(
            'empresa' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa[/:page]',
                    'defaults' => array(
                        'controller' => 'Empresa\Controller\Empresa',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Nova empresa
            'empresaNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/empresa/novo',
                    'defaults' => array(
                        'controller' => 'Empresa\Controller\Empresa',
                        'action'     => 'novo',
                    ),
                ),
            ),

            'empresaAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa/alterar[/:id]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                	),
                    'defaults' => array(
                        'controller' => 'Empresa\Controller\Empresa',
                        'action'     => 'alterar',
                    ),
                ),
            ),

            'empresaDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/empresa/deletarempresa[/:id]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                	),
                    'defaults' => array(
                        'controller' => 'Empresa\Controller\Empresa',
                        'action'     => 'deletarempresa',
                    ),
                ),
            ),
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Empresa\Controller\Empresa' => 'Empresa\Controller\EmpresaController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'form/empresa'              => __DIR__ . '/../view/partials/formEmpresa.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);