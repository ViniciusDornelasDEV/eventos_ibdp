<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Pagar extends BaseForm {
     
   public function __construct($name)
    {

        parent::__construct($name);      
        
        //empresa
        $this->genericTextInput('nome_fantasia', 'Empresa: ', false);
        
        //nome_completo
        $this->genericTextInput('nome_completo', 'Associado: ', false);

        //cpf
        $this->genericTextInput('cpf', 'CPF: ', false);

        //nome_categoria
        $this->genericTextInput('nome_categoria', 'Categoria: ', false);

        //descricao_anuidade
        $this->genericTextInput('descricao_anuidade', 'Anuidade: ', false);

        //periodo
		$this->genericTextInput('periodo', 'Período: ', false);

        //data_pagamento
	    $this->genericTextInput('data_pagamento', 'Data de pagamento: ', false);

        //data_baixa
        $this->genericTextInput('data_baixa', 'Data da baixa: ', false);

        //usuario_baixa
        $this->genericTextInput('nome_usuario', 'Usuário que realizou a baixa: ', false);

        //valor
		$this->genericTextInput('valor', 'Valor: ', false);


        $this->_addDropdown('forma_pagamento', '* Tipo:', true, array('' => '-- Selecione --', '2' => 'BOLETO BANCÁRIO', '5' => 'PAYPAL', '6' => 'CIELO', '8' => 'TRANSFERÊNCIA BANCÁRIA', '9' => 'NÃO SE APLICA'));      

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setData($data){
        $data['periodo'] = parent::converterData($data['data_inicio']).' a '.parent::converterData($data['data_fim']);
        $data['data_pagamento'] = parent::converterData($data['data_pagamento']);
        $data['data_baixa'] = parent::converterData($data['data_baixa']);
        parent::setData($data);
    }
 }
