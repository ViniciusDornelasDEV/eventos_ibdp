<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class QuestaoAlternativa extends BaseForm {
     
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
        
        $this->genericIntegerInput('ordem', '* Ordem: ', true);

        $this->genericTextInput('titulo', '* Título: ', true);

        $this->_addDropdown('tipo', '* Tipo: ', true, array('F' => 'Fechada', 'A' => 'Aberta'));

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
