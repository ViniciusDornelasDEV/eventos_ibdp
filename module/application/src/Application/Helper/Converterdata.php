<?php

/**
 * Tech Studio Limited
 * 
 * General application view isMobile helper
 * 
 * @author  Vinicius Silva <vinicius.s.dornelas@gmail.com>
 * @version 1.0
 */

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;

class Converterdata extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($data) {
        if(!empty($data)){
            if(strpos($data, ' ')){
                return self::ConverteTimestamp($data);
            }else{
                return self::ConverteData($data);
            }
         }
    }
    
    private function ConverteData($data){
        @$TipoData = stristr($data, "/");
        if($TipoData != false){
            $Texto = explode("/",$data);
            return $Texto[2]."-".$Texto[1]."-".$Texto[0];
        }else{
            $Texto = explode("-",$data);
            return $Texto[2]."/".$Texto[1]."/".$Texto[0];
         }
    }
    
    private function ConverteTimestamp($data){
        $Dados = explode(" ", $data);
        return self::ConverteData($Dados[0]).' '.substr($Dados[1], 0, -3);
    }
}