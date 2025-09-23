<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Chaveamento extends BaseForm {
     
   public function __construct($name, $serviceLocator, $idCompeticao)
    {

        $this->setServiceLocator($serviceLocator);
        parent::__construct($name);      
        

        $serviceFaculdades = $this->serviceLocator->get('Faculdade');
        $faculdades = $serviceFaculdades->getRecords('S', 'ativo', array('id', 'nome'), 'nome')->toArray();
        $faculdades = $serviceFaculdades->prepareForDropDown($faculdades, array('id', 'nome'));
        
        $this->_addDropdown('recorrente', '* Recorrente:', true, $faculdades);

        $this->_addDropdown('recorrido', '* Recorrido:', true, $faculdades);

        $serviceSala = $this->serviceLocator->get('CompeticaoSalas');
        $salas = $serviceSala->getRecords($idCompeticao, 'competicao', array('id', 'nome'), 'nome')->toArray();
        $salas = $serviceSala->prepareForDropDown($salas, array('id', 'nome'));

        $this->_addDropdown('sala', '* Sala:', true, $salas);

        $this->genericTextInput('data', '* Data: ', true);

        $this->genericTextInput('hora', '* Hora: ', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }


     public function setData($data){
        $data->data = parent::converterData($data->data);
        parent::setData($data);
    }
 }
