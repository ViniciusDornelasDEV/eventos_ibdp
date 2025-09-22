<?php
return array(
    'router' => array(
        'routes' => array(
            'cliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente[/:page]',
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Novo cliente
            'clienteNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/cliente/novo',
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'novo',
                    ),
                ),
            ),

            'clienteAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/alterar[/:inscricao]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                	),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'alterar',
                    ),
                ),
            ),

            'clienteDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/deletarcliente[/:id][/:inscricao]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                        'inscricao'     => '[0-9]+'
                	),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'deletarcliente',
                    ),
                ),
            ),
            'buscarClienteCpf' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/cliente/buscar/cpf',
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'buscarcliente',
                    ),
                ),
            ),
            //CATEGORIA DE CLIENTES
            'categoriaCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/categoria[/:page]',
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Categoria',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Nova categoria
            'categoriaClienteNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/cliente/categoria/novo',
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Categoria',
                        'action'     => 'novo',
                    ),
                ),
            ),

            'categoriaClienteAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/categoria/alterar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Categoria',
                        'action'     => 'alterar',
                    ),
                ),
            ),

            'uploadSocios' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/categoria/upload[/:idCategoria]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Categoria',
                        'action'     => 'uploadsocios',
                    ),
                ),
            ),
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Cliente\Controller\Cliente' => 'Cliente\Controller\ClienteController',
            'Cliente\Controller\Categoria' => 'Cliente\Controller\CategoriaController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);