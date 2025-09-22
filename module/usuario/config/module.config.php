<?php
return array(
    'router' => array(
        'routes' => array(
            //Login
            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/module/login',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'login',
                    ),
                ),
            ),
            'loginCompeticao' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/module/login/avaliador',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'loginavaliador',
                    ),
                ),
            ),

            //Login
            'logout' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/logout',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'logout',
                    ),
                ),
            ),

            'usuario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario[/:page]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Novo usuario
            'usuarioNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/novo[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'novo',
                    ),
                ),
            ),
            //Alterar usuario
            'usuarioAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/alterar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            //Deletar usuario
            'usuarioDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/deletarusuario[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'deletarusuario',
                    ),
                ),
            ),


            'tipousuario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario[/:page]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Novo tipousuario
            'tipousuarioNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/novo[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'novo',
                    ),
                ),
            ),
            //Alterar tipousuario
            'tipousuarioAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/alterar[/:id][/:recurso]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            //Deletar tipousuario
            'tipousuarioDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/deletartipousuario[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'deletartipousuario',
                    ),
                ),
            ),

            //Desvincular recurso
            'recursoDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/deletarrecurso[/:id][/:tipousuario]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'deletarrecurso',
                    ),
                ),
            ),
            //Alterar senha
            'alterarSenha' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/alterarsenha',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'alterarsenha',
                    ),
                ),
            ),
            'recuperarSenha' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/recuperarsenha',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'recuperarsenha',
                    ),
                ),
            ),
            'tokenRecuperar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/recuperarsenha/token[/:token]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'tokenrecuperar',
                    ),
                ),
            ),

            'descricaoRecurso' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/descricaorecurso',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'descricaorecurso',
                    ),
                ),
            ),

        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Usuario\Controller\Cliente' => 'Usuario\Controller\ClienteController',
            'Usuario\Controller\Usuario' => 'Usuario\Controller\UsuarioController',
            'Usuario\Controller\Tipousuario' => 'Usuario\Controller\TipousuarioController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'form/login'        => __DIR__ . '/../view/partials/formLogin.phtml',
            'form/recuperaSenha'        => __DIR__ . '/../view/partials/formRecuperaSenha.phtml',
            'layout/login'           => __DIR__ . '/../view/layout/layoutlogin.phtml',
            'layout/loginavaliador'           => __DIR__ . '/../view/layout/layoutloginavaliador.phtml'
        ),
    ),
);