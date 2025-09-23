<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Zend\Authentication\Storage\Session;
use Zend\Authentication\AuthenticationService;
use Zend\Db\TableGateway\TableGateway;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Application\Factory;
use Zend\Session\Container;
use Application\Model\BaseTable;
use Application\Params\Parametros as arrayParams;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {   
        //se veio HTTP redir p/ HTTPS
        /*if($_SERVER['SERVER_PORT'] != '443') {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit();
        }*/
        ini_set('date.timezone', "America/Sao_Paulo");
        //Config app e service manager
        $this->app = $e->getApplication();
        $this->serviceManager = $this->app->getServiceManager();

        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        //caso haja algum erro não renderizar o layout(ele tem o menu!)
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'handleError'));
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'handleError'));

        //set up some rules here 
        $viewHelperManager = $e->getApplication()->getServiceManager()->get('ViewHelperManager');
        $pluralHelper = $viewHelperManager->get('Plural');
        // Here is the rule for English
        $pluralHelper->setPluralRule('nplurals=2; plural=(n==1 ? 0 : 1)');

        //Pegar rota atual
        $router = $this->serviceManager->get('router');
        $request = $this->serviceManager->get('request');
            $routeMatch = $router->match($request);
        if($routeMatch){
            $rota = $routeMatch->getMatchedRouteName();
            //Verifica acessos
            $session = $this->serviceManager->get('session');
            $usuario = $this->serviceManager->get('Usuario');
            

            if(!$this->verificaAcesso($session, $usuario, $rota)){
                $usuario = $this->serviceManager->get('session')->read();
                $this->dispatchToLogout($e);
            }
        }
        header('Access-Control-Allow-Origin: *');
    }

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
                    'Application\Validator'
                ),
            ),
        );
    }

    public function getServiceConfig() {

        return array(
            'factories' => array(
                /* DATABASE ADAPTER OBJECTS */
                'db_adapter_factory' => function($sm) {
                    return new Factory\MyAdapterFactory($sm);
                },
                'db_adapter_main' => function($sm) {
                    $factory = $sm->get('db_adapter_factory');
                    $factory->setConfigKey('db');
                    
                    return $factory->createService();
                },
                'Mailer' => function() {
                    $params = new arrayParams();
                    $from = array(
                        'name' => $params->getEmailName(),
                        'email' => $params->getEmail(),
                        'contact_details' => array());

                    $mailer = new Service\Mailer($from);
                    $mailer->setContactDetails($from['contact_details']);

                    return $mailer;
                },
                'session' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session']['config']['options']['name'];
                        
                        //Various Session options
                        $manager = new \Zend\Session\SessionManager();                        
                        
                         if(filter_input(INPUT_SERVER, 'APPLICATION_ENV') === 'production'){
                             
                            $manager->getConfig()
                                    ->setCookieHttpOnly(true)
                                    ->setCookieSecure(false);
                            $manager->start();

                        }
                        
                        return new Session($session);
                    }
                },
                'Pais' => function($sm) {
                    $tableGateway = new TableGateway('tb_pais', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Estado' => function($sm) {
                    $tableGateway = new TableGateway('tb_estado', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Cidade' => function($sm) {
                    $tableGateway = new TableGateway('tb_cidade', $sm->get('db_adapter_main'));
                    $updates = new Model\Cidade($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Recurso' => function($sm) {
                    $tableGateway = new TableGateway('tb_recurso', $sm->get('db_adapter_main'));
                    $updates = new Model\Recurso($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
            ),
        );
    }

    public function verificaAcesso($session, $usuario, $rota = 'home') { 
        $rotasPublicas = array('logout', 'login', 'recuperarSenha', 'realizarInscricao', 'meusDadosInscricao', 'cadastrarSenhaInscrito', 'cidade',
                                'pagamento', 'sucesso', 'boletoBradesco', 'cielo', 'retornoCielo',
                                'statusCielo', 'paypal', 'retornoPaypal', 'cancelamentoPaypal', 'inscricaoMembroTime', 
                                'atividades', 'realizarInscricao2',
                                'atividades2', 'pagamento2', 'sucesso2', 'https://pagseguro.uol.com.br/checkout/v2/payment.html',
                                'buscarCertificado', 'cep', 'buscarClienteCpf', 'pesquisarAssociados', 'downloadArquivoTrabalho',
                                'listarTrabalhosPublic', 'tokenRecuperar', 
                                'competicaoClassificacao', 'competicaoVisualizarChaveamento', 'loginCompeticao', 'retornoAssociadoIpag',
                                'realizarPagamentoInscricaoIpag', 'realizarPagamentoInscricaoIpagPix', 'retornoInscricaoIpag'
            );

        if(in_array($rota, $rotasPublicas)) {
            return true;
        }

        //verificar se usuário está logado (caso não esteja redir para login)
        $auth = new AuthenticationService;
        $auth->setStorage($session);
        if(!$auth->hasIdentity()){
            //redir para login 
            return false;
        }
        $user = $auth->getIdentity();
        if($user['id_usuario_tipo'] == 4){
          $usuario = $this->serviceManager->get('Avaliador');
          $user = $usuario->getUserData(array('tb_competicao_avaliador.id' => $user['id']));
        }else{
          $user = $usuario->getUserData(array('tb_usuario.id' => $user['id']));
        }

        //verificar se usuário tem permissão para acessar página (caso não tenha redir para logout)
        $container = new Container();
        if($container->acl->isAllowed($container->acl->getRole($user->perfil), $rota, $rota)){
            return true;
        }else{
            return false;
        }
    }

    protected function dispatchToLogout($event) {
        $url = $event->getRouter()->assemble(array('controller' => 'Usuario\Controller\Usuario'), array('name' => 'logout'));
        $response = $event->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
    }


    public function handleError(MvcEvent $event) {
        $result = $event->getResult(); 
        $result->setTerminal(true);
    }
}
