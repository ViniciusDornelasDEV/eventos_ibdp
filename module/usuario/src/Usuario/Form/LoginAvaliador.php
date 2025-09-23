<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 
 
 
 class LoginAvaliador extends BaseForm
 {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name = null)
    {
        parent::__construct($name);
        $this->addEmailElement('login', 'Email', true, 'Email de acesso');
        
        //$this->_addPassword('password', 'Password', 'Senha');
        $this->_addCheckbox('remember_me', 'Remember me', false, '');
        $this->addEmailElement('password', 'Senha', true, 'Confirme seu email', 'login');

        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));
        $this->addSubmit('Entrar', 'btn btn-lg btn-success btn-block');
    }
 }
