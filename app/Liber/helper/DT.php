<?php
/**
 *
 * @package core.helpers
 * @author      djalmaoliveira@gmail.com
 * @copyright   djalmaoliveira@gmail.com
 * @license
 * @link
 * @since       Version 1.0
 */

    /**
    *   Return a textual date/time between two dates $past and $future specified.
    *   $past and $future can be a text like '2010-01-10 15:20:25' or as a seconds like 1263144025.
    *   This function consider one month with 30 days.
    *   $detail_level show more detailed text from range 1 to 6.
    *   @param mixed $past
    *   @param mixed $future
    *   @param integer $detail_level
    *   @return String
    */
    function dt_diffsince_($past, $future, $detail_level=0) {
        static $i18n = null;
        $lang = strtolower(Liber::conf('LANG'));
        if ( !is_array($i18n[$lang]) ) {
            $i18n[$lang] = include Liber::conf('BASE_PATH').'i18n/helper/DT.'.$lang.'.php';
        }
        $periods = &$i18n[$lang]['PERIODS'];
        $future = !is_numeric($future)?strtotime($future):$future;
        $past   = !is_numeric($past)?strtotime($past):$past;

        $seconds = ($future)-($past);
        $range = Array();
        $aDate = Array();
        $range['year']  = 31536000; //  60*60*24*365
        $range['month'] =  2592000; //  day*30
        $range['week']  =   604800; //  day * 7
        $range['day']   =    86400; //  hour * 24
        $range['hour']  =     3600; //  60*60
        $range['minute']=       60;
        $range['second']=        1;

        foreach($range as $id => $count) {
            if ( $detail_level >= 0 and $seconds >= $count ) {
                $v   = floor($seconds/$count);
                $aDate[$id] =  $v.' '.($v>1?$periods[$id][1]:$periods[$id][0]);
                $seconds = $seconds % $count;
                $detail_level--;
            }
        }

        if ( count($aDate) > 1 ) {
            $last = end($aDate);
            $aDate[key($aDate)] = $i18n[$lang]['AND'].' '.$last;
        }

        return implode(' ',$aDate);
    }

    /**
    *   Return a textual date/time passed from $past specified to now.
    *   See dt_diffsince_().
    *   @param mixed $past
    *   @param integer $detail_level
    *   @return String
    */
    function dt_timesince_($past, $detail_level=0) {
        return dt_diffsince_($past, time(), $detail_level);
    }
?>