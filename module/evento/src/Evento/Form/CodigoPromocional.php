<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 use Application\Validator\Porcentagem;

 class CodigoPromocional extends BaseForm {
     
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
        
        //código
        $this->genericTextInput('codigo_promocional', '* Código promocional:', true);

        //desconto
        $this->genericTextInput('desconto', '* % de desconto:', true, 'máx 100%');

        //quantidade
        $this->genericTextInput('quantidade_maxima', '* Quantidade que pode ser utilizada:', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

        $this->getInputFilter()->get('desconto')->getValidatorChain()->addValidator(new Porcentagem());
    }
 }
