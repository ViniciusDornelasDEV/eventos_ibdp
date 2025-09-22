<?php

 namespace Empresa\Form;
 
 use Application\Form\Base as BaseForm; 
 use Application\Validator\Cnpj;
 
 class Empresa extends BaseForm {
     
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
        
        $this->genericTextInput('nome_fantasia', '* Nome:', true, 'Nome fantasia');

        $this->textInputCnpj('cnpj', '* Cnpj:', true, 'CNPJ');

        $this->genericTextInput('responsavel', ' Responsável:', false, 'Funcionário responsável');

        $this->genericTextInput('agencia', 'Agência:', false, 'Agência no boleto');

        //$this->genericTextInput('agencia_dv', '* Dígito verificador:', true, 'DV da agência');

        $this->genericTextInput('conta', 'Conta:', false, 'Conta no boleto');

        $this->genericTextInput('conta_dv', ' Dígito verificador:', false, 'DV da conta');

        $this->genericTextInput('endereco', 'Endereço:', false, 'Rua, nº e bairro');

        $this->genericTextInput('cidade_uf', ' Cidade:', false, 'Cidade, UF e CEP');

        $this->_addRadio('anuidade_paypal', '* PayPal: ', true, array('S' => 'Sim', 'N' => 'Não'));

        $this->_addRadio('anuidade_cielo', '* Cielo: ', true, array('S' => 'Sim', 'N' => 'Não'));

        $this->_addRadio('anuidade_deposito', '* Transferência: ', true, array('S' => 'Sim', 'N' => 'Não'));

         $this->genericTextArea('dados_deposito', 'Dados da transferência: ', false, false, false, 0, 900000);

        $this->_addDropdown('ativo', 'Ativo:', false, array('S' => 'Sim', 'N' => 'Não'));
                
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

        $this->getInputFilter()->get('cnpj')->getValidatorChain()->addValidator(new Cnpj());
    }


    public function setData($data){

        $data['dados_deposito'] = html_entity_decode($data['dados_deposito']);
        parent::setData($data);
    }
 }
