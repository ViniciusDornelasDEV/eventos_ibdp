<?php

 namespace Usuario\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class PesquisaUsuario extends BaseForm
 {
     
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
        $this->genericTextInput('nome', 'Nome do usuário:', false, 'Nome do usuário');

        //EMPRESA
        $serviceEmpresa = $this->serviceLocator->get('Empresa');
        $empresas = $serviceEmpresa->getRecords('S', 
                                                'ativo', 
                                                array('id', 'nome_fantasia'),
                                                'nome_fantasia'
                                            );
        if(!$empresas){
            $empresas = array();
        }
        $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
        
        $this->_addDropdown('empresa', ' Empresa:', false, $empresas);

        //CLIENTE
        $serviceCliente = $this->serviceLocator->get('Cliente');
        $clientes = $serviceCliente->getRecordsFromArray(array(), 'nome_completo', array('id', 'nome_completo'));

        if(!$clientes){
            $clientes = array();
        }
        $clientes = $serviceCliente->prepareForDropDown($clientes, array('id', 'nome_completo'));
        
        $this->_addDropdown('cliente', ' Cliente:', false, $clientes);


        $this->setAttributes(array(
            'class'  => 'form-signin',
            'role'   => 'form'
        ));

    }
 }
