<?php
namespace Evento\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Sql\Expression;

class Inscricao Extends BaseTable {
    
    public function mudarStatusPagamento($inscricao){
        if($inscricao->status_pagamento == 2){
          $this->update(array('status_pagamento' => 1), array('id' => $inscricao->id));
        }else{
          $this->update(array('status_pagamento' => 2), array('id' => $inscricao->id));
        }
    }

    public function update($dados, array $where){
      $adapter = $this->tableGateway->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();
      try {
        $inscricao = $this->getRecord($where['id']);
        if(isset($dados['status_pagamento']) && !empty($dados['status_pagamento'])){
          if($inscricao['status_pagamento'] != 8 && $inscricao['status_pagamento'] != 2){
            if($dados['status_pagamento'] == 8 || $dados['status_pagamento'] == 2){
              //atualizar quantidade de inscritos na categoria
              $tbCategoria = new TableGateway('tb_evento_cliente_categoria', $adapter);
              $categoria = $tbCategoria->select(array('id' => $inscricao['cliente_categoria']))->current();
              $categoria->quantidade_inscritos++;
              $tbCategoria->update(array('quantidade_inscritos' => $categoria->quantidade_inscritos), array('id' => $inscricao['cliente_categoria']));
            }
          }
        }

        $res = parent::update($dados, $where);
        $connection->commit();
        return $res;
      } catch (Exception $e) {
        $connection->rollback();
        return false;
      }
      $connection->rollback();
      return false;

    }

    public function concederDesconto($inscricao, $codigoPromocional){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();

      try {
        //Se veio o codigo pesquisar por código promocional
        $tbPromocao = new TableGateway('tb_evento_codigo_promocional', $adapter);
        $promocao = $tbPromocao->select(array('codigo_promocional' => $codigoPromocional))->current();
        $dadosInscricao = array();
        if($promocao){
            //Verificar se quantidade_utilizada < quantidade_total
            if($promocao->quantidade_utilizada < $promocao->quantidade_maxima){
                //adicionar +1 em quantidade utilizada
                $quantidade = $promocao->quantidade_utilizada + 1;
                $tbPromocao->update(array('quantidade_utilizada' => $quantidade), array('id' => $promocao->id));
                $desconto = $promocao->desconto;
                $dadosInscricao['codigo_desconto'] = $codigoPromocional;
            }
            
        }else{
            //nenhuma promoção encontrada
            return false;
        }   
        $dadosInscricao['valor_total'] = $this->calcularValorInscricao($inscricao['valor_bruto'], $desconto, $inscricao['quantidade_membros']);
        
        if($dadosInscricao['valor_total'] == 0){
          $dadosInscricao['status_pagamento'] = 2;
          $dadosInscricao['forma_pagamento'] = 7;
          $dadosInscricao['valor_total'] = '0';
        }

        parent::update($dadosInscricao, array('id' => $inscricao['id']));
        $connection->commit();
        return true;
      } catch (Exception $e) {
        $connection->rollback();
        return false;
      }
      $connection->rollback();
      return false;
    }



