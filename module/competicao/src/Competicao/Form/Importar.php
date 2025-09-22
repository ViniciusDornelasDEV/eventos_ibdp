<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm;
 
 class Importar extends BaseForm {
     
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

        $this->addImageFileInput('planilha', '* Planilha (.xls): ', true);
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
