<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;

use Cliente\Form\Cidade as cidadeForm;
use Application\Form\PesquisaDash as formDash;

use Zend\Crypt\Password\Bcrypt;

use Cliente\Form\Upload as formUpload;


class IndexController extends BaseController
{

    /*public function estadoCivil($estado){
        if(strpos($estado, 'Solt')){
            return 1;
        }

        if(strpos($estado, 'Cas')){
            return 2;
        }

        if(strpos($estado, 'Sep')){
            return 3;
        }

        if(strpos($estado, 'Divo')){
            return 4;
        }

        if(strpos($estado, 'Vi')){
            return 5;
        }
    }

    public function nacionalidade($nacionalidade){
        if(strpos($nacionalidade, 'Br')){
            return 1;
        }

        return 2;
    }

    public function formatarCep($cep){
        $cep = str_replace('-', '', $cep);
        $cep = str_replace('.', '', $cep);
        $cep = trim($cep);
        if(!empty($cep) && strlen($cep) == 8){
            $cep = $this->mask($cep,'#####-###');
        }
        return $cep;
    }

    public function formatarCpf($cpf, $row){
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('-', '', $cpf);
        $cpf = str_replace(' ', '', $cpf);
        if(strlen($cpf) != 11){
            $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
        }
         $cpf = $this->mask($cpf, "###.###.###-##");

        return $cpf;
    }


    function mask($val, $mask){
        $maskared = '';
        $k = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++){
        if($mask[$i] == '#'){
            if(isset($val[$k]))
                $maskared .= $val[$k++];
            }else{
                if(isset($mask[$i]))
                $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }*/



    public function indexAction()
    {   
        /*$objReader = new \PHPExcel_Reader_Excel5();
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load('public/arquivos/enderecos.xls');
        $objPHPExcel->setActiveSheetIndex(0);

        $sheet = $objPHPExcel->getSheet(0); 
        $highestRow = $sheet->getHighestRow(); 

        $serviceAssociado = $this->getServiceLocator()->get('Associado');
        $serviceCidade = $this->getServiceLocator()->get('Cidade');
        $cpfAnterior = '';
        $numEndereco = 1;
        for ($row = 2; $row <= $highestRow; $row++){
            $rowData = $sheet->rangeToArray('A'.$row.':'.'W'.$row,
                                                NULL,
                                                true,
                                                true,
                                                false);
            $rowData = $rowData[0];
            if(!empty($rowData[1])){
                $cpf = $this->formatarCpf($rowData[1], $row);
                if($cpf != $cpfAnterior){
                    $cpfAnterior = $cpf;
                    $numEndereco = 1;
                    $associado = $serviceAssociado->getAssociadoPlanilha($cpf)->current();
                    if(!$associado){
                        $cpfAnterior = '';
                        continue;
                    }
                }else{
                    $numEndereco++;
                }
                $cidade = $serviceCidade->getCidadeAssociado($rowData[4], $rowData[3]);
                
                echo utf8_decode('UPDATE tb_cliente SET 
                    endereco_'.$numEndereco.' = "'.$rowData[2].'", 
                    cidade_'.$numEndereco.' = '.$cidade['id'].', 
                    telefone_'.$numEndereco.' = "'.$rowData[5].'",
                    site_'.$numEndereco.' = "'.$rowData[6].'",
                    email_'.$numEndereco.' = "'.$rowData[7].'" WHERE id = '.$associado['cliente'].';<br>');
                
            }


        }
       
        die();*/
        
        $usuario = $this->getServiceLocator()->get('session')->read();
        $params = array();
        if($usuario['id_usuario_tipo'] == 3){
            $this->layout('layout/empresa');
            $params['empresa'] = $usuario['empresa'];
        }

        $formDash = new formDash('formDash', $this->getServiceLocator(), $params);
        $serviceEvento = $this->getServiceLocator()->get('Evento');
        $serviceInscricao = $this->getServiceLocator()->get('Inscricao');
        //form de pesquisa
        if($this->getRequest()->isPost()){
            $post = $this->getRequest()->getPost();
            $params['id'] = $post['evento'];
            $evento = $serviceEvento->getRecordFromArray($params);
        }else{
            $evento = $serviceEvento->getRecordsFromArray($params, 'id DESC')->current();
        }
        if(!$evento){
            return new ViewModel();
        }

        $formDash->setData(array('evento' => $evento->id));

        //pesquisar totais de inscrições
        $totaisInscricoes = array();
        $totaisInscricoes['incompletas'] = $serviceInscricao
            ->getInscricoesIncompletas($evento->id)->count();

        $totaisInscricoes['pagar'] = $serviceInscricao->getInscricoesPagar($evento->id)->count();

        $totaisInscricoes['pagas'] = $serviceInscricao
            ->getInscricoesPagas(array('evento' => $evento->id))->count();

        //pesquisar 10 ultimos inscritos
        $ultimosInscritos = $serviceInscricao->getUltimosInscritos($evento->id);
        
        //quantidade de inscritos por categoria
        $categorias = array();
        $categoriasEvento = $this->getServiceLocator()
            ->get('QuantidadeCategoria')->getQuantidadeInscricoesCategoriaByEvento($evento->id)->toArray();
        foreach ($categoriasEvento as $categoria) {
            $quantidade = $serviceInscricao->getRecordsFromArray(array('cliente_categoria' => $categoria['id']))->count();
            $categorias[] = array(
                'nome_categoria' => $categoria['nome_categoria'],
                'quantidade'     => $quantidade
            );
        }

        //inscritos por categoria e status
        $categoriasStatus = array();
        foreach ($categoriasEvento as $categoria) {
            $incompletas = $serviceInscricao->getInscricoesIncompletas($evento->id, $categoria['id'])->count();
            
            $aPagar = $serviceInscricao->getInscricoesPagar($evento->id, $categoria['id'])->count();

            $paga = $serviceInscricao->getInscricoesPagas(array(
                'evento' => $evento->id, 
                'cliente_categoria' => $categoria['id']
            ))->count();
            
            $categoriasStatus[] = array(
                'nome_categoria'        => $categoria['nome_categoria'],
                'quantidade_incompleta' => $incompletas,
                'quantidade_pagar'      => $aPagar,
                'quantidade_paga'       => $paga
            );
        }

        return new ViewModel(array(
            'formDash'          =>  $formDash,
            'totaisInscricoes'  =>  $totaisInscricoes,
            'ultimosInscritos'  =>  $ultimosInscritos,
            'categorias'        =>  $categorias,
            'categoriasStatus'  =>  $categoriasStatus,
            'evento'            =>  $evento
        ));
    }

