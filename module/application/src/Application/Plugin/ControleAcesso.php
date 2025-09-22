<?php

namespace Application\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\Container;
use Zend\Authentication\AuthenticationService;

class ControleAcesso extends AbstractPlugin
{
    public function verificaAcesso($session, $usuario, $action = 'home') {   	
        //verificar se usuário está logado (caso não esteja redir para login)
        $auth = new AuthenticationService;
        $auth->setStorage($session);
        if(!$auth->hasIdentity()){
        	//redir para login 
        	return false;
        }
        $user = $auth->getIdentity();
        $user = $usuario->getUserData(array('tb_usuario.id' => $user['id']));

        //verificar se usuário tem permissão para acessar página (caso não tenha redir para logout)
        $container = new Container();
        $rota = $action;
        if($container->acl->isAllowed($container->acl->getRole($user->perfil), $rota, $action)){
        	//die('Tem permissao');
        }else{
        	//die('Não tem permissão!');
        }
    }
}
?>