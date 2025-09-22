<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 use Application\Validator\Porcentagem;

 class CadastrarSenha extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericTextInput('login', 'Login:', false);

        $this->_addPassword('senha', '* Senha: ', 'Senha');
        
        $this->_addPassword('confirma_senha', '* Confirma senha: ', 'Confirmar senha', 'senha');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
