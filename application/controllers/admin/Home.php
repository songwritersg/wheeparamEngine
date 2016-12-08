<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends WE_Controller {

    public function index()
    {
        $this->theme = $this->site->get_admin_layout();
        $this->view = "home/index";
    }
}
