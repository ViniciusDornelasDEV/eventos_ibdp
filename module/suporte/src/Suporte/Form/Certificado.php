<?php



 namespace Suporte\Form;

 

 use Application\Form\Base as BaseForm; 

 use Application\Validator\Cpf;

 

 class Certificado extends BaseForm {

     

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

        

       $this->textInputCpf('cpf', 'CPF: ', true, '', 'form-control input-lg');

                

        $this->setAttributes(array(

            'class'  => 'form-inline'

        ));



        //$this->getInputFilter()->get('cpf')->getValidatorChain()->addValidator(new Cpf());

    }



 }

