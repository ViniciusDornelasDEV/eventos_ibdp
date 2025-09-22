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

class StatusAssociado extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($status) {
        
        if($status == 'A') {
            return 'Adimplente';
        }else{
            return 'Todos';
        }
        
    }
}