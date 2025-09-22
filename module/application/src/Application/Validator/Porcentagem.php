<?php
namespace Application\Validator;
use Zend\Validator\AbstractValidator;

class Porcentagem extends AbstractValidator
{
    const FLOAT = 'float';

    protected $messageTemplates = array(
        self::FLOAT => "'%value%' não é uma procentagem válida"
    );

    public function isValid($value)
    {
       $this->setValue($value);
        //retirar %
        $value = str_replace('%', '', $value);

        //verificar se é maior ou igual a 100
        if($value > 100){
            $this->error(self::FLOAT);
        	return false;
        }

        if($value < 1){
            $this->error(self::FLOAT);
        	return false;
        }

        return true;

    }
}

?>