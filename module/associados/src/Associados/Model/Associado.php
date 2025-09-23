<?php
namespace Associados\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Crypt\Password\Bcrypt;

class Associado Extends BaseTable {
    public $msg = '';
    public function inserirAssociado($dados){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $result = array();
            $result['status'] = false;
            $dadosInsert = $dados;
            unset($dadosInsert['empresa']);
            unset($dadosInsert['estado']);
            unset($dadosInsert['categoria_associado']);
            unset($dadosInsert['senha']);
            unset($dadosInsert['estado_1']);
            unset($dadosInsert['estado_2']);
            unset($dadosInsert['estado_3']);
            foreach ($dadosInsert as $key => $dado) {
                if(empty($dadosInsert[$key])){
                    unset($dadosInsert[$key]);
                }
            }
            $dadosCliente = $dadosInsert;
            unset($dadosCliente['ativo']);
            $serviceUsuario = new TableGateway('tb_usuario', $adapter);

            //verificar se existe cliente
            $serviceCliente = new TableGateway('tb_cliente', $adapter);
            $cliente = $serviceCliente->select(array('cpf' => $dados['cpf']))->current();
            if($cliente){
                //verificar se ele já é um associado da empresa
                $associado = parent::getRecordFromArray(array('empresa' => $dados['empresa'], 'cliente' => $cliente['id']));
                if($associado){
                    //retornar mensagem, cadastro já existe!
                    $result['msg'] = 'Associado já vinculado a esta empresa!';
                    $result['status'] = false;
                }else{
                    //inserir associado a empresa selecionada
                    $serviceCliente->update($dadosCliente, array('id' => $cliente->id));
                    $result['status'] = parent::insert(array('empresa' => $dados['empresa'], 'cliente' => $cliente->id, 'categoria_associado' => $dados['categoria_associado']));
                    $result['msg'] = 'Associado vinculado com sucesso!';

                    //caso veio senha, atualizar a senha
                    if(!empty($dados['senha'])){
                        $bcrypt = new bcrypt();
                        $senha = $bcrypt->create($dados['senha']);
                        $serviceUsuario->update(array('senha' => $senha), array('cliente' => $cliente->id));
                        $result['msg'] = 'Associado vinculado e senha alterada com sucesso!';
                    }

                }
            }else{
                //cadastrar cliente 
                $serviceCliente->insert($dadosCliente);
                $idCliente = $serviceCliente->getLastInsertValue();
                
                //usuario
                $bcrypt = new bcrypt();
                if(empty($dados['senha'])){
                    $senha = $bcrypt->create($this->eliminaMascaras($dados['cpf']));
                    $msg = 'Um novo usuário foi criado com a senha '.$this->eliminaMascaras($dados['cpf']).'!';
                }else{
                    $senha = $bcrypt->create($dados['senha']);
                    $msg = 'Um novo usuário foi criado!';
                }
                $serviceUsuario->insert(array(
                    'nome'              =>  $dados['nome_completo'],
                    'login'             =>  $dados['cpf'],
                    'senha'             =>  $senha,
                    'id_usuario_tipo'   =>   2,
                    'cliente'           =>  $idCliente
                ));

                //inserir associado
                $result['status'] = parent::insert(array('empresa' => $dados['empresa'], 'cliente' => $idCliente, 'categoria_associado' => $dados['categoria_associado']));
                $result['msg'] = 'Associado vinculado com sucesso. '.$msg;
            }


             
            $connection->commit();
            return $result;
        } catch (Exception $e) {
            $connection->rollback();
            return $result;
        }
        return $result;
    }

    public function atualizarAssociado($dados, $associado){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $dadosInsert = $dados;
            unset($dadosInsert['empresa']);
            unset($dadosInsert['estado']);
            unset($dadosInsert['categoria_associado']);
            unset($dadosInsert['senha']);
            unset($dadosInsert['estado_1']);
            unset($dadosInsert['estado_2']);
            unset($dadosInsert['estado_3']);
            
            foreach ($dadosInsert as $key => $dado) {
                if(empty($dadosInsert[$key]) && strpos($key, 'cidade') !== false){
                    unset($dadosInsert[$key]);
                }
            }
            //se veio vazio, não dar update pois é chave estrangeira...
            if(empty($dadosInsert['estado_civil'])){
                unset($dadosInsert['estado_civil']);
            }

            if(empty($dadosInsert['nacionalidade'])){
                unset($dadosInsert['nacionalidade']);
            }

            parent::update(
                array('empresa' => $dados['empresa'], 'categoria_associado' => $dados['categoria_associado'], 'ativo' => $dadosInsert['ativo']),
                array('id' => $associado->id_associado)
            );

            unset($dadosInsert['ativo']);

            $serviceCliente = new TableGateway('tb_cliente', $adapter);
            $serviceCliente->update($dadosInsert, array('id' => $associado->cliente));

            //se vier senha alterar a senha
            $dadosUsuario = array('nome' => $dados['nome_completo'], 'login' => $dados['cpf']);
            if(!empty($dados['senha'])){
                $bcrypt = new bcrypt();
                $dadosUsuario['senha'] = $bcrypt->create($dados['senha']);
            }
            $serviceUsuario = new TableGateway('tb_usuario', $adapter);
            $serviceUsuario->update($dadosUsuario, array('cliente' => $associado->cliente));


            //se mudou de categoria, mudar também a categoria dos pagamentos. Pesquisar categoria com o mesmo nome
            if($dados['categoria_associado'] != $associado['categoria_associado']){
                $serviceAnuidade = new TableGateway('tb_associado_anuidade', $adapter);
                $servicePagamento = new TableGateway('tb_associado_pagamento', $adapter);
                $serviceComprovante = new TableGateway('tb_associado_pagamento_comprovante', $adapter);
                $anuidades = $serviceAnuidade->select(array('categoria' => $associado['categoria_associado']));

                foreach ($anuidades as $anuidade) {
                    $novaAnuidade = $serviceAnuidade->select(array(
                        'descricao' => $anuidade['descricao'],
                        'categoria' => $dados['categoria_associado'] 
                    ))->current();


                    if(!$novaAnuidade){
                        return false;
                    }

                    //alterar pagamento
                    $servicePagamento->update(
                        array('anuidade' => $novaAnuidade['id']), 
                        array('associado' => $associado['id_associado'], 'anuidade' => $anuidade['id'])
                    );

                    //alterar comprovante
                    $serviceComprovante->update(
                        array('anuidade' => $novaAnuidade['id']), 
                        array('associado' => $associado['id_associado'], 'anuidade' => $anuidade['id'])
                    );
                }
            }

            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        return false;
    }

    public function getAssociados($params){
        return $this->getTableGateway()->select(function($select) use ($params) {

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = empresa',
                    array('nome_fantasia')
                );

            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('nome_completo', 'cpf', 'email', 'bairro', 'nm_rua', 'numero', 'complemento', 'telefone', 'celular')
                );

            $select->join(
                    array('ac' => 'tb_associado_categoria'),
                    'ac.id = categoria_associado',
                    array('nome_categoria' => 'nome', 'certificado', 'id_categoria' => 'id', 'validade_certificado')
                );

            $select->join(
                    array('cid' => 'tb_cidade'),
                    'cid.id = c.cidade',
                    array('nome_cidade' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('est' => 'tb_estado'),
                    'est.id = cid.estado',
                    array('nome_estado' => 'nome'),
                    'LEFT'
                );

            if(!empty($params['empresa'])){
                $select->where(array('e.id' => $params['empresa']));
            }

            if(!empty($params['categoria_associado']) && $params['categoria_associado'] != 'T'){
                $select->where(array('categoria_associado' => $params['categoria_associado']));
            }

            if(!empty($params['nome_completo'])){
                $select->where->like('nome_completo', '%'.$params['nome_completo'].'%');
            }

            if(!empty($params['cpf'])){
                $select->where(array('cpf' => $params['cpf']));
            }

            if(!empty($params['cliente'])){
                $select->where(array('c.id' => $params['cliente']));
            }

            if(!empty($params['cidade'])){
                $select->where(array('cid.id' => $params['cidade']));
            }

            if(!empty($params['adimplente'])){
                $select->where(array('tb_associado.adimplente' => $params['adimplente']));
            }

            if(!empty($params['exibir_site'])){
                $select->where(array('tb_associado.exibir_site' => $params['exibir_site']));
            }

            if(!empty($params['estado'])){
                $select->where(array('est.id' => $params['estado']));
            }       

            $select->order('e.nome_fantasia, c.nome_completo');
            
        });
    }

     public function getAssociado($idAssociado){
        return $this->getTableGateway()->select(function($select) use ($idAssociado) {
            $select->columns(array('id_associado' => 'id', 'empresa', 'cliente', 'categoria_associado', 'adimplente', 'ativo'));
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('*')
                );

            $select->join(
                array('ac' => 'tb_associado_categoria'),
                'ac.id = categoria_associado',
                array('nome_categoria' => 'nome', 'certificado', 'id_categoria' => 'id', 'validade_certificado', 'parcelas')
            );

            $select->where(array('tb_associado.id' => $idAssociado));
        })->current();
    }

    public function getAssociadoByClienteId($idCliente = false, $idCategoria = false){
        return $this->getTableGateway()->select(function($select) use ($idCliente, $idCategoria) {
            $select->columns(array('id_associado' => 'id', 'empresa', 'cliente', 'categoria_associado', 'adimplente', 'ativo'));
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('*')
                );

                $select->join(
                    array('ca' => 'tb_associado_categoria'),
                    'ca.id = tb_associado.categoria_associado',
                    array('certificado', 'validade_certificado')
                );

            if($idCliente){
                $select->where(array('c.id' => $idCliente));
            }

            if($idCategoria){
                $select->where(array('ca.id' => $idCategoria));
            }
        })->current();
    }

    public function getAssociadoByCpf($cpf){
        return $this->getTableGateway()->select(function($select) use ($cpf) {
            $select->columns(array('id_associado' => 'id', 'empresa', 'cliente', 'categoria_associado', 'adimplente', 'ativo'));
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('*')
                );
            $select->join(
                    array('a' => 'tb_associado_anuidade'),
                     new Expression('a.categoria = categoria_associado AND "'.date('Y-m-d').'" BETWEEN a.data_inicio AND a.data_fim'),
                    array()
                );
            $select->join(
                    array('p' => 'tb_associado_pagamento'),
                    'p.associado = tb_associado.id AND anuidade = a.id',
                    array('data_pagamento')
                );


            $select->where(array('c.cpf' => $cpf));
            
        })->current();
    }

    public function getAssociadoByUser($idUsuario){
        return $this->getTableGateway()->select(function($select) use ($idUsuario) {
            $select->join(
                    array('u' => 'tb_usuario'),
                    'u.cliente = tb_associado.cliente',
                    array()
                );

            $select->where(array('u.id' => $idUsuario));
        })->current();
    }

     public function getPagamentos($params, $group = false){
        return $this->getTableGateway()->select(function($select) use ($params, $group) {
            $select->columns(array('id_associado' => 'id', 'empresa', 'cliente', 'categoria_associado', 'adimplente'));
            
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('*')
                );

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_associado.empresa',
                    array('nome_fantasia')
                );

            $select->join(
                    array('ac' => 'tb_associado_categoria'),
                    'ac.id = categoria_associado',
                    array('nome_categoria' => 'nome')
                );

            $select->join(
                    array('aa' => 'tb_associado_anuidade'),
                    //'aa.categoria = categoria_associado',
                    new Expression('aa.categoria = categoria_associado AND YEAR(aa.data_pagamento) >= c.ano_cadastro'), 
                    array('descricao_anuidade' => 'descricao', 'id_anuidade' => 'id', 'data_inicio', 'data_fim', 'data_vencimento' => 'data_pagamento', 'valor_anuidade' => 'valor')
                );

            $juncAdimplente = 'LEFT';
             if(!empty($params['adimplente'])){
                if($params['adimplente'] == 'S'){
                    $juncAdimplente = 'INNER';
                }else{
                    //quando ap.id IS NULL
                    $select->where->isNull('ap.id');
                }
            }
            $select->join(
                    array('ap' => 'tb_associado_pagamento'),
                    'ap.associado = tb_associado.id AND ap.anuidade = aa.id',
                    array('data_pagamento', 'valor_pagamento'),
                    $juncAdimplente
                );

            $select->join(
                    array('fp' => 'tb_forma_pagamento'),
                    'fp.id = ap.forma_pagamento',
                    array('nome_forma' => 'nome'),
                    'LEFT'
                );

            $juncComprovante = 'LEFT';
            if(isset($params['comprovante']) && !empty($params['comprovante'])){
                if($params['comprovante'] == 'S'){
                    $juncComprovante = 'INNER';
                    $select->order('e.nome_fantasia, apc.data_hora DESC');
                }else{
                    $select->where->isNull('apc.id');
                    //ordenar por pagamento 
                    $select->order('e.nome_fantasia, aa.data_pagamento DESC, c.nome_completo');
                }
            }else{
                //ordenar por pagamento 
                    $select->order('e.nome_fantasia, aa.data_pagamento DESC, c.nome_completo');
            }
            
            $select->join(
                array('apc' => 'tb_associado_pagamento_comprovante'),
                'apc.anuidade = aa.id AND apc.associado = tb_associado.id',
                array('data_hora_comprovante' => 'data_hora', 'comprovante' => 'arquivo', 'id_comprovante' => 'id'),
                $juncComprovante
            );

            if(!empty($params['empresa'])){
                $select->where(array('e.id' => $params['empresa']));
            }

            if(!empty($params['categoria_associado'])){
                $select->where(array('tb_associado.categoria_associado' => $params['categoria_associado']));
            }

            if(!empty($params['anuidade'])){
                $select->where(array('aa.id' => $params['anuidade']));
            }

            if(!empty($params['nome_completo'])){
                $select->where->like('nome_completo', '%'.$params['nome_completo'].'%');
            }

            if(!empty($params['cpf'])){
                $select->where(array('cpf' => $params['cpf']));
            }

            if(!empty($params['cliente'])){
                $select->where(array('c.id' => $params['cliente']));
            } 

            if($group){
              $select->group('tb_associado.id');
            }           
        });
    }

    public function getPagamento($idAssociado, $idAnuidade){
        return $this->getTableGateway()->select(function($select) use ($idAssociado, $idAnuidade) {
            $select->columns(array('id_associado' => 'id', 'empresa', 'cliente', 'categoria_associado', 'adimplente'));
            
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('*')
                );

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_associado.empresa',
                    array('nome_fantasia', 'anuidade_paypal', 'anuidade_cielo', 'anuidade_deposito', 'anuidade_ipag', 'dados_deposito')
                );


            $select->join(
                    array('ac' => 'tb_associado_categoria'),
                    'ac.id = categoria_associado',
                    array('nome_categoria' => 'nome')
                );
            
            $select->join(
                    array('aa' => 'tb_associado_anuidade'),
                    'aa.categoria = categoria_associado',
                    array('descricao_anuidade' => 'descricao', 'id_anuidade' => 'id', 'valor', 'data_inicio', 'data_fim')
                );

            $select->join(
                    array('ap' => 'tb_associado_pagamento'),
                    'ap.associado = tb_associado.id AND aa.id = ap.anuidade',
                    array('data_pagamento', 'data_baixa', 'forma_pagamento', 'id_pagamento' => 'id', 'valor_pagamento'),
                    'LEFT'
                );

            $select->join(
                    array('u' => 'tb_usuario'),
                    'u.id = ap.usuario_baixa',
                    array('nome_usuario' => 'nome'),
                    'LEFT'
                );

            $select->where(array('tb_associado.id' => $idAssociado, 'aa.id' => $idAnuidade));

        })->current();
    }

    public function importarAssociados($objPHPExcel, $dados){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $sheet = $objPHPExcel->getSheet(0); 
            $highestRow = $sheet->getHighestRow(); 
            $serviceCliente = new TableGateway('tb_cliente', $adapter);
            $serviceEstado = new TableGateway('tb_estado', $adapter);
            $serviceCidade = new TableGateway('tb_cidade', $adapter);
            $serviceUsuario = new TableGateway('tb_usuario', $adapter);
            $bcrypt = new Bcrypt();
            for ($row = 2; $row <= $highestRow; $row++){
                $rowData = $sheet->rangeToArray('A'.$row.':'.'N'.$row,
                                                NULL,
                                                true,
                                                true,
                                                false);
                $rowData = $rowData[0];
                $rowData[13] = preg_replace("/[^0-9]/", "", $rowData[13]);
                $rowData[13] = parent::mask($rowData[13], '###.###.###-##');
                //se vazio fim da importação
                if(empty($rowData[1])){
                    $this->msg .= 'Linha '.$row.' desconsiderada, coluna B vazia!<br>';
                    continue;
                }
                //inserir cliente
                if(!isset($rowData[13]) || strlen($rowData[13]) != 14){
                    $this->msg .= 'Linha '.$row.' desconsiderada, cpf: "'.$rowData[13].'" não tem 14 caracteres!<br>';
                    continue;
                }
                $cliente = $serviceCliente->select(array('cpf' => $rowData[13]))->current();
                if($cliente){
                    $idCliente = $cliente->id;
                }else{
                    $telefone = $rowData[8];
                    if(empty($rowData[8])){
                        $telefone = $rowData[9];
                    }

                    $arrayInsert = array(
                        'cpf'               =>  $rowData[13],
                        'nome_completo'     =>  $rowData[1],
                        'nome_certificado'  =>  $rowData[1],
                        'nome_cracha'       =>  $rowData[1],
                        'nm_rua'            =>  $rowData[3],
                        'bairro'            =>  $rowData[4],
                        'cep'               =>  $rowData[7],
                        'telefone'          =>  $telefone,
                        'celular'           =>  $rowData[10],
                        'email'             =>  $rowData[11],
                        'rg'                =>  $rowData[12],
                    );

                    $estado = $serviceEstado->select(array('uf' =>  $rowData[6]))->current();
                    $idCidade = '';
                    if($estado){
                        $cidade = $serviceCidade->select(array('nome' => $rowData[5], 'estado' => $estado->id))->current();
                        if($cidade){
                            $arrayInsert['cidade'] = $cidade->id;
                        }
                    }

                    $serviceCliente->insert($arrayInsert);

                    $idCliente = $serviceCliente->getLastInsertValue();
                    
                    //criar login
                    $cpf = str_replace('.', '', $rowData[13]);
                    $cpf = str_replace('-', '', $cpf);
                    $serviceUsuario->insert(array(
                        'nome'              =>  $rowData[1],
                        'login'             =>  $rowData[13],
                        'senha'             =>  $bcrypt->create($cpf),
                        'id_usuario_tipo'   =>  2,
                        'cliente'           =>  $idCliente,
                    ));
                }

                //inserir associado
                $associado = parent::getRecord($idCliente, 'cliente');
                if(!$associado){
                    parent::insert(array(
                        'empresa'               => $dados['empresa'],
                        'cliente'               => $idCliente,
                        'adimplente'            => 'S',
                        'categoria_associado'   => $dados['categoria_associado']
                    ));
                }
            
            }
            $connection->commit();
            return $row-1;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        return false;
    }


    public function exportarAssociados($empresa = false){

        return $this->getTableGateway()->select(function($select) use ($empresa) {
            $select->columns(array('id_associado' => 'id', 'empresa', 'cliente', 'categoria_associado', 'adimplente', 'status_associado' => 'ativo'));
            
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('*')
                );

            $select->join(
                    array('ec' => 'tb_estado_civil'),
                    'ec.id = c.estado_civil',
                    array('nome_estado_civil' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('n' => 'tb_nacionalidade'),
                    'n.id = c.nacionalidade',
                    array('nome_nacionalidade' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('cid' => 'tb_cidade'),
                    'cid.id = c.cidade',
                    array('nome_cidade' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('est' => 'tb_estado'),
                    'est.id = cid.estado',
                    array('nome_estado' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_associado.empresa',
                    array('nome_fantasia')
                );

            $select->join(
                    array('ac' => 'tb_associado_categoria'),
                    'ac.id = categoria_associado',
                    array('nome_categoria' => 'nome')
                );

            $select->join(
                    array('aa' => 'tb_associado_anuidade'),
                    'aa.categoria = categoria_associado',
                    array('descricao_anuidade' => 'descricao', 'id_anuidade' => 'id', 'data_inicio', 'data_fim', 'data_vencimento' => 'data_pagamento', 'valor_anuidade' => 'valor')
                );

            $select->join(
                    array('ap' => 'tb_associado_pagamento'),
                    'ap.associado = tb_associado.id AND ap.anuidade = aa.id',
                    array('id_pagamento' => 'id', 'data_pagamento', 'data_baixa', 'valor_pagamento'),
                    'LEFT'
                );


            $select->join(
                    array('fp' => 'tb_forma_pagamento'),
                    'fp.id = ap.forma_pagamento',
                    array('nome_forma' => 'nome'),
                    'LEFT'
                );
            
            $select->join(
                array('apc' => 'tb_associado_pagamento_comprovante'),
                'apc.anuidade = aa.id AND apc.associado = tb_associado.id',
                array('id_comprovante' => 'id', 'observacoes_comprovante' => 'observacoes'),
                'LEFT'
            );

            if(!empty($empresa)){
                $select->where(array('e.id' => $empresa));
            }

            $select->order('c.nome_completo, c.id, aa.id');
        });
    }

    public function getpromocao($categoriaEvento, $cpf){
        return $this->getTableGateway()->select(function($select) use ($categoriaEvento, $cpf) {
            $select->columns(array('id_associado' => 'id', 'empresa', 'cliente', 'categoria_associado', 'adimplente'));
            
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array()
                );

            $select->join(
                    array('ap' => 'tb_associado_promocao'),
                    'ap.categoria_associado = tb_associado.categoria_associado',
                    array('desconto', 'status_associado')
                );

            $select->join(
                    array('aa' => 'tb_associado_anuidade'),
                    new Expression('aa.categoria = ap.categoria_associado AND "'.date('Y-m-d').'" BETWEEN data_inicio AND data_fim'), 
                    array()
                );
            
            $select->join(
                    array('apg' => 'tb_associado_pagamento'),
                     new Expression('apg.associado = tb_associado.id AND apg.anuidade = aa.id'), 
                    array('id_pagamento' => 'id'),
                    'LEFT'
                );

            $select->where(array('c.cpf' => $cpf, 'ap.categoria_evento' => $categoriaEvento));
            
        })->current();
    }

    public function getArquivosAssociado($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->columns(array('id_associado' => 'id', 'empresa', 'cliente', 'categoria_associado', 'adimplente', 'exibir_site'));
            
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('*')
                );

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_associado.empresa',
                    array('nome_fantasia')
                );

            $select->join(
                    array('ac' => 'tb_associado_categoria'),
                    'ac.id = categoria_associado',
                    array('nome_categoria' => 'nome')
                );

            $select->join(
                    array('aa' => 'tb_associado_arquivo'),
                    'aa.categoria = ac.id',
                    array('descricao_arquivo' => 'descricao', 'arquivo', 'id_arquivo' => 'id', 'status_associado')
                );

          
            $select->order('e.nome_fantasia, ac.nome, aa.descricao');

            $select->where(array('c.id' => $params['cliente']));
            

        });
    }

    public function adimplenteByAnuidade($idAnuidade, $idCategoria, $pagamentos){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            if($pagamentos->count() == 0){
              return false;
            }
            parent::update(array('adimplente' => 'N'), array('categoria_associado' => $idCategoria));
            $sql = 'UPDATE tb_associado SET adimplente = "S" WHERE id IN(';
            foreach ($pagamentos as $pagamento) {
                $sql .= $pagamento->associado.', ';
            }

            $sql = substr($sql, 0, -2);
            $sql .= ');';

            $adapter = $this->tableGateway->getAdapter();
            $sql = str_replace('`', '', $sql);

            $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        $connection->rollback();
        return false;
    }

    public function listarByAnuidade($anuidade, $idCategoria, $pagamentos){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            parent::update(array('exibir_site' => 'N'), array('categoria_associado' => $idCategoria));
            if($pagamentos->count() > 0){
              $sql = 'UPDATE tb_associado SET exibir_site = "S" WHERE id IN(';
              foreach ($pagamentos as $pagamento) {
                  $sql .= $pagamento->associado.', ';
              }
              
              $sql = substr($sql, 0, -2);
              $sql .= ');';
              $adapter = $this->tableGateway->getAdapter();
              $sql = str_replace('`', '', $sql);
              $resultSet = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

              $connection->commit();
            }
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
        $connection->rollback();
        return false;
    }

    public function getQuestionariosAberto($idEmpresa, $idQuestionario){
        return $this->getTableGateway()->select(function($select) use ($idEmpresa, $idQuestionario) {
            $select->columns(array('quantidade' => new Expression('COUNT(tb_associado.id)')));

            $select->join(
                    array('qa' => 'tb_questionario_associado'),
                    new Expression('qa.associado = tb_associado.id AND qa.questionario = ?', $idQuestionario), 
                    array('id_resposta' => 'id'),
                    'LEFT'
                );

            $select->where(array('tb_associado.empresa' => $idEmpresa));
            $select->where->isNull('qa.id');

        })->current();
    }

    public function getQuestionariosRespondidos($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = tb_associado.cliente', 
                    array('nome_completo')
                );

            $select->join(
                    array('qa' => 'tb_questionario_associado'),
                    new Expression('qa.associado = tb_associado.id AND qa.questionario = ?', $params['questionario']),  
                    array('id_resposta' => 'id', 'data_hora_resposta'),
                    'LEFT'
                );

            $select->join(
                    array('q' => 'tb_questionario'),
                    'q.id = qa.questionario',
                    array('nome_questionario' => 'nome'),
                    'LEFT'
                );

            $select->order('nome_completo');

            if($params){

                if(isset($params['nome']) && !empty($params['nome'])){
                    $select->where->like('nome_completo', '%'.$params['nome'].'%');
                }

                if(isset($params['empresa']) && !empty($params['empresa'])){
                    $select->where(array('tb_associado.empresa' => $params['empresa']));
                }


                if(isset($params['respondida']) && !empty($params['respondida'])){
                    if($params['respondida'] == 'S'){
                        $select->where->isNotNull('qa.id');
                    }else{
                        $select->where->isNull('qa.id');
                    }
                }
            }
        });
    }

    public function getAssociadosSite($cidade){
        return $this->getTableGateway()->select(function($select) use ($cidade) {

            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('nome_completo', 'cpf', 'email', 'bairro', 'nm_rua', 'numero', 'complemento', 'telefone', 'celular',
                    'endereco_1', 'telefone_1', 'telefone_1_2', 'site_1', 'email_1', 'endereco_2', 'telefone_2', 'telefone_2_2', 'site_2', 'email_2', 'endereco_3', 'telefone_3', 'telefone_3_2', 'site_3', 'email_3', 'conselho', 'outro_conselho', 'numero_conselho')
                );

            $select->join(
                    array('cid1' => 'tb_cidade'),
                    'cid1.id = c.cidade_1',
                    array('nome_cidade1' => 'nome', 'id_cidade1' => 'id'),
                    'LEFT'
                );

            $select->join(
                    array('est1' => 'tb_estado'),
                    'est1.id = cid1.estado',
                    array('nome_estado1' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('cid2' => 'tb_cidade'),
                    'cid2.id = c.cidade_2',
                    array('nome_cidade2' => 'nome', 'id_cidade2' => 'id'),
                    'LEFT'
                );

            $select->join(
                    array('est2' => 'tb_estado'),
                    'est2.id = cid2.estado',
                    array('nome_estado2' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('cid3' => 'tb_cidade'),
                    'cid3.id = c.cidade_3',
                    array('nome_cidade3' => 'nome', 'id_cidade3' => 'id'),
                    'LEFT'
                );

            $select->join(
                    array('est3' => 'tb_estado'),
                    'est3.id = cid3.estado',
                    array('nome_estado3' => 'nome'),
                    'LEFT'
                );


            $select->where->nest
                ->equalTo('categoria_associado', 2)
                ->or->equalTo('categoria_associado', 3)
            ->unnest;

            $select->where->nest
                ->equalTo('cidade_1', $cidade)
                ->or->equalTo('cidade_2', $cidade)
                ->or->equalTo('cidade_3', $cidade)
            ->unnest;
            
            $select->where(array('tb_associado.exibir_site' => "S"));
            
            $select->order('c.nome_completo');
            
        });
    }

}
