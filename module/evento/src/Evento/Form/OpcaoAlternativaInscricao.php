<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class OpcaoAlternativaInscricao extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $opcoes)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        $idOpcao = 0;
        $alternativas = array();
        foreach ($opcoes as $opcao) {
            if($idOpcao != $opcao->evento_opcao && $idOpcao != 0){
                //escrever campo

            }

            //jogar alternativas da opcao em um array
            $alternativas[$opcao->id] = $opcao->atividade;

        }
        

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
