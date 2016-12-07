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
        $this->output->set_output($output_data);
    }
}