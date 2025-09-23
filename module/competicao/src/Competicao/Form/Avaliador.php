<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
  use Application\Validator\Cpf;

 class Avaliador extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericTextInput('nome', '* Nome do avaliador: ', true);

        $this->addEmailElement('email', '* Email de acesso: ', true);

        $this->genericTextInput('cpf', 'CPF: ', false);

        $this->genericTextInput('senha', 'Senha: ', false);

        $this->_addDropdown('ativo', 'Status:', false, array('S' => 'Ativo', 'N' => 'Inativo'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

         $this->getInputFilter()->get('cpf')->getValidatorChain()->addValidator(new Cpf());
    }

 }
