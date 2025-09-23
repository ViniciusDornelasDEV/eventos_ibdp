<?php

namespace Trabalhos\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Trabalhos\Form\VincularAvaliador as formVincularAvaliador;



use Trabalhos\Form\EnviarTrabalho as formTrabalho;
use Trabalhos\Form\CategoriaTrabalho as formCategoria;
use Trabalhos\Form\VisualizarTrabalho as formVisualizarTrabalho;
use Trabalhos\Form\Avaliar as formAvaliar;
use Trabalhos\Form\AvaliacaoEscrita as formAvaliacaoEscrita;
use Trabalhos\Form\AvaliacaoOral as formAvaliacaoOral;



class AvaliadoresController extends BaseController
{
    public function vincularavaliadorAction(){
        $idInscricao = $this->params()->fromRoute('idInscricao');
        $inscricao = $this->getServiceLocator()->get('Inscricao')->getInscricaoById($idInscricao);
        if(!$inscricao){
            $this->flashMessenger()->addWarningMessage('Trabalho não encontrado!');
            return $this->redirect()->toRoute('listarTrabalhos');
        }

        $trabalho = $this->getServiceLocator()->get('Inscricao')->getTrabalhos(array(), $idInscricao)->current();
        $autores = $this->getServiceLocator()
            ->get('InscricaoTrabalhoIntegrante')
            ->getRecords($trabalho['id_trabalho'], 'trabalho')
            ->toArray();

        $frmVincularAvaliador = new formVincularAvaliador('frmVincular', $this->getServiceLocator(), $autores);

        if($this->getRequest()->isPost()){
            $frmVincularAvaliador->setData($this->getRequest()->getPost());
            if($frmVincularAvaliador->isValid()){
                $this->getServiceLocator()->get('InscricaoTrabalho')
                    ->update($frmVincularAvaliador->getData(), array('id' => $trabalho['id_trabalho']));    
                $this->flashMessenger()->addSuccessMessage('Avaliadores vinculados com sucesso!');
                return $this->redirect()->toRoute('listarTrabalhos');
            }
            
        }

        $frmVincularAvaliador->setData($trabalho);
        return new ViewModel(array(
            'frmVincularAvaliador'  =>  $frmVincularAvaliador,
            'trabalho'              =>  $trabalho
        ));
    }

    public function listartrabalhosAction(){
        $this->layout('layout/cliente');

        //pegar usuário logado
        $usuario = $this->getServiceLocator()->get('session')->read();
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        $trabalhos = $serviceInscricao->getTrabalhos(array('avaliador' => $usuario['id']))->toArray();
        
        //paginação
        $paginator = new Paginator(new ArrayAdapter($trabalhos));
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage(40);
        $paginator->setPageRange(5);

        return new ViewModel(array(
                    'trabalhos'        => $paginator
                ));
    }

    public function visualizartrabalhoAction(){
        $this->layout('layout/cliente');

        $idTrabalho = $this->params()->fromRoute('idTrabalho');
        $usuario = $this->getServiceLocator()->get('session')->read();
        $trabalho = $this->getServiceLocator()->get('Inscricao')->getTrabalhos(array('avaliador' => $usuario['id'], 'id_trabalho' => $idTrabalho))->current();
        if($this->getRequest()->isPost()){
            if($trabalho->trabalho_aprovado == 'N'){
                $this->getServiceLocator()->get('InscricaoTrabalho')
                    ->update(array('aprovado' => 'S'), array('inscricao' => $idInscricao));
            }else{
                $this->getServiceLocator()->get('InscricaoTrabalho')
                    ->update(array('aprovado' => 'N'), array('inscricao' => $idInscricao));
            }
            $this->flashMessenger()->addSuccessMessage('Status alterado com sucesso!');
            return $this->redirect()->toRoute('listarTrabalhos');
        }

        $formTrabalho = new formVisualizarTrabalho('frmTrabalho');
        $formTrabalho->setData($trabalho);
        $tipoAvaliacao = 'Oral';
        if ($usuario['id'] == $trabalho['avaliador1'] || $usuario['id'] == $trabalho['avaliador2']) {
            if ($usuario['id'] == $trabalho['avaliador3'] || $usuario['id'] == $trabalho['avaliador4']) {
                $tipoAvaliacao = 'Todas';
            }else{
                $tipoAvaliacao = 'Escrita';
            }
        }

        return new ViewModel(array(
            'formTrabalho'  =>  $formTrabalho,
            'trabalho'      =>  $trabalho,
            'idInscricao'   =>  $idInscricao,
            'tipoAvaliacao' =>  $tipoAvaliacao
        ));
    }

