<?php
return array(
    'router' => array(
        'routes' => array(
            'listaCategoriaAssociados' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/categorias[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Nova categoria
            'categoriaAssociadosNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/categorias/novo',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'novo',
                    ),
                ),
            ),

            'categoriaAssociadosAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/categorias/alterar[/:idCategoria][/:idAnuidade][/:idArquivo]',
                    'constraints' => array(
                        'idCategoria'   => '[0-9]+',
                        'idAnuidade'    => '[0-9]+',
                        'idArquivo'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            
            'downloadArquivoAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/download[/:idArquivo]',
                    'constraints' => array(
                        'idArquivo'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'downloadassociado',
                    ),
                ),
            ),
            
            'deletarArquivoAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/arquivos/deletar[/:idArquivo][/:idCategoria]',
                    'constraints' => array(
                        'idArquivo'     => '[0-9]+',
                        'idCategoria'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'deletararquivoassociado',
                    ),
                ),
            ),
            'mensagemAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/mensagem',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'mensagemassociado',
                    ),
                ),
            ),
            'enviarEmail' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/mensagem/enviar/email',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'enviaremail',
                    ),
                ),
            ),
            'associadosAdimplentesAnuidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/marcar/adimplente[/:idAnuidade][/:idCategoria]',
                    'constraints' => array(
                        'idAnuidade'     => '[0-9]+',
                        'idCategoria'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'associadosadimplentes',
                    ),
                ),
            ),
            'associadosListarSite' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/marcar/listagem[/:idAnuidade][/:idCategoria]',
                    'constraints' => array(
                        'idAnuidade'     => '[0-9]+',
                        'idCategoria'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Categoria',
                        'action'     => 'listarassociadossite',
                    ),
                ),
            ),


            //associados
           'listaAssociados' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/lista[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Associados',
                        'action'     => 'index',
                    ),
                ),
            ),
            'associadosNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/novo',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Associados',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'associadosAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/alterar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Associados',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            'downloadArquivoIndividual' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/arquivos/individual/download[/:idArquivo]',
                    'constraints' => array(
                        'idArquivo'       => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Associados',
                        'action'     => 'downloadarquivoindividual',
                    ),
                ),
            ),
            'deletarArquivoIndividualAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/arquivos/individual/deletar[/:idAssociado][/:idArquivo]',
                    'constraints' => array(
                        'idAssociado'     => '[0-9]+',
                        'idArquivo'       => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Associados',
                        'action'     => 'deletararquivo',
                    ),
                ),
            ),
            'carregarCategoriaAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/carregar/categoria/associado',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Associados',
                        'action'     => 'carregarcategoriaassociado',
                    ),
                ),
            ),
            'importarAssociados' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/importar',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Associados',
                        'action'     => 'importarassociados',
                    ),
                ),
            ),
            'exportarAssociados' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/exportar',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Associados',
                        'action'     => 'exportarassociados',
                    ),
                ),
            ),

            //pagamento
            'listaPagamentosAssociados' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/pagamentos[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Pagamento',
                        'action'     => 'index',
                    ),
                ),
            ),
            'downloadComprovanteAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/pagamentos/comprovante[/:id]',
                    'constraints' => array(
                        'page'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Pagamento',
                        'action'     => 'downloadcomprovante',
                    ),
                ),
            ),
            'pagarAnuidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/pagamentos/pagar[/:idAssociado][/:idAnuidade]',
                    'constraints' => array(
                        'idAssociado'     => '[0-9]+',
                        'idAnuidade'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Pagamento',
                        'action'     => 'pagar',
                    ),
                ),
            ),
            'cancelarPagamentoAnuidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/pagamentos/cancelar[/:idAssociado][/:idAnuidade]',
                    'constraints' => array(
                        'idAssociado'     => '[0-9]+',
                        'idAnuidade'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Pagamento',
                        'action'     => 'cancelarpagamento',
                    ),
                ),
            ),
            'carregarAnuidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/pagamentos/carregaranuidade',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Pagamento',
                        'action'     => 'carregaranuidade',
                    ),
                ),
            ),
            'anuidadePaypal' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associado/pagamento/paypal[/:idAssociado][/:idAnuidade]',
                    'constraints' => array(
                        'idAssociado'     => '[0-9]+',
                        'idAnuidade'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Pagamento',
                        'action'     => 'paypal',
                    ),
                ),
            ),
            'anuidadeRetornoPaypal' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associado/pagamento/paypal/retorno',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Pagamento',
                        'action'     => 'retornopaypal',
                    ),
                ),
            ),
            'anuidadeCancelamentoPaypal' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associado/pagamento/paypal/cancelamento',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Pagamento',
                        'action'     => 'cancelamentopaypal',
                    ),
                ),
            ),

            //QUESTIONARIO
            'listaQuestionarioAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/questionario/listar[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Questionario',
                        'action'     => 'index',
                    ),
                ),
            ),
            'listarRespostasQuestionario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/questionarios/respostas[/:page]',
                    'constraints' => array(
                        'page'              => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Questionario',
                        'action'     => 'listarrespostasquestionario',
                    ),
                ),
            ),
             
            'novoQuestionarioAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/questionario/novo',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Questionario',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'alterarQuestionarioAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/questionario/alterar[/:idQuestionario]',
                    'constraints' => array(
                        'idQuestionario'   => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Questionario',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            'questionarioQuestaoAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/questionario/questao[/:idQuestionario][/:idQuestao]',
                    'constraints' => array(
                        'idQuestionario'   => '[0-9]+',
                        'idQuestao'   => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Questionario',
                        'action'     => 'questao',
                    ),
                ),
            ),
            'questaoAlternativaAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/questionario/alternativa[/:idQuestionario][/:idQuestao][/:idAlternativa]',
                    'constraints' => array(
                        'idQuestionario'   => '[0-9]+',
                        'idQuestao'   => '[0-9]+',
                        'idAlternativa'   => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Questionario',
                        'action'     => 'alternativa',
                    ),
                ),
            ),
            'responderQuestionarioAssociado' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/associados/questionario/responder[/:idQuestionario]',
                    'constraints' => array(
                        'idQuestionario'   => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Questionario',
                        'action'     => 'responderquestionario',
                    ),
                ),
            ),

            //GRÁFICOS
            'graficoAssociadosQuestao' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/grafico/pizza/questao',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Graficos',
                        'action'     => 'pizzaquestao',
                    ),
                ),
            ),

            'graficoAssociadosQuestionario' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/grafico/barras/questionario',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Graficos',
                        'action'     => 'barrasquestionario',
                    ),
                ),
            ),

            'graficoAssociadosRespondido' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/grafico/respondidos',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Graficos',
                        'action'     => 'respondidos',
                    ),
                ),
            ),

            'questionariosByEmpresa' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/grafico/pesquisar/questionarios',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Graficos',
                        'action'     => 'questionariosbyempresa',
                    ),
                ),
            ),

            'questoesByQuestionario' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/associados/grafico/pesquisar/questoes',
                    'defaults' => array(
                        'controller' => 'Associados\Controller\Graficos',
                        'action'     => 'questoesbyquestionario',
                    ),
                ),
            ),


        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Associados\Controller\Categoria' => 'Associados\Controller\CategoriaController',
            'Associados\Controller\Associados' => 'Associados\Controller\AssociadosController',
            'Associados\Controller\Pagamento' => 'Associados\Controller\PagamentoController',
            'Associados\Controller\Questionario' => 'Associados\Controller\QuestionarioController',
            'Associados\Controller\Graficos'    => 'Associados\Controller\GraficosController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'form/associado'              => __DIR__ . '/../view/partials/formAssociado.phtml',
            'form/questionario'              => __DIR__ . '/../view/partials/formQuestionario.phtml',
            'responder/questionario'         => __DIR__ . '/../view/partials/responderQuestionario.phtml',
            'visualizar/questionario'         => __DIR__ . '/../view/partials/visualizarQuestionario.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'adimplente'      => 'Associados\Helper\Adimplente',
        ),
   ),
);