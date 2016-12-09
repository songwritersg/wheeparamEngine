<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*************************************************************
 * Class Members
 * -----------------------------------------------------------
 * 회원 관련 컨트롤러
 *************************************************************/
class Members extends WE_Controller {

    public function index()
    {
        $redirect = ( $this->member->is_login() ) ? 'info' : 'login';
        redirect( base_url('members/'.$redirect) );
        exit;
    }

    /**********************************************************
     * 회원가입
     **********************************************************/
    public function join()
    {
        if( $this->member->is_login() ) {
            alert('이미 로그인 상태입니다.', base_url("members/info"));
            exit;
        }

        $form_attributes['id'] = "form-join";
        $form_attributes['autocomplete'] = "off";
        $form_attributes['name'] = "form_join";
        $form_attributes['data-role'] = "form-join";
        $form_attributes['class'] = "form-horizontal";

        $this->data['form_open'] = form_open(NULL, $form_attributes);
        $this->data['form_close'] = form_close();

        $this->data['agreement']['site'] = nl2br($this->site->config('agreement_site'));
        $this->data['agreement']['privacy'] = nl2br($this->site->config('agreement_privacy'));

        $this->data['use_message'] = $this->site->config('message_use') == 'Y';
        $this->data['use_icon'] = $this->site->config('member_icon_use') == 'Y' && $this->site->config('member_icon_width') > 0 &&  $this->site->config('member_icon_height') > 0;
        $this->data['member_icon_width'] = $this->data['use_icon'] ? $this->site->config('member_icon_width') : 0;
        $this->data['member_icon_height'] = $this->data['use_icon'] ? $this->site->config('member_icon_height') : 0;

        $this->data['use_photo'] = $this->site->config('member_photo_use') == 'Y' && $this->site->config('member_photo_width') > 0 &&  $this->site->config('member_photo_height') > 0;
        $this->data['member_photo_width'] = $this->data['use_photo'] ? $this->site->config('member_photo_width') : 0;
        $this->data['member_photo_height'] = $this->data['use_photo'] ? $this->site->config('member_photo_height') : 0;

        $this->data['use_profile'] = $this->site->config('member_profile_use') == 'Y';

        $this->site->meta_title = "사용자 회원가입";
        $this->theme = $this->site->get_layout();
        $this->skin_type = SKIN_TYPE_MEMBER;
        $this->skin = $this->site->config( ($this->site->viewmode == DEVICE_MOBILE) ? 'skin_members_mobile' :'skin_members');
        $this->view = "join";
    }

    /**********************************************************
     * 회원가입완료 페이지
     **********************************************************/
    public function welcome()
    {
        if(! $this->member->is_login() )
        {
            alert('잘못된 경로로 접근하셧습니다.');
            exit;
        }

        $this->site->meta_title = "회원가입 완료";
        $this->theme = $this->site->get_layout();
        $this->skin_type = SKIN_TYPE_MEMBER;
        $this->skin = $this->site->config( ($this->site->viewmode == DEVICE_MOBILE) ? 'skin_members_mobile' :'skin_members');
        $this->view = "welcome";
    }

    /**********************************************************
     * 사용자 로그인
     **********************************************************/
    public function login()
    {
        if( $this->member->is_login() ) {
            alert('이미 로그인 상태입니다.', base_url("members/info"));
            exit;
        }

        $form_attributes['id'] = "form-login";
        $form_attributes['autocomplete'] = "off";
        $form_attributes['name'] = "form_login";
        $form_attributes['data-role'] = "form-login";
        $form_hidden_inputs['reurl'] = set_value('reurl', $this->input->get("reurl", TRUE, base_url()));

        $this->data['form_open'] = form_open(NULL, $form_attributes, $form_hidden_inputs);
        $this->data['form_close'] = form_close();

        $this->site->meta_title = "사용자 로그인";
        $this->theme = $this->site->get_layout();
        $this->skin_type = SKIN_TYPE_MEMBER;
        $this->skin = $this->site->config( ($this->site->viewmode == DEVICE_MOBILE) ? 'skin_members_mobile' :'skin_members');
        $this->view = "login";
    }


    /**********************************************************
     * 사용자 로그아웃
     **********************************************************/
    public function logout()
    {
        $reurl = $this->input->get("reurl", TRUE, base_url());

        if( get_cookie(COOKIE_AUTOLOGIN) )
        {
            $this->member->remove_autologin($this->member->is_login());
        }
        $this->session->sess_destroy();
        redirect( $reurl );
        exit;
    }
}
