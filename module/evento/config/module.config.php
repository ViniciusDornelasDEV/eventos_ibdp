<?php
return array(
    'router' => array(
        'routes' => array(
            'evento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento[/:page]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Novo evento
            'eventoNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/novo',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'novo',
                    ),
                ),
            ),

            'eventoAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/alterar[/:id][/:quantidadeinscricao][/:promocao][/:alternativa][/:promocaoassociado]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                        'quantidadeinscricao'     => '[0-9]+',
                        'promocao'     => '[0-9]+',
                        'alternativa'     => '[0-9]+',
                        'promocaoassociado' => '[0-9]+'
                	),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'alterar',
                    ),
                ),
            ),

            'opcaoAtividadeNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/opcao/adicionar[/:evento][/:opcao]',
                    'constraints' => array(
                        'evento'     => '[0-9]+',
                        'opcao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'adicionaropcao',
                    ),
                ),
            ),
            'opcaoAtividadeDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/opcao/deletar[/:id][/:evento]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'evento'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletaropcao',
                    ),
                ),
            ),


            'eventoDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/deletarevento[/:id]',
                    'constraints' => array(
                    	'id'     => '[0-9]+',
                	),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletarevento',
                    ),
                ),
            ),

            'quantidadeInscricoesDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/deletarquantidadeinscricao[/:id][/:evento]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletarquantidadeinscricoes',
                    ),
                ),
            ),

            'valorInscricoesDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/deletarvalorinscricao[/:id][/:evento]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletarvalorinscricao',
                    ),
                ),
            ),

            'promocaoDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/deletarpromocao[/:id][/:evento]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletarpromocao',
                    ),
                ),
            ),

            'promocaoAssociadoDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/deletarpromocao/associado[/:id][/:evento]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletarpromocaoassociado',
                    ),
                ),
            ),

            

            'alternativaDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/deletaralternativa[/:id][/:evento]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletaralternativa',
                    ),
                ),
            ),

            'mensagemParticipante' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/mensagem[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'mensagemparticipante',
                    ),
                ),
            ),
            'enviarEmailInscritos' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscritos/mensagem/enviar/email',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'enviaremail',
                    ),
                ),
            ),
            

            'camposObrigatorios' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/camposobrigatorios[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'camposobrigatorios',
                    ),
                ),
            ),

            'replicarEvento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/replicar[/:idEvento]',
                    'constraints' => array(
                        'idEvento'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'replicarevento',
                    ),
                ),
            ),

            'categoriasTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/trabalhos/categorias[/:idEvento]',
                    'constraints' => array(
                        'idEvento'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
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
                        'controller' => 'Evento\Controller\Evento',
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
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletarcategoriatrabalho',
                    ),
                ),
            ),
            'cadastrarTransmissao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/transmissoes/cadastrar[/:evento][/:transmissao]',
                    'constraints' => array(
                        'evento'     => '[0-9]+',
                        'transmissao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'cadastrartransmissao',
                    ),
                ),
            ),
            'deletarTransmissao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/transmissoes/deletar[/:evento][/:transmissao]',
                    'constraints' => array(
                        'evento'     => '[0-9]+',
                        'transmissao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Evento',
                        'action'     => 'deletartransmissao',
                    ),
                ),
            ),

            //Inscrições
            'visualizaInscricoes' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'visualizarinscricoes',
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
                        'controller' => 'Evento\Controller\Inscricao',
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
                        'controller' => 'Evento\Controller\Inscricao',
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
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'listartrabalhos',
                    ),
                ),
            ),


            'gerenciarInscricao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/gerenciar[/:id]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'gerenciarinscricao',
                    ),
                ),
            ),
            'statusPagamento' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricao/status/pagamento[/:id]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'statuspagamento',
                    ),
                ),
            ),
            'inserirCodigoPromocional' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/gerenciar/promocao[/:inscricao]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'inserircodigopromocional',
                    ),
                ),
            ),
            'pagarInscricao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/gerenciar/pagar[/:inscricao]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'pagarinscricao',
                    ),
                ),
            ),
            'deletarInscricao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/deletar[/:id]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'deletarinscricao',
                    ),
                ),
            ),
            'realizarInscricao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/novo[/:siglaEvento]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'realizarinscricao',
                    ),
                ),
            ),
            'meusDadosInscricao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/meusdados[/:tipoPessoa][/:siglaEvento][/:valorInscricao]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'meusdados',
                    ),
                ),
            ),
            'cadastrarSenhaInscrito' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/inscricoes/senha',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'cadastrarsenha',
                    ),
                ),
            ),
            'atividades' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/atividades[/:inscricao]',
                    'constraints' => array(
                        'inscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'atividades',
                    ),
                ),
            ),

            
            'sucesso' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/sucesso[/:inscricao]',
                    'constraints' => array(
                        'inscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'sucesso',
                    ),
                ),
            ),
            













            'realizarInscricao2' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/eventos/inscricoes/novo[/:tipoPessoa][/:siglaEvento][/:checkout]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao2',
                        'action'     => 'realizarinscricao',
                    ),
                ),
            ),
            'atividades2' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/eventos/inscricoes/atividades[/:checkout]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao2',
                        'action'     => 'atividades',
                    ),
                ),
            ),
            'pagamento2' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/eventos/inscricoes/pagamento[/:checkout]',
                    'constraints' => array(
              
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao2',
                        'action'     => 'pagamento',
                    ),
                ),
            ),
            'sucesso2' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/eventos/inscricoes/sucesso',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao2',
                        'action'     => 'sucesso',
                    ),
                ),
            ),

            'inscricaoMembroTime' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/time[/:codigolider]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'inscricaomembrotime',
                    ),
                ),
            ),

            'mudarCategoria' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricao/mudarstatus[/:inscricao]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'mudarcategoria',
                    ),
                ),
            ),

            //Rotas de pagamento
            'boletoBradesco' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/pagamento/boleto/itau[/:codigoBoleto]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Pagamento',
                        'action'     => 'boletobradesco',
                    ),
                ),
            ),
            'cielo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/pagamento/cielo',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Pagamento',
                        'action'     => 'cielo',
                    ),
                ),
            ),

            'retornoCielo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/pagamento/cielo/retorno/2y10hQmJgqrxkrksR6k0Yj22uwPG32A3yJprxbT7PAUlyFYQdZbSZgzG',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Pagamento',
                        'action'     => 'retornocielo',
                    ),
                ),
            ),
            'statusCielo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/pagamento/cielo/status/2y10hQmJgqrxkrksR6k0Yj22uwPe32A3yJprxbT7PAUlyFYQdZbSZgzG',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Pagamento',
                        'action'     => 'mudarstatuscielo',
                    ),
                ),
            ),
            'planilhaCielo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/pagamento/cielo/planilha',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Pagamento',
                        'action'     => 'planilhacielo',
                    ),
                ),
            ),

            //PAYPAL
            'paypal' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/pagamento/paypal',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Pagamento',
                        'action'     => 'paypal',
                    ),
                ),
            ),

            'retornoPaypal' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/retorno/paypal',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Pagamento',
                        'action'     => 'retornopaypal',
                    ),
                ),
            ),

            'cancelamentoPaypal' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/cancelamento/paypal',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Pagamento',
                        'action'     => 'cancelamentopaypal',
                    ),
                ),
            ),

            /* RELATORIOS */
            'relatorioInscricoes' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/evento/inscricoes/relatorio[/:evento]',
                    'constraints' => array(
                        'evento'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Relatorios',
                        'action'     => 'relatorioinscricoes',
                    ),
                ),
            ),
            'relatoriosEmpresa' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/evento/relatorio/empresa',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Relatorios',
                        'action'     => 'relatoriosempresa',
                    ),
                ),
            ),
            //cliente
            'inscricoesCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/inscricoes[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'inscricoes',
                    ),
                ),
            ),
            'comprovanteInscricao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/inscricao/comprovante[/:idInscricao]',
                    'constraints' => array(
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'comprovanteinscricao',
                    ),
                ),
            ),
            'downloadComprovante' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/inscricao/comprovante/download[/:idInscricao]',
                    'constraints' => array(
                        'idInscricao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'downloadcomprovante',
                    ),
                ),
            ),
            
            'anuidadesCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/anuidades[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'anuidades',
                    ),
                ),
            ),
            'uploadComprovanteAnuidade' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/anuidades/comprovante[/:idAnuidade][/:idCliente]',
                    'constraints' => array(
                        'idAnuidade'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'uploadcomprovanteanuidade',
                    ),
                ),
            ),
            'pagarAnuidadeCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/anuidades/pagar[/:associado][/:anuidade]',
                    'constraints' => array(
                        'associado'     => '[0-9]+',
                        'anuidade'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'pagaranuidade',
                    ),
                ),
            ),
            'arquivosCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/arquivos[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'arquivos',
                    ),
                ),
            ),
            'questionariosCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/questionarios[/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'questionarios',
                    ),
                ),
            ),

            'visualizarQuestionarioCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/questionario/visualizar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'visualizarquestionario',
                    ),
                ),
            ),

            'meusDadosCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/painel/cliente/meusdados',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'meusdados',
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
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'enviartrabalho',
                    ),
                ),
            ),

            'listarTrabalhosPublic' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos[/:sigla]',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'listartrabalhospublic',
                    ),
                ),
            ),

            'visualizarTrabalhosPublic' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/trabalhos/visualizar[/:idTrabalho]',
                    'constraints' => array(
                        'idTrabalho'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'visualizartrabalhospublic',
                    ),
                ),
            ),

            'downloadArquivoTrabalho' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/painel/cliente/trabalho/download[/:idArquivo]',
                    'constraints' => array(
                        'idArquivo'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Inscricao',
                        'action'     => 'downloadarquivotrabalho',
                    ),
                ),
            ),

            'listarTransmissoes' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/transmissoes',
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'listartransmissoes',
                    ),
                ),
            ),

            'visualizarTransmissao' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/transmissoes/visualizar[/:transmissao]',
                    'constraints' => array(
                        'transmissao'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Evento\Controller\Cliente',
                        'action'     => 'visualizartransmissao',
                    ),
                ),
            ),
            

            

            
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Evento\Controller\Evento' => 'Evento\Controller\EventoController',
            'Evento\Controller\Inscricao' => 'Evento\Controller\InscricaoController',
            'Evento\Controller\Inscricao2' => 'Evento\Controller\Inscricao2Controller',
            'Evento\Controller\Pagamento' => 'Evento\Controller\PagamentoController',
            'Evento\Controller\Relatorios' => 'Evento\Controller\RelatoriosController',
            'Evento\Controller\Cliente'     => 'Evento\Controller\ClienteController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/avaliacao'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/empresa'           => __DIR__ . '/../view/layout/layoutempresa.phtml',
            'layout/cliente'           => __DIR__ . '/../view/layout/layoutcliente.phtml',
            'form/inscricao'              => __DIR__ . '/../view/partials/formInscricao.phtml',
            'form/inscricao2'              => __DIR__ . '/../view/partials/formInscricao2.phtml',
            'layout/cielo'           => __DIR__ . '/../view/layout/layoutcielo.phtml',
            'form/evento'              => __DIR__ . '/../view/partials/formEvento.phtml',
            'form/meusdados'              => __DIR__ . '/../view/partials/formMeusDados.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'statusAssociado'      => 'Evento\Helper\StatusAssociado',
        ),
   ),
);