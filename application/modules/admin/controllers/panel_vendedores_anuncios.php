<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Panel_vendedores_anuncios extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->_validar_conexion();
    }

    /**
     * 
     */
    public function agregar() {
        $user_id = $this->authentication->read('identifier');
        $vendedor = $this->usuario_model->get_full_identidad($user_id);
        $cantidad = $this->vendedor_model->get_cantidad_anuncios_disp($vendedor["vendedor"]->id);

        if ($cantidad > 0) {
            $formValues = $this->input->post();
            if ($formValues !== false) {
                $accion = $this->input->post('accion');

                if ($accion === "item-crear") {
                    $data = array(
                        "titulo" => $this->input->post('titulo'),
                        "contenido" => $this->input->post('contenido'),
                        "fecha_publicacion" => date("Y-m-d H:i:s"),
                        "destacada" => 0,
                        "vendedor_id" => $vendedor["vendedor"]->id,
                        "imagen" => null,
                    );

                    $this->anuncio_model->insert($data);
                    redirect('panel_vendedor/anuncio/listado');
                } else {
                    redirect('panel_vendedor');
                }
            } else {
                $this->template->set_title("Panel de Control - Mercabarato.com");
                $this->template->set_layout('panel_vendedores');
                //$this->template->add_js("fileupload.js");
                //$this->template->add_js("modules/admin/panel_vendedores/productos.js");

                $this->template->load_view('admin/panel_vendedores/anuncio/anuncio_agregar');
            }
        } else {
            $this->template->set_title("Panel de Control - Mercabarato.com");
            $this->template->set_layout('panel_vendedores');
            $this->template->load_view('admin/panel_vendedores/anuncio/anuncio_limite');
        }
    }

    /**
     * 
     * @param type $id
     */
    public function editar($id) {
        $user_id = $this->authentication->read('identifier');
        $vendedor = $this->usuario_model->get_full_identidad($user_id);
        $anuncio_id = $id;

        $res = $this->anuncio_model->get_vendedor_id_del_anuncio($anuncio_id);
        if ($res == $vendedor["vendedor"]->id) {
            $formValues = $this->input->post();
            if ($formValues !== false) {
                $accion = $this->input->post('accion');

                if ($accion === "item-editar") {
                    $data = array(
                        "titulo" => $this->input->post('titulo'),
                        "contenido" => $this->input->post('contenido'),
                    );

                    $this->anuncio_model->update($anuncio_id, $data);
                    $this->session->set_flashdata('success', 'Anuncio modificado con exito');
                    redirect('panel_vendedor/anuncio/listado');
                } else {
                    redirect('panel_vendedor');
                }
            } else {
                $anuncio = $this->anuncio_model->get($id);
                if ($anuncio) {
                    $this->template->set_title("Panel de Administracion - Mercabarato.com");
                    //$this->template->add_js("modules/admin/panel_vendedores/productos.js");
                    //$categorias = $this->categoria_model->get_all();
                    $vendedor = $this->vendedor_model->get($anuncio->vendedor_id);

                    $data = array(
                        "anuncio" => $anuncio);


                    $this->template->set_layout('panel_vendedores');
                    $this->template->load_view('admin/panel_vendedores/anuncio/anuncio_editar', $data);
                } else {
                    redirect('panel_vendedor/anuncio/listado');
                }
            }
        } else {            
            redirect('panel_vendedor/anuncio/listado');
        }
    }

    /**
     * 
     */
    public function listado() {
        $this->template->set_title("Panel de Control - Mercabarato.com");
        $this->template->set_layout('panel_vendedores');
        $this->template->add_js("modules/admin/panel_vendedores/anuncios_listado.js");
        $this->template->load_view('admin/panel_vendedores/anuncio/anuncio_listado');
    }

    /**
     * 
     */
    public function borrar($id) {
        if ($this->input->is_ajax_request()) {
            $user_id = $this->authentication->read('identifier');
            $vendedor = $this->usuario_model->get_full_identidad($user_id);
            $anuncio_id = $id;

            $res = $this->anuncio_model->get_vendedor_id_del_anuncio($anuncio_id);
            if ($res == $vendedor["vendedor"]->id) {
                $this->anuncio_model->delete($id);
                redirect('panel_vendedor/anuncio/listado');
            } else {                
                redirect('panel_vendedor/anuncio/listado');
            }
        } else {
            redirect('404');
        }
    }

    /**
     * 
     */
    public function ajax_get_listado_resultados() {
        $formValues = $this->input->post();

        $params = array();
        if ($formValues !== false) {
            if ($this->input->post('titulo') != "") {
                $params["titulo"] = $this->input->post('titulo');
            }
            $user_id = $this->authentication->read('identifier');
            $vendedor = $this->usuario_model->get_full_identidad($user_id);
            $params["vendedor_id"] = $vendedor["vendedor"]->id;
            $pagina = $this->input->post('pagina');
        } else {
            $pagina = 1;
        }

        $limit = $this->config->item("admin_default_per_page");
        $offset = $limit * ($pagina - 1);
        $anuncios_array = $this->anuncio_model->get_admin_search($params, $limit, $offset);
        $flt = (float) ($anuncios_array["total"] / $limit);
        $ent = (int) ($anuncios_array["total"] / $limit);
        if ($flt > $ent || $flt < $ent) {
            $paginas = $ent + 1;
        } else {
            $paginas = $ent;
        }        

        if ($anuncios_array["total"] == 0) {
            $anuncios_array["anuncios"] = array();            
        }
        $data = array(
            "anuncios" => $anuncios_array["anuncios"],
            "search_params" => array(
                "anterior" => (($pagina - 1) < 1) ? -1 : ($pagina - 1),
                "siguiente" => (($pagina + 1) > $paginas) ? -1 : ($pagina + 1),
                "pagina" => $pagina,
                "total_paginas" => $paginas,
                "por_pagina" => $limit,
                "total" => $anuncios_array["total"],
                "hasta" => ($pagina * $limit < $anuncios_array["total"]) ? $pagina * $limit : $anuncios_array["total"],
                "desde" => (($pagina * $limit) - $limit) + 1));

        $this->template->load_view('admin/panel_vendedores/anuncio/anuncio_tabla_resultados', $data);
    }

}