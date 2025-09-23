<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class ArquivosAssociados extends BaseForm {
     
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

        $this->genericTextInput('descricao', '* Descrição:', true, '');

        $this->addImageFileInput('arquivo', '* Arquivo: ', true, false, false, false, false, 'image/png, image/jpeg, application/pdf');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
