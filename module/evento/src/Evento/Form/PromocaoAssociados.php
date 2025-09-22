<?php

 namespace Evento\Form;
 
 use Application\Form\Base as BaseForm; 
  use Application\Validator\Porcentagem;
  
 class PromocaoAssociados extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $evento)
    {

        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        

        //categoria
        $serviceCategoria = $this->serviceLocator->get('QuantidadeCategoria');
        $categorias = $serviceCategoria->getQuantidadeInscricoesCategoriaByEvento($evento->id)->toArray();

        $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome_categoria'), false, array('' => '-- selecione --', 'T' => 'Todos'));
        $this->_addDropdown('categoria_evento', '* Categoria do evento:', true, $categorias);

        $serviceCategoriaAssociados = $this->serviceLocator->get('CategoriaAssociado');
        $categorias = $serviceCategoriaAssociados->getRecordsFromArray(array('empresa' => $evento->empresa, 'ativo' => 'S'), 'nome');
        $categorias = $serviceCategoriaAssociados->prepareForDropDown($categorias, array('id', 'nome'), false, array('' => '-- selecione --', 'T' => 'Todos'));
        $this->_addDropdown('categoria_associado', '* Categoria de associados:', true, $categorias);
        
        $this->_addDropdown('status_associado', '* Status do associado:', true, array('' => '-- Selecione --', 'A' => 'Adimplentes', 'T' => 'Todos'));

        $this->genericTextInput('desconto', '* Porcentagem de desconto:', true);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));

        $this->getInputFilter()->get('desconto')->getValidatorChain()->addValidator(new Porcentagem());
    }


 }
