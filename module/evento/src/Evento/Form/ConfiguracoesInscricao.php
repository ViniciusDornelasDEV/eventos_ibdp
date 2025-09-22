<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm;
 
 class ConfiguracoesInscricao extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $camposInscricao)
    {
        
        $this->setServiceLocator($serviceLocator);
        
        parent::__construct($name);  

        
        $data = array();
        foreach ($camposInscricao as $campo) {
        	$data[$campo['nome_campo'].'1id'] = $campo['id_campo_evento'];
        	$data[$campo['nome_campo'].'1aparecer'] = $campo['aparecer'];
        	$data[$campo['nome_campo'].'1obrigatorio'] = $campo['obrigatorio'];
        	$data[$campo['nome_campo'].'1label'] = $campo['label_campo'];
        	
        	//id
        	$this->addHiddenInput($campo['nome_campo'].'1id', true);

        	//label
        	$this->genericTextInput($campo['nome_campo'].'1label', 'Título: ', true);

        	//aparecer
        	$this->_addRadio($campo['nome_campo'].'1aparecer', 'Mostrar campo na inscrição? ', true, array('S' => 'Sim', 'N' => 'Não'));

        	//obrigatorio
        	$this->_addRadio($campo['nome_campo'].'1obrigatorio', 'Campo obrigatório? ', true, array('S' => 'Sim', 'N' => 'Não'));

        	
        }
        
        //popular formulário
        $this->setData($data);
                
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

    }
 }
