<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Mensagem extends BaseForm {
     
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
        $this->_addDropdown('empresa', 'Empresa:', false, $empresas, 'carregarCategoriasAssociado(this.value, "false", "true");');

        //categoria_associado
        $this->_addDropdown('categoria_associado', 'Categoria:', false, array('' => 'Selecione uma empresa'), 'carregarAnuidades(this.value, "true");');
        
        $this->_addDropdown('anuidade', 'Anuidade:', false, array('' => 'Selecione uma categoria')); 

        $this->_addDropdown('adimplente', 'Adimplente:', false, array('' => '-- Selecione --', 'S' => 'Sim', 'N' => 'Não'));

        //título
        $this->genericTextInput('assunto', '* Assunto:', true, 'Assunto do email');

        //mensagem
        $this->genericTextArea('mensagem', '* Mensagem:', true, false, true, 0, 2000, 'width: 100%;');



        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }
 }
