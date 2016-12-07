<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*************************************************************
 * Class HookPreController
 ************************************************************/
class HookPreController {

    /*********************************************************
     * 컨트롤러가 호출되기 직전에 실행합니다.
     ********************************************************/
    function init()
    {
        $this->page_define();
    }

    /**********************************************************
     * 현재페이지를 정의합니다.
     **********************************************************/
    function page_define()
    {
        $uri =& load_class('URI', 'core');
        $seg = $uri->segment(1);

        define("PAGE_ADMIN", strtoupper($seg) === 'ADMIN');
        define("PAGE_INSTALL", strtoupper($seg) === 'INSTALL');
        define("PAGE_AJAX", strtoupper($seg) === 'API' && ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) == 'XMLHTTPREQUEST' ));
    }
}