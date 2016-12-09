<?php
defined('BASEPATH') OR exit();
/**
 * A base controller for CodeIgniter with view autoloading, layout support,
 * model loading, helper loading, asides/partials and per-controller 404
 *
 * @link http://github.com/jamierumbelow/codeigniter-base-controller
 * @copyright Copyright (c) 2012, Jamie Rumbelow <http://jamierumbelow.net>
 */

class WE_Controller extends CI_Controller
{
    protected $view = FALSE;
    protected $data = array();
    protected $asides = array();
    public $theme = FALSE;
    public $active 	= NULL;
    public $skin	= NULL;
    public $skin_type = NULL;

    public function __construct()
    {
        parent::__construct();
    }

    public function _remap($method)
    {
        if (method_exists($this, $method)) call_user_func_array(array($this, $method), array_slice($this->uri->rsegments, 2));
        else
        {
            if (method_exists($this, '_404')) call_user_func_array(array($this, '_404'), array($method));
            else show_404(strtolower(get_class($this)).'/'.$method);
        }

        $this->_load_view();
    }

    protected function _load_view()
    {
        if( empty($this->view) ) return;

        $this->data['skin_url'] =  ( $this->skin && $this->skin_type ) ? base_url("views/".DIR_SKIN . '/' . $this->skin_type . '/' . $this->skin . '/') : NULL;
        $this->data['theme_url'] = (isset($this->theme) && $this->theme !== FALSE) ? base_url("views/". DIR_THEME . "/" . $this->theme . "/") : NULL;

        $view = ( $this->skin && $this->skin_type ) ? DIR_SKIN . '/' . $this->skin_type . '/' . $this->skin . '/' . $this->view : DIR_THEME . '/' . $this->theme . '/' . $this->view ;
        $data['contents'] = $this->load->view($view, $this->data, TRUE);
        if( $this->skin && $this->skin_type )
        {
            $data['contents'] = "<div id=\"skin-{$this->skin_type}-{$this->skin}\">" . $data['contents'] . '</div>';
        }

        if (!empty($this->asides))
        {
            foreach ($this->asides as $name => $file)
            {
                $data['asides_'.$name] = $this->load->view($file, $this->data, TRUE);
            }
        }

        $data = array_merge($this->data, $data);
        $theme = (isset($this->theme) && $this->theme !== FALSE) ? $this->theme : NULL;

        $output_data = ($theme == FALSE) ? $data['contents'] : $this->load->view( DIR_THEME . '/' . $theme. '/' . "theme" , $data, TRUE);
        $output_data = preg_replace_callback('!\[widget([^\]]*)\]!is', array($this, '_widget_replace'), $output_data);
        $this->output->set_output($output_data);
    }

    protected function _widget_replace( $matches )
    {
        $vars = trim($matches[1]);
        $vars = preg_replace('/\r\n|\r|\n|\t/',' ',$vars);
        $vars = str_replace( array('"','  '), array('',' '), $vars );
        $vars = trim(str_replace( " ", '&', $vars ));

        parse_str($vars, $vars_array);

        $vars_array = array_merge( $vars_array, $this->data );

        // Name이 정의되지 않았다면 리턴
        if( ! isset($vars_array['name']) ) return $this->load->view('tools/widget_error', array("message"=>"위젯 속성중 [name] 값이 정의되지 않았습니다."), TRUE);
        if( ! file_exists( VIEWPATH . DIR_WIDGET . '/' . $vars_array['name']."/widget.php") ) return $this->load->view('tools/widget_error', array("message"=>"{$vars_array['name']} 위젯파일이 존재하지 않습니다."), TRUE);

        // CSS와 JS파일이 있다면 로드
        if( file_exists( VIEWPATH . DIR_WIDGET . '/' . $vars_array['name']."/widget.css") ) $this->site->add_css( '/views/' . DIR_WIDGET . '/' . $vars_array['name'] . "/widget.css");
        if( file_exists( VIEWPATH . DIR_WIDGET . '/' . $vars_array['name']."/widget.js") ) $this->site->add_js( '/views/' . DIR_WIDGET . '/' . $vars_array['name'] . "/widget.js");

        return "<div id=\"widget-{$vars_array['name']}\">". $this->load->view( DIR_WIDGET . '/' . $vars_array['name'] . '/widget', $vars_array, TRUE ) . "</div>";
    }
}