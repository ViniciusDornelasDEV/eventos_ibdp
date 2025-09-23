<?php

 namespace Associados\Form;
 
 use Application\Form\Base as BaseForm; 
 
 class Associado extends BaseForm {
     
    /**
     * Sets up generic form.
     * 
     * @access public
     * @param array $fields
     * @return void
     */
   public function __construct($name, $serviceLocator, $required = true, $empresa = false)
    {
        if($serviceLocator)
           $this->setServiceLocator($serviceLocator);

        parent::__construct($name);      
        
        if(!$empresa){
            //empresa
            $serviceEmpresa = $this->serviceLocator->get('Empresa');
            $empresas = $serviceEmpresa->getRecords('S', 'ativo', array('id', 'nome_fantasia'), 'nome_fantasia')->toArray();
            $empresas = $serviceEmpresa->prepareForDropDown($empresas, array('id', 'nome_fantasia'));
            $this->_addDropdown('empresa', '* Empresa:', true, $empresas, 'carregarCategoriasAssociado(this.value, "true");');
        }

        if($empresa){
            $serviceCategoria = $this->serviceLocator->get('CategoriaAssociado');
            $categorias = $serviceCategoria->getRecords($empresa, 'empresa');
            $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome'));
            $this->_addDropdown('categoria_associado', '* Categoria:', $required, $categorias);
        }else{
            //categoria_associado
            $this->_addDropdown('categoria_associado', '* Categoria:', $required, array('' => 'Selecione uma empresa'));
        }
        
        //cpf
        $this->textInputCpf('cpf', '* CPF: ', true, 'CPF');
        
        //rg
        $this->genericTextInput('rg', 'RG: ', false);

        //nome_completo
        $this->genericTextInput('nome_completo', 'Nome completo: ', false, 'Nome completo');

        //nome_certificado
        $this->genericTextInput('nome_certificado', 'Nome no certificado: ', false, 'Nome no certificado');

        //nome_cracha
        $this->genericTextInput('nome_cracha', 'Nome no crachá: ', false, 'Nome no crachá');                

        //telefone
        $this->genericTextInput('telefone', 'Telefone: ', false, 'Telefone');

        //celular
        $this->genericTextInput('celular', 'Celular: ', false, 'Celular');

        //email
        $this->addEmailElement('email', 'Email: ', false, 'Email');
        
        //data_nascimento
        $this->genericTextInput('data_nascimento', 'Data de nascimento: ', false);

        //país
        $serviceEstadoCivil = $this->getServiceLocator()->get('EstadoCivil');
        $paises = $this->getServiceLocator()->get('Pais')->fetchAll(array('id', 'nome'))->toArray();
        $paises = $serviceEstadoCivil->prepareForDropDown($paises, array('id', 'nome'), false, array());
        $this->_addDropdown('pais_endereco', 'País: ', false, $paises, 'carregarPais(this.value);');
        
        //cep
        $this->genericTextInput('cep', 'CEP: ', false);


        //uf_internacional
        $this->genericTextInput('uf_internacional', 'Estado: ', false, 'Estado');

        //cidade_internacional
        $this->genericTextInput('cidade_internacional', 'Cidade: ', false, 'Cidade');

        //uf
        $estados = $this->getServiceLocator()->get('Estado')->fetchAll(array('id', 'uf'))->toArray();
        $estados = $serviceEstadoCivil->prepareForDropDown($estados, array('id', 'uf'));
        $this->_addDropdown('estado', 'Estado: ', false, $estados, 'CarregaCidade(this.value, "false");');

        //cidade
        $cidades = array('-- Selecione um estado --');
        $this->_addDropdown('cidade', 'Cidade: ', false, $cidades);

        //bairro
        $this->genericTextInput('bairro', 'Bairro: ', false, 'Bairro');

        //nm_rua
        $this->genericTextInput('nm_rua', 'Rua: ', false, 'Rua');

        //numero
        $this->genericTextInput('numero', 'Número: ', false, 'Numero');

        //complemento
        $this->genericTextInput('complemento', 'Complemento: ', false, 'Complemento');
        
        //estado_civil
        $estadoCivil = $serviceEstadoCivil->fetchAll(array('id', 'nome'))->toArray();
        $estadoCivil = $serviceEstadoCivil->prepareForDropDown($estadoCivil, array('id', 'nome'));
        $this->_addDropdown('estado_civil', 'Estado civil: ', false, $estadoCivil);

        //nacionalidade
        $nacionalidade = $this->getServiceLocator()->get('Nacionalidade')->fetchAll(array('id', 'nome'))->toArray();
        $nacionalidade = $serviceEstadoCivil->prepareForDropDown($nacionalidade, array('id', 'nome'));    
        $this->_addDropdown('nacionalidade', 'Nacionalidade: ', false, $nacionalidade);

        //sexo
        $sexo = array('' => '--', 'M' => 'Masculino', 'F' => 'Feminino');    
        $this->_addDropdown('sexo', 'Sexo: ', false, $sexo);

        //conselho
        $conselhos = array('' => '-- Selecione --', 'CRO' => 'CRO', 'OAB' => 'OAB', 'CRM' => 'CRM', 'COREN' => 'COREN', 'CRN' => 'CRN', 'Outro' => 'Outro (Explicar no campo abaixo)');
        $this->_addDropdown('conselho', 'Conselho: ', false, $conselhos);
        
        //numero_conselho
        $this->genericTextInput('numero_conselho', 'Nº do conselho: ', false);

        //especialidade
        $this->genericTextInput('especialidade', 'Especialidade: ', false);

        //profissao
        $this->genericTextInput('profissao', 'Profissão: ', false, 'Profissão');

        //cargo
        $this->genericTextInput('cargo', 'Cargo: ', false, 'Cargo');

        //SENHA
        $this->genericTextInput('senha', 'Senha: ', false, 'Senha');
        
        $this->_addDropdown('ativo', 'Ativo:', false, array('S' => 'Sim', 'N' => 'Não'));

        //endereço comercial 1
        $this->genericTextInput('endereco_1', 'Endereço: ', false);

        $this->_addDropdown('estado_1', 'Estado: ', false, $estados, 'CarregaCidade(this.value, "false", "#cidade_1");');

        $this->_addDropdown('cidade_1', 'Cidade: ', false, $cidades);

        $this->genericTextInput('telefone_1', 'Telefone: ', false);

        $this->genericTextInput('telefone_1_2', 'Telefone: ', false);

        $this->genericTextInput('site_1', 'Site: ', false);

        $this->addEmailElement('email_1', 'Email: ', false);

        //endereço comercial 2
        $this->genericTextInput('endereco_2', 'Endereço: ', false);

        $this->_addDropdown('estado_2', 'Estado: ', false, $estados, 'CarregaCidade(this.value, "false", "#cidade_2");');

        $this->_addDropdown('cidade_2', 'Cidade: ', false, $cidades);

        $this->genericTextInput('telefone_2', 'Telefone: ', false);

        $this->genericTextInput('telefone_2_2', 'Telefone: ', false);

        $this->genericTextInput('site_2', 'Site: ', false);

        $this->addEmailElement('email_2', 'Email: ', false);

        //endereço comercial 3
        $this->genericTextInput('endereco_3', 'Endereço: ', false);

        $this->_addDropdown('estado_3', 'Estado: ', false, $estados, 'CarregaCidade(this.value, "false", "#cidade_3");');

        $this->_addDropdown('cidade_3', 'Cidade: ', false, $cidades);

        $this->genericTextInput('telefone_3', 'Telefone: ', false);

        $this->genericTextInput('telefone_3_2', 'Telefone: ', false);

        $this->genericTextInput('site_3', 'Site: ', false);

        $this->addEmailElement('email_3', 'Email: ', false);

        $this->setAttributes(array(
            'class'  => 'form-inline'
        ));
    }

    public function setCidadesByEstado($estado, $nomeCidade = 'cidade'){
        //buscar cidade
        $serviceCidade = $this->serviceLocator->get('Cidade');
        $cidades = $serviceCidade->getRecords($estado, 'estado', array('*'), 'ordem, nome');
        $cidades = $serviceCidade->prepareForDropDown($cidades, array('id', 'nome'));
        //Setando valores
        $cidades = $this->get($nomeCidade)->setAttribute('options', $cidades);
        
        return $cidades;      
    }

    public function setCategoriasByEmpresa($empresa, $todos = false){
        //buscar cidade
        $serviceCategoria = $this->serviceLocator->get('CategoriaAssociado');
        $categorias = $serviceCategoria->getRecords($empresa, 'empresa');

        if($todos == "false"){
          $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome'));
        }else{
          $categorias = $serviceCategoria->prepareForDropDown($categorias, array('id', 'nome'), false, array('T' => 'Todas as categorias'));
        }
        //Setando valores
        $categorias = $this->get('categoria_associado')->setAttribute('options', $categorias);
        
        return $categorias;      
    }

    public function setData($data){
        if(isset($data['estado'])){
            $this->setCidadesByEstado($data['estado']);
        }

        if(isset($data['estado_1'])){
            $this->setCidadesByEstado($data['estado_1'], 'cidade_1');
        }

        if(isset($data['estado_2'])){
            $this->setCidadesByEstado($data['estado_2'], 'cidade_2');
        }

        if(isset($data['estado_3'])){
            $this->setCidadesByEstado($data['estado_3'], 'cidade_3');
        }

        if(isset($data['cidade']) && !isset($data['estado'])){
            //find estado by cidade
            $cidade = $this->serviceLocator->get('Cidade')->getRecord($data['cidade']);
            $data['estado'] = $cidade['estado'];
            $this->setCidadesByEstado($data['estado']);
        }

        if(isset($data['cidade_1']) && !isset($data['estado_1'])){
            //find estado by cidade
            $cidade = $this->serviceLocator->get('Cidade')->getRecord($data['cidade_1']);
            $data['estado_1'] = $cidade['estado'];
            $this->setCidadesByEstado($data['estado_1'], 'cidade_1');
        }

        if(isset($data['cidade_2']) && !isset($data['estado_2'])){
            //find estado by cidade
            $cidade = $this->serviceLocator->get('Cidade')->getRecord($data['cidade_2']);
            $data['estado_2'] = $cidade['estado'];
            $this->setCidadesByEstado($data['estado_2'], 'cidade_2');
        }

        if(isset($data['cidade_3']) && !isset($data['estado_3'])){
            //find estado by cidade
            $cidade = $this->serviceLocator->get('Cidade')->getRecord($data['cidade_3']);
            $data['estado_3'] = $cidade['estado'];
            $this->setCidadesByEstado($data['estado_3'], 'cidade_3');
        }


        if(isset($data['empresa'])){
            $this->setCategoriasByEmpresa($data['empresa']);
        }



        if(isset($data['data_nascimento']) && !empty($data['data_nascimento'])){
            $data['data_nascimento'] = parent::converterData($data['data_nascimento']);
        }
        
        parent::setData($data);
    }

 }
