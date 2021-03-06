<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Poblacion_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->_table = "poblacion";
    }

    function get_all_by_provincia($provincia_id) {
        $this->db->where("provincia_id", $provincia_id);
        $this->db->order_by("nombre","asc");
        $query = $this->db->get($this->_table);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

}
