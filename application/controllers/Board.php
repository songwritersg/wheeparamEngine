<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Board extends WE_Controller {

    function __construct()
    {
        parent::__construct();

        $this->load->library('boardlib');
    }

    function lists($brd_key)
    {
        if(! $this->data['board'] = $this->boardlib->get_board($brd_key) )
        {
            alert('존재하지 않는 게시판입니다.');
            exit;
        }

        $this->site->meta_title = "사용자 로그인";
        $this->theme = $this->site->get_layout();
        $this->skin_type = SKIN_TYPE_BOARD;
        $this->skin = $this->site->config( ($this->site->viewmode == DEVICE_MOBILE) ? 'skin_members_mobile' :'skin_members');
        $this->view = "list";
    }

}
