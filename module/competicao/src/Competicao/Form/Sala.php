<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Sala extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        $this->genericTextInput('nome', '* Nome da sala: ', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