    public function realizarInscricao($dadosForm, $cliente, $valorInscricao, $clienteCategoria, $promocaoAssociado){
    	$adapter = $this->getTableGateway()->getAdapter();
		  $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();

        try {
            $desconto = 0;
            $dadosInscricao = array();

            if(!empty($dadosForm['codigo'])){
                //Se veio o codigo pesquisar por código promocional
                $tbPromocao = new TableGateway('tb_evento_codigo_promocional', $adapter);
                $promocao = $tbPromocao->select(array('codigo_promocional' => $dadosForm['codigo'], 'evento' => $valorInscricao->evento))->current();
                
                if($promocao){
                    //Verificar se quantidade_utilizada < quantidade_total
                    if($promocao->quantidade_utilizada < $promocao->quantidade_maxima){
                        //adicionar +1 em quantidade utilizada
                        $quantidade = $promocao->quantidade_utilizada + 1;
                        $tbPromocao->update(array('quantidade_utilizada' => $quantidade), array('id' => $promocao->id));
                        $desconto = $promocao->desconto;
                        $dadosInscricao['codigo_desconto'] = $dadosForm['codigo'];
                    }
                    
                }else{
                    //nenhuma promoção encontrada
                    return false;
                }   
            }
            //verificar se é um associado e se existe promoção para este associado!
            if($promocaoAssociado && $desconto == 0){
              if($promocaoAssociado->status_associado == 'T'){
                $desconto = $promocaoAssociado->desconto;
              }else{
                if(!empty($promocaoAssociado->id_pagamento)){
                  $desconto = $promocaoAssociado->desconto;
                }
              }
            }
            
            $dadosInscricao['cliente_categoria'] = $clienteCategoria->id;


            //calcular desconto do evento
            $retorno = array();
            $dadosInscricao['inscricao_status'] = 1;
            $dadosInscricao['valor_total'] = $this->calcularValorInscricao($valorInscricao->valor_inscricao, $desconto, $dadosForm['quantidade_membros']);
            
            if($dadosInscricao['valor_total'] == 0){
              //inscricao_status
              $dadosInscricao['inscricao_status'] = 3;
              //status_pagamento
              $dadosInscricao['status_pagamento'] = 2;

              //+1 em número de inscritos
              $tbCategoria = new TableGateway('tb_evento_cliente_categoria', $adapter);
              $categoria = $tbCategoria->select(array('id' => $dadosInscricao['cliente_categoria']))->current();
              $categoria->quantidade_inscritos++;
              $tbCategoria->update(array('quantidade_inscritos' => $categoria->quantidade_inscritos), array('id' => $dadosInscricao['cliente_categoria']));
            

            }
            
            if($dadosForm['quantidade_membros'] > 0){
                $dadosInscricao['quantidade_membros'] = $dadosForm['quantidade_membros'];
                
                $dadosInscricao['codigo_lider'] = $cliente->id.'43sdfdfgt'.date('s').$valorInscricao->evento.'time'.date('smY');
                $retorno['codigo_lider'] = $dadosInscricao['codigo_lider'];
            }
            //preencher dados da inscrição
            $dadosInscricao['cliente'] = $cliente->id;
            $dadosInscricao['evento'] = $valorInscricao->evento;
            
            $dadosInscricao['como_ficou_sabendo'] = $dadosForm['como_ficou_sabendo'];
            $dadosInscricao['valor_bruto'] = $valorInscricao->valor_inscricao;
            
            //insert de inscrição
            $idInscricao = $this->insert($dadosInscricao);


            $connection->commit();
            $retorno['id'] = $idInscricao;
            return $retorno;
        } catch (Exception $e) {
            $connection->rollback();
        }

    }

    public function deletar($inscricao){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();

      try {
        $opcaoAlternativa = new TableGateway('tb_evento_opcao_alternativa_inscricao', $adapter);
        $opcaoAlternativa->delete(array('inscricao' => $inscricao['id']));
        
        $this->delete(array('id' => $inscricao['id']));
        
        /*$tbUsuario = new TableGateway('tb_usuario', $adapter);
        $tbUsuario->delete(array('cliente' => $inscricao['cliente']));

        $tbCliente = new TableGateway('tb_cliente', $adapter);
        $tbCliente->delete(array('id' => $inscricao['cliente']));*/

        $connection->commit();
        return true;
      } catch (Exception $e) {
        $connection->rollback();
        return false;
      }
      $connection->rollback();
      return false;      
    }

