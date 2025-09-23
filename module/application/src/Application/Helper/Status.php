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

class Status extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($ativo) {
        
        if($ativo == 'S') {
            return 'Ativo';
        }else{
            return 'Inativo';
        }
        
    }
}