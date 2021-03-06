<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Anuncio_model extends MY_Model {

    public $before_create = array('pre_insertado');

    function __construct() {
        parent::__construct();
        $this->_table = "anuncio";
    }

    public function get_admin_search($params, $limit, $offset) {
        $this->db->start_cache();
        $this->db->select("anuncio.*,vendedor.nombre AS Vendedor,usuario.email");
        $this->db->from($this->_table);
        $this->db->join("vendedor", "vendedor.id=anuncio.vendedor_id", 'INNER');
        $this->db->join("cliente", "cliente.id=vendedor.cliente_id", 'INNER');
        $this->db->join("usuario", "usuario.id=cliente.usuario_id", 'INNER');
        $this->db->join("vendedor_paquete", "vendedor.id=vendedor_paquete.vendedor_id AND vendedor_paquete.aprobado='1' AND"
                . " vendedor_paquete.fecha_terminar >='" . date("Y-m-d") . "'", 'LEFT');
        $this->db->join("localizacion", "localizacion.usuario_id=usuario.id", 'INNER');

        if (isset($params['titulo'])) {
            $this->db->like('anuncio.titulo', $params['titulo'], 'both');
        }
        if (isset($params['email'])) {
            $this->db->like('usuario.email', $params['email'], 'both');
        }
        if (isset($params['vendedor'])) {
            $this->db->like('vendedor.nombre', $params['vendedor'], 'both');
        }
        if (isset($params['vendedor_id'])) {
            $this->db->where('anuncio.vendedor_id', $params['vendedor_id']);
        }
        if (isset($params['autorizado_por'])) {
            $this->db->where('vendedor_paquete.autorizado_por', $params['autorizado_por']);
        }
        if (isset($params['pais_id'])) {
            $this->db->where('localizacion.pais_id', $params['pais_id']);
        }
        if (isset($params['provincia_id'])) {
            $this->db->where('localizacion.provincia_id', $params['provincia_id']);
        }
        if (isset($params['poblacion_id'])) {
            $this->db->where('localizacion.poblacion_id', $params['poblacion_id']);
        }

        $this->db->stop_cache();
        $count = $this->db->count_all_results();

        if ($count > 0) {
            $this->db->order_by('id', 'asc');
            $this->db->limit($limit, $offset);
            $anuncios = $this->db->get()->result();
            $this->db->flush_cache();
            return array("anuncios" => $anuncios, "total" => $count);
        } else {
            $this->db->flush_cache();
            return array("total" => 0);
        }
    }

    public function get_anuncio($id) {
        $this->db->select("anuncio.*,vendedor.nombre as vendedor_nombre,usuario.email");
        $this->db->from($this->_table);
        $this->db->join("vendedor", "vendedor.id=anuncio.vendedor_id", 'INNER');
        $this->db->join("cliente", "cliente.id=vendedor.cliente_id", 'INNER');
        $this->db->join("usuario", "usuario.id=cliente.usuario_id", 'INNER');
        $this->db->where('anuncio.id', $id);
        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->row();
        } else {
            return FALSE;
        }
    }

    public function get_ultimos_anuncios($count = 5) {
        $this->db->select("anuncio.*");
        $this->db->from($this->_table);
        $this->db->where("habilitado", "1");
        $this->db->order_by("fecha_publicacion", "desc");
        $this->db->limit($count, 0);
        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->result();
        } else {
            return FALSE;
        }
    }

    public function get_vendedor_id_del_anuncio($anuncio_id) {
        $anuncio = $this->get($anuncio_id);
        if ($anuncio) {
            return $anuncio->vendedor_id;
        } else {
            return FALSE;
        }
    }

    /**
     * 
     * @param type $vendedor_id
     * @param type $count
     * @return boolean
     */
    public function get_ultimos_anuncios_ids($vendedor_id, $count) {
        $this->db->select('id');
        $this->db->from('anuncio');
        $this->db->where("vendedor_id", $vendedor_id);
        $this->db->order_by("id", "desc");
        $this->db->limit($count, 0);
        $response = $this->db->get()->result_array();

        if ($response) {
            $anuncio_ids = array();
            foreach ($response as $val) {
                $anuncio_ids[] = $val['id'];
            }
            return $anuncio_ids;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $params
     */
    public function habilitar_anuncios($params) {
        $data = array("habilitado" => 1);
        if (isset($params["limit"]) && isset($params["vendedor_id"])) {
            $ids = $this->get_ultimos_anuncios_ids($params["vendedor_id"], $params["limit"]);

            $this->db->where_in('id', $ids);
            $this->db->where('vendedor_id', $params["vendedor_id"]);
        }
        $this->db->update("anuncio", $data);
        return $this->db->affected_rows();
    }

    public function inhabilitar($anuncio_id) {
        $this->update($anuncio_id, array("habilitado" => "0"));
    }

    public function habilitar($anuncio_id) {
        $this->update($anuncio_id, array("habilitado" => "1"));
    }

    /**
     * 
     * @param type $cliente_id
     * @return type
     */
    public function get_anuncios_para_cliente($cliente_id) {
        $cliente = $this->cliente_model->get($cliente_id);

        $params = array("estado" => "2", "usuario" => $cliente->usuario_id);
        $invitaciones_ids = $this->invitacion_model->get_ids_invitaciones($params);

        if (sizeof($invitaciones_ids) > 0) {
            $this->db->select("anuncio.*");
            $this->db->from($this->_table);
            $this->db->join("vendedor", "vendedor.id=anuncio.vendedor_id", 'INNER');
            $this->db->join("cliente", "cliente.id=vendedor.cliente_id", 'INNER');
            $this->db->join("usuario", "usuario.id=cliente.usuario_id", 'INNER');
            $this->db->where("anuncio.habilitado", "1");
            $this->db->where_in("usuario.id", $invitaciones_ids);
            $this->db->order_by("anuncio.fecha_publicacion", "desc");

            $this->db->limit(5, 0);
            $result = $this->db->get();

            if ($result->num_rows() > 0) {
                $anuncios = $result->result();
                if ($result->num_rows() < 5) {
                    $ids = array();
                    foreach ($anuncios as $anuncio) {
                        $ids[] = $anuncio->id;
                    }

                    $diff = 5 - $result->num_rows();

                    $this->db->select("anuncio.*");
                    $this->db->from($this->_table);
                    $this->db->where("habilitado", "1");
                    $this->db->where_not_in("anuncio.id", $ids);
                    $this->db->order_by("fecha_publicacion", "desc");
                    $this->db->limit($diff, 0);
                    $query = $this->db->get();

                    if ($query->num_rows() > 0) {
                        $anuncios_extra = $query->result();
                        $anuncios = array_merge($anuncios, $anuncios_extra);
                    }
                }
                return $anuncios;
            } else {
                return $this->get_ultimos_anuncios(5);
            }
        } else {
            return $this->get_ultimos_anuncios(5);
        }
    }

    /**
     * Full Delete de un anuncio
     * @param type $id
     */
    public function delete($id) {
        $this->visita_model->delete_by("anuncio_id", $id);
        parent::delete($id);
    }

    public function get_anuncios_del_vendedor($vendedor_id, $count = 5) {
        $this->db->select('*');
        $this->db->from('anuncio');
        $this->db->where("vendedor_id", $vendedor_id);
        $this->db->order_by("id", "desc");
        $this->db->limit($count, 0);
        $response = $this->db->get()->result();

        if ($response) {
            return $response;
        } else {
            return false;
        }
    }

    protected function pre_insertado($anuncio) {
        $paquete = $this->vendedor_model->get_paquete_en_curso($anuncio["vendedor_id"]);
        if ($paquete) {            
            $this->vendedor_paquete_model->update($paquete->id, array("anuncios_publicados" => (int) $paquete->anuncios_publicados + 1));
        }
        return $anuncio;
    }

}
