<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PagarInscricao extends BaseForm {
     
   public function __construct($name, $serviceLocator, $inscricao)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        $this->_addDropdown('forma_pagamento', '* Tipo:', true, array('' => '-- Selecione --', '5' => 'PayPal', '6' => 'Cielo', '8' => 'Transferência bancária', '10' => 'Devolução', '11' => 'Empenho'));      

        $serviceValor = $this->serviceLocator->get('ValorEvento');
        $valores = $serviceValor->getValoresByCadEvento($inscricao->id_evento, false, $inscricao->cliente_categoria);
        $valoresTratados = array();

        foreach($valores as $valor){
            $valoresTratados[] = array(
                'id'    => $valor->id,
                'nome'  => $valor->nome_categoria.' - R$ '.$valor->valor_inscricao
            );
        }
        $valoresTratados = $serviceValor->prepareForDropDown($valoresTratados, array('id', 'nome'));
        $this->_addDropdown('categoria', 'Mudar lote:', false, $valoresTratados);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

 }
