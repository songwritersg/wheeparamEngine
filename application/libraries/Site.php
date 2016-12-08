<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Site {

    public $viewmode;
    public $device;
    protected $config;
    protected $css_before = array();
    protected $css_after = array();
    protected $js_before = array();
    protected $js_after = array();
    public $meta_title 			= "";
    public $meta_description 	= "";
    public $meta_keywords 		= "";
    public $meta_image			= "";

    function __construct()
    {

    }

    /**********************************************************
     * 사이트 전역설정중 특정 컬럼의 값을 반환한다.
     * @param $column 반활할 컬럼 이름
     * @return var 컬럼의 값
     *********************************************************/
    public function config($column) {
        // 컬럼값이 없으면 리턴한다.
        if( empty($column) ) return NULL;
        // 캐시 드라이버 로드
        $CI =& get_instance();
        $CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        if( ! $config = $CI->cache->get('site_config') )
        {
            $result = $CI->db->get("config");
            $config_list = $result->result_array();
            $config = array();
            foreach( $config_list as $row ) {
                $config[$row['cfg_key']] = $row['cfg_value'];
            }
            $CI->cache->save('site_config', $config);
        }
        return element($column, $config, NULL);
    }

    /*********************************************************
     * 현재 접속 기기에 따라 필요한 레이아웃을 가져온다.
     *********************************************************/
    public function get_layout()
    {
        return ( $this->viewmode == DEVICE_MOBILE ) ? $this->config('theme_mobile') : $this->config('theme_desktop');
    }

    /*********************************************************
     * 관리자 레이아웃을 가져온다.
     *********************************************************/
    public function get_admin_layout()
    {
        return "admin";
    }

    /*********************************************************
     * 사이트에 사용할 CSS를 추가합니다.
     * @param $url 추가할 CSS
     * @param bool $insert_last 마지막에 추가할지 처음에 추가할지
     ********************************************************/
    public function add_css( $url, $insert_first = FALSE) {
        if(!empty($url) && ! in_array($url, $this->css_after) && !in_array($url, $this->css_before)) {
            if( $insert_first ) {
                array_push($this->css_before, $url);
            }
            else {
                array_push($this->css_after, $url);
            }
        }
    }

    /*********************************************************
     * 사이트에 사용할 JS를 추가한다.
     * @param $url 추가할 JS
     * @param bool $insert_last 마지막에 추가할것인가?
     ********************************************************/
    public function add_js( $url, $insert_first = FALSE ) {
        if(!empty($url) && ! in_array($url, $this->js_before) && ! in_array($url, $this->js_after)) {
            if( $insert_first ) {
                array_push($this->js_before, $url);
            }
            else {
                array_push($this->js_after, $url);
            }
        }
    }

    /*********************************************************
     * 배열에 담긴 CSS를 메타태그와 함께 같이 출력한다.
     * @return string
     ********************************************************/
    public function display_css() {
        $CI =& get_instance();
        $return = '';

        // Layout 기본 CSS가 있다면 추가한다.
        if( $CI->theme && file_exists(VIEWPATH.'/'.DIR_THEME.'/'.$CI->theme."/theme.css") ) {
            $this->add_css( base_url("views/".DIR_THEME."/".$CI->theme."/theme.css"), TRUE);
        }
        if( $CI->skin_type && $CI->skin && file_exists(VIEWPATH.'/'.DIR_SKIN.'/'.$CI->skin_type.'/'.$CI->skin.'/skin.css')) {
            $this->add_css( base_url("views/".DIR_SKIN."/".$CI->skin_type.'/'.$CI->skin."/skin.css"), TRUE);
        }

        $css_array = array_merge($this->css_before, $this->css_after);
        $css_array = array_unique($css_array);
        foreach($css_array as $css) {

            if( is_my_domain( $css ) ) {
                $filepath = str_replace(base_url(), "/", $css);
                $css .= "?" . date('YmdHis', filemtime( FCPATH.ltrim($filepath,DIRECTORY_SEPARATOR) ));

                if( ! (strpos($css, base_url()) !== FALSE) ) {
                    $css = base_url($css);
                }
            }

            $return .= '<link rel="stylesheet" type="text/css" href="'.$css.'" />';
        }
        return $return;
    }

    /*********************************************************
     * 배열에 담긴 JS를 메타태그와 함께 같이 출력한다.
     * @return string
     ********************************************************/
    public function display_js() {
        $CI =& get_instance();
        $return = '';
        // Layout 기본 CSS가 있다면 추가한다.
        if( isset($CI->theme) && $CI->theme && file_exists(VIEWPATH.'/'.DIR_THEME.'/'.$CI->theme."/theme.js") ) {
            $this->add_js(base_url("views/".DIR_THEME."/".$CI->theme."/theme.js"), TRUE);
        }
        if( $CI->skin_type && $CI->skin && file_exists(VIEWPATH.'/'.DIR_SKIN.'/'.$CI->skin_type.'/'.$CI->skin.'/skin.js')) {
            $this->add_js(base_url("views/".DIR_SKIN."/".$CI->skin_type.'/'.$CI->skin."/skin.js"), TRUE);
        }
        $js_array = array_merge($this->js_before, $this->js_after);
        $js_array = array_unique($js_array);
        foreach($js_array as $js) {
            if( is_my_domain( $js ) ) {
                $filepath = str_replace(base_url(), "/", $js);
                $js .= "?" . date('YmdHis', filemtime( FCPATH.ltrim($filepath,DIRECTORY_SEPARATOR) ));

                if( ! (strpos($js, base_url()) !== FALSE) ) {
                    $js = base_url($js);
                }
            }
            $return .= '<script src="'.$js.'"></script>';
        }
        // 사이트를 위한 javascript
        $return .= '<script>';
        $return .= 'var base_url="'.base_url().'";';
        $return .= 'var current_url="'.current_url().'";';
        $return .= '</script>';
        return $return;
    }

    /*********************************************************
     * 메타태그를 자동으로 생성하여 표시한다.
     ********************************************************/
    public function display_meta(){
        // Default 값 설정
        $this->meta_title = $this->meta_title ? $this->meta_title : $this->config('site_subtitle');
        if( ! empty($this->meta_title) ) $this->meta_title .= ' :: ';
        $this->meta_title .= $this->config('site_title');
        $this->meta_description = $this->meta_description ? $this->meta_description : $this->config('site_meta_description');
        $this->meta_keywords = $this->meta_keywords ? $this->meta_keywords : "";
        $this->meta_image = $this->meta_image ? $this->meta_image : ($this->config('site_meta_image' )? base_url($this->config('site_meta_image')): NULl);
        $default_keywords = explode(",", $this->config('site_meta_keywords'));
        $in_keywords = explode(",", $this->meta_keywords);
        foreach($in_keywords as $keyword) {
            $keyword = trim($keyword);
            if(! in_array($keyword, $default_keywords)) {
                array_push($default_keywords, $keyword);
            }
        }
        $default_keywords = array_unique($default_keywords);
        $this->meta_keywords = "";
        // 합친 키워드를 다시 직렬화
        foreach($default_keywords as $keyword) {
            $this->meta_keywords .= $keyword.",";
        }
        $this->meta_keywords = rtrim($this->meta_keywords,",");

        // 기본태그
        $return = "";
        $return .= '<meta charset="utf-8">';
        $return .=  ($this->viewmode == DEVICE_DESKTOP) ? '<meta name="viewport" content="width=device-width,initial-scale=1">' : '<meta name="viewport" content="width=device-width,initial-scale=1">';
        $return .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        // 기본 메타 태그
        $return .= '<title>' . $this->meta_title . '</title>';
        $return .= '<meta name="description" content="'.$this->meta_description.'">';
        $return .= '<meta name="keywords" content="'. $this->meta_keywords.'">';
        $return .= $this->meta_image ? '<link rel="image_src" href="'.$this->meta_image.'">': '';
        // 페이스북 메타 태그
        $return .= '<meta property="og:title" content="'.$this->meta_title.'" />';
        $return .= '<meta property="og:type" content="article" />';
        $return .= '<meta property="og:url" content="'.current_url().'" />';
        $return .= $this->meta_image ? '<meta property="og:image" content="'.$this->meta_image.'" />': '';
        $return .= '<meta property="og:description" content="'.$this->meta_description.'" />';
        $return .= '<meta property="og:site_name" content="'.$this->config('site_title').'" />';
        // 트위터 메타 태그
        $return .= '<meta name="twitter:card" content="summary"/>';
        $return .= '<meta name="twitter:site" content="'.$this->config('site_title').'"/>';
        $return .= '<meta name="twitter:title" content="'.$this->meta_title.'">';
        $return .= '<meta name="twitter:description" content="'.$this->meta_description.'"/>';
        $return .= '<meta name="twitter:creator" content="'.$this->config('site_title').'"/>';
        $return .= $this->meta_image ? '<meta name="twitter:image:src" content="'.$this->meta_image.'"/>' : '';
        $return .= '<meta name="twitter:domain" content="'.base_url().'"/>';
        // 네이트온 메타 태그
        $return .= '<meta name="nate:title" content="'.$this->meta_title.'" />';
        $return .= '<meta name="nate:description" content="'.$this->meta_description.'" />';
        $return .= '<meta name="nate:site_name" content="'.$this->config('site_title').'" />';
        $return .= '<meta name="nate:url" content="'.current_url().'" />';
        $return .= $this->meta_image ? '<meta name="nate:image" content="'.$this->meta_image.'" />' : '';
        // Verification 이 있다면 메타태그 추가
        if(! empty($this->config('verification_google')) ) $return .= '<meta name="google-site-verification" content="'.$this->config('verification_google').'">';
        if(! empty($this->config('verification_naver')) ) $return .= '<meta name="naver-site-verification" content="'.$this->config('verification_naver').'">';

        $CI =& get_instance();
        // 구글애널리틱스 코드가 있다면?
        if( $CI->uri->segment(1) != 'admin' && ! $CI->input->is_ajax_request() && ! $CI->agent->is_robot() )
        {
            if(! empty($this->config('analytics_google')) )
            {
                $return .= "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){";
                $return .= "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),";
                $return .= "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)";
                $return .= "})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');";
                $return .= "ga('create', '".$this->config('analytics_google')."', 'auto');";
                $return .= "ga('send', 'pageview');";
                $return .= "</script>";
            }

            if(! empty($this->config('analytics_naver')) )
            {
                $return .= '<script src="http://wcs.naver.net/wcslog.js"></script>';
                $return .= '<script>if(!wcs_add) var wcs_add = {};wcs_add["wa"] = "'.$this->config('analytics_naver').'";wcs_do();</script>';
            }
        }
        return $return;
    }
}