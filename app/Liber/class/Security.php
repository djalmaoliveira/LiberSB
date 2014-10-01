<?php
/**
*   Class with some useful method used with security implementations.
*   .
*   @package classes
*/
class Security {

	protected $oSession;

	function __construct() {
        Liber::loadClass('Session');
		$this->oSession = new Session('security');
	}

    /**
    *   Generate and/or return a token in a current session.
    *   @param boolean $renew
    *   @return String
    */
    function token($renew=false) {
        if ( $renew or !($this->oSession->val('token')) ) {
            $this->oSession->val('token', uniqid('liber'));
        }
        return $this->oSession->val('token');
    }

    /**
    *   Verify if $token specified is the same in current session.
    *   @param String $token
    *   @return boolean
    */
    function validToken($token) {
        return ($token==$this->oSession->val('token'));
    }

	/**
	*	Start watching monitor by client.
	*	@param Array $options - Options that should be verified.
	*/
	function clientWatch( $options=Array('ip', 'agent') ) {
		$monitors = Array();
		if ( isset($options['ip']) ) {
			$monitors['ip'] = $_SERVER['REMOTE_ADDR'];
		}
		if ( isset($options['agent']) ) {
			$monitors['agent'] = md5($_SERVER['HTTP_USER_AGENT']);
		}

		$this->oSession->val('monitor', $monitors);
	}

	/**
	*	Return Array of changes detected since clientWatch() method call.
	*	@return Array
	*/
	function clientChanged() {
		$changes = Array();
		foreach( $this->oSession->val('monitor') as $type => $value ) {
			switch ($type) {

				case 'ip':
					if ( $_SERVER['REMOTE_ADDR'] != $value ) {
						$changes[] = 'ip';
					}
				break;

				case 'agent':
					if ( md5($_SERVER['HTTP_USER_AGENT']) != $value ) {
						$changes[] = 'agent';
					}
				break;
			}
		}
		return $changes;
	}

}


?>