<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisarChaveamento extends BaseForm {
     
   public function __construct($name, $serviceLocator, $idCompeticao, $rodadas)
    {
      $this->setServiceLocator($serviceLocator);
      parent::__construct($name);      

      $serviceSala = $this->serviceLocator->get('CompeticaoSalas');
      $salas = $serviceSala->getRecords($idCompeticao, 'competicao', array('id', 'nome'), 'nome')->toArray();
      $salas = $serviceSala->prepareForDropDown($salas, array('id', 'nome'));

      $this->_addDropdown('sala', 'Sala:', false, $salas, '', 'width: 100%');
      
      $datas = array('-- Selecione --');
      $horas = array('-- Selecione --');
      foreach ($rodadas as $rodada) {
        $datas[$rodada['data']] = substr(parent::converterData($rodada['data']), 0, -5);
        $horas[$rodada['hora']] = substr($rodada['hora'], 0, -3);
      }
      $this->_addDropdown('data', 'Data:', false, $datas);
      
      $this->_addDropdown('hora', 'Hora:', false, $horas);

      $this->setAttributes(array(
          'class'  => 'form-inline'
      ));
    }

 }
