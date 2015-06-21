<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vendedor_model extends MY_Model {

    function __construct() {
        parent::__construct();
        $this->_table = "vendedor";
    }
    /**
     * 
     * @param type $nombre
     * @return boolean
     */
    function get_by_nombre($nombre) {
        $limit = 10;
        $this->db->select('id,nombre');
        $this->db->from($this->_table);
        $this->db->like('nombre', $nombre, 'both');
        $this->db->limit($limit);

        $vendedores = $this->db->get()->result();

        if (sizeof($vendedores) > 0) {
            return $vendedores;
        } else {
            return false;
        }
    }
    /**
     * 
     * @param type $params
     * @param type $limit
     * @param type $offset
     * @return type
     */
    public function get_admin_search($params, $limit, $offset) {
        $this->db->start_cache();
        $this->db->select("vendedor.*,cliente.direccion,cliente.telefono_fijo,cliente.telefono_movil,cliente.usuario_id,usuario.email,usuario.ultimo_acceso,usuario.ip_address");
        $this->db->from($this->_table);
        $this->db->join("cliente", "cliente.id=vendedor.cliente_id", 'INNER');
        $this->db->join("usuario", "usuario.id=cliente.usuario_id", 'INNER');

        if (isset($params['nombre'])) {
            $this->db->like('vendedor.nombre', $params['nombre'], 'both');
        }
        if (isset($params['email'])) {
            $this->db->like('usuario.email', $params['email'], 'both');
        }
        if (isset($params['actividad'])) {
            $this->db->like('vendedor.actividad', $params['actividad'], 'both');
        }
        if (isset($params['sitio_web'])) {
            $this->db->like('vendedor.sitio_web', $params['sitio_web'], 'both');
        }

        $this->db->stop_cache();
        $count = $this->db->count_all_results();

        if ($count > 0) {
            $this->db->order_by('vendedor.id', 'asc');
            $this->db->limit($limit, $offset);
            $vendedores = $this->db->get()->result();
            $this->db->flush_cache();
            return array("vendedores" => $vendedores, "total" => $count);
        } else {
            $this->db->flush_cache();
            return array("total" => 0);
        }
    }
    /**
     * Devuelve un Vendedor
     * @param type $id
     * @return boolean
     */
    public function get_vendedor($id) {
        $this->db->select("vendedor.*,cliente.direccion,cliente.telefono_fijo,cliente.telefono_movil,cliente.usuario_id,usuario.email,usuario.ultimo_acceso,usuario.ip_address");
        $this->db->from($this->_table);
        $this->db->join("cliente", "cliente.id=vendedor.cliente_id", 'INNER');
        $this->db->join("usuario", "usuario.id=cliente.usuario_id", 'INNER');
        $this->db->where('vendedor.id', $id);
        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->row();
        } else {
            return FALSE;
        }
    }
    
    /**
     * Habilitar un vendedor
     * @param type $id
     */
    public function habilitar_vendedor($id) {
        $data = array(
            "habilitado" => 1,
        );

        $this->update($id, $data);
    }

    /**
     * Verificar si es posible asignarle un vendedor_paquete a este vendedor.
     * - Si tiene paquetes con aprobado=0 FALSE
     * - Si tiene paquetes aprobados en curso FALSE
     * @param type $id
     */
    public function verificar_disponibilidad($vendedor_id) {
        $this->db->select('*');
        $this->db->from('vendedor_paquete');
        $this->db->where('vendedor_id', $vendedor_id);
        $paquetes = $this->db->get()->result();
        $hoy = date("Y-m-d");

        $flag = true;
        if (sizeof($paquetes) > 0) {
            foreach ($paquetes as $paquete) {
                if ($paquete->aprobado == 0) {
                    $flag = false;
                } elseif ($paquete->fecha_terminar > $hoy) {
                    $flag = false;
                }
            }
        }
        return $flag;
    }
    
    /**
     * Retornar el vendedor_paquete que esta en curso
     * @param type $id
     */
    public function get_paquete_en_curso($vendedor_id){
        $this->db->select("*");
        $this->db->from('vendedor_paquete');        
        $this->db->where('vendedor_id', $vendedor_id);
        $this->db->where('aprobado', '1');
        $this->db->where('fecha_terminar >',date('Y-m-d'));
        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->row();
        } else {
            return FALSE;
        }
    }
    
    /**
     * Devuelve la cantidad de productos disponibles por insertar
     * @param type $vendedor_id
     */
    public function get_cantidad_productos_disp($vendedor_id){
        $vendedor_paquete=$this->get_paquete_en_curso($vendedor_id);                        
        if($vendedor_paquete){            
            $this->db->select('id')->from('producto')->where('vendedor_id',$vendedor_id);
            $total = $this->db->count_all_results();
            return ($vendedor_paquete->limite_productos-$total);
        }else{
            return 0;
        }        
    }  
    
    /**
     * Devuelve la cantidad de anuncios disponibles por insertar
     * @param type $vendedor_id
     */
    public function get_cantidad_anuncios_disp($vendedor_id){
        $vendedor_paquete=$this->get_paquete_en_curso($vendedor_id);                        
        if($vendedor_paquete){
            $this->db->select('id')->from('anuncio')->where('vendedor_id',$vendedor_id);
            $total = $this->db->count_all_results();
            return ($vendedor_paquete->limite_anuncios-$total);
        }else{
            return 0;
        }        
    }
    
    /**
     * Retornar el vendedor_paquete que esta pendiente por aprobacion, solo deberia existir uno
     * @param type $vendedor_id
     */
    public function get_paquete_pendiente($vendedor_id){
        $this->db->select("*");
        $this->db->from('vendedor_paquete');        
        $this->db->where('vendedor_id', $vendedor_id);
        $this->db->where('aprobado', '0');        
        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->row();
        } else {
            return FALSE;
        }
    }

}
