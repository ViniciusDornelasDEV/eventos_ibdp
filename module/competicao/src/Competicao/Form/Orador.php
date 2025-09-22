<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Orador extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericTextInput('nome', '* Nome: ', true);

        $this->addEmailElement('email', 'Email: ', false);

        $this->_addDropdown('ativo', '* Status: ', true, array('S' => 'Ativo', 'N' => 'Inativo'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