    public function avaliacaoescritaAction(){
        $this->layout('layout/cliente');
        $idTrabalho = $this->params()->fromRoute('idTrabalho');
        $usuario = $this->getServiceLocator()->get('session')->read();
        $avaliacoesAvaliadas = $this->getServiceLocator()->get('EventoTrabalhoAvaliador')->getRecordsFromArray(array(
            'avaliacao' =>  $idTrabalho,
            'avaliador' =>  $usuario['id']
        ))
        ->toArray();
        $avaliacao = false;
        foreach ($avaliacoesAvaliadas as $avaliacaoAvaliada) {
            if (!empty($avaliacaoAvaliada['normas'])) {
                $avaliacao = $avaliacaoAvaliada;
            }
        }
        
        $trabalho = $this->getServiceLocator()->get('Inscricao')
            ->getTrabalhos(array('avaliador' => $usuario['id'], 'id_trabalho' => $idTrabalho))->current();
        $formAvaliar = new formAvaliacaoEscrita('frmAvaliar');
        /*if($trabalho['trabalho_aprovado'] == 'S'){
            $this->flashMessenger()->addWarningMessage('Não é possível alterar a avaliação em um trabalho aprovado!');
            return $this->redirect()->toRoute('listaTrabalhosAvaliador');
        }*/

        if($avaliacao !== false){
            $formAvaliar->setData($avaliacao);
        }

        if($this->getRequest()->isPost()){
            $avaliacoes = $this->getServiceLocator()
                ->get('EventoTrabalhoAvaliador')
                ->getRecordsFromArray(array('avaliacao' => $trabalho['id_trabalho']))
                ->toArray();
            $medianas = parent::gerarMediasAvaliacoes($avaliacoes);
            $this->getServiceLocator()->get('InscricaoTrabalho')
                ->update(
                    array(
                        'pontuacao' => $medianas['total']
                    ), 
                    array(
                        'inscricao' => $trabalho['id_inscricao'],
                    )
                );
            if ($avaliacao !== false) {
                //update
                $dados = $this->getRequest()->getPost()->toArray();
                $this->getServiceLocator()->get('EventoTrabalhoAvaliador')->update(array(
                    'avaliacao'      =>  $idTrabalho,
                    'normas'         =>  $dados['normas'],
                    'originalidade'  =>  $dados['originalidade'],
                    'relevancia'     =>  $dados['relevancia'],
                ), array(
                    'id'    =>  $avaliacao->id
                ));
                
                $this->flashMessenger()->addSuccessMessage('Avaliação alterada com sucesso!');
                return $this->redirect()->toRoute('listaTrabalhosAvaliador');
            }else{
                //insert
                $dados = $this->getRequest()->getPost()->toArray();
                $this->getServiceLocator()->get('EventoTrabalhoAvaliador')->insert(array(
                    'avaliacao'      =>  $idTrabalho,
                    'avaliador'      =>  $usuario['id'],
                    'normas'         =>  $dados['normas'],
                    'originalidade'  =>  $dados['originalidade'],
                    'relevancia'     =>  $dados['relevancia'],
                ));
                
                $this->flashMessenger()->addSuccessMessage('Trabalho avaliado com sucesso!');
                return $this->redirect()->toRoute('listaTrabalhosAvaliador');
            }
        }

        return new ViewModel(array(
            'formAvaliar'  =>  $formAvaliar,
            'idTrabalho'   =>  $idTrabalho
        ));
    }

