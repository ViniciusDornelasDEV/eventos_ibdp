<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Opcao extends BaseForm {
     
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
        
        //sigla
        $this->genericTextInput('titulo', '* Título:', true, 'Título da opção');
        
        //nome
        $this->genericTextInput('data', '* Data\hora:', true);
        

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
     
        $data->data = parent::converterData($data->data);


        parent::setData($data);
    }

 }
