<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Video extends BaseForm {
     
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
        
        $this->genericTextInput('descricao', '* Descrição:', true);
        
        $this->genericTextArea('link_video', '* Link do vídeo: ', true, false, false, 0, 900000);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
