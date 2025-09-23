<?php
return array(
    'router' => array(
        'routes' => array(
            'categoriasTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/categorias[/:idEvento]',
                    'constraints' => array(
                        'idEvento'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'categoriastrabalho',
                    ),
                ),
            ),
            'novaCategoriaTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/categorias/nova[/:idEvento]',
                    'constraints' => array(
                        'idEvento'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'novacategoriatrabalho',
                    ),
                ),
            ),
            'deletarCategoriaTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/categorias/excluir[/:idCategoria]',
                    'constraints' => array(
                        'idCategoria'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'deletarcategoriatrabalho',
                    ),
                ),
            ),





            'visualizarTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/visualizar[/:idInscricao]',
                    'constraints' => array(
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'visualizartrabalho',
                    ),
                ),
            ),
            'deletarTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/deletar[/:idInscricao]',
                    'constraints' => array(
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'deletartrabalho',
                    ),
                ),
            ),
            
            'listarTrabalhos' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/listar[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'listartrabalhos',
                    ),
                ),
            ),
            'recalcularTrabalhos' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/recalcular',
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'recalculartrabalhos',
                    ),
                ),
            ),
            'exportarTrabalhos' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/exportar',
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'exportartrabalhos',
                    ),
                ),
            ),

            'enviarTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/trabalho/enviar[/:idInscricao]',
                    'constraints' => array(
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'enviartrabalho',
                    ),
                ),
            ),

            'vincularPosterTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/poster/enviar[/:idInscricao]',
                    'constraints' => array(
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'vincularposter',
                    ),
                ),
            ),

            'listarTrabalhosPublic' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos[/:page][/:sigla]',
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'listartrabalhospublic',
                    ),
                ),
            ),

            'downloadArquivoTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/trabalho/download[/:idArquivo][/:nomeCampo]',
                    'constraints' => array(
                        'idArquivo'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Trabalho',
                        'action'     => 'downloadarquivotrabalho',
                    ),
                ),
            ),

            'vincularAvaliadorEvento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos/vincular/avaliador[/:idInscricao]',
                    'constraints' => array(
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Avaliadores',
                        'action'     => 'vincularavaliador',
                    ),
                ),
            ),

            'listaTrabalhosAvaliador' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos/listar/avaliador[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Avaliadores',
                        'action'     => 'listartrabalhos',
                    ),
                ),
            ),
            'visualizarTrabalhosAvaliador' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos/avaliador/visualizar[/:idTrabalho]',
                    'constraints' => array(
                        'idTrabalho'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Avaliadores',
                        'action'     => 'visualizartrabalho',
                    ),
                ),
            ),
            'avaliarTrabalhosEventoEscrita' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos/avaliador/avaliar/escrita[/:idTrabalho]',
                    'constraints' => array(
                        'idTrabalho'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Avaliadores',
                        'action'     => 'avaliacaoescrita',
                    ),
                ),
            ),
            'avaliarTrabalhosEventoOral' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos/avaliador/avaliar/oral[/:idTrabalho]',
                    'constraints' => array(
                        'idTrabalho'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Avaliadores',
                        'action'     => 'avaliacaooral',
                    ),
                ),
            ),
            'visualizarAvaliacaoAdminEscrita' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos/avaliacoes/escrita/visualizar[/:idInscricao][/:idAvaliacao]',
                    'constraints' => array(
                        'idAvaliacao'     => '[0-9]+',
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Avaliadores',
                        'action'     => 'visualizaravaliacaoadminescrita',
                    ),
                ),
            ),
            'visualizarAvaliacaoAdminOral' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos/avaliacoes/oral/visualizar[/:idInscricao][/:idAvaliacao]',
                    'constraints' => array(
                        'idAvaliacao'     => '[0-9]+',
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Trabalhos\Controller\Avaliadores',
                        'action'     => 'visualizaravaliacaoadminoral',
                    ),
                ),
            ),
            

        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Trabalhos\Controller\Trabalho' => 'Trabalhos\Controller\TrabalhoController',
            'Trabalhos\Controller\Avaliadores' => 'Trabalhos\Controller\AvaliadoresController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            //'form/empresa'              => __DIR__ . '/../view/partials/formEmpresa.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);