<?php
/**
 * Paginate helpers.
 *
 * @package     helpers_paginate
 * @author      djalmaoliveira@gmail.com
 * @copyright   djalmaoliveira@gmail.com
 * @since       Version 2.0.15
 */

/**
 * Set or return options for paginating helper.
 *
 * <pre>
 *     $options:
 *         'url'            => 'Url base to generate pagination.'
 *         'rows_fetched'   => 'Number of rows fetched that will be used as base to generate pages.'
 *         'total'          => 'Total pages avaliable.'
 *         'rows'           => 'Default number of rows for each page is 15.'
 *         'param'          => 'Query param name for pagination.'
 *         'sort'           => 'Current sorting field.'
 *         'direct'         => 'Current direction, that can be <asc|desc>.'
 *         'page'           => 'Current page number.'
 *         'query'          => 'Current search query terms.'
 *         'url_query'      => 'Aditional url query params like: name=John&age=25'
 * </pre>
 * <code>
 *      Usages:
 *            // return array with all page options
 *            page_options_();
 *
 *            // set 10 as a number of rows per page
 *            page_options_('rows', 10);
 *
 *            // set many options in once call
 *            page_options_( array('rows' => 10, 'url' => 'http://localhost/list') );
 *
 *            // return current url base
 *            page_options_('url');
 *
 * </code>
 * @param  array|string $options Paginating options.
 * @param  string $value  Value of option.
 * @param  string $value  Set value option.
 * @return array | void
 */
function page_options_($options=null, $value=null) {

    static $_options;

    if ( !$_options ) {
        Liber::loadHelper('Url');
        $_options = array(
                        'url'           => '',
                        'rows_fetched'  => 0,
                        'rows'          => 15,
                        'total'         => 1,
                        'param'         => 'pagination',
                        'query'         => '',
                        'url_query'     => $_SERVER['QUERY_STRING']
                        );

        list($_options['url'])    = explode("?", url_current_(true));

        $current = explode(":",Http::get($_options['param']));
        $_options['page']   = empty($current[0])?1:$current[0];
        $_options['sort']   = isset($current[1])?$current[1]:'';
        $_options['direct'] = isset($current[2])?$current[2]:'desc';
        $_options['query']  = isset($current[3])?$current[3]:$_options['query'];

    }


    if ( func_num_args() == 0 ) {
        return $_options;
    } elseif ( func_num_args() == 1 ) {
        if ( is_array($options) ) {
            $_options = array_merge($_options, $options);
        } elseif( is_string($options) ) {
            return (isset($_options[$options]))?$_options[$options]:'';
        }
    } elseif ( func_num_args() == 2 ) {
        $_options[$options] = $value;
    }

}

/**
 * Print a pagination URL using $options specified.
 * @param  array  $options  Page options.
 * @param  boolean $return  true return URL
 * @return void|string
 */
function page_url_($options, $return=false ) {
    $op     = &$options;
    $query  = array();
    parse_str($options['url_query'], $query);
    $query[$op['param']] = "{$op['page']}:{$op['sort']}:{$op['direct']}:{$op['query']}:";
    $url = $op['url']."?".http_build_query($query);

    if ( $return ) {
        return $url;
    }

    echo $url;
}

/**
 * Print a pagination URL with specified page $num.
 * @param  integer  $num   Page number.
 * @param  boolean $return  true return URL
 * @return void|string
 */
function page_num_( $num, $return=false ) {
    $op         = page_options_();
    $op['page'] = $num;
    $url        = page_url_($op, true);
    if ( $return ) {
        return $url;
    }

    echo $url;
}

/**
 * Print next pagination URL.
 * @param  boolean $return  true return URL
 * @return void|string
 */
function page_next_( $return=false ) {
    $op         = page_options_();

    // next page ?
    if ( ($op['rows_fetched'] > $op['rows']) and ($op['rows_fetched'] % $op['rows']) > 0 ) {
        $op['page']++;
        if ( $op['page'] <= 0 ) { $op['page'] = 1; }
    }

    $url        = page_url_($op, true);
    if ( $return ) {
        return $url;
    }
    echo $url;
}

/**
 * Print previous pagination URL.
 * @param  boolean $return  true return URL
 * @return void|string
 */
function page_prev_( $return=false ) {
    $op         = page_options_();
    $op['page']--;
    if ( $op['page'] <= 0 ) { $op['page'] = 1; }
    $url        = page_url_($op, true);
    if ( $return ) {
        return $url;
    }
    echo $url;
}

/**
 * Print a pagination URL with specified $sort field.
 * @param  string  $sort   Sort field.
 * @param  boolean $return  true return URL
 * @return void|string
 */
function page_sort_($sort, $return=false ) {
    $op         = page_options_();
    $op['sort'] = &$sort;
    $op['direct'] = $op['direct']=='asc'?'desc':'asc';
    $url        = page_url_($op, true);
    if ( $return ) {
        return $url;
    }
    echo $url;
}


?>