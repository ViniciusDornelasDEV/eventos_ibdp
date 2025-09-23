<?php

 namespace Trabalhos\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class AvaliacaoOral extends BaseForm {
     
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
        
        //postura
        $this->_addRadio('postura', '1 - Postura do apresentador e tempo de apresentação: ', false, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        //conhecimentos
        $this->_addRadio('conhecimentos', '2 - Conhecimentos científicos do apresentador: ', false, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        //clareza
        $this->_addRadio('clareza', '3 - Clareza de exposição e concordância do resumo ao que foi apresentado: ', false, array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
