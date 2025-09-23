<?php
return array(
    'router' => array(
        'routes' => array(
            'faculdades' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/faculdades[/:page]',
                    'constraints' => array(
                      'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Faculdade',
                        'action'     => 'index',
                    ),
                ),
            ),
            'faculdadeNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/faculdade/novo',
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Faculdade',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'faculdadeAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/faculdade/alterar[/:id]',
                    'constraints' => array(
                      'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Faculdade',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            'faculdadeOradores' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/faculdade/oradores[/:idFaculdade][/:id]',
                    'constraints' => array(
                      'idFaculdade'     => '[0-9]+',
                      'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Faculdade',
                        'action'     => 'oradores',
                    ),
                ),
            ),
            'importarFaculdades' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/faculdade/importar',
                    'constraints' => array(
                      'idFaculdade'     => '[0-9]+',
                      'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Faculdade',
                        'action'     => 'importarfaculdades',
                    ),
                ),
            ),

            //PAINEL
            'competicoes' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicoes[/:page]',
                    'constraints' => array(
                      'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'index',
                    ),
                ),
            ),
            'competicaoNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/competicao/nova',
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'competicaoAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/alterar[/:idCompeticao]',
                    'constraints' => array(
                      'idCompeticao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            'competicaoSalas' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/salas[/:idCompeticao][/:idSala]',
                    'constraints' => array(
                      'idCompeticao'     => '[0-9]+',
                      'idSala'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'salas',
                    ),
                ),
            ),
            'competicaoDeletarSalas' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/salas/deletar[/:idCompeticao][/:idSala]',
                    'constraints' => array(
                      'idCompeticao'     => '[0-9]+',
                      'idSala'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'deletarsalas',
                    ),
                ),
            ),
            'competicaoChaveamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/chaveamento[/:idCompeticao][/:idChave]',
                    'constraints' => array(
                      'idCompeticao' => '[0-9]+',
                      'idChave'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'chaveamento',
                    ),
                ),
            ),
            'deletarRodada' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/chaveamento/deletar[/:idCompeticao][/:idChave]',
                    'constraints' => array(
                      'idCompeticao' => '[0-9]+',
                      'idChave'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'deletarrodada',
                    ),
                ),
            ),
            'visualizarAvaliacoesCompeticao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/chaveamento/notas[/:idChave][/:avaliacao]',
                    'constraints' => array(
                      'idChave'     => '[0-9]+',
                      'avaliacao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'visualizaravaliacoes',
                    ),
                ),
            ),
            'deletarAvaliacaoCompeticao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/chaveamento/notas/deletar[/:idCompeticao][/:idChave][/:idNota]',
                    'constraints' => array(
                      'idCompeticao' => '[0-9]+',
                      'idNota'     => '[0-9]+',
                      'idChave'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'deletaravaliacao',
                    ),
                ),
            ),
            
            'competicaoClassificacao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/classificacao[/:idCompeticao]',
                    'constraints' => array(
                      'idCompeticao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'classificacao',
                    ),
                ),
            ),
            'competicaoVisualizarChaveamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/chaveamento/visualizar[/:idCompeticao]',
                    'constraints' => array(
                      'idCompeticao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'visualizarchaveamento',
                    ),
                ),
            ),
            'exportarCompeticao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/chaveamento/exportar[/:idCompeticao]',
                    'constraints' => array(
                      'idCompeticao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'exportarcompeticao',
                    ),
                ),
            ),
            'exportarCompetidores' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/competidores/exportar[/:idCompeticao]',
                    'constraints' => array(
                      'idCompeticao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Competicao',
                        'action'     => 'exportarcompetidores',
                    ),
                ),
            ),


            //AVALIADOR
            'avaliarCompeticao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/competicao/avaliar[/:idPartida]',
                    'constraints' => array(
                      'idPartida'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Avaliador',
                        'action'     => 'avaliar',
                    ),
                ),
            ),
            'avaliadoresCompeticao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliadores[/:page]',
                    'constraints' => array(
                      'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Avaliador',
                        'action'     => 'index',
                    ),
                ),
            ),
            'avaliadorNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliador/novo',
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Avaliador',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'avaliadorAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/avaliador/alterar[/:id]',
                    'constraints' => array(
                      'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Avaliador',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            'importarAvaliadores' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/avaliador/importar',
                    'constraints' => array(
                      'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Competicao\Controller\Avaliador',
                        'action'     => 'importar',
                    ),
                ),
            ),

        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Competicao\Controller\Faculdade'     => 'Competicao\Controller\FaculdadeController',
            'Competicao\Controller\Avaliador'  => 'Competicao\Controller\AvaliadorController',
            'Competicao\Controller\Competicao'        => 'Competicao\Controller\CompeticaoController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(
          'layout/competicao'           => __DIR__ . '/../view/layout/layoutCompeticao.phtml',
          'layout/chaveamento'           => __DIR__ . '/../view/layout/layoutChaveamento.phtml',
         ),
    ),
);