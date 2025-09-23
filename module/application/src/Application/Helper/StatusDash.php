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

class StatusDash extends AbstractHelper
{
    public function __invoke($statusInscricao, $statusPagamento) {
        
        if($statusInscricao == 1){
        	return 'Incompleta';
        }

        if($statusPagamento == 2 || $statusPagamento == 8 || $statusPagamento == 9){
        	return 'Paga';
        }

        return 'A pagar';
        
    }
}