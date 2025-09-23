<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Anuidade extends BaseForm {
     
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

        $this->genericTextInput('valor', '* Valor anuidade:', true, '');

        $this->genericTextInput('data_inicio', '* Início do período:', true, '');

        $this->genericTextInput('data_fim', '* Fim do período:', true, '');

        $this->genericTextInput('data_pagamento', '* Data de pagamento:', true, '');


        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
        $data->data_inicio = parent::converterData($data->data_inicio);
        $data->data_fim = parent::converterData($data->data_fim);
        $data->data_pagamento = parent::converterData($data->data_pagamento);
        $data->valor = parent::numberInsertMysql($data->valor);

        parent::setData($data);
    }

 }
