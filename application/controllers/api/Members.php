<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
/**************************************************************
 * 회원관련 API
 *************************************************************/
class Members extends REST_Controller  {

    function __construct()
    {
        parent::__construct();
        
        if( !$this->input->is_ajax_request() ) $this->response(["result"=>FALSE,"message"=>"잘못된 요청입니다."], 400);
    }

    /**************************************************************
     * 로그인 처리
     *************************************************************/
    function login_post()
    {
        if( $this->member->is_login() ) $this->response(["result"=>FALSE,"message"=>"이미 로그인된 상태입니다."], 400);
        
        $login_id = trim($this->post('login_id', TRUE));
        $login_pass = trim($this->post('login_pass', TRUE));
        $login_keep	= trim($this->input->post('login_keep', TRUE) === 'Y') ? TRUE : FALSE;

        if( empty($login_id) ) $this->response(["result"=>TRUE,"message"=>"아이디를 입력하셔야 합니다."], 400);
        if( empty($login_pass) ) $this->response(["result"=>TRUE,"message"=>"비밀번호를 입력하셔야 합니다."], 400);
        if( ! $info = $this->member->get_member($login_id) ) $this->response(["result"=>TRUE,"message"=>"존재하지 않는 사용자이거나, 잘못된 비밀번호 입니다."], 400);
        if( $info['mem_password'] != get_password_hash($login_pass) OR $info['mem_status'] == 'N' ) $this->response(["result"=>TRUE,"message"=>"존재하지 않는 사용자이거나, 잘못된 비밀번호 입니다."], 400);
        if( $info['mem_status'] == 'D' ) $this->response(["result"=>TRUE,"message"=>"해당 사용자는 접근이 거부된 사용자입니다."], 400);
        if( $info['mem_status'] == 'H' ) $this->response(["result"=>TRUE,"message"=>"해당 사용자는 장기간 미접속으로 인하여 휴먼계정으로 전환된 아이디 입니다."], 400);

        $this->member->login_process($info, $login_keep);
        $this->response(["result"=>TRUE,"message"=>"로그인 성공"]);
        exit;
    }

    /**************************************************************
     * 사용자 정보 획득
     ***************************************************************/
    function info_get()
    {

    }

    function validate_get()
    {
        $userid = trim($this->get('userid', TRUE));
        if( empty($userid) ) $this->response(["result"=>TRUE,"message"=>"사용자 아이디가 올바르지 않습니다."], 400);

        $member = $this->member->get_member($userid);
        $this->response(["result"=>$member,"message"=>"성공"]);
    }

}