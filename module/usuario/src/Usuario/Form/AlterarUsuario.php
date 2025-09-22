<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class AlterarUsuario extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $usuario)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);
        $this->genericTextInput('nome', '* Nome do usuário:', true, 'Nome do usuário');

        $this->genericTextInput('login', '* Login', true, 'Login');
        
        //Tipo de usuário
        $serviceTipoUsuario = $this->serviceLocator->get('UsuarioTipo');
        $tipos = $serviceTipoUsuario->fetchAll(array('id', 'perfil'));

        if(!$tipos){
            $tipos = array();
        }
        $tipos = $serviceTipoUsuario->prepareForDropDown($tipos, array('id', 'perfil'));
        $this->_addDropdown('id_usuario_tipo', 'Tipo de usuário: ', false, $tipos);

        $this->genericTextInput('senha', 'Alterar senha: ', false, 'Nova senha');

        if($usuario['id_usuario_tipo'] == 3){
            //EMPRESAS
            $empresas = $this->serviceLocator->get('Empresa')->getRecordsFromArray(array(), 'nome_fantasia', array('id', 'nome_fantasia'));
            $empresas = $serviceTipoUsuario->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
            $this->_addDropdown('empresa', ' Empresa:', false, $empresas);
        }

        if($usuario['id_usuario_tipo'] == 2){
            //CLIENTE
            $clientes = $this->serviceLocator->get('Cliente')->getRecordsFromArray(array(), 'nome_completo', array('id', 'nome_completo', 'cpf'));
            $clientes = $serviceTipoUsuario->prepareForDropDown($clientes, array('id', 'nome_completo', 'cpf'), ' - ');
            $this->_addDropdown('cliente', ' Cliente:', false, $clientes);
        }
        

        $this->_addDropdown('ativo', 'Ativo:', false, array('S' => 'Sim', 'N' => 'Não'));
        
        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
