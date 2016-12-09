<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member {

    protected $CI;
    protected $member_info;

    const POINT_TYPE_NONE = 'NONE';
    const POINT_TYPE_POST_WRITE = 'POST_WRITE';
    const POINT_TYPE_POST_LIKE = 'POST_LIKE';
    const POINT_TYPE_COMMENT_WRITE = 'CMT_WRITE';
    const POINT_TYPE_COMMENT_LIKE = 'CMT_LIKE';
    const POINT_TYPE_MEMBER_LOGIN = 'LOGIN';
    const POINT_TYPE_MEMBER_JOIN = 'JOIN';

    function __construct()
    {
        $this->CI =& get_instance();
    }

    /***********************************************************
     * 현재 로그인 여부를 확인하고,
     * 로그인중이라면 회원고유 PK를 얻어온다.
     ***********************************************************/
    function is_login()
    {
        if ($this->CI->session->userdata('ss_mem_idx')) {
            return $this->CI->session->userdata('ss_mem_idx');
        } else {
            return FALSE;
        }
    }

    /**********************************************************
     * 최고 관리자 권한을 갖고있는지 확인한다.
     *********************************************************/
    function is_admin()
    {
        // 로그인중이 아닐땐 FALSE;
        if( ! $this->is_login() ) return FALSE;
        if( $this->info('admin') == 'Y' ) return TRUE;
        else return FALSE;
    }

    /***********************************************************
     * 현재 로그인 중인 사용자의 정보를 얻어온다.
     ***********************************************************/
    function info($column="")
    {
        $prefix = "mem_";
        if(! $mem_idx = $this->is_login() ) return NULL;

        if( ! $this->member_info )
        {
            $this->member_info = $this->get_member($mem_idx, "mem_idx");
        }

        return $this->member_info[$prefix.$column];
    }

    function auth_name()
    {
        $auth_level = $this->auth();
        $auth_name = json_decode($this->CI->site->config('name_auth_level'), TRUE);
        return $auth_name[$auth_level];
    }

    /***********************************************************
     * 특정 ID의 사용자 정보를 획득한다.
     ***********************************************************/
    function get_member($mem_userid="", $mem_column="mem_userid")
    {
        if(empty($mem_userid)) return NULL;

        $result = $this->CI->db
            ->from("member")
            ->where($mem_column, $mem_userid)
            ->limit(1)
            ->get();

        $member = $result->row_array();
        return $member;
    }

    /***********************************************************
     * 현재사용자의 권한 레벨을 가져온다.
     ***********************************************************/
    function auth()
    {
        if( ! $this->is_login() ) return 0;
        return (int) $this->info('auth');
    }

    /***********************************************************
     * 로그인 처리를 진행한다.
     ***********************************************************/
    function login_process($member_info, $login_keep=FALSE, $login_keep_update=FALSE)
    {
        if( empty($member_info) ) return FALSE;

        // 로그인 세션 저장
        $this->CI->session->set_userdata('ss_mem_idx', $member_info['mem_idx']);

        // DB에 로그 기록 작성
        $log_data['mem_idx'] 		= $member_info['mem_idx'];
        $log_data['mlg_ip']			= ip2long( $this->CI->input->ip_address() );
        $log_data['mlg_regtime']	= date('Y-m-d H:i:s');
        $log_data['mlg_browser']	= $this->CI->agent->browser();
        $log_data['mlg_version']	= $this->CI->agent->version();
        $log_data['mlg_platform']	= $this->CI->agent->platform();
        $log_data['mlg_is_mobile']	= $this->CI->agent->is_mobile() ? 'Y' : 'N';
        $log_data['mlg_mobile']		= $this->CI->agent->mobile();
        $this->CI->db->insert('member_log', $log_data);

        // 최종로그인 시간 업데이트
        $this->CI->db
            ->where('mem_idx', $member_info['mem_idx'])
            ->set('mem_logtime', date('Y-m-d H:i:s'))
            ->set('mem_logip', ip2long( $this->CI->input->ip_address() ))
            ->set('mem_logcount', 'mem_logcount+1', FALSE)
            ->update("member");

        // 로그인시 포인트 부여
        if( $this->CI->site->config('point_use') == 'Y' && $this->CI->site->config('point_member_login') > 0 )
        {
            $this->add_point($member_info['mem_idx'], $this->CI->site->config('point_member_login'), TRUE, self::POINT_TYPE_MEMBER_LOGIN, date('Y-m-d') . ' 사용자 로그인', 0);
        }

        // 자동로그인 설정시 자동로그인 처리
        if( $login_keep )
        {
            // 자동로그인 날짜 갱신이라면?
            if($login_keep_update)
            {
                $this->CI->db
                    ->where('aul_key', get_password_hash( $member_info['mem_userid'] ))
                    ->where('aul_ip', ip2long( $this->CI->input->ip_address() ))
                    ->set('aul_regtime', date('Y-m-d H:i:s'))
                    ->update("member_autologin");
            }
            // 자동로그인 신규 추가라면?
            else
            {
                // 자동 로그인 DB 입력
                $aul_data['mem_idx']	= $member_info['mem_idx'];
                $aul_data['aul_key']	= get_password_hash( $member_info['mem_userid'] );
                $aul_data['aul_ip']		= ip2long( $this->CI->input->ip_address() );
                $aul_data['aul_regtime']= date('Y-m-d H:i:s');
                $this->CI->db->insert("member_autologin", $aul_data);
            }

            // 쿠키 생성 (한달만료)
            set_cookie(COOKIE_AUTOLOGIN, get_password_hash( $member_info['mem_userid'] ), 60*60*24*30);
        }
    }

    /***********************************************************
     * 현재 저장된 자동로그인을 삭제합니다.
     ***********************************************************/
    function remove_autologin($mem_idx="")
    {
        $this->CI->db->where('mem_idx', $mem_idx);
        $this->CI->db->where('aul_ip', ip2long( $this->CI->input->ip_address() ));
        $this->CI->db->delete('member_autologin');

        delete_cookie(COOKIE_AUTOLOGIN);
    }


    /**********************************************************
     * 포인트 추가 실제 처리
     *********************************************************/
    public function add_point($mem_idx, $point, $point_on_day=FALSE, $target_type="", $description="",$target_idx="")
    {
        $target_type = strtoupper($target_type);
        $target_array = array('NONE','POST_WRITE','POST_LIKE','CMT_WRITE','CMT_LIKE','LOGIN','JOIN');
        // 회원 IDX가 잘못된경우 리턴
        if( (int) $mem_idx <= 0 ) return FALSE;
        // 포인트가 0 일경우 리턴
        if( (int) $point == 0 ) return FALSE;
        // 포인트 종류가 다를경우 리턴
        if( !in_array($target_type, $target_array) ) return FALSE;

        // 하루에 한번 입력하는경우 오늘 입력된 데이타가 있는지 확인한다.
        if( $point_on_day && (int) $point > 0 )
        {
            $this->CI->db->select("COUNT(*) AS `cnt`");
            $this->CI->db->where("mem_idx", $mem_idx);
            $this->CI->db->where("target_type", $target_type);
            $this->CI->db->where("mpo_value >", "0");
            $this->CI->db->where("mpo_regtime >=", date('Y-m-d 00:00:00'));
            $this->CI->db->where("mpo_regtime <=", date('Y-m-d 23:59:59'));
            $temp = $this->CI->db->get("member_point");
            $count = (int) $temp->row(0)->cnt;
            if( $count > 0 )
            {
                return FALSE;
            }
        }

        // 입력할 데이타 정리
        $this->CI->db->set('mem_idx', $mem_idx);
        $this->CI->db->set('mpo_value', $point);
        $this->CI->db->set('mpo_description', $description);
        $this->CI->db->set('target_type', $target_type);
        $this->CI->db->set('target_idx', $target_idx);
        $this->CI->db->set('mpo_regtime', date('Y-m-d H:i:s') );
        $this->CI->db->insert('member_point');

        // 회원 DB에 반영
        $this->CI->db->set('mem_point', "mem_point + {$point}", FALSE);
        $this->CI->db->where('mem_idx', $mem_idx);
        $this->CI->db->update('member');

        return TRUE;
    }

    /***********************************************************
     * 회원 목록을 얻는다.
     * @param  array $param 설정정보
     ***********************************************************/
    function get_list($param=array())
    {
        $param['page'] 		= element('page', $param, 1);
        $param['page_rows'] = element('page_rows', $param, 20);
        $param['start']		= ($param['page'] - 1) * $param['page_rows'];
        $param['status']	= element('status', $param, "Y");
        $param['order_by']	= element('order_by', $param, 'mem_idx DESC');
        $param['st']		= element('st', $param, NULL);
        $param['sc']		= element('sc', $param, NULL);

        if( $param['st'] && $param['sc'] ) {
            $sc = NULL;
            if( $param['sc'] == 'nickname' ) {
                $sc = "mem_nickname";
            }
            else if ($param['sc'] == 'userid') {
                $sc = "mem_userid";
            }

            if( $sc )
            {
                $this->CI->db->group_start();
                $st = explode(" ", $param['st']);
                foreach($st as $word)
                {
                    $this->CI->db->or_like($sc, trim($word));
                }
                $this->CI->db->group_end();
            }
        }

        $this->CI->db->select("SQL_CALC_FOUND_ROWS *", FALSE);
        $this->CI->db->from("member");
        $this->CI->db->where("mem_status", $param['status']);
        $this->CI->db->order_by($param['order_by']);
        $this->CI->db->limit($param['page_rows'], $param['start']);
        $result = $this->CI->db->get();

        $return['list'] = $result->result_array();
        $result = $this->CI->db->query("SELECT FOUND_ROWS() AS `cnt`");
        $return['total_count'] = $result->row(0)->cnt;

        return $return;
    }
}