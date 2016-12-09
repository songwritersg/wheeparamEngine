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
        $login_keep	= trim($this->post('login_keep', TRUE) === 'Y') ? TRUE : FALSE;
        $reurl = $this->post('reurl', TRUE) ? $this->post('reurl', TRUE) : base_url();

        if( empty($login_id) ) $this->response(["result"=>TRUE,"message"=>"아이디를 입력하셔야 합니다."], 400);
        if( empty($login_pass) ) $this->response(["result"=>TRUE,"message"=>"비밀번호를 입력하셔야 합니다."], 400);
        if( ! $info = $this->member->get_member($login_id) ) $this->response(["result"=>TRUE,"message"=>"존재하지 않는 사용자이거나, 잘못된 비밀번호 입니다."], 400);
        if( $info['mem_password'] != get_password_hash($login_pass) OR $info['mem_status'] == 'N' ) $this->response(["result"=>TRUE,"message"=>"존재하지 않는 사용자이거나, 잘못된 비밀번호 입니다."], 400);
        if( $info['mem_status'] == 'D' ) $this->response(["result"=>TRUE,"message"=>"해당 사용자는 접근이 거부된 사용자입니다."], 400);
        if( $info['mem_status'] == 'H' ) $this->response(["result"=>TRUE,"message"=>"해당 사용자는 장기간 미접속으로 인하여 휴먼계정으로 전환된 아이디 입니다."], 400);

        $this->member->login_process($info, $login_keep);
        $this->response(["result"=>TRUE,"message"=>"로그인 성공","reurl"=>$reurl]);
        exit;
    }

    /**************************************************************
     * 사용자 정보 획득
     ***************************************************************/
    function info_get()
    {

    }
    
    
    /**************************************************************
     * 사용자 정보 추가
     ***************************************************************/
    function info_put()
    {
        $agree = trim($this->put('agree', TRUE));
        $mem_userid     = trim($this->put('userid', TRUE));
        $mem_password   = trim($this->put('userpass', TRUE));
        $mem_password_confirm = trim($this->put('userpass_confirm', TRUE));
        $mem_nickname   = trim($this->put('usernick', TRUE));
        $mem_username   = trim($this->put('username', TRUE));
        $mem_use_message = trim($this->put('use_message', TRUE));
        $mem_use_profile = trim($this->put('use_profile', TRUE));
        $mem_profile = trim($this->put('profile', FALSE));

        // 약관동의
        if( $agree !== 'Y' ) $this->response(["result"=>FALSE,"message"=>"이용약관에 동의를 하셔야 합니다."], 400);

        // 아이디 체크
        $regex_email = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
        if( empty($mem_userid) ) $this->response(["result"=>FALSE,"message"=>"사용하실 ID를 입력하셔야 합니다."], 400);
        if( ! preg_match($regex_email, $mem_userid) ) $this->response(["result"=>FALSE,"message"=>"올바른 형식의 이메일주소가 아닙니다."], 400);
        $deny_id = explode(",", $this->site->config('deny_id'));
        $id_tmp = explode("@", $mem_userid);
        $id = $id_tmp[0];
        if( in_array($id, $deny_id) ) $this->response(["result"=>FALSE,"message"=>"아이디에 사용불가능한 단어가 포함되어 있습니다 : ". $id], 400);
        if( $this->member->get_member($mem_userid) ) $this->response(["result"=>FALSE,"message"=>"이미 존재하는 아이디 입니다."], 400);
        
        // 비밀번호 체크
        if( empty($mem_password) ) $this->response(["result"=>FALSE,"message"=>"비밀번호를 입력하셔야 합니다."], 400);
        if( strlen($mem_password) < 6 ) $this->response(["result"=>FALSE,"message"=>"비밀번호는 최소 6자리이상을 설정하셔야 합니다."], 400);
        if( strlen($mem_password) > 20 ) $this->response(["result"=>FALSE,"message"=>"비밀번호는 최대 20자리까지 가능합니다."], 400);
        
        // 비밀번호 확인 체크
        if( $mem_password != $mem_password_confirm) $this->response(["result"=>FALSE,"message"=>"비밀번호와 확인이 서로 다릅니다."], 400);
        
        // 닉네임 체크
        if( empty($mem_nickname)) $this->response(["result"=>FALSE,"message"=>"사용자 닉네임을 입력하셔야 합니다."], 400);
        if( strlen($mem_nickname) > 20) $this->response(["result"=>FALSE,"message"=>"사용자 닉네임은 최대 20자까지 설정 가능합니다."], 400);
        if( strlen($mem_nickname) < 2) $this->response(["result"=>FALSE,"message"=>"사용자 닉네임은 최소 2자 이상 설정 가능합니다."], 400);

        $deny_nickname = explode(",", $this->site->config('deny_nickname'));
        if( in_array($mem_nickname, $deny_nickname)) $this->response(["result"=>FALSE,"message"=>"닉네임에 사용불가능한 단어가 포함되어 있습니다 : ". $mem_nickname], 400);

        if( $this->member->get_member($mem_nickname, 'mem_nickname') ) $this->response(["result"=>FALSE,"message"=>"이미 존재하는 닉네임 입니다."], 400);

        // 입력시킬 데이타를 정리한다.
        $data['mem_status'] = 'Y';
        $data['mem_userid'] = $mem_userid;
        $data['mem_password'] = get_password_hash($mem_password);
        $data['mem_nickname'] = $mem_nickname;
        $data['mem_username'] = $mem_username ? $mem_username : $mem_nickname;
        $data['mem_auth'] = 1;
        $data['mem_level'] = 1;
        $data['mem_point'] = 0;
        $data['mem_use_message'] = in_array($mem_use_message, array('A','N','F')) ? $mem_use_message : 'N';
        $data['mem_use_profile'] = in_array($mem_use_profile, array('A','N','F')) ? $mem_use_profile : 'N';
        $data['mem_profile'] = $mem_profile ? $mem_profile : '';
        $data['mem_regtime'] = date('Y-m-d H:i:s');
        $data['mem_regip'] = ip2long($this->input->ip_address());
        $data['mem_admin'] = 'N';
        $data['mem_following'] = 0;
        $data['mem_followed'] = 0;
        $data['mem_icon'] = '';
        $data['mem_photo'] = '';

        if(! $this->db->insert("member", $data) ) {
            $this->response(["result"=>FALSE,"message"=>"서버 오류가 발생하였습니다."], 500);
        }
        $data['mem_idx'] = $this->db->insert_id();

        $this->member->login_process($data);
        $this->response(["result"=>TRUE,"message"=>"회원가입 성공"], 201);
    }
}