    public function getInscricaoById($idInscricao, $empresa = false, $cliente = false){
        return $this->getTableGateway()->select(function($select) use ($idInscricao, $empresa, $cliente) {

            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('cpf', 
                          'cnpj_cliente'        => 'cnpj',
                          'nome_completo', 
                          'rua_cliente'         => 'nm_rua', 
                          'numero_cliente'      => 'numero', 
                          'complemento_cliente' => 'complemento',
                          'bairro_cliente'      =>  'bairro',
                          'cep_cliente'         => 'cep',
                          'email_cliente'       => 'email',
                          'telefone_cliente'    => 'telefone',
                          'celular_cliente'     => 'celular'
                        ),
                    'inner'
                );

            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = evento',
                    array('sigla', 'nome_evento' => 'nome', 
                          'fone_responsavel', 
                          'dias_vencimento_boleto', 
                          'taxa_boleto', 
                          'fim_evento' => '  data_fim',
                          'multa_vencimento',
                          'site_evento',
                          'mensagem_cadastro_novo',
                          'mensagem_cadastro_antigo',
                          'mensagem_pagamento',
                          'mensagem_transferencia',
                          'mensagem_trabalho',
                          'empresa',
                          'id_evento' => 'id'
                        ),
                    'inner'
                );

            $select->join(
                array('ci' => 'tb_cidade'),
                'ci.id = c.cidade',
                array('nome_cidade' => 'nome'),
                'left'
                );

            $select->join(
                array('es' => 'tb_estado'),
                'es.id = ci.estado',
                array('uf'),
                'left'
                );

            $select->join(
                    array('emp' => 'tb_empresa'),
                    'emp.id = e.empresa',
                    array('agencia', 
                          'agencia_dv', 
                          'conta', 
                          'conta_dv', 
                          'nome_fantasia', 
                          'cnpj', 
                          'endereco_empresa' => 'endereco', 
                          'cidade_uf',
                          'merchant_id'),
                    'inner'
                );

            $select->join(
                array('s' =>  'tb_inscricao_status'),
                's.id = inscricao_status',
                array('nome_status_inscricao' => 'nome'),
                'inner'
              );

            $select->join(
                array('cf' =>  'tb_como_ficou_sabendo'),
                'cf.id = como_ficou_sabendo',
                array('nome_como_ficou_sabendo' => 'nome'),
                'left'
              );

            $select->join(
                array('fp' =>  'tb_forma_pagamento'),
                'fp.id = forma_pagamento',
                array('nome_forma_pagamento' => 'nome', 'id_forma' => 'id'),
                'left'
              );

            $select->join(
                array('sp' =>  'tb_status_pagamento'),
                'sp.id = status_pagamento',
                array('nome_status' => 'nome'),
                'left'
              );

            $select->join(
                array('ecc' => 'tb_evento_cliente_categoria'),
                'ecc.id = tb_inscricao.cliente_categoria',
                array(),
                'INNER'
              );

            $select->join(
                array('cc' => 'tb_cliente_categoria'),
                'cc.id = ecc.cliente_categoria',
                array('nome_categoria' => 'nome', 'id_categoria' => 'id'),
                'INNER'
              );

            if($empresa){
              $select->where(array('e.empresa' => $empresa));
            }
            if($cliente){
              $select->where(array('tb_inscricao.cliente' => $cliente));
            }

            $select->where(array('tb_inscricao.id' => $idInscricao));
        })->current();
    }

    public function getInscricaoByBoleto($codigoBoleto){
        return $this->getTableGateway()->select(function($select) use ($codigoBoleto) {

            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('cpf', 
                          'nome_completo', 
                          'rua_cliente'         => 'nm_rua', 
                          'numero_cliente'      => 'numero', 
                          'complemento_cliente' => 'complemento',
                          'bairro_cliente'      =>  'bairro',
                          'cep_cliente'         => 'cep',
                          'email_cliente'       => 'email',
                          'telefone_cliente'    => 'telefone',
                          'celular_cliente'     => 'celular'
                        ),
                    'inner'
                );

            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = evento',
                    array('sigla', 'nome_evento' => 'nome', 
                          'fone_responsavel', 
                          'dias_vencimento_boleto', 
                          'taxa_boleto', 
                          'fim_evento' => '  data_fim',
                          'multa_vencimento',
                          'site_evento'
                        ),
                    'inner'
                );

            $select->join(
                array('ci' => 'tb_cidade'),
                'ci.id = c.cidade',
                array('nome_cidade' => 'nome'),
                'left'
                );

            $select->join(
                array('es' => 'tb_estado'),
                'es.id = ci.estado',
                array('uf'),
                'left'
                );

            $select->join(
                    array('emp' => 'tb_empresa'),
                    'emp.id = e.empresa',
                    array('agencia', 
                          'agencia_dv', 
                          'conta', 
                          'conta_dv', 
                          'nome_fantasia', 
                          'cnpj', 
                          'endereco_empresa' => 'endereco', 
                          'cidade_uf'),
                    'inner'
                );

            $select->where(array('tb_inscricao.numero_pagamento_boleto' => $codigoBoleto));
        })->current();
    }

    public function getInscricoesByParams(array $params){
      
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('cpf', 
                          'nome_completo', 
                          'cnpj',
                          'nome_certificado'
                        ),
                    'inner'
                );

            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = tb_inscricao.evento',
                    array('sigla', 'nome_evento' => 'nome', 'certificado_1', 'enviar_trabalho'),
                    'inner'
                );

            $select->join(
                    array('emp' => 'tb_empresa'),
                    'emp.id = e.empresa',
                    array('nome_fantasia'),
                    'inner'
                );

            $select->join(
                  array('si' => 'tb_inscricao_status'),
                  'tb_inscricao.inscricao_status = si.id',
                  array('status_inscricao' => 'nome'),
                  'inner'
              );

            $select->join(
                array('sp' => 'tb_status_pagamento'),
                'sp.id = tb_inscricao.status_pagamento',
                array('nome_status_pagamento' => 'nome'),
                'LEFT'
              );

            $select->join(
                array('ecc' => 'tb_evento_cliente_categoria'),
                'ecc.id = tb_inscricao.cliente_categoria',
                array(),
                'INNER'
              );

            $select->join(
                array('cc' => 'tb_cliente_categoria'),
                'cc.id = ecc.cliente_categoria',
                array('nome_categoria' => 'nome'),
                'INNER'
              );

            $select->join(
                array('et' => 'tb_evento_trabalho'),
                'et.inscricao = tb_inscricao.id',
                array('categoria_trabalho' => 'categoria', 'id_trabalho' => 'id', 'trabalho_aprovado' => 'aprovado', 'poster'),
                'LEFT'
              );

            /*$select->join(
                array('etp' => 'tb_evento_trabalho_poster'),
                'et.id = etp.avaliacao',
                array('id_poster' => 'id'),
                'LEFT'
              );*/
            
            if(!empty($params['empresa'])){
              $select->where(array('empresa' => $params['empresa']));
            }

            if(!empty($params['cliente'])){
              $select->where(array('tb_inscricao.cliente' => $params['cliente']));
            }
            
            if(!empty($params['evento'])){
              $select->where(array('tb_inscricao.evento' => $params['evento']));
            }
            
            if(!empty($params['inscricao_status'])){
              $select->where(array('inscricao_status' => $params['inscricao_status']));
            }

            if(!empty($params['status_pagamento'])){
              $select->where(array('status_pagamento' => $params['status_pagamento'])); 
            }

            if(!empty($params['cliente_categoria'])){
              $select->where(array('ecc.cliente_categoria' => $params['cliente_categoria'])); 
            }

            if(!empty($params['nome'])){
               $select->where->like('c.nome_completo', '%'.$params['nome'].'%');
            }

            if(!empty($params['cpf'])){
              $select->where(array('c.cpf' => $params['cpf']));
            }

            if(!empty($params['comprovante'])){
              if($params['comprovante'] == 'S'){
                $select->where->isNotNull('comprovante');
                 $select->where->notEqualTo('comprovante', '');
              }else{
                $select->where->isNull('comprovante');
              }
            }

            $select->order('tb_inscricao.id DESC');
        });
    }

    public function getInscricoesExcel($idEvento){
       return $this->getTableGateway()->select(function($select) use ($idEvento) {
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array(
                      'id_cliente' => 'id', 'cpf', 'cnpj', 'nome_completo', 'nome_certificado', 'nome_cracha', 'estado_civil', 
                      'nacionalidade', 'sexo', 'rg', 'data_nascimento', 'email', 'nome_empresa', 'endereco_comercial', 'cep', 'cidade', 
                      'bairro', 'nm_rua', 'numero', 'complemento', 'profissao', 'cargo', 'telefone', 'celular', 'conselho', 
                      'outro_conselho', 'numero_conselho', 'especialidade', 'endereco', 'pais', 'observacoes'),
                    'INNER'
                );
            $select->join(
                    array('n' => 'tb_nacionalidade'),
                    'c.nacionalidade = n.id',
                    array('nome_nacionalidade' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('cid' => 'tb_cidade'),
                    'c.cidade = cid.id',
                    array('nome_cidade' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('uf' => 'tb_estado'),
                    'cid.estado = uf.id',
                    array('uf'),
                    'LEFT'
                );

            $select->join(
                    array('est' => 'tb_estado_civil'),
                    'est.id = c.estado_civil',
                    array('nome_civil' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('ec' => 'tb_evento_cliente_categoria'),
                    'ec.id = tb_inscricao.cliente_categoria',
                    array(),
                    'INNER'
                );

            $select->join(
                    array('cc' => 'tb_cliente_categoria'),
                    'cc.id = ec.cliente_categoria',
                    array('nome_categoria' => 'nome'),
                    'INNER'
                );

            $select->join(
                    array('sp' => 'tb_status_pagamento'),
                    'sp.id = status_pagamento',
                    array('status_pagamento' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('fp' => 'tb_forma_pagamento'),
                    'fp.id = tb_inscricao.forma_pagamento',
                    array('nome_forma_pagamento' => 'nome'),
                    'LEFT'
                );

            $select->join(
                    array('ecp' => 'tb_evento_codigo_promocional'),
                    new Expression('ecp.evento = tb_inscricao.evento AND ecp.codigo_promocional = tb_inscricao.codigo_desconto'), 
                    array('porcentagem_desconto' => 'desconto'),
                    'LEFT'
                );

            $select->join(
                    array('isp' => 'tb_inscricao_status_pagamento'),
                    'isp.inscricao = tb_inscricao.id',
                    array('data_hora_pagamento2' => 'data_hora'),
                    'LEFT'
                );


            $select->where(array('tb_inscricao.evento' => $idEvento));

            $select->group('tb_inscricao.id');
            $select->order('sp.nome, c.nome_completo');
        });        
    }

    public function getInscricoesFromArray(array $params){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('cpf', 
                          'nome_completo', 
                          'email'
                        ),
                    'inner'
                );

            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = tb_inscricao.evento',
                    array('nome_evento' => 'nome'
                        ),
                    'inner'
                );
            $select->where($params);
            //$select->order('tb_inscricao.id DESC');
        });
    }

    public function getInscricaoMensagens($params){
      return $this->getTableGateway()->select(function($select) use ($params) {
        $select->join(
                array('c' => 'tb_cliente'),
                'c.id = cliente',
                array('cpf', 
                      'nome_completo', 
                      'email'
                    ),
                'inner'
            );

        $select->join(
                array('e' => 'tb_evento'),
                'e.id = tb_inscricao.evento',
                array('nome_evento' => 'nome'
                    ),
                'inner'
            );
        
        $select->where(array('evento' => $params['evento']));

        if(isset($params['cliente_categoria']) && $params['cliente_categoria'] != 'T'){
          $select->where(array('cliente_categoria' => $params['cliente_categoria']));
        }

        if(isset($params['pago'])){
          if($params['pago'] == 'S'){
            $select->where->nest
                ->equalTo('status_pagamento', 2)
                ->or->equalTo('status_pagamento', 8)
                ->or->equalTo('status_pagamento', 9)
              ->unnest;
          }

          if($params['pago'] == 'N'){
            $select->where->nest
                ->notEqualTo('status_pagamento', 2)
                ->and->notEqualTo('status_pagamento', 8)
                ->and->notEqualTo('status_pagamento', 9)
                ->or->isNull('status_pagamento')
              ->unnest;
          }
        }
        //$select->order('tb_inscricao.id DESC');
        });
    }

    public function pagarPaypal($dados, $inscricao){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();

      try {
        //atualizar inscrição
        parent::update(array(
          'token_paypal'        => $dados['TOKEN'],
          'payer_id_paypal'     => $dados['PAYERID'],
          'forma_pagamento'     => 5,
          'status_pagamento'    => 2
          ), array('id' => $inscricao->id
        ));

        //atualizar status de pagamento
        $tbInscricaoStatus = new TableGateway('tb_inscricao_status_pagamento', $adapter);
        $tbInscricaoStatus->insert(array('inscricao' => $inscricao->id, 'status' => 2));
        
        //somar +1 à quantidade de inscritos da categoria
        $tbCategoria = new TableGateway('tb_evento_cliente_categoria', $adapter);
        $categoria = $tbCategoria->select(array('id' => $inscricao['cliente_categoria']))->current();
        $categoria->quantidade_inscritos++;
        $tbCategoria->update(array('quantidade_inscritos' => $categoria->quantidade_inscritos), array('id' => $inscricao['cliente_categoria']));

        $connection->commit();
        return true;
      } catch (Exception $e) {
        $connection->rollback();
        return false;
      }
      $connection->rollback();
      return false;
    }

    public function pagarIpag($callback, $inscricao){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();
      
      try {
        //atualizar inscrição
        parent::update(array(
          'tipo_pagamento'=>  $callback->attributes->method,
          'ipag_id'       =>  $callback->id,
          'descricao'     =>  $callback->attributes->status->code.' - '.
                              $callback->attributes->status->message.': '.
                              $callback->attributes->gateway->message,
          'data'          =>  date('Y-m-d H:i:s'),
          'forma_pagamento'     => 12,
          'status_pagamento'    => 2
          ), array('id' => $inscricao->id
        ));

        //atualizar status de pagamento
        $tbInscricaoStatus = new TableGateway('tb_inscricao_status_pagamento', $adapter);
        $tbInscricaoStatus->insert(array('inscricao' => $inscricao->id, 'status' => 2));
        
        //somar +1 à quantidade de inscritos da categoria
        $tbCategoria = new TableGateway('tb_evento_cliente_categoria', $adapter);
        $categoria = $tbCategoria->select(array('id' => $inscricao['cliente_categoria']))->current();
        $categoria->quantidade_inscritos++;
        $tbCategoria->update(array('quantidade_inscritos' => $categoria->quantidade_inscritos), array('id' => $inscricao['cliente_categoria']));

        $connection->commit();
        return true;
      } catch (Exception $e) {
        $connection->rollback();
        return false;
      }
      $connection->rollback();
      return false;
    }


    private function calcularValorInscricao($valorInscricao, $desconto, $quantidadeMembros){
        if($desconto > 0){
            $valorDesconto = ($valorInscricao*$desconto)/100;
            $valorLiquido = $valorInscricao - $valorDesconto;
        }else{
            $valorLiquido = $valorInscricao;
        }
        $quantidadeMembros = (INT) $quantidadeMembros;
        if($quantidadeMembros > 0){
            $valorLiquido = ($quantidadeMembros * $valorLiquido) + $valorLiquido;
        }

        return $valorLiquido;        
    }

      public function getInscricoesPagas(array $params){
      
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('cpf', 
                          'nome_completo', 
                          'cnpj',
                          'nome_certificado',
                          'id_cliente'  =>  'id'
                        ),
                    'inner'
                );

            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = tb_inscricao.evento',
                    array('sigla', 'nome_evento' => 'nome', 'certificado_1', 'certificado_2', 'certificado_3'),
                    'inner'
                );

            $select->join(
                    array('emp' => 'tb_empresa'),
                    'emp.id = e.empresa',
                    array('nome_fantasia'),
                    'inner'
                );

            $select->join(
                  array('si' => 'tb_inscricao_status'),
                  'tb_inscricao.inscricao_status = si.id',
                  array('status_inscricao' => 'nome'),
                  'inner'
              );

            $select->join(
                array('sp' => 'tb_status_pagamento'),
                'sp.id = tb_inscricao.status_pagamento',
                array('nome_status_pagamento' => 'nome'),
                'LEFT'
              );

            $select->join(
                array('ecc' => 'tb_evento_cliente_categoria'),
                'ecc.id = tb_inscricao.cliente_categoria',
                array(),
                'INNER'
              );

            $select->join(
                array('cc' => 'tb_cliente_categoria'),
                'cc.id = ecc.cliente_categoria',
                array('nome_categoria' => 'nome'),
                'INNER'
              );

             $select->where->nest
                ->equalTo('status_pagamento', 2)
                ->or->equalTo('status_pagamento', 8)
                ->or->equalTo('status_pagamento', 9)
                ->unnest;

            if(!empty($params['cliente'])){
              $select->where(array('tb_inscricao.cliente' => $params['cliente']));
            }
            
            if(!empty($params['evento'])){
              $select->where(array('tb_inscricao.evento' => $params['evento']));
            }

            if(!empty($params['nome'])){
               $select->where->like('c.nome_completo', '%'.$params['nome'].'%');
            }

            if(!empty($params['cpf'])){
              $select->where(array('c.cpf' => $params['cpf']));
            }

            if(!empty($params['cliente_categoria'])){
              $select->where(array('tb_inscricao.cliente_categoria' => $params['cliente_categoria']));
            }

            $select->order('tb_inscricao.id DESC');
        });
    }

    public function getInscricoesPagar($idEvento, $categoria = false){
      return $this->getTableGateway()->select(function($select) use ($idEvento, $categoria) {
            
            $select->where->nest
                ->notEqualTo('status_pagamento', 2)
                ->and->notEqualTo('status_pagamento', 8)
                ->and->notEqualTo('status_pagamento', 9)
                ->or->isNull('status_pagamento')
                ->unnest;
            
            $select->where->notEqualTo('inscricao_status', 1);

            if($categoria){
              $select->where(array('cliente_categoria' => $categoria));
            }

            $select->where(array('tb_inscricao.evento' => $idEvento));
        });
    }

    public function getInscricoesIncompletas($idEvento, $categoria = false){
      return $this->getTableGateway()->select(function($select) use ($idEvento, $categoria) {
            
            $select->where->nest
                ->notEqualTo('status_pagamento', 2)
                ->and->notEqualTo('status_pagamento', 8)
                ->and->notEqualTo('status_pagamento', 9)
                ->or->isNull('status_pagamento')
                ->unnest;
            
            $select->where(array('inscricao_status' => 1));

            if($categoria){
              $select->where(array('cliente_categoria' => $categoria));
            }

            $select->where(array('tb_inscricao.evento' => $idEvento));
        });
    }

    public function getUltimosInscritos($idEvento){
      return $this->getTableGateway()->select(function($select) use ($idEvento) {
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('cpf', 
                          'nome_completo', 
                          'cnpj',
                          'nome_certificado'
                        ),
                    'inner'
                );

            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = tb_inscricao.evento',
                    array('sigla', 'nome_evento' => 'nome', 'certificado_1'),
                    'inner'
                );

            $select->where(array('evento' => $idEvento));
            $select->order('tb_inscricao.id DESC');
            $select->limit(5);
        });
    }
    
    public function getTrabalhos(array $params, $idInscricao = false){
      
        return $this->getTableGateway()->select(function($select) use ($params, $idInscricao) {
            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = cliente',
                    array('cpf', 
                          'nome_completo', 
                          'cnpj',
                          'id_cliente'  =>  'id'
                        ),
                    'inner'
                );

            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = tb_inscricao.evento',
                    array('sigla', 'nome_evento' => 'nome'),
                    'inner'
                );

            $select->join(
                    array('emp' => 'tb_empresa'),
                    'emp.id = e.empresa',
                    array('nome_fantasia'),
                    'inner'
                );

            $select->join(
                    array('et' => 'tb_evento_trabalho'),
                    'et.inscricao = tb_inscricao.id',
                    array('id_inscricao' => 'inscricao', 'id_trabalho' => 'id', 'categoria_trabalho' => 'categoria', 'titulo_trabalho' => 'titulo', 'autores_trabalho' => 'autores', 
                      'resumo_trabalho' => 'resumo', 'trabalho_aprovado' => 'aprovado', 'afiliacao', 'descritor1', 'descritor2', 'descritor3', 
                      'apoio_financeiro', 'avaliador1', 'avaliador2', 'avaliador3', 'avaliador4', 'poster', 'apoio_financeiro', 'pontuacao', 
                      'poster', 'pode_postar', 'link_video', 'comite_etica', 'stricto_sensu', 'mestrado_doutorado', 'poster'),
                    'inner'
                );

            $select->join(
                    array('etc' => 'tb_evento_trabalho_categoria'),
                    'etc.id = et.categoria',
                    array('nome_categoria' => 'categoria'),
                    'left'
                );


             $select->where->nest
                ->equalTo('status_pagamento', 2)
                ->or->equalTo('status_pagamento', 8)
                ->or->equalTo('status_pagamento', 9)
                ->unnest;

            if(isset($params['empresa']) && !empty($params['empresa'])){
              $select->where(array('e.empresa' => $params['empresa']));
            }
            
            if(!empty($params['evento'])){
              $select->where(array('tb_inscricao.evento' => $params['evento']));
            }

            if(isset($params['status'])){
              if($params['status'] == 'A'){
                $select->where->isNull('et.id');
              }else{
                $select->where->isNotNull('et.id');
              }
            }
            

            if(!empty($params['nome'])){
              $select->where->like('c.nome_completo', '%'.$params['nome'].'%');
            }

            if($idInscricao){
              $select->where(array('tb_inscricao.id' => $idInscricao));
            }

            if(!empty($params['aprovado'])){
              $select->where(array('et.aprovado' => $params['aprovado']));
            }

            if(!empty($params['categoria'])){
              $select->where(array('etc.id' => $params['categoria']));
            }

            if(!empty($params['titulo'])){
              $select->where->like('et.titulo', '%'.$params['titulo'].'%');
            }

            if(!empty($params['autores'])){
              $select->where->like('c.nome_completo', '%'.$params['autores'].'%');
            }

            if(!empty($params['sigla'])){
              $select->where->like('e.sigla', '%'.$params['sigla'].'%');
            }

            if(!empty($params['visualizar_trabalhos'])){
              $select->where(array('e.visualizar_trabalhos' => 'S'));
            }

            if(!empty($params['avaliador'])){
              $select->where->nest
                ->equalTo('et.avaliador1', $params['avaliador'])
                ->or->equalTo('et.avaliador2', $params['avaliador'])
                ->or->equalTo('et.avaliador3', $params['avaliador'])
                ->or->equalTo('et.avaliador4', $params['avaliador'])
              ->unnest;

              $select->join(
                array('eta' => 'tb_evento_trabalho_avaliador'),
                new Expression('eta.avaliador = '.$params['avaliador'].' AND et.id = eta.avaliacao'),
                array('normas', 'id_trabalho_avaliacao' => 'id'),
                'left'
              );

              $select->group(array('et.id'));
            }else{
              $select->join(
                array('eta' => 'tb_evento_trabalho_avaliador'),
                'et.id = eta.avaliacao',
                array(),
                'left'
              );
              $select->group(array('et.id'));
              $select->columns(array('avaliacoes' => new Expression('COUNT(eta.id)'), 'id_inscricao' => 'id'));

              if($params['avaliacoes'] !== '' && $params['avaliacoes'] !== NULL){
                $select->having('COUNT(eta.avaliacao) = '.$params['avaliacoes']);
              }

            }

            if($params['id_trabalho']){
              $select->where(array('et.id' => $params['id_trabalho']));
            }

            $select->order('et.id');
        });
    }

     public function getTransmissoes($idCliente){
      
        return $this->getTableGateway()->select(function($select) use ($idCliente) {
            $select->join(
                    array('e' => 'tb_evento'),
                    'e.id = tb_inscricao.evento',
                    array('sigla', 'nome_evento' => 'nome', 'certificado_1', 'enviar_trabalho'),
                    'inner'
                );

            $select->join(
                    array('et' => 'tb_evento_transmissao'),
                    'e.id = et.evento',
                    array('id_transmissao' => 'id', 'descricao_transmissao' => 'descricao', 'sala', 'inicio_transmissao' => 'inicio', 'fim_transmissao' => 'fim', 
                    'codigo_embed'),
                    'inner'
                );


            $select->where(array('tb_inscricao.cliente' => $idCliente));
            
            $select->where->nest
                ->equalTo('status_pagamento', 2)
                ->or->equalTo('status_pagamento', 8)
                ->or->equalTo('status_pagamento', 9)
                ->unnest;

            //verificar data/hora
            $dataAtual = date('Y-m-d H:i:s');
            //$select->where->between("$dataAtual", 'et.inicio', 'et.fim');
            $select->where('et.fim >= "'.$dataAtual.'"');

            $select->order('tb_inscricao.id DESC, inicio ASC, sala');

        });
    }

}



