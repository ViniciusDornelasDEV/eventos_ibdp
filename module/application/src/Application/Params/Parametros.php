<?php 
 
 namespace Application\Params;

 class Parametros
 {
 	public function getEmailName(){
 		//return 'Eventos Time';
 		//return 'Eventos SBDOF';
 		return 'Eventos IBDP';
 	}

 	public function getEmail(){
 		//return 'support@eventostime.com.br';
 		//return 'contato@eventossbdof.com.br';
 		return 'eventos@direitoprocessual.org.br';
 	}

 	public function getBaseUrl(){
 		//return 'http://www.eventostime.com.br';
 		//return 'http://eventossbdof.com.br';
 		return 'http://eventosibdp.com.br';
 	}

  public function getMensagemInscricaoAssociado(){
    return 'Favor regularizar sua situação cadastral com a nossa secretaria. Entre em contato pelo e-mail: sbdof@sbdof.com.br ou 11 93735-5599 (WhatsApp)';
  }

  public static function getCredenciaisReCaptcha(){
    //ibdp
    return array(
      'public'  =>  '6LdbcKcaAAAAAFj95HwYg1xhcpmFUZPTJee2Ovd0',
      'private' =>  '6LdbcKcaAAAAAGQGJ3j9ru6Bj7NmnhTqPcQ8NNUH',
    );

    //sbdof
    /*return array(
      'public'  =>  '6LfJ-64aAAAAAEJvAYArifmOIEC2BKJB2qR8I3mP',
      'private' =>  '6LfJ-64aAAAAAHf9tDc4C4fQSJY8hSBjQKzlRWWA',
    );*/

    //eventostime
    /*return array(
      'public'  =>  '6LfO-64aAAAAAOvgxhVJAQFw-s7xvEnx4gxaP1Qx',
      'private' =>  '6LfO-64aAAAAALjgklWh4jRZUIUpr8uBWyJ7D6I_',
    );*/
  }

  public function getEmailHost()
  {
    //SBDOF
    //return 'smtp.eventossbdof.com.br';
    
    //IBDP
    return 'smtp.eventosibdp.com.br';
  }

  public function getEmailUser()
  {
    //SBDOF
    //return 'contato@eventossbdof.com.br';
    
    //IBDP
    return 'eventos@direitoprocessual.org.br';
  }

  public function getEmailPassword()
  {
    //SBDOF
    //return 'KMUUSp5itwKHJPK@';

    //IBDP
    return 'KMUUSp5itwKHJPK@';
  }

 }
?>