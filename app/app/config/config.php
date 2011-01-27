<?php

$project    = realpath((dirname(__FILE__)).'/../../').'/';
$aConfigs   = Array(
                'configs'=>Array(
                    //
                    // Configurations
                    //
                    'APP_PATH'      => $project.'app/',
                    'BASE_PATH'     => $project.'Liber/',
                    'APP_MODE'      => 'DEV',
                    'CONTENT_PATH'  => 'content/',
                    'EMAIL'         => 'contact@localhost.localdomain'

                ),

                'routes'=>Array(),

                'dbconfig'=>Array(
                    'DEV'  => Array('localhost','liber_blog','root','root', 'mysql'),
                    'PROD' => Array('localhost','liber_blog','root','root', 'mysql')
                )


            );

$route      = &$aConfigs['routes'];

/*
    $route[URI][METHOD] = Array()
*/

$route['/']['*']                 = Array('MainController', '*');
$route['/notfound']['*']         = Array('NotFoundController', 'index');
$route['/admin']['*']            = Array('AdminController', '*');
$route['/admin/content']['*']    = Array('ContentController', '*');

?>