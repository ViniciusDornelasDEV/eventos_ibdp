<?php

 namespace Cliente\Form;
 
 use Application\Form\Base as BaseForm;
 
 class Upload extends BaseForm {
     
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

        //nome
        $this->addImageFileInput('planilha', '* Planilha: ', true);
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
