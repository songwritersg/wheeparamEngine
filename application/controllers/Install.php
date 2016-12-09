<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Install extends CI_Controller {

    /**********************************************************
     * 인스톨 페이지
     **********************************************************/
    public function index()
    {
        if( file_exists( APPPATH . "config/wheeparam.php" ))
        {
            alert('이미 설치가 되어있습니다.');
            exit;
        }

        $this->load->library('form_validation');

        $this->form_validation->set_rules("site_title", "사이트 이름", "trim|required");
        
        $this->form_validation->set_rules("db_host", "데이타베이스 호스트", "trim|required");
        $this->form_validation->set_rules("db_user", "데이타베이스 사용자", "trim|required");
        $this->form_validation->set_rules("db_pass", "데이타베이스 비밀번호", "trim|required");
        $this->form_validation->set_rules("db_name", "데이타베이스 이름", "trim|required");

        $this->form_validation->set_rules("dbprefix", "데이타베이스 테이블 접두어", "trim|required");

        $this->form_validation->set_rules("admin_id", "관리자 아이디", "required|trim|min_length[6]|max_length[100]|valid_email");
        $this->form_validation->set_rules("admin_pass", "관리자 비밀번호", "required|trim|min_length[6]|max_length[20]");
        $this->form_validation->set_rules("admin_pass_confirm", "관리자 비밀번호 확인", "required|trim|min_length[6]|max_length[20]|matches[admin_pass]");
        $this->form_validation->set_rules("admin_nick", "관리자 닉네임", "required|trim|min_length[2]|max_length[20]");

        if( $this->form_validation->run() !== FALSE )
        {
            $this->db_check();
            $this->load->database();
            $this->db_create();
            $this->insert_default_data();
            $this->make_config_file();

            alert("설치가 완료되었습니다.", base_url());
            exit;
        }
        else
        {
            $data['php_version'] = is_php('5.3.0');
            $data['gd_support']  = function_exists('imagecreatefromgif');
            $data['curl_support'] = function_exists('curl_init');
            $data['mbstring_support'] = function_exists('mb_convert_encoding');
            $data['json_support'] = function_exists('json_encode');

            $data['base_url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
            $data['base_url'] .= "://" . $_SERVER['HTTP_HOST'];
            $data['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

            $data['library_check'] = $data['php_version'] && $data['gd_support'] && $data['json_support'] && $data['mbstring_support'] && $data['curl_support'];

            $this->load->view('tools/install', $data);
        }
    }

    /***********************************************************
     * DB가 접속가능한지 체크합니다.
     **********************************************************/
    protected function db_check()
    {
        $config['hostname'] = $this->input->post("db_host", TRUE);
        $config['username'] = $this->input->post("db_user", TRUE);
        $config['password'] = $this->input->post("db_pass", TRUE);
        $config['database'] = $this->input->post("db_name", TRUE);
        $config['dbdriver'] = 'mysqli';
        $config['dbprefix'] = $this->input->post("dbprefix", TRUE);
        $config['pconnect'] = FALSE;
        $config['db_debug'] = FALSE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = '';
        $config['char_set'] = 'utf8';
        $config['dbcollat'] = 'utf8_general_ci';

        // 입력된 정보를 이용하여 DB 연결을 시도합니다.
        $DB_INSTALL = @$this->load->database($config, TRUE);

        if( ! $DB_INSTALL->conn_id) {
            alert("DB 설정값이 잘못되었거나 DB에 접속할수 없습니다.\\n입력정보를 다시 확인해주세요");
            exit;
        }

        if( ! $this->make_database_file() ) {
            alert("DATABASE 파일 생성도중 오류가 발생하였습니다.");
            exit;
        }

        $DB_INSTALL->close();
    }

    /***********************************************************
     * 데이타베이스 파일을 생성합니다.
     **********************************************************/
    protected function make_database_file()
    {
        $this->load->helper("file");

        $file_string = "<?php defined('BASEPATH') OR exit('No direct script access allowed');".PHP_EOL;
        $file_string .= '$active_group = "default";'.PHP_EOL;
        $file_string .= '$query_builder = TRUE;'.PHP_EOL;
        $file_string .= '$db["default"] = array('.PHP_EOL;
        $file_string .= "    'dsn'	=> '',".PHP_EOL;
        $file_string .= "    'hostname' => '".$this->input->post("db_host", TRUE)."',".PHP_EOL;
        $file_string .= "    'username' => '".$this->input->post("db_user", TRUE)."',".PHP_EOL;
        $file_string .= "    'password' => '".$this->input->post("db_pass", TRUE)."',".PHP_EOL;
        $file_string .= "    'database' => '".$this->input->post("db_name", TRUE)."',".PHP_EOL;
        $file_string .= "    'dbdriver' => 'mysqli',".PHP_EOL;
        $file_string .= "    'dbprefix' => '". $this->input->post('dbprefix', TRUE)."',".PHP_EOL;
        $file_string .= "    'pconnect' => FALSE,".PHP_EOL;
        $file_string .= "    'db_debug' => FALSE,".PHP_EOL;
        $file_string .= "    'cache_on' => FALSE,".PHP_EOL;
        $file_string .= "    'cachedir' => '',".PHP_EOL;
        $file_string .= "    'char_set' => 'utf8',".PHP_EOL;
        $file_string .= "    'dbcollat' => 'utf8_general_ci',".PHP_EOL;
        $file_string .= "    'swap_pre' => '',".PHP_EOL;
        $file_string .= "    'encrypt' => FALSE,".PHP_EOL;
        $file_string .= "    'compress' => FALSE,".PHP_EOL;
        $file_string .= "    'stricton' => FALSE,".PHP_EOL;
        $file_string .= "    'failover' => array(),".PHP_EOL;
        $file_string .= "    'save_queries' => TRUE".PHP_EOL;
        $file_string .= ");".PHP_EOL;

        return write_file(APPPATH . "/config/database.php", $file_string);
    }

    protected function make_config_file()
    {
        $this->load->helper("file");
        $file_string = "<?php defined('BASEPATH') OR exit('No direct script access allowed');".PHP_EOL.PHP_EOL;
        $file_string .= 'define("DEVICE_MOBILE", "mobile");'.PHP_EOL;
        $file_string .= 'define("DEVICE_DESKTOP", "desktop");'.PHP_EOL;
        $file_string .= 'define("COOKIE_VIEWMODE", "viewmode");'.PHP_EOL;
        $file_string .= 'define("COOKIE_AUTOLOGIN", "autologin");'.PHP_EOL;
        $file_string .= 'define("COOKIE_STATICS", "visit");'.PHP_EOL;
        $file_string .= 'define("DIR_UPLOAD", "files");'.PHP_EOL;
        $file_string .= 'define("DIR_THEME", "themes");'.PHP_EOL;
        $file_string .= 'define("DIR_SKIN", "skins");'.PHP_EOL;
        $file_string .= 'define("DIR_WIDGET", "widget");'.PHP_EOL;
        $file_string .= 'define("SKIN_TYPE_BOARD", "board");'.PHP_EOL;
        $file_string .= 'define("SKIN_TYPE_MEMBER", "member");'.PHP_EOL;

        return write_file(APPPATH . "/config/wheeparam.php", $file_string);
    }

    protected function insert_default_data()
    {
        // 환경설정 기본 데이타 삽입
        $data[] = $this->array_config('agreement_privacy', '개인정보 취급방침');
        $data[] = $this->array_config('agreement_site', '사이트 이용약관');
        $data[] = $this->array_config('allow_host', "www.youtube.com\nwww.youtube-nocookie.com\nmaps.google.co.kr\nmaps.google.com\nflvs.daum.net\nplayer.vimeo.com\nsbsplayer.sbs.co.kr\nserviceapi.rmcnmv.naver.com\nserviceapi.nmv.naver.com\nwww.mgoon.com\nvideofarm.daum.net\nplayer.sbs.co.kr\nsbsplayer.sbs.co.kr\nwww.tagstory.com\nplay.tagstory.com\nflvr.pandora.tv");
        $data[] = $this->array_config('analytics_google', '');
        $data[] = $this->array_config('analytics_naver', '');
        $data[] = $this->array_config('deny_id', 'admin,administrator,webmaster,sysop,manager,root,su,guest,super');
        $data[] = $this->array_config('deny_ip', '');
        $data[] = $this->array_config('deny_nickname', 'admin,administrator,관리자,운영자,어드민,주인장,webmaster,웹마스터,sysop,시삽,시샵,manager,매니저,메니저,root,루트,su,guest,방문객');
        $data[] = $this->array_config('deny_word', '18아,18놈,18새끼,18년,18뇬,18노,18것,18넘,개년,개놈,개뇬,개새,개색끼,개세끼,개세이,개쉐이,개쉑,개쉽,개시키,개자식,개좆,게색기,게색끼,광뇬,뇬,눈깔,뉘미럴,니귀미,니기미,니미,도촬,되질래,뒈져라,뒈진다,디져라,디진다,디질래,병쉰,병신,뻐큐,뻑큐,뽁큐,삐리넷,새꺄,쉬발,쉬밸,쉬팔,쉽알,스패킹,스팽,시벌,시부랄,시부럴,시부리,시불,시브랄,시팍,시팔,시펄,실밸,십8,십쌔,십창,싶알,쌉년,썅놈,쌔끼,쌩쑈,썅,써벌,썩을년,쎄꺄,쎄엑,쓰바,쓰발,쓰벌,쓰팔,씨8,씨댕,씨바,씨발,씨뱅,씨봉알,씨부랄,씨부럴,씨부렁,씨부리,씨불,씨브랄,씨빠,씨빨,씨뽀랄,씨팍,씨팔,씨펄,씹,아가리,아갈이,엄창,접년,잡놈,재랄,저주글,조까,조빠,조쟁이,조지냐,조진다,조질래,존나,존니,좀물,좁년,좃,좆,좇,쥐랄,쥐롤,쥬디,지랄,지럴,지롤,지미랄,쫍빱,凸,퍽큐,뻑큐,빠큐,ㅅㅂㄹㅁ');
        $data[] = $this->array_config('https_use', 'N');
        $data[] = $this->array_config('icode_userid', '');
        $data[] = $this->array_config('icode_userpw', '');
        $data[] = $this->array_config('theme_desktop', 'basic');
        $data[] = $this->array_config('theme_mobile', 'mobile');
        $data[] = $this->array_config('member_icon_height', '20');
        $data[] = $this->array_config('member_icon_width', '20');
        $data[] = $this->array_config('member_icon_use', 'Y');
        $data[] = $this->array_config('member_photo_width', '80');
        $data[] = $this->array_config('member_photo_height', '20');
        $data[] = $this->array_config('member_photo_use', 'Y');
        $data[] = $this->array_config('member_profile_use', 'Y');
        $data[] = $this->array_config('member_extra_fields', '[]');
        $data[] = $this->array_config('message_use', 'Y');
        $data[] = $this->array_config('name_auth_level', '["비회원","일반회원","일반회원","일반회원","일반회원","일반회원","일반회원","일반회원","부관리자","관리자","최고관리자"]');
        $data[] = $this->array_config('point_member_login', '5');
        $data[] = $this->array_config('point_member_register', '20');
        $data[] = $this->array_config('point_name', '포인트');
        $data[] = $this->array_config('point_use', 'Y');
        $data[] = $this->array_config('site_meta_description', '');
        $data[] = $this->array_config('site_meta_image', '');
        $data[] = $this->array_config('site_meta_image_type', 'AUTO');
        $data[] = $this->array_config('site_meta_keywords', '');
        $data[] = $this->array_config('site_subtitle', '');
        $data[] = $this->array_config('site_title', $this->input->post('site_title', TRUE));
        $data[] = $this->array_config('skin_members', 'basic');
        $data[] = $this->array_config('skin_members_mobile', 'basic');
        $data[] = $this->array_config('social_facebook_appid', '');
        $data[] = $this->array_config('social_facebook_secret', '');
        $data[] = $this->array_config('social_facebook_use', 'N');
        $data[] = $this->array_config('social_google_clientid', '');
        $data[] = $this->array_config('social_google_secret', '');
        $data[] = $this->array_config('social_google_use', 'N');
        $data[] = $this->array_config('social_kakao_clientid', '');
        $data[] = $this->array_config('social_kakao_use', 'N');
        $data[] = $this->array_config('social_naver_clientid', '');
        $data[] = $this->array_config('social_naver_secret', '');
        $data[] = $this->array_config('social_naver_use', 'N');
        $data[] = $this->array_config('social_use', 'N');
        $data[] = $this->array_config('style_ip_display', '1100');
        $data[] = $this->array_config('verification_google', '');
        $data[] = $this->array_config('verification_naver', '');

        $this->db->insert_batch("config", $data);

        // 관리자 회원필드 추가
        $mem['mem_status'] = 'Y';
        $mem['mem_userid'] = trim($this->input->post('admin_id', TRUE));
        $mem['mem_password'] = get_password_hash( trim($this->input->post('admin_pass', TRUE)));
        $mem['mem_nickname'] = $mem['mem_username'] =trim($this->input->post('admin_nick', TRUE));
        $mem['mem_auth'] = 10;
        $mem['mem_level'] = 1;
        $mem['mem_point'] = 0;
        $mem['mem_use_message'] = 'A';
        $mem['mem_use_profile'] = 'Y';
        $mem['mem_profile'] = '';
        $mem['mem_regtime'] = date('Y-m-d H:i:s');
        $mem['mem_regip'] = ip2long( $this->input->ip_address() );
        $mem['mem_logcount'] = 0;
        $mem['mem_admin'] = 'Y';
        $mem['mem_following'] = 0;
        $mem['mem_followed'] = 0;
        $mem['mem_icon'] = "";
        $mem['mem_photo'] = "";

        $this->db->insert("member", $mem);
    }

    protected function array_config($cfg_key,$cfg_value)
    {
        return array(
            "cfg_key" => $cfg_key,
            "cfg_value" => $cfg_value
        );
    }

    /***********************************************************
     * 테이블을 생성합니다.
     **********************************************************/
    protected function db_create()
    {
        $this->load->dbforge();

        /* 환경설정 테이블 */
        $field['cfg_key'] = array("type"=>"VARCHAR", "constraint"=>30);
        $field['cfg_value'] = $this->_text_array();
        $this->_create_table("config","cfg_key", $field);
        unset($field);

        /* 회원 테이블 */
        $field['mem_idx'] = $this->_pk_array();
        $field['mem_status'] = $this->_enum_array(array('Y','N','D','H'), 'Y');
        $field['mem_userid'] = $this->_varchar_array(100);
        $field['mem_password'] = array("type"=>"CHAR","constraint"=>32);
        $field['mem_nickname'] = $this->_varchar_array(20);
        $field['mem_username'] = $this->_varchar_array(20);
        $field['mem_auth'] = array("type"=>"TINYINT", "constraint"=>3, "default"=>1, "unsigned"=>TRUE);
        $field['mem_level'] = array("type"=>"SMALLINT", "constraint"=>5, "default"=>1, "unsigned"=>TRUE);
        $field['mem_point'] = $this->_int_array();
        $field['mem_use_message'] = $this->_enum_array(array('A','F','N'), 'N');
        $field['mem_use_profile'] = $this->_enum_array(array('A','F','N'), 'N');
        $field['mem_profile'] = $this->_text_array();
        $field['mem_regtime'] = $this->_datetime_array();
        $field['mem_regip'] = $this->_int_array();
        $field['mem_logtime'] = $this->_datetime_array();
        $field['mem_logip'] = $this->_int_array();
        $field['mem_logcount'] = $this->_int_array();
        $field['mem_admin'] = $this->_enum_array(array('Y','N'), 'N');
        $field['mem_following'] = $this->_int_array();
        $field['mem_followed'] = $this->_int_array();
        $field['mem_icon'] = $this->_varchar_array(255);
        $field['mem_photo'] = $this->_varchar_array(255);
        $this->_create_table("member","mem_idx", $field);
        unset($field);

        /* 회원 추가 메타 */
        $field['mem_idx'] = $this->_int_array();
        $field['mev_key'] = $this->_varchar_array(20);
        $field['mev_value'] = $this->_text_array();
        $this->_create_table("member_meta",array('mem_idx','mev_key'), $field);
        unset($field);


        /* 회원 자동로그인 테이블 */
        $field['aul_idx']   = $this->_pk_array();
        $field['mem_idx']   = $this->_int_array();
        $field['aul_key']   = array("type"=>"CHAR","constraint"=>32);
        $field['aul_ip']        = $this->_int_array();
        $field['aul_regtime']   = $this->_datetime_array();
        $this->_create_table("member_autologin","aul_idx", $field);
        unset($field);

        /* 회원 Follow 테이블 */
        $field['mfw_idx'] = $this->_pk_array();
        $field['mem_idx'] = $this->_int_array();
        $field['mfw_target'] = $this->_int_array();
        $field['mfw_regtime'] = $this->_datetime_array();
        $this->_create_table("member_follow","mfw_idx", $field);
        unset($field);

        /* 회원 로그인 로그 */
        $field['mlg_idx'] = $this->_pk_array();
        $field['mem_idx'] = $this->_int_array();
        $field['mlg_ip'] = $this->_int_array();
        $field['mlg_regtime'] = $this->_datetime_array();
        $field['mlg_browser'] = $this->_varchar_array(50);
        $field['mlg_version'] = $this->_varchar_array(50);
        $field['mlg_platform'] = $this->_varchar_array(100);
        $field['mlg_is_mobile'] = $this->_enum_array(array('Y','N'),'N');
        $field['mlg_mobile'] = $this->_varchar_array(50);
        $this->_create_table("member_log","mlg_idx", $field);
        unset($field);

        /* 회원 쪽지 */
        $field['mno_idx'] = $this->_pk_array();
        $field['mno_sender'] = $this->_int_array();
        $field['mno_reciver'] = $this->_int_array();
        $field['mno_content'] = $this->_text_array();
        $field['mno_regtime'] = $this->_datetime_array();
        $field['mno_readtime'] = $this->_datetime_array();
        $this->_create_table("member_message","mno_idx", $field);
        unset($field);

        /* 회원 포인트 */
        $field['mpo_idx'] = $this->_pk_array();
        $field['mem_idx'] = $this->_int_array();
        $field['mpo_value'] = $this->_int_array(FALSE);
        $field['mpo_description'] = $this->_varchar_array(255);
        $field['target_type'] = $this->_enum_array(array('NONE','POST_WRITE','POST_LIKE','CMT_WRITE','CMT_LIKE','LOGIN','JOIN'),'NONE');
        $field['target_idx'] = $this->_int_array();
        $field['mpo_regtime'] = $this->_datetime_array();
        $this->_create_table("member_point","mpo_idx", $field);
        unset($field);

        /* 회원 스크랩 */
        $field['scr_idx'] = $this->_pk_array();
        $field['mem_idx'] = $this->_int_array();
        $field['brd_key'] = $this->_varchar_array(20);
        $field['post_idx'] = $this->_int_array();
        $field['target_mem'] = $this->_int_array();
        $field['scr_regtime'] = $this->_datetime_array();
        $field['scr_title'] = $this->_varchar_array(255);
        $this->_create_table("member_scrap","scr_idx", $field);
        unset($field);

        /* 회원 소셜 로그인 연동*/
        $field['soc_idx'] = $this->_pk_array();
        $field['soc_type'] = $this->_enum_array(array('FACEBOOK','NAVER','KAKAO','GOOGLE'),'NAVER');
        $field['soc_key'] = $this->_varchar_array(255);
        $field['mem_idx'] = $this->_int_array();
        $field['soc_regtime'] = $this->_datetime_array();
        $field['soc_content'] = $this->_text_array();
        $this->_create_table("member_social","soc_idx", $field);
        unset($field);

        /* 일반 문서 */
        $field['doc_idx'] = $this->_pk_array();
        $field['doc_key'] = $this->_varchar_array(20);
        $field['doc_status'] = $this->_enum_array(array('PUBLISH','WRITED','TEMP','DELETED'), 'TEMP');
        $field['doc_title'] = $this->_varchar_array(255);
        $field['doc_contents'] = $this->_longtext_array();
        $field['doc_regtime'] = $this->_datetime_array();
        $field['doc_modtime'] = $this->_datetime_array();
        $field['doc_keywords'] = $this->_varchar_array(255);
        $field['doc_description'] = $this->_text_array();
        $this->_create_table("documents","doc_idx", $field);
        unset($field);

        /* 통계 테이블 - 상세 */
        $field['sta_idx'] = $this->_pk_array();
        $field['sta_regtime'] = $this->_datetime_array();
        $field['sta_browser'] = $this->_varchar_array(50);
        $field['sta_version'] = $this->_varchar_array(50);
        $field['sta_is_mobile'] = $this->_enum_array(array('Y','N'),'N');
        $field['sta_mobile'] = $this->_varchar_array(50);
        $field['sta_platform'] = $this->_varchar_array(100);
        $field['sta_referrer'] = $this->_varchar_array(255);
        $field['sta_referrer_host'] = $this->_varchar_array(255);
        $field['sta_keyword'] = $this->_varchar_array(255);
        $field['sta_ip'] = $this->_int_array();
        $this->_create_table("statics","sta_idx", $field);
        unset($field);

        /* 통계 테이블 - 일자별 */
        $field['std_date'] = array("TYPE"=>'DATE');
        $field['std_count'] = $this->_int_array();
        $field['std_mobile'] = $this->_int_array();
        $this->_create_table("statics_date","std_date", $field);
        unset($field);

        /* 게시판 그룹 */
        $field['grp_key'] = $this->_varchar_array(20);
        $field['grp_name'] = $this->_varchar_array(30);
        $field['grp_sort'] = $this->_int_array();
        $this->_create_table("board_group","grp_key", $field);
        unset($field);

        /* 게시판 */
        $field['brd_key'] = $this->_varchar_array(20);
        $field['grp_key'] = $this->_varchar_array(20);
        $field['brd_title'] = $this->_varchar_array(30);
        $field['brd_title_m'] = $this->_varchar_array(20);
        $field['brd_skin'] = $this->_varchar_array(100);
        $field['brd_skin_m'] = $this->_varchar_array(100);
        $field['brd_sort'] = $this->_int_array();
        $field['brd_search'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_lv_list'] = $this->_tinyint_array();
        $field['brd_lv_read'] = $this->_tinyint_array();
        $field['brd_lv_write'] = $this->_tinyint_array();
        $field['brd_lv_reply'] = $this->_tinyint_array();
        $field['brd_lv_comment'] = $this->_tinyint_array();
        $field['brd_lv_download'] = $this->_tinyint_array();
        $field['brd_lv_upload'] = $this->_tinyint_array();
        $field['brd_use_protect'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_scrap'] =  $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_anonymous'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_category'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_notice'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_secret'] = $this->_enum_array(array('Y','N','A'),'Y');
        $field['brd_use_reply'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_comment'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_thumbnail'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_thumbnail_m'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_attach'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_wysiwyg'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_share'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_like'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_report'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_list'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_cmt_like'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_cmt_report'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_cmt_secret'] = $this->_enum_array(array('Y','N','A'),'Y');
        $field['brd_use_rss'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_use_total_rss'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_rss_count'] = $this->_int_array();
        $field['brd_use_sitemap'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_best'] = $this->_tinyint_array();
        $field['brd_best_where'] = $this->_enum_array(array('DAY_1','TIME_1','DAY_7','TIME_7','DAY_30','TIME_30'),'DAY_1');
        $field['brd_best_opt'] = $this->_enum_array(array('HIT','LIKE'),'HIT');
        $field['brd_best_min'] = $this->_int_array();
        $field['brd_cmt_best'] = $this->_tinyint_array();
        $field['brd_display_time'] = $this->_enum_array(array('SNS','BASIC0','BASIC1','BASIC2','BASIC3','BASIC4','BASIC5'), 'SNS');
        $field['brd_count_post'] = $this->_int_array();
        $field['brd_page_rows'] = $this->_tinyint_array();
        $field['brd_page_rows_m'] = $this->_tinyint_array();
        $field['brd_fixed_num'] = $this->_tinyint_array();
        $field['brd_fixed_num_m'] = $this->_tinyint_array();
        $field['brd_header'] = $this->_longtext_array();
        $field['brd_footer'] = $this->_longtext_array();
        $field['brd_header_m'] = $this->_longtext_array();
        $field['brd_footer_m'] = $this->_longtext_array();
        $field['brd_point_write'] = $this->_int_array(FALSE);
        $field['brd_point_comment'] = $this->_int_array(FALSE);
        $field['brd_point_good'] = $this->_int_array(FALSE);
        $field['brd_point_nogood'] = $this->_int_array(FALSE);
        $field['brd_point_cmt_good'] = $this->_int_array(FALSE);
        $field['brd_point_cmt_nogood'] = $this->_int_array(FALSE);
        $field['brd_point_download'] = $this->_int_array(FALSE);
        $field['brd_member_profile'] = $this->_enum_array(array('Y','N'),'Y');
        $field['brd_time_new'] = $this->_int_array();
        $field['brd_hit_count'] = $this->_int_array();
        $field['brd_list_thumb_w'] = $this->_int_array();
        $field['brd_list_thumb_h'] = $this->_int_array();
        $field['brd_list_thumb_w_m'] = $this->_int_array();
        $field['brd_list_thumb_h_m'] = $this->_int_array();
        $field['brd_allowed_ext'] = $this->_text_array();
        $this->_create_table("board","brd_key", $field);
        unset($field);

        /* 게시판 관리자 */
        $field['brd_key'] = $this->_varchar_array(20);
        $field['mem_idx'] = $this->_int_array();
        $this->_create_table("board_admin",array("boa_idx","mem_idx"), $field);
        unset($field);

        /* 게시판 첨부파일 */
        $field['att_idx'] = $this->_pk_array();
        $field['brd_key'] = $this->_varchar_array(20);
        $field['post_idx'] = $this->_int_array();
        $field['att_origin'] = $this->_varchar_array(255);
        $field['att_filename'] = $this->_varchar_array(255);
        $field['att_downloads'] = $this->_int_array();
        $field['att_filename'] = $this->_int_array();
        $field['att_is_image'] = $this->_enum_array(array('Y','N'),'N');
        $field['att_ext'] = $this->_varchar_array(10);
        $field['att_regtime'] = $this->_datetime_array();
        $field['att_ip'] = $this->_int_array();
        $this->_create_table("board_attach","att_idx", $field);
        unset($field);

        /* 게시판 카테고리 */
        $field['bca_idx'] = $this->_pk_array();
        $field['bca_key'] = $this->_varchar_array(20);
        $field['brd_key'] = $this->_varchar_array(20);
        $field['bca_count'] = $this->_int_array();
        $field['bca_sort'] = $this->_int_array();
        $this->_create_table("board_category","bca_idx", $field);
        unset($field);

        /* 게시판 코멘트 */
        $field['cmt_idx'] = $this->_pk_array();
        $field['brd_key'] = $this->_varchar_array(20);
        $field['post_idx'] = $this->_int_array();
        $field['cmt_num'] = $this->_int_array();
        $field['cmt_depth'] = $this->_int_array();
        $field['cmt_secret'] = $this->_enum_array(array('Y','N'),'N');
        $field['mem_idx'] = $this->_int_array();
        $field['mem_nickname'] = $this->_varchar_array(20);
        $field['cmt_regtime'] = $this->_datetime_array();
        $field['cmt_modtime'] = $this->_datetime_array();
        $field['cmt_content'] = $this->_text_array();
        $field['cmt_ip'] = $this->_int_array();
        $field['cmt_good'] = $this->_int_array();
        $field['cmt_nogood'] = $this->_int_array();
        $field['cmt_report'] = $this->_int_array();
        $field['cmt_status'] = $this->_enum_array(array('Y','N','B'),'Y');
        $field['cmt_mobile'] = $this->_enum_array(array('Y','N'),'N');
        $this->_create_table("board_comment","cmt_idx", $field);
        unset($field);

        /* 게시판 좋아요/신고 */
        $field['prs_idx'] = $this->_pk_array();
        $field['brd_key'] = $this->_varchar_array(20);
        $field['prs_type'] = $this->_enum_array(array('GOOD','NOGOOD','REPORT'),'GOOD');
        $field['prs_target_type'] = $this->_enum_array(array('BOARD','COMMENT'),'BOARD');
        $field['prs_target_idx'] = $this->_int_array();
        $field['mem_idx'] = $this->_int_array();
        $field['mem_target_idx'] = $this->_int_array();
        $field['prs_regtime'] = $this->_datetime_array();
        $field['prs_ip'] = $this->_int_array();
        $this->_create_table("board_like","prs_idx", $field);
        unset($field);

        /* 게시판 추가 필드 */
        $field['bmt_idx'] = $this->_pk_array();
        $field['bmt_key'] = $this->_varchar_array(20);
        $field['brd_key'] = $this->_varchar_array(20);
        $field['bmt_name'] = $this->_varchar_array(30);
        $field['bmt_type'] = $this->_enum_array(array('TEXT','TEXTAREA','SELECT','RADIO','CHECKBOX','ADDRESS','URL','EMAIL','PHONE','DATE'),'TEXT');
        $field['bmt_is_required'] = $this->_enum_array(array('Y','N'),'N');
        $field['bmt_option'] = $this->_text_array();
        $field['bmt_sort'] = $this->_int_array();
        $this->_create_table("board_meta","bmt_idx", $field);
        unset($field);

        /* 게시글 */
        $field['post_idx'] = $this->_pk_array();
        $field['post_num'] = $this->_int_array();
        $field['post_depth'] = $this->_int_array();
        $field['brd_key'] = $this->_varchar_array(20);
        $field['bca_idx'] = $this->_int_array();
        $field['bca_key'] = $this->_varchar_array(20);
        $field['post_title'] = $this->_varchar_array(255);
        $field['post_content'] = $this->_longtext_array();
        $field['post_status'] = $this->_enum_array(array('PUBLISH','WRITED','TEMP','DELETED','BLIND'), 'TEMP');
        $field['mem_idx'] = $this->_int_array();
        $field['mem_nickname'] = $this->_varchar_array(20);
        $field['post_regtime'] = $this->_datetime_array();
        $field['post_modtime'] = $this->_datetime_array();
        $field['post_count_comment'] = $this->_int_array();
        $field['post_recent_comment'] = $this->_datetime_array();
        $field['post_secret'] = $this->_enum_array(array('Y','N'),'N');
        $field['post_html'] = $this->_enum_array(array('Y','N'),'Y');
        $field['post_notice'] = $this->_enum_array(array('Y','N'),'N');
        $field['post_hit'] = $this->_int_array();
        $field['post_good'] = $this->_int_array();
        $field['post_nogood'] = $this->_int_array();
        $field['post_report'] = $this->_int_array();
        $field['post_mobile'] = $this->_enum_array(array('Y','N'),'N');
        $this->_create_table("board_post","post_idx", $field);
        unset($field);

        /* 게시글 추가 메타*/
        $field['bmt_idx'] = $this->_int_array();
        $field['post_idx'] = $this->_int_array();
        $field['bmt_value'] = $this->_text_array();
        $this->_create_table("board_post_meta",array("post_idx","bmt_idx"), $field);
        unset($field);
    }

    protected function _create_table($table_name, $pk, $column)
    {
        $attributes = array('ENGINE' => 'InnoDB');

        $this->dbforge->drop_table($table_name);
        $this->dbforge->add_field($column);
        $this->dbforge->add_key($pk, TRUE);
        if( $this->dbforge->create_table($table_name, TRUE, $attributes) === FALSE) {
            alert('테이블 생성에 실패하였습니다. : '. $table_name);
            exit;
        }
    }

    protected function _varchar_array($length=255)
    {
        return array("type"=>"VARCHAR", "constraint"=>$length, "default"=>'');
    }

    protected function _pk_array()
    {
        return array("TYPE"=>"INT", "constraint"=>10, "unsigned"=>TRUE, "auto_increment"=>TRUE );
    }

    protected function _int_array($unsigned=TRUE)
    {
        return array("TYPE"=>"INT", "constraint"=>10, "default"=>0, "unsigned"=>$unsigned);
    }

    protected function _tinyint_array($unsigned=TRUE)
    {
        return array("TYPE"=>"TINYINT", "constraint"=>3, "default"=>0, "unsigned"=>$unsigned);
    }

    protected function _datetime_array()
    {
        return array("TYPE"=>"DATETIME");
    }

    protected function _text_array()
    {
        return array("TYPE"=>"TEXT", "default"=>'');
    }

    protected function _longtext_array()
    {
        return array("TYPE"=>"LONGTEXT", "default"=>'');
    }

    protected function _enum_array($array=array(), $default="")
    {
        foreach($array as &$arr) {
            $arr = "'{$arr}'";
        }
        $return['TYPE'] = "ENUM(" .implode(",",$array).")";
        $return['default'] = $default;
        return $return;
    }
}
