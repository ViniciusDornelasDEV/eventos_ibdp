<?php

namespace Usuario\Controller;

use Application\Controller\BaseController;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter as AuthAdapter;
use Zend\Crypt\Password\Bcrypt;
use Zend\Authentication\Result;
use Zend\Session\SessionManager;

use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Mail;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Session\Container;
use Usuario\Form\Usuario as usuarioForm;
use Usuario\Form\AlterarUsuario as alterarUsuarioForm;
use Usuario\Form\PesquisaUsuario as pesquisaForm;
use Usuario\Form\AlterarSenha as alterarSenhaForm;
use Usuario\Form\AlterarToken as alterarToken;
use Usuario\Form\RecuperarSenha as novaSenhaForm;
use Usuario\Form\Login as loginForm;
use Usuario\Form\LoginAvaliador as loginAvaliadorForm;
use Application\Params\Parametros;

class UsuarioController extends BaseController
{
    public function loginAction()
    { 
        $this->layout('layout/login');
    	$form = new loginForm();
        
        //Log in
        $request = $this->getRequest();
        $post = $request->getPost();
        
        if(!isset($post->login)) {
            if(isset($_POST['login'])){
                $post = $_POST;
            }else{
                //header("Location: http://www.rstconsultoria.com.br/");
                //die();
            }
        }

        $reCaptchaValid = true;
        $credenciais = Parametros::getCredenciaisReCaptcha();
        if ($request->isPost()) {
            $form->setData($post);

            if ($form->isValid()) {
                if($_POST['g-recaptcha-response']){
                    $resposta = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$credenciais['private'].'&response='.$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
                    $resposta = json_decode($resposta);
                    if(!$resposta->success){
                        die('Ocorreu algum problema, por favor tente novamente!');
                    }

                    $data = $form->getData();

                    // Configure the instance with constructor parameters...

                    $authAdapter = new AuthAdapter($this->getServiceLocator()
                                        ->get('db_adapter_main'), 'tb_usuario', 'login', 'senha', 
                                        function($dbCredential, $requestCredential) {
                                            $bcrypt = new Bcrypt();
                                            return $bcrypt->verify($requestCredential, $dbCredential);
                    });
                    //apenas ativo = S
                    $select = $authAdapter->getDbSelect();
                    $select->where('ativo = "S"');
                    $authAdapter
                            ->setTableName('tb_usuario')
                            ->setIdentityColumn('login')
                            ->setCredentialColumn('senha');

                    $authAdapter
                            ->setIdentity($data['login'])
                            ->setCredential($data['password']);    

                    $result = $authAdapter->authenticate()->getCode();    
                    
                    
                    $session = $this->getServiceLocator()->get('session'); 
                   
                    if ($result === Result::SUCCESS) {
                        //remember me?
                        if(isset($post->remember_me) && $post->remember_me == 1) {                     
                            $defaultNamespace = new SessionManager();
                            $defaultNamespace->rememberMe();
                        }            
                        
                        $user = (array)$authAdapter->getResultRowObject();    
                        $session->write($user);                                       
                        
                        //Create acl config
                        $sessao = new Container();
                        $sessao->acl = $this->criarAutorizacao();
                        if(!empty($user['cliente'])){
                            return $this->redirect()->toRoute('inscricoesCliente');
                        }

                        return $this->redirect()->toRoute('home');
                        
                    } else {
                    	//form invalido
                        $session->clear();
                        //$this->flashMessenger()->addWarningMessage('Login ou senha inválidos!');
                        return $this->redirect()->toRoute('login');
                    }
                }else{
                    $reCaptchaValid = false;
                }
            }
        }        

        return new ViewModel(array(
            'form' => $form, 
            'reCaptchaValid' => $reCaptchaValid,
            'credenciais' => $credenciais
        ));

    }

