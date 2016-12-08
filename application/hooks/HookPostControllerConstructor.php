<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *
 * HookPostControllerConstructor.php
 *
 * 컨트롤러가 인스턴스화 된 직후 가동되는 후킹 클래스.
 *
 */
class HookPostControllerConstructor {

    protected $CI;

    /************************************************
     * 후킹 초기 실행 지점
     ***********************************************/
    function init() {
        // 인스턴스화 된 컨트롤러를 불러와 참조시킨다.
        $this->CI =& get_instance();

        if( PAGE_INSTALL ) return;

        $this->load_config();
        $this->setup_device_view();
        $this->autologin_check();
        $this->admin_check();
        $this->statics();
    }

    /************************************************
     * 환경설정 파일을 로드한다.
     ***********************************************/
    function load_config()
    {
        require_once APPPATH . "config/wheeparam.php";
        $this->CI->load->database();

        $this->CI->load->library('site');
        $this->CI->load->library('session');
        $this->CI->load->library('member');
        $this->CI->load->library('user_agent');
    }

    /************************************************
     * 현재 접속한 기기정보와, 보기 모드 설정들을 정의한다.
     ***********************************************/
    function setup_device_view()
    {
        // 모바일 접속여부에 따라 device 정보 확인
        $device = $viewmode = $this->CI->agent->is_mobile() ? DEVICE_MOBILE : DEVICE_DESKTOP;

        // 해당 모드로 보기 쿠키가 존재한다면 해당 보기 모드로
        if( get_cookie( COOKIE_VIEWMODE )  && ( get_cookie( COOKIE_VIEWMODE ) == DEVICE_DESKTOP OR get_cookie( COOKIE_VIEWMODE ) == DEVICE_MOBILE) )
        {
            $viewmode = get_cookie(COOKIE_VIEWMODE);
        }

        // 사이트 정보에 저장
        $this->CI->site->device = $device;
        $this->CI->site->viewmode = $viewmode;
    }

    /**************************************************
     * 자동로그인 체크
     ***********************************************/
    function autologin_check()
    {
        if( PAGE_INSTALL ) return;
        if( $this->CI->agent->is_robot() ) return;	// 로봇일경우도 건너뛴다.
        if( $this->CI->member->is_login() ) return;	// 로그인중이라면 건너뛴다.

        // 자동로그인 쿠키가 있다면
        if($aul_key = get_cookie(COOKIE_AUTOLOGIN))
        {
            // DB에 저장된 자동로그인 데이타가 있는지 확인한다.
            $result =
                $this->CI->db
                    ->where('aul_key', $aul_key)
                    ->where('aul_ip', ip2long( $this->CI->input->ip_address() ))
                    ->limit(1)
                    ->get('member_autologin');
            $autologin = $result->row_array();

            if( ! $autologin )
            {
                // DB에 데이타가 없다면 쿠키삭제
                delete_cookie(COOKIE_AUTOLOGIN);
                return FALSE;
            }
            else if( strtotime($autologin['aul_regtime']) + (60*60*24*30) < time() )
            {
                $this->CI->member->remove_autologin($autologin['mem_idx']);
                return FALSE;
            }
            else
            {
                $member_info = $this->CI->member->get_member( $autologin['mem_idx'], "mem_idx" );

                if(! $member_info)
                {
                    // 회원정보가 없다면 삭제
                    $this->CI->member->remove_autologin($autologin['mem_idx']);
                    return FALSE;
                }
                else if( $member_info['mem_status'] != 'Y' )
                {
                    // 회원상태가 '정상'이 아닌경우도 자동로그인 삭제
                    $this->CI->member->remove_autologin($autologin['mem_idx']);
                    return FALSE;
                }
                else
                {
                    $this->CI->member->login_process( $member_info, TRUE, TRUE );
                }
            }
        }
    }

    /**************************************************
     * 관리자 페이지인 경우,
     * 로그인 체크및 관리자 권한을 체크합니다.
     ***********************************************/
    function admin_check()
    {
        if( ! PAGE_ADMIN ) return;

        if( ! $this->CI->member->is_login() )
        {
            alert_login();
            exit;
        }
        else
        {
            if(! $this->CI->member->is_admin())
            {
                alert('해당 페이지에 접근할 권한이 없습니다.');
                exit;
            }
        }
    }

    /**************************************************
     * 통계데이타 기록
     ***********************************************/
    function statics()
    {
        if ( PAGE_INSTALL || PAGE_ADMIN ) return;
        if ( $this->CI->input->is_ajax_request() ) return;	// AJAX 요청인경우도 리턴
        if ( $this->CI->agent->is_robot() ) return;		// 검색봇의 경우도 리턴
        if ( get_cookie(COOKIE_STATICS) ) return;		// 방문자 쿠키가 있는경우 리턴

        $expire = strtotime(date('Y-m-d 23:59:59')) - time();
        set_cookie(COOKIE_STATICS, ip2long($this->CI->input->ip_address()), $expire );

        $sta_data['sta_regtime'] 	= date('Y-m-d H:i:s');
        $sta_data['sta_browser'] 	= $this->CI->agent->browser();
        $sta_data['sta_version'] 	= $this->CI->agent->version();
        $sta_data['sta_is_mobile']	= $this->CI->agent->is_mobile() ? 'Y':'N';
        $sta_data['sta_mobile'] 	= $this->CI->agent->mobile();
        $sta_data['sta_platform']	= $this->CI->agent->platform();
        $sta_data['sta_referrer']	= "";
        $sta_data['sta_referrer_host'] = "";
        $sta_data['sta_keyword']	= "";
        $sta_data['sta_ip']			= ip2long($this->CI->input->ip_address());

        if( $this->CI->agent->is_referral() )
        {
            $sta_data['sta_referrer'] = $this->agent->referrer();

            // 리퍼러에서 호스트와 패러미터 추출
            $referrer = parse_url($sta_data['sta_ua_referrer']);
            $sta_data['sta_referrer_host'] = (isset($referrer['host'])) ? $referrer['host'] : "";

            // 검색키워드 분석
            $keyword = '';
            if(isset($referrer['query']) && $referrer['query'])
            {
                $queries = explode('&', $referrer['query']);
                foreach ($queries as $query) {
                    if (preg_match('/^(query|q|p)=(.+)$/i', $query, $matches)) {
                        $keyword = urldecode($matches[2]);
                        break;
                    }
                }
            }
            $sta_data['sta_keyword'] = trim($keyword);
        }
        // 집계 DB에 저장
        $this->CI->db->insert("statics", $sta_data);

        // 통계 DB에도 저장
        $query = "	INSERT INTO {$this->CI->db->dbprefix}statics_date SET `std_date` = '".date('Y-m-d')."', `std_count` = 1 ";
        if( $this->CI->agent->is_mobile() ) $query .= ", `std_mobile` = 1 ";
        $query .= ' ON DUPLICATE KEY UPDATE `std_count` = `std_count` + 1';
        if( $this->CI->agent->is_mobile() ) $query .= ", `std_mobile` = `std_mobile` + 1";

        $this->CI->db->query($query);
    }
}