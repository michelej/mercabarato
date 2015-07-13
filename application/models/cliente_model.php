<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cliente_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->_table = "cliente";
    }

    public function get_admin_search($params, $limit, $offset) {
        $this->db->start_cache();
        $this->db->select("cliente.*,usuario.email,usuario.ultimo_acceso,usuario.ip_address,usuario.fecha_creado");
        $this->db->from($this->_table);
        $this->db->join("usuario", "cliente.usuario_id=usuario.id", 'INNER');

        if (isset($params['nombre'])) {
            $this->db->like('CONCAT(cliente.nombres," ",cliente.apellidos)', $params['nombre'], 'both');
        }
        if (isset($params['sexo'])) {
            $this->db->where('cliente.sexo', $params['sexo']);
        }
        if (isset($params['email'])) {
            $this->db->like('usuario.email', $params['email'], 'both');
        }
        if (isset($params['es_vendedor'])) {
            $this->db->where('cliente.es_vendedor', $params["es_vendedor"]);
        }
        if (isset($params['excluir_admins'])) {
            $this->db->where('usuario.is_admin', "0");
        }
        if (isset($params['usuario_activo'])) {
            $this->db->where('usuario.activo', $params['usuario_activo']);
        }

        if (isset($params['keywords'])) {
            foreach ($params['keywords'] as $keyword) {
                $this->db->like('cliente.keyword', $keyword, 'both');
            }
        }

        if (isset($params['excluir_cliente_ids'])) {
            $this->db->where_not_in('cliente.id', $params['excluir_cliente_ids']);
        }

        $this->db->stop_cache();
        $count = $this->db->count_all_results();

        if ($count > 0) {
            $this->db->order_by('cliente.id', 'asc');
            $this->db->limit($limit, $offset);
            $clientes = $this->db->get()->result();
            $this->db->flush_cache();
            return array("clientes" => $clientes, "total" => $count);
        } else {
            $this->db->flush_cache();
            return array("total" => 0);
        }
    }

    public function es_vendedor($cliente_id) {
        $this->db->where('cliente_id', $cliente_id);
        $query = $this->db->get('vendedor');
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Full Delete de un producto
     * @param type $id
     */
    public function delete($id) {
        $cliente = $this->get($id);
        if ($cliente) {
            $usuario = $this->usuario_model->get($cliente->usuario_id);
            $this->localizacion_model->delete_by("usuario_id", $usuario->id);
            $this->invitacion_model->delete_by("cliente_id", $cliente->id);
            $this->solicitud_seguro_model->delete_by("cliente_id",$id);
            $this->visita_model->delete_by("cliente_id", $id);
            
            $grupos = $this->grupo_model->get_many_by("cliente_id", $id);
            if ($grupos) {
                foreach($grupos as $grupo){
                    $this->grupo_tarifa_model->delete_by("grupo_id",$grupo->id);
                    $this->grupo_oferta_model->delete_by("grupo_id",$grupo->id);
                }
                $this->grupo_model->delete_by("cliente_id",$id);
            }
            
            
            
            $vendedor=$this->vendedor_model->get_by("cliente_id",$id);
            if($vendedor){
             $this->vendedor_model->delete($vendedor->id);                 
            }            
            parent::delete($id);
        } else {
            return false;
        }
    }

}
