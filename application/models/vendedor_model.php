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
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM ";
        $query .= "(SELECT vendedor.*,usuario.email,usuario.ultimo_acceso,usuario.ip_address,usuario.activo,usuario.temporal ";

        $query.= "FROM vendedor ";
        $query.="INNER JOIN cliente ON cliente.id=vendedor.cliente_id ";
        $query.="INNER JOIN usuario ON usuario.id=cliente.usuario_id ";
        $query.="LEFT JOIN localizacion ON usuario.id=localizacion.usuario_id ";

        if (isset($params['autorizado_por'])) {
            $query.="INNER JOIN vendedor_paquete ON vendedor_paquete.vendedor_id=vendedor.id AND vendedor_paquete.aprobado ='1' ";
            $query.="AND vendedor_paquete.autorizado_por ='" . $params['autorizado_por'] . "' AND vendedor_paquete.fecha_terminar >='" . date("Y-m-d") . "'";
        }


        $query.="WHERE 1 ";

        if (isset($params['nombre'])) {
            $query.="AND vendedor.nombre LIKE '%" . $params['nombre'] . "%'";
        }
        if (isset($params['email'])) {
            $query.="AND usuario.email LIKE '%" . $params['email'] . "%'";
        }
        if (isset($params['sitio_web'])) {
            $query.="AND vendedor.sitio_web LIKE '%" . $params['sitio_web'] . "%'";
        }

        if (isset($params['poblacion'])) {
            $query .= " AND localizacion.poblacion_id='" . $params['poblacion'] . "' ";
        } elseif (isset($params['provincia'])) {
            $query .= " AND localizacion.provincia_id='" . $params['provincia'] . "' ";
        } elseif (isset($params['pais'])) {
            $query .= " AND localizacion.pais_id='" . $params['pais'] . "' ";
        }

        $query.=" ) p";


        $query.=" ORDER BY id DESC";
        $query.=" LIMIT " . $offset . " , " . $limit;

        $result = $this->db->query($query);
        $vendedores = $result->result();

        $query_total = "SELECT FOUND_ROWS() as rows;";
        $result_total = $this->db->query($query_total);
        $total = $result_total->row();

        if ($total->rows > 0) {
            return array("vendedores" => $vendedores, "total" => $total->rows);
        } else {
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

    public function get_vendedor_by_slug($slug) {
        $this->db->select("vendedor.*,cliente.usuario_id,usuario.email,usuario.ultimo_acceso,usuario.ip_address");
        $this->db->from($this->_table);
        $this->db->join("cliente", "cliente.id=vendedor.cliente_id", 'INNER');
        $this->db->join("usuario", "usuario.id=cliente.usuario_id", 'INNER');
        $this->db->where('vendedor.unique_slug', $slug);
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
        $vendedor = $this->get($id);
        $cliente = $this->cliente_model->get($vendedor->cliente_id);
        $usuario = $this->usuario_model->get($cliente->usuario_id);

        $data = array(
            "habilitado" => 1,
        );

        $this->update($id, $data);

        $permiso = $this->permisos_model->get_permiso_vendedor_afiliado();
        $this->usuario_model->update($usuario->id, array("permisos_id" => $permiso->id));
    }

    public function habilitar_productos($id) {        
        $vigente = $this->vendedor_model->get_paquete_en_curso($id);        
        if ($vigente) {            
            $productos = $this->producto_model->get_many_by("vendedor_id", $vigente->vendedor_id);
            $anuncios = $this->anuncio_model->get_many_by("vendedor_id", $vigente->vendedor_id);

            if (sizeof($productos) <= $vigente->limite_productos || $vigente->limite_productos == -1) {
                $this->producto_model->update_by(array('vendedor_id' => $vigente->vendedor_id), array('habilitado' => 1));
            } else {
                $this->producto_model->update_by(array('vendedor_id' => $vigente->vendedor_id), array('habilitado' => 0));
                $this->producto_model->habilitar_productos(array('vendedor_id' => $vigente->vendedor_id, "limit" => $vigente->limite_productos));
            }

            if (sizeof($anuncios) <= $vigente->limite_anuncios || $vigente->limite_anuncios == -1) {
                $this->anuncio_model->update_by(array('vendedor_id' => $vigente->vendedor_id), array('habilitado' => 1));
            } else {
                $this->anuncio_model->update_by(array('vendedor_id' => $vigente->vendedor_id), array('habilitado' => 0));
                $this->anuncio_model->habilitar_anuncios(array('vendedor_id' => $vigente->vendedor_id, "limit" => $vigente->limite_anuncios));
            }
        }
    }

    /**
     * Verificar si es posible asignarle un vendedor_paquete a este vendedor.
     * - Si tiene paquetes con aprobado=0 FALSE
     * - Si tiene paquetes aprobados en curso FALSE
     * @param type $vendedor_id
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
                } elseif ($paquete->fecha_terminar >= $hoy) {
                    $flag = false;
                }
            }
        }
        return $flag;
    }

    /**
     * 
     * @param type $vendedor_id
     * @return boolean
     */
    public function verificar_disponibilidad_renovacion($vendedor_id) {
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
                }
            }
        }
        return $flag;
    }

    /**
     * Retornar el vendedor_paquete que esta en curso
     * @param type $vendedor_id
     */
    public function get_paquete_en_curso($vendedor_id) {
        $this->db->select("*");
        $this->db->from('vendedor_paquete');
        $this->db->where('vendedor_id', $vendedor_id);
        $this->db->where('aprobado', '1');
        $this->db->where('fecha_terminar >=', date('Y-m-d'));
        $this->db->where('fecha_inicio <=', date('Y-m-d'));
        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->row();
        } else {
            return FALSE;
        }
    }

    /**
     * Devuelve la cantidad de productos disponibles por insertar
     * OJO Devuelve 1 si es un paquete ilimitado
     * @param type $vendedor_id
     */
    public function get_cantidad_productos_disp($vendedor_id) {
        $vendedor_paquete = $this->get_paquete_en_curso($vendedor_id);
        if ($vendedor_paquete) {
            if ($vendedor_paquete->limite_productos == -1) {
                return 1;
            } else {
                $this->db->select('id')->from('producto')->where('vendedor_id', $vendedor_id);
                $total = $this->db->count_all_results();
                return ($vendedor_paquete->limite_productos - $total);
            }
        } else {
            return 0;
        }
    }

    /**
     * Devuelve la cantidad de productos que falta por habilitar
     * Devuelve 1 si es ilimitado
     * @param type $vendedor_id
     * @return int
     */
    public function get_cantidad_productos_por_habilitar($vendedor_id) {
        $vendedor_paquete = $this->get_paquete_en_curso($vendedor_id);
        if ($vendedor_paquete) {
            if ($vendedor_paquete->limite_productos == -1) {
                return 1;
            } else {
                $this->db->select('id')->from('producto')->where('vendedor_id', $vendedor_id)->where('habilitado', '1');
                $total = $this->db->count_all_results();
                return ($vendedor_paquete->limite_productos - $total);
            }
        } else {
            return 0;
        }
    }

    /**
     * Devuelve la cantidad de anuncios disponibles por insertar
     * @param type $vendedor_id
     */
    public function get_cantidad_anuncios_disp($vendedor_id) {
        $vendedor_paquete = $this->get_paquete_en_curso($vendedor_id);
        if ($vendedor_paquete) {
            if ($vendedor_paquete->limite_anuncios == -1) {
                return 1;
            } else {
                /* $this->db->select('id')->from('anuncio')->where('vendedor_id', $vendedor_id);
                  $total = $this->db->count_all_results();
                  return ($vendedor_paquete->limite_anuncios - $total); */
                return ($vendedor_paquete->limite_anuncios - $vendedor_paquete->anuncios_publicados);
            }
        } else {
            return 0;
        }
    }

    /**
     * Devuelve la cantidad de anuncios disponibles por habilitar
     * Devielve 1 si es ilimitado
     * @param type $vendedor_id
     * @return int
     */
    public function get_cantidad_anuncios_por_habilitar($vendedor_id) {
        $vendedor_paquete = $this->get_paquete_en_curso($vendedor_id);
        if ($vendedor_paquete) {
            if ($vendedor_paquete->limite_anuncios == -1) {
                return 1;
            } else {
                $this->db->select('id')->from('anuncio')->where('vendedor_id', $vendedor_id)->where('habilitado', '1');
                $total = $this->db->count_all_results();
                return ($vendedor_paquete->limite_anuncios - $total);
            }
        } else {
            return 0;
        }
    }

    /**
     * Retornar el vendedor_paquete que esta pendiente por aprobacion, solo deberia existir uno
     * @param type $vendedor_id
     */
    public function get_paquete_pendiente($vendedor_id) {
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

    /**
     * 
     * @param type $vendedor_id
     * @return boolean
     */
    public function get_paquete_renovacion($vendedor_id) {
        $this->db->select("*");
        $this->db->from('vendedor_paquete');
        $this->db->where('vendedor_id', $vendedor_id);
        $this->db->where('aprobado', '1');
        $this->db->where('fecha_inicio >', date("Y-m-d"));
        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->row();
        } else {
            return FALSE;
        }
    }

    /**
     * 
     * @param type $params
     * @param type $limit
     * @param type $offset
     * @return type
     */
    public function get_site_search($params, $limit, $offset) {
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM "
                . "(SELECT vendedor.*,cliente.usuario_id,usuario.email,usuario.ultimo_acceso,usuario.ip_address, "
                . "pais.nombre as pais,provincia.nombre as provincia,poblacion.nombre as poblacion,i1.id as invitacion_id1,i2.id as invitacion_id2 ";

        if (isset($params['keyword'])) {
            $keywords = explode(";", $params["keyword"]);
            $query.=", (";
            foreach ($keywords as $keyword) {
                $query.="MATCH (keywords) AGAINST ('" . $keyword . "' IN BOOLEAN MODE) + ";
            }
            $query = substr($query, 0, -3);
            $query.=" ) as relevance ";
        }


        $query.= "FROM vendedor ";
        $query.="LEFT JOIN keyword ON vendedor.keyword=keyword.id ";
        $query.="INNER JOIN cliente ON cliente.id=vendedor.cliente_id ";
        $query.="INNER JOIN usuario ON usuario.id=cliente.usuario_id ";
        $query.="LEFT JOIN localizacion ON usuario.id=localizacion.usuario_id ";
        $query.="LEFT JOIN pais ON pais.id=localizacion.pais_id ";
        $query.="LEFT JOIN provincia ON provincia.id=localizacion.provincia_id ";
        $query.="LEFT JOIN poblacion ON poblacion.id=localizacion.poblacion_id ";
        $query.="INNER JOIN vendedor_paquete ON vendedor_paquete.vendedor_id=vendedor.id AND vendedor_paquete.aprobado ='1' ";
        $query.="AND vendedor_paquete.fecha_terminar >='" . date("Y-m-d") . "'";


        if (isset($params['usuario_id'])) {
            $query.="LEFT JOIN invitacion i1 ON i1.invitar_desde=usuario.id AND i1.invitar_para=" . $params['usuario_id'] . " ";
            $query.="LEFT JOIN invitacion i2 ON i2.invitar_para=usuario.id AND i2.invitar_desde=" . $params['usuario_id'] . " ";
        } else {
            $query.="LEFT JOIN invitacion i1 ON i1.invitar_desde=usuario.id AND i1.invitar_para='0' ";
            $query.="LEFT JOIN invitacion i2 ON i2.invitar_para=usuario.id AND i2.invitar_desde='0' ";
        }

        $query.="WHERE vendedor.habilitado='1' ";

        if (isset($params['nombre'])) {
            $query.="AND vendedor.nombre LIKE '%" . $params['nombre'] . "%'";
        }
        if (isset($params['descripcion'])) {
            $query.="OR vendedor.descripcion LIKE '%" . $params['descripcion'] . "%'";
        }

        if (isset($params['infocompra_general'])) {
            $query.="AND vendedor_paquete.limite_productos!='0'";
        }

        if (isset($params['poblacion'])) {
            $query .= " AND localizacion.poblacion_id='" . $params['poblacion'] . "' ";
        } elseif (isset($params['provincia'])) {
            $query .= " AND localizacion.provincia_id='" . $params['provincia'] . "' ";
        } elseif (isset($params['pais'])) {
            $query .= " AND localizacion.pais_id='" . $params['pais'] . "' ";
        }

        if (isset($params['not_vendedor'])) {
            if (is_array($params['not_vendedor'])) {
                $kk = implode(",", $params['not_vendedor']);
                $query .=" AND vendedor.id NOT IN(" . $kk . ") ";
            }
        }

        if (isset($params['solo_vendedor'])) {
            if (is_array($params['solo_vendedor'])) {
                $kk = implode(",", $params['solo_vendedor']);
                $query .=" AND vendedor.id IN(" . $kk . ") ";
            }
        }


        /* if (isset($params['paquete_vigente'])) {
          $this->db->where('vendedor_paquete.aprobado', '1');
          $this->db->where('vendedor_paquete.fecha_terminar >=', date('Y-m-d'));
          } */

        $query.=" ) p";

        if (isset($params['keyword'])) {
            $query.=" ORDER BY relevance DESC";
        } else {
            $query.=" ORDER BY id DESC";
        }

        if ($limit) {
            $query.=" LIMIT " . $offset . " , " . $limit;
        }


        $result = $this->db->query($query);
        $vendedores = $result->result();

        $query_total = "SELECT FOUND_ROWS() as rows;";
        $result_total = $this->db->query($query_total);
        $total = $result_total->row();

        if ($total->rows > 0) {
            if (sizeof($vendedores) == 0) {
                return $this->get_site_search($params, $limit, 0);
            } else {
                return array("vendedores" => $vendedores, "total" => $total->rows);
            }
        } else {
            return array("total" => 0);
        }
    }

    /**
     * 
     * @param type $vendedor_id
     */
    public function cleanup_image($vendedor_id) {
        $vendedor = $this->get($vendedor_id);
        if ($vendedor->filename != null) {
            unlink('./assets/' . $this->config->item('vendedores_img_path') . '/' . $vendedor->filename);
            unlink('./assets/' . $this->config->item('vendedores_img_path') . '/thumbnail/' . $vendedor->filename);
        }
        $this->update($vendedor_id, array("filename" => null));
    }

    /**
     * FULL DELETE
     * @param type $id
     */
    public function delete($id) {
        $vendedor = $this->get($id);
        if ($vendedor) {
            $cliente = $this->cliente_model->get($vendedor->cliente_id);
            $this->punto_venta_model->delete_by("vendedor_id", $id);
            $this->invitacion_model->delete_by("invitar_desde", $cliente->usuario_id);
            $this->invitacion_model->delete_by("invitar_para", $cliente->usuario_id);

            $seguros = $this->infocompra_model->get_many_by("vendedor_id", $id);
            if ($seguros) {
                foreach ($seguros as $seguro) {
                    $this->infocompra_model->delete($seguro->id);
                }
            }

            $this->vendedor_paquete_model->delete_by("vendedor_id", $id);

            $anuncios = $this->anuncio_model->get_many_by("vendedor_id", $id);
            if ($anuncios) {
                foreach ($anuncios as $anuncio) {
                    $this->anuncio_model->delete($anuncio->id);
                }
            }
            $productos = $this->producto_model->get_many_by("vendedor_id", $id);
            if ($productos) {
                foreach ($productos as $producto) {
                    $this->producto_model->delete($producto->id);
                }
            }

            parent::delete($id);
            $this->cliente_model->delete($vendedor->cliente_id);
            $this->usuario_model->delete($cliente->usuario_id);
        } else {
            return false;
        }
    }

    /**
     * Inhabilitar al vendedor y a todos sus productos
     * @param type $vendedor_id
     */
    public function inhabilitar($vendedor_id) {
        $vendedor = $this->get($vendedor_id);
        if ($vendedor) {
            $this->vendedor_model->update($vendedor_id, array("habilitado" => "0"));
            $this->producto_model->update_by(array("vendedor_id" => $vendedor_id), array("habilitado" => "0"));
            $this->anuncio_model->update_by(array("vendedor_id" => $vendedor_id), array("habilitado" => "0"));
        }
    }

    public function get_email($vendedor_id) {
        $this->db->select("usuario.email");
        $this->db->from($this->_table);
        $this->db->join("cliente", "cliente.id=vendedor.cliente_id", 'INNER');
        $this->db->join("usuario", "usuario.id=cliente.usuario_id", 'INNER');
        $this->db->where("vendedor.id", $vendedor_id);

        $result = $this->db->get();

        if ($result->num_rows() > 0) {
            return $result->row()->email;
        } else {
            return FALSE;
        }
    }

    public function es_vendedor_habilitado($vendedor_id) {
        $this->db->where('id', $vendedor_id);
        $query = $this->db->get('vendedor');
        if ($query->num_rows() > 0) {
            $vendedor = $query->result();
            if ($vendedor->habilitado == "1") {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function get_vendedor_from_usuario_ids($usuario_ids) {
        $this->db->select('vendedor.id');
        $this->db->from($this->_table);
        $this->db->join("cliente", "cliente.id=vendedor.cliente_id", "inner");

        foreach ($usuario_ids as $id) {
            $this->db->or_where('cliente.usuario_id', $id);
        }
        $vendedores = $this->db->get()->result();
        $ids = array();
        if (sizeof($vendedores) > 0) {
            foreach ($vendedores as $vendedor) {
                $ids[] = $vendedor->id;
            }
        }
        return $ids;
    }

}