    public function loginavaliadorAction()
    { 
      ini_set('session.gc_maxlifetime', 1209600);
      $this->layout('layout/loginavaliador');
      $form = new loginAvaliadorForm();
        
      //Log in
      $request = $this->getRequest();
      $post = $request->getPost();
      
      if(!isset($post->login)) {
          if(isset($_POST['login'])){
              $post = $_POST;
          }else{
              //header("Location: http://www.rstconsultoria.com.br/");
              //die();
          }
      }
      $reCaptchaValid = true;
      $credenciais = Parametros::getCredenciaisReCaptcha();
      if ($request->isPost()) {
          $form->setData($post);

          if ($form->isValid()) {
            if($_POST['g-recaptcha-response']){
                $resposta = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$credenciais['private']."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
                $resposta = json_decode($resposta);
                if(!$resposta->success){
                    die('Ocorreu algum problema, por favor tente novamente!');
                }

                $data = $form->getData();
                $data['login'] = strtoupper($data['login']);
                $data['password'] = strtoupper($data['password']);

                // Configure the instance with constructor parameters...

                $authAdapter = new AuthAdapter($this->getServiceLocator()
                                  ->get('db_adapter_main'), 'tb_competicao_avaliador', 'email', 'senha', 
                                  function($dbCredential, $requestCredential) {
                                      $bcrypt = new Bcrypt();
                                      return $bcrypt->verify($requestCredential, $dbCredential);
                });
                //apenas ativo = S
                $select = $authAdapter->getDbSelect();
                $select->where('ativo = "S"');
                $authAdapter
                      ->setTableName('tb_competicao_avaliador')
                      ->setIdentityColumn('email')
                      ->setCredentialColumn('senha');

                $authAdapter
                      ->setIdentity($data['login'])
                      ->setCredential($data['password']);    

                $result = $authAdapter->authenticate()->getCode();    


                $session = $this->getServiceLocator()->get('session'); 


                if ($result === Result::SUCCESS) {
                  //remember me?
                  if(isset($post->remember_me) && $post->remember_me == 1) {                     
                      $defaultNamespace = new SessionManager();
                      $defaultNamespace->rememberMe();
                  }            
                  
                  $user = (array)$authAdapter->getResultRowObject();
                  //$user['id_usuario_tipo'] = 4;
                  $session->write($user);                                       
                  //Create acl config
                  $sessao = new Container();
                  $sessao->acl = $this->criarAutorizacao();
                  $competicao = $this->getServiceLocator()
                    ->get('Competicao')
                    ->getRecords('S', 'ativo', array('*'), 'id DESC')->current();

                  return $this->redirect()->toRoute('competicaoVisualizarChaveamento', array('idCompeticao' => $competicao->id));

                  
                } else {
                //form invalido
                  $session->clear();
                  //$this->flashMessenger()->addWarningMessage('Login ou senha inválidos!');
                  return $this->redirect()->toRoute('login');
                }
            }else{
                $reCaptchaValid = false;
            }

          }
        }        

        return new ViewModel(array(
            'form' => $form, 
            'reCaptchaValid' => $reCaptchaValid,
            'credenciais' => $credenciais
        ));

    }

    public function logoutAction() {
        $session = $this->getServiceLocator()->get('session');  
        $defaultNamespace = new SessionManager();
        $defaultNamespace->destroy();
        $session->clear();
        return $this->redirect()->toRoute('login');
    }

    public function alterarsenhaAction() {
        $usuario = $this->getServiceLocator()->get('session')->read();
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
        }

        if($usuario['id_usuario_tipo'] == 2){
            $this->layout('layout/cliente');
        }

