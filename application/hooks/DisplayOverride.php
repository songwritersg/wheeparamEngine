<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 * HookPostControllerConstructor.php
 *
 * 컨트롤러가 인스턴스화 된 직후 가동되는 후킹 클래스.
 *
 */

class DisplayOverride {

    function init()
    {
        $this->CI =& get_instance();
        $output = $this->set_layout( $this->CI->output->get_output() );
        $this->CI->output->_display($output);
    }

    function set_layout($output)
    {
        if( PAGE_AJAX OR PAGE_INSTALL ) return $output;

        // Script Tag를 모두 가져와서 body 밑으로
        preg_match_all("/<script\\b[^>]*>([\\s\\S]*?)<\\/script>/", $output, $matches);
        $output = preg_replace("/<script\\b[^>]*>([\\s\\S]*?)<\\/script>/","", $output);

        $head = '<!DOCTYPE html><html lang="ko"><head>';
        $head .= $this->CI->site->display_meta();
        $head .= '<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/earlyaccess/nanumgothic.css" />';
        $head .= '<link rel="stylesheet" type="text/css" href="'.base_url("static/css/common.min.css").'">';
        $head .= $this->CI->site->display_css();

        // IE8 미만에서 html5shiv, respond 로드
        $head .= '<!--[if lt IE 9]>';
        $head .= '<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>';
        $head .= '<script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>';
        $head .= '<![endif]-->';
        $head .= '</head><body>';

        $foot = "";
        $foot .= '<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>';
        $foot .= '<script type="text/javascript" src="'.base_url("static/js/common.min.js").'"></script>';
        $foot .= $this->CI->site->display_js();
        foreach($matches[0] as $match) $foot .= $match;
        $foot .= '</body></html>';

        $output = $head.$output.$foot;

        return $output;
    }

}
