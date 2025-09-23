<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class DadosIpag extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $numeroParcelas = 1)
    {
        parent::__construct($name);      

        //holder
        $this->genericTextInput('holder', 'NOME ESCRITO NO CARTÃO: ', true, 'NOME ESCRITO NO CARTÃO');

        //number
        $this->genericTextInput('number', 'NÚMERO DO CARTÃO: ', true, 'NÚMERO DO CARTÃO');

        //expiry_month
        $this->genericTextInput('expiry_date', 'DATA DE EXPIRAÇÃO: ', true, 'DATA DE EXPIRAÇÃO');

        //expiry_year
        //$this->genericIntegerInput('expiry_year', 'Mês de expiração: ', true, '', date('Y'), date('Y')+20);

        //cvv
        $this->genericTextInput('cvv', 'CÓDIGO DE VERIFICAÇÃO: ', true, 'cvv');

        //bandeira
        $this->_addDropdown('bandeira', 'BANDEIRA: ', true, array(
            '' => '--',
            'visa' => 'Visa',
            'mastercard' => 'Mastercard',
            'elo' => 'Elo',
            'amex' => 'Amex',
            'diners' => 'Diners',
            'discover' => 'Discover',
            'hipercard' => 'Hipercard',
            'hiper' => 'Hiper',
            'jcb' => 'JCB',
            'aura' => 'Aura',
            'visaelectron' => 'Visaelectron - débito',
            'maestro' => 'Maestro - débito'
        ));

        $parcelas = array();
        for ($i=1; $i <= $numeroParcelas; $i++) { 
            $parcelas[$i] = $i;
        }
        $this->_addDropdown('parcelas', 'PARCELAS: ', true, $parcelas);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
