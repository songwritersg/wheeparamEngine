<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*****************************************************************************************
 * Alert 창을 띄우고 특정 URL로 이동합니다.
 * @param string $msg
 * @param string $url
 ****************************************************************************************/
function alert($msg = '', $url = '')
{
    if (empty($msg)) {
        $msg = '잘못된 접근입니다';
    }
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
    echo '<script type="text/javascript">alert("' . $msg . '");';
    if (empty($url)) {
        echo 'history.go(-1);';
    }
    if ($url) {
        echo 'document.location.href="' . $url . '"';
    }
    echo '</script>';
    exit;
}

/*****************************************************************************************
 * Alert 창을 띄우고 현재 팝업창을 닫습니다.
 * @param string $msg
 ****************************************************************************************/
function alert_close($msg='')
{
    if (empty($msg)) {
        $msg = '잘못된 접근입니다';
    }
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
    echo '<script type="text/javascript">alert("' . $msg . '");';
    echo 'window.close();';
    echo '</script>';
    exit;
}

/*****************************************************************************************
 * 로그인이 필요한 페이지임을 알리고, 로그인 페이지로 이동합니다.
 * @param string $msg
 ****************************************************************************************/
function alert_login()
{
    $url = base_url("members/login")."?reurl=".current_full_url(TRUE);
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
    echo '<script type="text/javascript">alert("로그인이 필요한 페이지입니다.");';
    echo 'document.location.href="' . $url . '"';
    echo '</script>';
    exit;
}


/****************************************************************************************
 * 배열의 특정 키값을 가져옵니다.
 * @param $item
 * @param $array
 * @param null $default
 * @return mixed|null
 ***************************************************************************************/
function element($item, $array, $default = NULL)
{
    return is_array($array) && array_key_exists($item, $array) &&  $array[$item] ? $array[$item] : $default;
}


/*****************************************************************************************
 * 현재 주소를 Parameter 포함해서 가져온다.
 * @return string
 ****************************************************************************************/
function current_full_url($urlencode = FALSE)
{
    $CI =& get_instance();
    $url = $CI->config->site_url($CI->uri->uri_string());
    $return = ($CI->input->server('QUERY_STRING'))
        ? $url . '?' . $CI->input->server('QUERY_STRING') : $url;
    return $urlencode ?  urlencode($return) : $return;
}

/******************************************************************************************
 * 특정문자열을 암호화하여 내보낸다.
 * @param $string
 * @return string
 *****************************************************************************************/
function get_password_hash($string)
{
    $CI =& get_instance();
    return hash('md5', $CI->config->item('encryption_key') . $string );
}

/******************************************************************************************
 * 해당 URL이 우리 서버 도메인을 가르키는지 확인한다.
 * @param $url 체크할 URL
 * @param bool $check_file_exist 파일존재 여부까지 확인한다.
 * @return bool
 *****************************************************************************************/
function is_my_domain($url, $check_file_exist = TRUE) {
    // 처음 시작이 / 이고 두번제 문자가 /이 아닐경우
    if( substr($url,0,1) === '/' && substr($url,1,1) !== '/' )
    {
        if( $check_file_exist ) {
            return file_exists( FCPATH . $url );
        }
        return TRUE;
    }
    if( strpos( $url, base_url()) !== FALSE ) {
        if( $check_file_exist ) {
            return file_exists( FCPATH . str_replace( base_url(), "", $url ));
        }
        return TRUE;
    }
    return FALSE;
}

/******************************************************************************************
 *
 *****************************************************************************************/
function get_editor($name, $contents="", $id="", $height="")
{
    $param['id'] = empty($id) ? $name : $id;
    $param['name'] = $name;
    $param['height'] = empty($height) ? '300px' : $height;
    $param['contents'] = $contents;

    $CI =& get_instance();
    $CI->site->add_js("/static/plugins/tinymce/tinymce.min.js");

    return $CI->load->view("tools/tinymce", $param, TRUE);
}