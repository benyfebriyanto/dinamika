<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Constants {

    private $CI;

    function __construct() {
        
        $this->CI =& get_instance();
        $this->CI->load->database();

        $query = $this->CI->db->get('constants');

        $result = $query->result();

        foreach( $result as $row)
        {
            define("$row->configname", "$row->configvalue");
        }

    }

}