<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vendedor_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->_table = "vendedor";
    }

    function get_by_nombre($nombre) {        
        $limit=10;        
        $this->db->select('id,nombre');  
        $this->db->from($this->_table);
        $this->db->like('nombre', $nombre,'both');        
        $this->db->limit($limit);
        
        $vendedores = $this->db->get()->result();

        if (sizeof($vendedores) > 0) {
            return $vendedores;
        } else {
            return false;
        }
    }

}
