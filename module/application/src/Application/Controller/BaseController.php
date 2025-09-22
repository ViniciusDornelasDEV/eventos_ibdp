<?php



/**

 * Zend Framework (http://framework.zend.com/)

 *

 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository

 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)

 * @license   http://framework.zend.com/license/new-bsd New BSD License

 */



namespace Application\Controller;



use Zend\Mvc\Controller\AbstractActionController;

use Zend\View\Model\ViewModel;



abstract class BaseController extends AbstractActionController {

    protected $sessao = 'sessao';

    public function setarFiltro($form){

        $this->sessao = new Container();

        if($this->getRequest()->isPost()){

            $dados = $this->getRequest()->getPost();

            

            if(isset($dados->limpar)){

                unset($this->sessao->parametros);

                $dados = false;

            }else{

                $this->sessao->parametros = $dados;

            }

        }



        if(isset($this->sessao->parametros)) {

            $form->setData($this->sessao->parametros);

        }



        return $form;

    }

    /**

     * Renders a HTML page.

     * 

     * @access public

     * @param array $data (default: array())

     * @return void

     */

    public function render(array $data = array()) {



        $view = new ViewModel($data);





        $this->layout()->identity = $this->getIdentity();



        $this->layout()->sessionIsValid = $this->sessionIsValid();



        $this->layout()->route = $this->getServiceLocator()

                ->get('router')

                ->match($this->getServiceLocator()->get('request'))

                ->getMatchedRouteName();



        if (isset($data['title']) && $data['title'])

            $this->layout()->title = $data['title'];



        if (isset($data['backLink']))

            $this->layout()->backLink = $data['backLink'];



        if (isset($data['actionLink']))

            $this->layout()->actionLink = $data['actionLink'];





        if (isset($data['template'])) {

            if ($data['template'] === false) {

                $view->setTerminal(true);

            } else {

                $view->setTemplate($data['template']);

            }

        }



        if (isset($data['ajax']) && $data['ajax'] === true) {

            $this->layout('layout/ajax');

        }



        if (isset($data['layout']) && $data['layout']) {

            $this->layout($data['layout']);

        }



        return $view;

    }



    /**

     * Will get a project and return it's variables to the view.

     * 

     * @access public

     * @param array $project (default: object())

     * @return void

     */

    public function renderProject($project) {

        

        $res = $this->getProjectFunding($project);        

        return $this->render($res);

    }

    

    protected function getProjectFunding($project) {

        

        $services = $this->getServiceLocator();

        $reward = $services->get('ProjectReward');

        $update = $services->get('ProjectUpdate');

        $payment = $services->get('Payment');



        $payments = $payment->getProjectSupporters($project);

        $funding = $payment->getTotalFunding($project);

        

        $updates = $update->getRecordsFromArray(array('project' => $project->id));

                

        $rewards = $reward->getRecordsFromArray(array('project' => $project->id));

        

        return array(

                    'project' => $project,

                    'updates' => $updates,

                    'rewards' => $rewards,

                    'backers' => ($payments) ? $payments->count() : false,

                    'funding' => $funding

        );

        

    }

        

    

    /**

     * Will render a CSV File

     * 

     * The first item of the results array must be the headers

     * 

     * @access 	public

     * @param 	mixed $results 

     * @return 	void

     */



    public function renderAsCSV($data, $filename = 'report.csv') {

        

        if($data) {

        

            //sets up the first item as the titles

            $keys = array ();        

            foreach($data[0] as $k=>$v) {

                $keys[] = $k;

            }  



            $results = array($keys);



            //adds the items to the array

            foreach($data as $item) {

                $results [] = $item;

            }

        

        } else {

            $results = array();

        }



        

        $view = new ViewModel();

        $view->setTemplate('download-csv')

                ->setVariable('results', $results)

                ->setTerminal(true);



        $output = $this->getServiceLocator()

                ->get('viewrenderer')

                ->render($view);



        $response = $this->getResponse();



        $headers = $response->getHeaders();

        $headers->addHeaderLine('Content-Type', 'text/csv')

                ->addHeaderLine(

                        'Content-Disposition', sprintf("attachment; filename=\"%s\"", $filename)

                )

                ->addHeaderLine('Accept-Ranges', 'bytes')

                ->addHeaderLine('Content-Length', strlen($output));



        $response->setContent($output);



        return $response;

    }

    

    /**

     * Returns a project the user has right to edit

     * 

     * @access public

     * @param array $id (int)

     * @return void

     */

    public function getUserProject($id) {

        $services = $this->getServiceLocator();



        $projectManager = $services->get('Project');



        $project = $projectManager->getEntireProjectForUser(array('project.id'=> $id));

        

        if (!$project) {

            return $this->getResponse()->setStatusCode(404);

        } else {

            return $project;

        }        

        

    }



    /**

     * Get user information from session.

     * 

     * @access 	protected

     * @param 	mixed $property (default: null)

     * @return 	void

     */