        $form = new alterarSenhaForm('frmUsuario');
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $form->setData($dados);
            if($form->isValid()){
                //Pegar usuário logado
                $serviceUsuario = $this->getServiceLocator()->get('Usuario');
                $usuario = $this->getServiceLocator()->get('session')->read();
                $bcrypt = new bcrypt();                

                if(!$bcrypt->verify($dados['senha_atual'], $usuario['senha'])){
                    $this->flashMessenger()->addWarningMessage('Senha atual não confere!');
                    return $this->redirect()->toRoute('alterarSenha');
                }
                //alterar senha
                $usuario['senha'] = $bcrypt->create($dados['senha']);
                if($serviceUsuario->update($usuario, array('id' => $usuario['id']))){
                    $this->flashMessenger()->addSuccessMessage('Senha alterada com sucesso!');  
                    return $this->redirect()->toRoute('logout');
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao alterar senha!');
                    return $this->redirect()->toRoute('alterarSenha');
                }
                
            }
        }
        return new ViewModel(array('form' => $form));
    }

    public function recuperarsenhaAction(){
        $this->layout('layout/login');
        $form = new novaSenhaForm('frmRecuperaSenha');
        
        $reCaptchaValid = true;
        $credenciais = Parametros::getCredenciaisReCaptcha();
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $form->setData($dados);
            if($form->isValid()){
                if($_POST['g-recaptcha-response']){
                    $resposta = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$credenciais['private']."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
                    $resposta = json_decode($resposta);
                    if(!$resposta->success){
                        die('Ocorreu algum problema, por favor tente novamente!');
                    }

                    $bcrypt = new bcrypt();                
                    $serviceUsuario = $this->getServiceLocator()->get('Usuario');
                    //pesquisar usuário por email
                    $usuario = $serviceUsuario->getRecord($dados->cpf, 'login');
                    //se não encontrar procurar em cliente
                    if($usuario){
                        $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($usuario->cliente);   
                        if(!$cliente){
                            $this->flashMessenger()->addErrorMessage('Cliente não encontrado!');
                            return $this->redirect()->toRoute('recuperarSenha');
                        }
                    }else{
                        $this->flashMessenger()->addErrorMessage('CPF não encontrado!');
                        return $this->redirect()->toRoute('recuperarSenha');
                    }

                    //gerar o token
                    $token = date('is').sprintf('%07X', mt_rand(0, 0xFFFFFFF)).'+'.$usuario->id;
                    
                    //recuperar baseUrl
                    $uri = $this->getRequest()->getUri();
                    $base = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
                    $base = $base.'/usuario/recuperarsenha/token/'.$token;
                    if($serviceUsuario->update(array('token_recuperar' => $token, 'token_expira' => date('Y-m-d H:i',strtotime('+1 hour',strtotime(date('Y-m-d H:i'))))), array('id' => $usuario->id))){
                        $this->flashMessenger()->addSuccessMessage('Enviamos um link de recuperação para seu email!');  
                        $mailer = $this->getServiceLocator()->get('mailer');
                        $mailer->mailUser($cliente->email, 'Recuperar senha', 'Acesse o link paa recuperar a senha: <br>'.$base.'
                            <br>O link tem validade de uma hora!');
                        return $this->redirect()->toRoute('login');
                    }else{
                        $this->flashMessenger()->addErrorMessage('Falha ao recuperar senha!');
                        return $this->redirect()->toRoute('recuperarSenha');
                    }
                }else{
                    $reCaptchaValid = false;
                }

                
            }
            
        }

        return new ViewModel(array(
            'form' => $form, 
            'reCaptchaValid' => $reCaptchaValid, 
            'credenciais'   => $credenciais
        ));
    }

    public function tokenrecuperarAction(){
        $this->layout('layout/login');

        //receber o token
        $token = $this->params()->fromRoute('token');

        //verificar  se existe esse token na base de dados, pesquisar usuário
        $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($token, 'token_recuperar');
        
        //verificar se token é do usuário
        $idUsuario = explode('+', $token);
        $idUsuario = $idUsuario[1];
        if(!$usuario || $idUsuario != $usuario['id'] || empty($token)){
            $this->flashMessenger()->addWarningMessage('Token inválido!');
            return $this->redirect()->toRoute('recuperarSenha');
        }

        //verificar se não expirou
        if(strtotime(date('Y-m-d H:i')) < $usuario['token_expira']){
            $this->flashMessenger()->addWarningMessage('Token inválido!');
            return $this->redirect()->toRoute('recuperarSenha');
        }

        $form = new alterarToken('frmUsuario');
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $form->setData($dados);
            if($form->isValid()){
                //Pegar usuário logado
                $bcrypt = new bcrypt();                

                //alterar senha
                $dadosUsuario = array();
                $dadosUsuario['senha'] = $bcrypt->create($dados['senha']);
                $dadosUsuario['token_recuperar'] = '';
                $dadosUsuario['token_expira'] = '';
                if($this->getServiceLocator()->get('Usuario')->update($dadosUsuario, array('id' => $usuario['id']))){
                    $this->flashMessenger()->addSuccessMessage('Senha alterada com sucesso!');  
                    return $this->redirect()->toRoute('logout');
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao alterar senha!');
                    return $this->redirect()->toRoute('alterarSenha');
                }
                
            }
        }
        return new ViewModel(array('form' => $form));
    }

    public function criarAutorizacao() {
        //pesquisar perfil de usuário
        $serviceUsuario = $this->getServiceLocator()->get('UsuarioTipo');
        $perfilUsuario = $serviceUsuario->getRecord($serviceUsuario->getIdentity('id_usuario_tipo'));
        
        //criando papel do usuário
        $acl = new Acl();
        $papel = new Role($perfilUsuario['perfil']);
        $acl->addRole($papel);

        //definindo recursos existentes no sistema
        $serviceRecurso = $this->getServiceLocator()->get('Recurso');
        $recursos = $serviceRecurso->fetchAll();
        foreach ($recursos as $resource) {
            $acl->addResource(new Resource($resource->nome));
        }

        //Adicionar permissões
        $recursosUsuario = $serviceRecurso->getRecursosByTipoUsuario(array('usuario_tipo' => $perfilUsuario['id']));
        foreach ($recursosUsuario as $resource) {
            $acl->allow($perfilUsuario['perfil'], $resource->nome);
        }
        return $acl;
    }

    public function indexAction(){
        $formPesquisa = new pesquisaForm('frmPesquisa', $this->getServiceLocator());

        $dados = false;
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formPesquisa->setData($dados);
        }
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');
        $usuarios = $serviceUsuario->getUsuariosByParams($dados);


        $Paginator = new Paginator(new ArrayAdapter($usuarios->toArray()));
        $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $Paginator->setItemCountPerPage(10);
        $Paginator->setPageRange(5);
        
        return new ViewModel(array(
                                'usuarios'      => $Paginator, 
                                'formPesquisa'   => $formPesquisa,
                            ));
    }

    public function novoAction(){
        $formUsuario = new usuarioForm('frmUsuario', $this->getServiceLocator());
        
        //caso venha um post salvar
        if($this->getRequest()->isPost()){
            //salvar e enviar para  edit
            $dados = $this->getRequest()->getPost();
            $serviceUsuario = $this->getServiceLocator()->get('Usuario');
            
            //validar form
            $formUsuario->setData($dados);
            if($formUsuario->isValid()){  
                $bcrypt = new Bcrypt();
                $dados = $formUsuario->getData();
                $dados['senha'] = $bcrypt->create($dados['senha']);

                $result = $serviceUsuario->insert($dados);
                if($result){
                    
                    //sucesso criar mensagem e redir para edit
                    $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');                
                    return $this->redirecionar($result);
                }else{
                    //falha, exibir mensagem
                    $this->flashMessenger()->addErrorMessage('Falha ao inserir usuário!');
                }
            }

        }
        
        return new ViewModel(array('formUsuario' => $formUsuario));
    }


    public function alterarAction(){
        //Pesquisar cliente
        $idUsuario = $this->params()->fromRoute('id');
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');
        $usuario = $serviceUsuario->getRecordFromArray(array('id' => $idUsuario));

        //Popular form
        $formUsuario = new alterarUsuarioForm('frmUsuario', $this->getServiceLocator(), $usuario);
        //$formUsuario->remove('senha');
        //$formUsuario->remove('confirma_senha');
        unset($usuario['senha']);
        $formUsuario->setData($usuario);
        
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost()->toArray();
            $formUsuario->setData($dados);
            
            if($formUsuario->isValid()){
                if((empty($dados['senha']))){
                    unset($dados['senha']);
                }else{
                    $bcrypt = new Bcrypt();
                    $dados['senha'] = $bcrypt->create($dados['senha']);
                    
                    //salvar log de alteração
                    $usuarioLogado = $this->getServiceLocator()->get('session')->read();
                    parent::logSistema('Usuario '.$usuarioLogado['id'].' alterou a senha do usuário '.$usuario->id.' em '.date('d/m/Y H:i:s'), 'alterarUsuario');
                }
                $serviceUsuario->update($dados, array('id'  =>  $usuario->id));
                $this->flashMessenger()->addSuccessMessage('Usuario alterado com sucesso!'); 
                return $this->redirecionar($usuario->id);
            }
        }

        return new ViewModel(array(
                                'formUsuario' => $formUsuario,
                                'usuario'     => $usuario
                                )
                            );
    }

    public function deletarusuarioAction(){
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');

        $res = $serviceUsuario->update(array('ativo' => 'N'), array('id' => $this->params()->fromRoute('id')));
        if($res){
           $this->flashMessenger()->addSuccessMessage('Usuário desativado com sucesso!');  
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao desativar usuário!');
        }
        return $this->redirect()->toRoute('usuario');
    }

    private function redirecionar($usuario){
        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario));
    }


}

