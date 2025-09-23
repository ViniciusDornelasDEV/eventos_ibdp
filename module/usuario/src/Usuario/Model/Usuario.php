<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;

use Zend\Crypt\Password\Bcrypt;
use Application\Model\BaseTable;

class Usuario Extends BaseTable {

    public function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->login = (isset($data['login'])) ? $data['login'] : null;
        $this->senha = (isset($data['senha'])) ? $data['senha'] : null;
    }

    protected function getEncryptedPassword() {

        $bcrypt = new Bcrypt();
        return $bcrypt->create($this->senha);
    }

    public function updateCurrent($data) {

        $this->exchangeArray($data);

        $user = array(
            'login' => $this->login,
        );

        if ($this->senha) {
            $user['senha'] = $this->getEncryptedPassword();
        }

        $id = $this->getIdentity('id');

        if ($id) {
            (int) $id;
            return $this->update($user, array('id' => $id));
        } else {
            throw new \Exception('Usuário a ser alterado não foi encontrado!');
        }
    }

    public function generatePasswordResetToken(\ArrayObject $user) {

        $token = strtolower(base64_encode(mt_rand() . crypt(time() . $user->login . uniqid(mt_rand(), true))));

        $this->update(array('reset_token' => $token), array('id' => $user->id));

        return $token;
    }

    public function resetPassword(\ArrayObject $user, $data) {
        
        $this->senha = $data['senha'];
        
        $res = $this->update(
                array(
                    'senha' => $this->getEncryptedPassword(), 
                    'reset_token' => null), 
                array(
                    'id' => $user->id, 
                    'reset_token' => $data['reset_token']
                ));
        
        return $res;
        
    }

    public function getUserData($params) {
        $rowset = $this->getTableGateway()->select(function($select) use ($params) {
                    $select->join(
                                array('t' => 'tb_usuario_tipo'), 
                                't.id = tb_usuario.id_usuario_tipo', 
                                array('perfil'));
                    
                    $select->where($params);
                    
                }); 
        if (!$row = $rowset->current()) {
            return false;
        }
        return $row;
    }

    public function getUsuariosByParams($params = false){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('ut' => 'tb_usuario_tipo'),
                    'ut.id = id_usuario_tipo',
                    array('perfil')
                );

            $select->join(
                    array('c' => 'tb_cliente'),
                    'c.id = tb_usuario.cliente',
                    array('nome_usuario' => 'nome_completo'),
                    'left'
                );

            $select->join(
                    array('e' => 'tb_empresa'),
                    'e.id = tb_usuario.empresa',
                    array('nome_empresa' => 'nome_fantasia'),
                    'left'
                );

            if($params){
                if($params['nome']){
                    $select->where->like('tb_usuario.nome', '%'.$params['nome'].'%');
                }    

                if($params['empresa']){
                    $select->where(array('e.id' => $params['empresa'])); 
                }

                if($params['cliente']){
                    $select->where(array('c.id' => $params['cliente'])); 
                }
            }
            $select->order('nome');
        }); 
    }

}
