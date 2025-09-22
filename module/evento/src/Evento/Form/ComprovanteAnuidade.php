<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class ComprovanteAnuidade extends BaseForm {
     
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

        $this->addImageFileInput('comprovante', '* Comprovante: ', true, false, false, false, false, 'image/png, image/jpeg, application/pdf');

        $this->genericTextArea('observacoes', 'Observações: ', false, false, false, 0, 900000);   

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
