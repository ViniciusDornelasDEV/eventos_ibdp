<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Questao extends BaseForm {
     
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
        
        $this->genericTextArea('enunciado', '* Enunciado: ', true, '', true, 1, 99999, 'width: 100%;');

        $this->genericIntegerInput('ordem', '* Ordem: ', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
