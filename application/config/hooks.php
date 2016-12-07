<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['pre_controller'][] = array(
    'class' 	=> 'HookPreController',
    'function' 	=> 'init',
    'filename' 	=> 'HookPreController.php',
    'filepath' 	=> 'hooks'
);
$hook['post_controller_constructor'][] = array(
    'class'    	=> 'HookPostControllerConstructor',
    'function' 	=> 'init',
    'filename' 	=> 'HookPostControllerConstructor.php',
    'filepath' 	=> 'hooks'
);
$hook['display_override'][] = array(
    'class'    	=> 'DisplayOverride',
    'function' 	=> 'init',
    'filename' 	=> 'DisplayOverride.php',
    'filepath' 	=> 'hooks'
);