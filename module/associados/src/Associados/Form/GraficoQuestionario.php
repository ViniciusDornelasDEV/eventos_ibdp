<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class GraficoQuestionario extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator)
    {
        
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecords('S', 'ativo', array('id', 'nome_fantasia'), 'nome_fantasia')->toArray();
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
        $this->_addDropdown('empresa', '* Empresa:', true, $empresas, 'carregarQuestionarios(this.value);');

        $this->_addDropdown('questionario', '* Questionário:', true, array('Selecione uma empresa'), 'carregarQuestoes(this.value);');

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 
    public function setQuestionariosByEmpresa($empresa){
        //buscar questionarios
        $serviceQuestionario = $this->serviceLocator->get('Questionario');
        $questionarios = $serviceQuestionario->getRecords($empresa, 'empresa');
        $questionarios = $serviceQuestionario->prepareForDropDown($questionarios, array('id', 'nome'));
        //Setando valores
        $questionarios = $this->get('questionario')->setAttribute('options', $questionarios);
        
        return $questionarios;      
    }

    public function setData($data){
        if(isset($data['empresa'])){
            $this->setQuestionariosByEmpresa($data['empresa']);
        }
       
        parent::setData($data);
    }


 }
