<?php

 namespace Trabalhos\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Avaliar extends BaseForm {
     
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

        //postura
        $this->_addRadio('postura', '2 - Postura do apresentador e tempo de apresentação: ', false, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        //conhecimentos
        $this->_addRadio('conhecimentos', '3 - Conhecimentos científicos do apresentador: ', false, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        //originalidade
        $this->_addRadio('originalidade', '4 - Originalidade, relevância e atualidade do tema: ', true, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        //relevancia
        $this->_addRadio('relevancia', '5 - Relevância e impacto dos resultados: ', true, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        //clareza
        $this->_addRadio('clareza', '6 - Clareza de exposição e concordância do resumo ao que foi apresentado: ', false, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }