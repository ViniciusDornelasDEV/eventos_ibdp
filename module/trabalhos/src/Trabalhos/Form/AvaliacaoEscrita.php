<?php

 namespace Trabalhos\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class AvaliacaoEscrita extends BaseForm {
     
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
        
        //normas
        $this->_addRadio('normas', '1 - Adequação às normas: ', true, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        //originalidade
        $this->_addRadio('originalidade', '2 - Originalidade, relevância e atualidade do tema: ', true, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        //relevancia
        $this->_addRadio('relevancia', '3 - Relevância e impacto dos resultados: ', true, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