    public function avaliacaooralAction(){
        $this->layout('layout/cliente');
        $idTrabalho = $this->params()->fromRoute('idTrabalho');
        $usuario = $this->getServiceLocator()->get('session')->read();
        $avaliacoesAvaliadas = $this->getServiceLocator()->get('EventoTrabalhoAvaliador')->getRecordsFromArray(array(
            'avaliacao' =>  $idTrabalho,
            'avaliador' =>  $usuario['id']
        ))
        ->toArray();
        $avaliacao = false;
        foreach ($avaliacoesAvaliadas as $avaliacaoAvaliada) {
            if (!empty($avaliacaoAvaliada['postura'])) {
                $avaliacao = $avaliacaoAvaliada;
            }
        }
        
        $trabalho = $this->getServiceLocator()->get('Inscricao')
            ->getTrabalhos(array('avaliador' => $usuario['id'], 'id_trabalho' => $idTrabalho))->current();
        $formAvaliar = new formAvaliacaoOral('frmAvaliar');
        /*if($trabalho['trabalho_aprovado'] == 'S'){
            $this->flashMessenger()->addWarningMessage('Não é possível alterar a avaliação em um trabalho aprovado!');
            return $this->redirect()->toRoute('listaTrabalhosAvaliador');
        }*/
        
        if ($avaliacao !== false) {
            $formAvaliar->setData($avaliacao);
        }

        if($this->getRequest()->isPost()){
            $avaliacoes = $this->getServiceLocator()
                ->get('EventoTrabalhoAvaliador')
                ->getRecordsFromArray(array('avaliacao' => $trabalho['id_trabalho']))
                ->toArray();
            $medianas = parent::gerarMediasAvaliacoes($avaliacoes);
            $this->getServiceLocator()->get('InscricaoTrabalho')
                ->update(
                    array(
                        'pontuacao' => $medianas['total']
                    ), 
                    array(
                        'inscricao' => $trabalho['id_inscricao'],
                    )
                );
            if($avaliacao !== false){
                //update
                $dados = $this->getRequest()->getPost()->toArray();
                $this->getServiceLocator()->get('EventoTrabalhoAvaliador')->update(array(
                    'avaliacao'      =>  $idTrabalho,
                    'postura'        =>  $dados['postura'],
                    'conhecimentos'  =>  $dados['conhecimentos'],
                    'clareza'        =>  $dados['clareza']
                ), array(
                    'id'    =>  $avaliacao->id
                ));
                
                $this->flashMessenger()->addSuccessMessage('Avaliação alterada com sucesso!');
                return $this->redirect()->toRoute('listaTrabalhosAvaliador');
            }else{
                //insert
                $dados = $this->getRequest()->getPost()->toArray();
                $this->getServiceLocator()->get('EventoTrabalhoAvaliador')->insert(array(
                    'avaliacao'      =>  $idTrabalho,
                    'avaliador'      =>  $usuario['id'],
                    'postura'        =>  $dados['postura'],
                    'conhecimentos'  =>  $dados['conhecimentos'],
                    'clareza'        =>  $dados['clareza']
                ));
                
                $this->flashMessenger()->addSuccessMessage('Trabalho avaliado com sucesso!');
                return $this->redirect()->toRoute('listaTrabalhosAvaliador');
            }
        }

        return new ViewModel(array(
            'formAvaliar'  =>  $formAvaliar,
            'idTrabalho'   =>  $idTrabalho
        ));
    }

    public function visualizaravaliacaoadminescritaAction(){
        $formAvaliar = new formAvaliacaoEscrita('frmAvaliar');
        $idAvaliacao = $this->params()->fromRoute('idAvaliacao');
        
        //verificar se trabalho já foi avaliado por este avaliador
        $avaliacao = $this->getServiceLocator()->get('EventoTrabalhoAvaliador')->getRecordFromArray(array(
            'id' =>  $idAvaliacao
        ));
            
        $formAvaliar->setData($avaliacao);

        return new ViewModel(array(
            'formAvaliar'  =>  $formAvaliar,
            'avaliacao'    =>  $avaliacao,
            'idInscricao'  =>  $this->params()->fromRoute('idInscricao')
        ));
    }

    public function visualizaravaliacaoadminoralAction(){
        $formAvaliar = new formAvaliacaoOral('frmAvaliar');
        $idAvaliacao = $this->params()->fromRoute('idAvaliacao');
        
        //verificar se trabalho já foi avaliado por este avaliador
        $avaliacao = $this->getServiceLocator()->get('EventoTrabalhoAvaliador')->getRecordFromArray(array(
            'id' =>  $idAvaliacao
        ));
            
        $formAvaliar->setData($avaliacao);

        return new ViewModel(array(
            'formAvaliar'  =>  $formAvaliar,
            'avaliacao'    =>  $avaliacao,
            'idInscricao'  =>  $this->params()->fromRoute('idInscricao')
        ));
    }
}

