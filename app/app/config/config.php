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
                    'FUNKY_PATH'    => 'c/',
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
$route['/comment']['*']          = Array('CommentController', 'comment');

$route['/admin']['*']            = Array('AdminController', '*');
$route['/admin/content']['*']    = Array('ContentController', '*');

?>