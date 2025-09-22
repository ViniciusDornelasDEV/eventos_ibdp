<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Transmissao extends BaseForm {
     
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

        $this->genericTextInput('sala', '* Sala:', true);

        $this->genericTextInput('inicio', '* Início:', true);

        $this->genericTextInput('fim', '* Fim:', true);
        
        $this->genericTextArea('codigo_embed', '* Código da transmissão: ', true, false, false, 0, 900000);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
    
     public function setData($data){
        $data->inicio = parent::converterData($data->inicio);
        $data->fim = parent::converterData($data->fim);
        parent::setData($data);
    }


 }
