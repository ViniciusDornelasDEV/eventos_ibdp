<?php

/**
 * Tech Studio Limited
 * 
 * General application view isMobile helper
 * 
 * @author  Vinicius Silva <vinicius.s.dornelas@gmail.com>
 * @version 1.0
 */

namespace Associados\Helper;

use Zend\View\Helper\AbstractHelper;

class Adimplente extends AbstractHelper
{
    protected $count = 0;

    public function __invoke($adimplente) {
        
        if($adimplente == 'S') {
            return 'Adimplente';
        }else{
            return 'Inadimplente';
        }
        
    }
}