    protected function getIdentity($property = null) {

        $storage = $this->getServiceLocator()->get('session');



        if (!$storage) {

            return false;

        }



        $data = $storage->read();



        if ($property && isset($data[$property])) {

            return $data[$property];

        }



        return $data;

    }



    /**

     * Returns TRUE if session is still valid (i.e. it hasn't expired).

     * 

     * @access public

     * @return void

     */

    public function sessionIsValid() {

        return time() <= $this->getIdentity('expiry');

    }



    /**

     * redirectToPrevious function.

     * 

     * @access protected

     * @return void

     */

    protected function redirectToPrevious() {

        $this->redirect()->toUrl($this->getPreviousUrl());

    }



    /**

     * getPreviousUrl function.

     * 

     * @access protected

     * @return void

     */

    protected function getPreviousUrl() {

        if (@$this->getRequest()->getHeader('Referer')) {

            return $this->getRequest()->getHeader('Referer')->getUri();

        } else {

            return BASE_URL;

        }

    }



    protected function getNewFileName($name, $prefix = false) {



        if (!$prefix)

            $prefix = time();



        return $prefix . '_' . preg_replace('/\s+/', '', basename($name));

    }



    protected function saveToS3($bucket, $src, $filename, $type) {



        $aws = $this->getServiceLocator()->get('aws');



        $s3 = $aws->get('s3');



        $result = $s3->putObject(array(

            'Bucket' => $bucket,

            'SourceFile' => $src,

            'Key' => $filename,

            'ContentType' => $type,

        ));



        return $result;

    }



    protected function deleteFromS3($filename, $bucket) {



        $aws = $this->getServiceLocator()->get('aws');



        $s3 = $aws->get('s3');



        $result = $s3->deleteObject(array(

            'Bucket' => $bucket,

            'Key' => $filename

        ));



        return $result;

    }



    public function eliminaMascaras($value){

        $value = preg_replace('[^0-9]', '', $value);

        $value = str_pad($value, 11, '0', STR_PAD_LEFT);

        $value = str_replace(".", "", $value);

        $value = str_replace("-", "", $value);

        return $value;

    }



    public function prepararCampos($campos){

        $aux = array();

        foreach ($campos as $campo) {

            $aux[$campo['nome_campo']] = $campo;

        }



        return $aux;

    }



    public function logSistema($mensagem, $arquivo){

        $caminho = 'public/log/';

        if(!file_exists($caminho)){

            mkdir($caminho);

        }



        //verifica se existe arquivo para a operacao

        $nomeArquivo = $caminho.'/'.$arquivo.'.txt';

        $arquivo = fopen($nomeArquivo, 'a');

        

        fwrite($arquivo, "\r\n"."\r\n".$mensagem);

        fclose($arquivo);

    }



    public function mensagemEmail($mensagem, $cliente, $formaPagamento = false){

        //substituir valores

        $mensagem = html_entity_decode($mensagem);

        $mensagem = str_replace('%LOGIN%', $this->eliminaMascaras($cliente->cpf), $mensagem);

        $mensagem = str_replace('%login%', $this->eliminaMascaras($cliente->cpf), $mensagem);

        $mensagem = str_replace('%NOME_INSCRITO%', $cliente->nome_completo, $mensagem);

        $mensagem = str_replace('%nome_inscrito%', $cliente->nome_completo, $mensagem);

        if($formaPagamento){

            $mensagem = str_replace('%FORMA_PAGAMENTO%', $formaPagamento, $mensagem);

            $mensagem = str_replace('%forma_pagamento%', $formaPagamento, $mensagem);

        }



        return $mensagem;

    }



    public function getExtensao($name){

        $extensao = explode('.', $name);

        return $extensao[1];

    }



    public function isCpf($cpf){

        if(!empty($cpf)){

            $cpf = str_replace(' ', '', $cpf);

            if(strlen($cpf) == 14){

                return $cpf;

            }

        }



        return false;

    }



    public function converterData($Data){

        if(!empty($Data)){

            if(strpos($Data, ' ')){

                return self::ConverteTimestamp($Data);

            }else{

                return self::ConverteData($Data);

            }

         }

    }

    

    private function ConverteData($Data){

        @$TipoData = stristr($Data, "/");

        if($TipoData != false){

            $Texto = explode("/",$Data);

            return $Texto[2]."-".$Texto[1]."-".$Texto[0];

        }else{

            $Texto = explode("-",$Data);

            return $Texto[2]."/".$Texto[1]."/".$Texto[0];

         }

    }

    

    private function ConverteTimestamp($Data){

        $Dados = explode(" ", $Data);

        return self::ConverteData($Dados[0]).' '.$Dados[1];

    }



    public function exibirMonetario($valor){

        if(empty($valor)){

            return '';

        }else{

            return number_format($valor, 2, ',', '.');

        }

    }

    public function validarEmail($email){

      if(filter_var(trim($email), FILTER_VALIDATE_EMAIL)){

          return true;

      }else{

          return false;

      }

    }


    public function getExcelLetter($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return $this->getExcelLetter($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }


}

