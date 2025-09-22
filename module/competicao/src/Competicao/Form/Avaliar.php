<?php

 namespace Competicao\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Avaliar extends BaseForm {
     
   public function __construct($name, $serviceLocator, $partida)
    {

        $this->setServiceLocator($serviceLocator);
        parent::__construct($name);      
        
        //orador_recorrente_1
        $serviceOrador = $this->serviceLocator->get('Orador');
        $oradores = $serviceOrador->getRecordsFromArray(array('ativo' => 'S', 'faculdade' => $partida['recorrente']), 'nome', array('id', 'nome'))->toArray();
        
        $oradores = $serviceOrador->prepareForDropDown($oradores, array('id', 'nome'));
        
        $this->_addDropdown('orador_recorrente_1', '1º Orador:', true, $oradores);

        //nota_lei_recorrente_1
        $this->genericIntegerInput('nota_lei_recorrente_1', '', true, '', 12, 25);
        
        //nota_fatos_recorrente_1
        $this->genericIntegerInput('nota_fatos_recorrente_1', '', true, '', 12, 25);
       
        //nota_postura_recorrente_1
        $this->genericIntegerInput('nota_postura_recorrente_1', '', true, '', 12, 25);

        //nota_resposta_recorrente_1
        $this->genericIntegerInput('nota_resposta_recorrente_1', '', true, '', 12, 25);

        //nota_total_recorrente_1
        $this->genericIntegerInput('nota_total_recorrente_1', '', false);

        //orador_recorrente_2
        $this->_addDropdown('orador_recorrente_2', '2º Orador:', true, $oradores);

        //nota_lei_recorrente_2
        $this->genericIntegerInput('nota_lei_recorrente_2', '', true, '', 12, 25);

        //nota_fatos_recorrente_2
        $this->genericIntegerInput('nota_fatos_recorrente_2', '', true, '', 12, 25);

        //nota_postura_recorrente_2
        $this->genericIntegerInput('nota_postura_recorrente_2', '', true, '', 12, 25);

        //nota_resposta_recorrente_2
        $this->genericIntegerInput('nota_resposta_recorrente_2', '', true, '', 12, 25);

        //nota_total_recorrente_2
        $this->genericIntegerInput('nota_total_recorrente_2', '', false);



        //RECORRIDOS
        $oradores = $serviceOrador->getRecordsFromArray(array('ativo' => 'S', 'faculdade' => $partida['recorrido']), 'nome', array('id', 'nome'))->toArray();
        $oradores = $serviceOrador->prepareForDropDown($oradores, array('id', 'nome'));
        
        $this->_addDropdown('orador_recorrido_1', 'Orador:', true, $oradores);

        //nota_lei_recorrido_1
        $this->genericIntegerInput('nota_lei_recorrido_1', '', true, '', 12, 25);

        //nota_fatos_recorrido_1
        $this->genericIntegerInput('nota_fatos_recorrido_1', '', true, '', 12, 25);

        //nota_postura_recorrido_1
        $this->genericIntegerInput('nota_postura_recorrido_1', '', true, '', 12, 25);

        //nota_resposta_recorrido_1
        $this->genericIntegerInput('nota_resposta_recorrido_1', '', true, '', 12, 25);

        //nota_total_recorrido_1
        $this->genericIntegerInput('nota_total_recorrido_1', '', false);

        //orador_recorrido_2
        $this->_addDropdown('orador_recorrido_2', 'Orador:', true, $oradores);

        //nota_lei_recorrido_2
        $this->genericIntegerInput('nota_lei_recorrido_2', '', true, '', 12, 25);

        //nota_fatos_recorrido_2
        $this->genericIntegerInput('nota_fatos_recorrido_2', '', true, '', 12, 25);

        //nota_postura_recorrido_2
        $this->genericIntegerInput('nota_postura_recorrido_2', '', true, '', 12, 25);

        //nota_resposta_recorrido_2
        $this->genericIntegerInput('nota_resposta_recorrido_2', '', true, '', 12, 25);

        //nota_total_recorrido_2
        $this->genericIntegerInput('nota_total_recorrido_2', '', false);
        
        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