    public function cidadeAction()
    {   
    	$params = $this->getRequest()->getPost();
    	//instanciar form
    	$formPessoaFisica = new cidadeForm('formPessoaFisica', $this->getServiceLocator(), $params->required);
        $cidade = $formPessoaFisica->setCidadesByEstado($params->estado);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('cidades' => $cidade));
        return $view;
    } 

    public function carregareventosbyempresaAction(){
        $params = $this->getRequest()->getPost();
        //instanciar form
        $formDash = new formDash('formDash', $this->getServiceLocator());
        $eventos = $formDash->setEventosByEmpresa($params->empresa);
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('eventos' => $eventos));
        return $view;
    }

    public function cepAction()
    {   
        $estado = $this->params()->fromRoute('estado');
        $cidade = $this->params()->fromRoute('cidade');
        
        $estado = $this->getServiceLocator()->get('Estado')->getRecord($estado, 'uf');
        $cidade = $this->getServiceLocator()->get('Cidade')->getCidadeByParams($estado->id, $cidade);

        $resp = false;
        if($estado && $cidade){
            $resp = array('estado' => $estado->id, 'cidade' => $cidade->id);
        }
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('dados' => $resp));
        return $view;
    } 

    public function pesquisarassociadosAction(){
        $idCidade = $this->params()->fromRoute('cidade');

        $associados = false;
        if($idCidade){
            $associados = $this->getServiceLocator()->get('Associado')
                    ->getAssociadosSite($idCidade)->toArray();

            foreach ($associados as $key => $associado) {
              if($idCidade != $associado['id_cidade1']){
                $associados[$key]['endereco_1'] = '';
                $associados[$key]['telefone_1'] = '';
                $associados[$key]['telefone_1_2'] = '';
                $associados[$key]['site_1'] = '';
                $associados[$key]['email_1'] = '';
                $associados[$key]['nome_cidade1'] = '';
                $associados[$key]['nome_estado1'] = '';
              }

              if($idCidade != $associado['id_cidade2']){
                $associados[$key]['endereco_2'] = '';
                $associados[$key]['telefone_2'] = '';
                $associados[$key]['telefone_2_2'] = '';
                $associados[$key]['site_2'] = '';
                $associados[$key]['email_2'] = '';
                $associados[$key]['nome_cidade2'] = '';
                $associados[$key]['nome_estado2'] = '';
              }

              if($idCidade != $associado['id_cidade3']){
                $associados[$key]['endereco_3'] = '';
                $associados[$key]['telefone_3'] = '';
                $associados[$key]['telefone_3_2'] = '';
                $associados[$key]['site_3'] = '';
                $associados[$key]['email_3'] = '';
                $associados[$key]['nome_cidade3'] = '';
                $associados[$key]['nome_estado3'] = '';
              }
            }
            
        }
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariables(array('associados' => $associados));
        return $view;
    }


}
