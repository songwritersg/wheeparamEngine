<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*************************************************************
 * Class Boardlib
 * ----------------------------------------------------------
 *
 * 게시판 라이브러리
 ************************************************************/
class Boardlib {

    protected $CI;

    function __construct()
    {
        $this->CI =& get_instance();
    }

    /*********************************************************
     * 게시판 정보를 가져온다.
     * @param string $brd_key
     ********************************************************/
    function get_board($brd_key="")
    {
        if(empty($brd_key)) return FALSE;

        $this->CI->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));

        if(! $board = $this->CI->cache->get('board_info_'.$brd_key) )
        {
            $this->CI->db->where('brd_key', $brd_key);
            $this->CI->db->limit(1);
            $result = $this->CI->db->get('tbl_board');

            if( ! $result OR ! $board = $result->row_array() ) return NULL;

            // 추가입력 필드를 가져온다.
            $this->CI->db->where('brd_key', $brd_key);
            $this->CI->db->order_by('bmt_sort ASC');
            $result = $this->CI->db->get('tbl_board_meta');
            $meta = $result->result_array();
            $board['brd_meta'] = $meta ? $meta : array();

            // 관리자 목록을 가져온다.
            $this->CI->db->select("A.*, M.*");
            $this->CI->db->from("tbl_board_admin AS A");
            $this->CI->db->join("tbl_member AS M","M.mem_idx=A.mem_idx","inner");
            $this->CI->db->where('A.brd_key', $brd_key);
            $this->CI->db->where("M.mem_status",'Y');
            $this->CI->db->order_by("M.mem_userid ASC");
            $result = $this->CI->db->get();
            $admin = $result->result_array();
            $board['brd_admin'] = $admin ? $admin : array();

            // 카테고리 목록을 가져온다.
            $this->CI->db->where('brd_key', $brd_key);
            $this->CI->db->order_by("bca_sort ASC");
            $result = $this->CI->db->get('tbl_board_category');
            $category = $result->result_array();
            $board['brd_category'] = $category ? $category : array();

            $this->CI->cache->save('board_info_'.$brd_key, $board, 60*30);
        }

        return $board;
    }

}