<?php

/**
 * Tech Studio Limited
 * 
 * General application view isMobile helper
 * 
 * @author  Vinicius Silva <vinicius.s.dornelas@gmail.com>
 * @version 1.0
 */

namespace Evento\Helper;

use Zend\View\Helper\AbstractHelper;

class OrdenarInscricao extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($coluna, $colunaSolicitada, $ordem) {
        if($coluna == $colunaSolicitada){
        	return $ordem;
        }
        return 'ASC';
    }
}