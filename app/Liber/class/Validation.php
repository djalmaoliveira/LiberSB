<?php

/**
*   @package core.class
*/


/**
*   Class that manipulates several kind of values validation.
*
*/

class Validation {

    const NOTNULL = 1;

    const NUMERIC = 2;

    const EMAIL   = 4;

    const DATE    = 8;

	const URL	  = 16;

    /**
    *   Validates a $value by specified $type.
    *   Return Array of errors matched.
    *   @param mixed $value
    *   @param integer $type - See Validation Constants
    *   @return Array.
    */
    static function validate($value, $type) {
        static $data;
        if ( !is_array($data) ) {
            $lang = (Liber::conf('LANG')=='')?'en':strtolower( Liber::conf('LANG') );
            $data = include Liber::conf('BASE_PATH').'i18n/class/Validation.'.$lang.'.php';
        }
        $aOut = Array();
        $max  = $type;

		if ( $max >= self::URL ) {
		    $max -= self::URL;
		    if ( !empty($value) and !filter_var(trim($value), FILTER_VALIDATE_URL) ) {
                $aOut['URL'] = $data['URL'];
			}
		}


		if ( $max >= self::DATE ) {
		    $max -= self::DATE;
		    if ( !empty($value) and strtotime($value) === false ) {
                $aOut['DATE'] = $data['DATE'];
			}
		}


		if ( $max >= self::EMAIL ) {
		    $max -= self::EMAIL;
		    if ( !filter_var(trim($value), FILTER_VALIDATE_EMAIL) ) {
                $aOut['EMAIL'] = $data['EMAIL'];
			}
		}

		if ( $max >= self::NUMERIC ) {
		    $max -= self::NUMERIC;
		    if ( !is_numeric($value) ) {
                $aOut['NUMERIC'] = $data['NUMERIC'];
			}
		}

		if ( $max >= self::NOTNULL ) {
		    $max -= self::NOTNULL;
		    if ( empty($value) ) {
                $aOut['NOTNULL'] = $data['NOTNULL'];
			}
		}

        return $aOut;
    }

}

?>
