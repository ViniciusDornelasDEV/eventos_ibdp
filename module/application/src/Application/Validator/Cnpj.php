<?php

namespace Application\Validator;
use Zend\Validator\AbstractValidator;

class Cnpj extends AbstractValidator
{
    const FLOAT = 'float';

    protected $messageTemplates = array(
        self::FLOAT => "'%value%' não é um CNPJ válido"
    );

    public function isValid($value)
    {

        $this->setValue($value);
        
        $cnpj = str_pad(str_replace(array('.','-','/'),'',$value),14,'0',STR_PAD_LEFT);
        if(!is_numeric($cnpj)){
        	$this->error(self::FLOAT);
			return false;
        }
		if (strlen($cnpj) != 14){
			$this->error(self::FLOAT);
			return false;
		}else{
			for($t = 12; $t < 14; $t++){
				for($d = 0, $p = $t - 7, $c = 0; $c < $t; $c++){
					$d += $cnpj{$c} * $p;
			    	$p  = ($p < 3) ? 9 : --$p;
			  	}
			  	$d = ((10 * $d) % 11) % 10;
				if($cnpj{$c} != $d){
					$this->error(self::FLOAT);
				    return false;
				}
			}
			return true;
		}
    }
}

?>