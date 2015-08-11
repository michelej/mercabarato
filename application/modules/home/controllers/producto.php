<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Producto extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     */
    public function view_principal() {
        $this->template->set_title('Mercabarato - Busca y Compara');
        $this->template->add_js('modules/home/producto_principal_listado.js');
        $subcategorias = $this->categoria_model->get_categorias_searchbar(0);
        $subcategorias_html = $this->_build_categorias_searchparams($subcategorias);
        $precios = precios_options();
        $paises = $this->pais_model->get_all();

        if ($this->authentication->is_loggedin()) {
            $user_id = $this->authentication->read('identifier');
            $cliente = $this->cliente_model->get_by("usuario_id", $user_id);
            $anuncios = $this->anuncio_model->get_anuncios_para_cliente($cliente->id);
        } else {
            $anuncios = $this->anuncio_model->get_ultimos_anuncios();
        }

        if (!$anuncios) {
            $anuncios = array();
        }

        $search_query = $this->session->userdata('search_query');
        $this->session->unset_userdata('search_query');

        $data = array(
            "productos" => array(),
            "anuncios" => $anuncios,
            "precios" => $precios,
            "subcategorias" => $subcategorias_html,
            "paises" => $paises,
            "search_query" => $search_query);

        $this->template->load_view('home/producto/listado_principal', $data);
    }

    /**
     * 
     * @param type $search_query
     */
    public function buscar_producto($search_query) {
        $this->session->unset_userdata('search_query');
        $query = urldecode($search_query);
        $this->session->set_userdata(array('search_query' => $query));
        redirect('');
    }

    /**
     *  AJAX Productos / Listado
     */
    public function ajax_get_listado_resultados() {
        if ($this->input->is_ajax_request()) {
            //$this->show_profiler();
            $formValues = $this->input->post();
            $params = array();
            if ($formValues !== false) {
                if ($this->input->post('search_query') != "") {
                    $params["nombre"] = $this->input->post('search_query');
                    $params["descripcion"] = $this->input->post('search_query');
                }

                if ($this->input->post('categoria_id') != "") {
                    $params["categoria_id"] = $this->input->post('categoria_id');
                }
                if ($this->input->post('precio_tipo1') !== "0") {
                    $params["precio_tipo1"] = $this->input->post('precio_tipo1');
                }
                if ($this->input->post('problacion') !== "0") {
                    $params["problacion"] = $this->input->post('problacion');
                }
                if ($this->input->post('provincia') !== "0") {
                    $params["provincia"] = $this->input->post('provincia');
                }
                if ($this->input->post('pais') !== "0") {
                    $params["pais"] = $this->input->post('pais');
                }

                if ($this->authentication->is_loggedin()) {
                    $user_id = $this->authentication->read('identifier');
                    $cliente = $this->cliente_model->get_by("usuario_id", $user_id);
                    $params["cliente_id"] = $cliente->id;
                    //if ($cliente->es_vendedor == '0') {
                    //}
                }
                if ($this->input->post('mostrar_solo_tarifas') == "true") {
                    $params["mostrar_solo_tarifas"] = true;
                }

                $params["habilitado"] = "1";
                $params["mostrar_producto"] = "1";

                if ($this->input->post('alt_layout')) {
                    $alt_layout = true;
                } else {
                    $alt_layout = false;
                }


                $pagina = $this->input->post('pagina');

                $limit = $this->config->item("principal_default_per_page");
                $offset = $limit * ($pagina - 1);
                $productos = $this->producto_model->get_site_search($params, $limit, $offset, "p.fecha_insertado", "DESC");
                $flt = (float) ($productos["total"] / $limit);
                $ent = (int) ($productos["total"] / $limit);
                if ($flt > $ent || $flt < $ent) {
                    $paginas = $ent + 1;
                } else {
                    $paginas = $ent;
                }

                if ($productos["total"] == 0) {
                    $productos["productos"] = array();
                }

                $search_params = array(
                    "anterior" => (($pagina - 1) < 1) ? -1 : ($pagina - 1),
                    "siguiente" => (($pagina + 1) > $paginas) ? -1 : ($pagina + 1),
                    "pagina" => $pagina,
                    "total_paginas" => $paginas,
                    "por_pagina" => $limit,
                    "total" => $productos["total"],
                    "hasta" => ($pagina * $limit < $productos["total"]) ? $pagina * $limit : $productos["total"],
                    "desde" => (($pagina * $limit) - $limit) + 1);

                $pagination = build_paginacion($search_params);
                $data = array(
                    "productos" => $productos["productos"],
                    "pagination" => $pagination);

                if ($alt_layout) {
                    $this->template->load_view('home/producto/tabla_resultados_principal', $data);
                } else {
                    $this->template->load_view('home/producto/tabla_resultados', $data);
                }
            }
        } else {
            redirect('');
        }
    }

    /**
     * 
     * @param type $id
     */
    public function ver_producto($slug) {
        $this->template->set_title('Mercabarato - Busca y Compara');
        $producto = $this->producto_model->get_by("unique_slug", $slug);
        if ($producto) {
            $this->template->add_js('modules/home/producto.js');

            $producto_imagen = $this->producto_resource_model->get_producto_imagen($producto->id);
            $producto_imagenes = $this->producto_resource_model->get_producto_imagenes($producto->id);
            $vendedor = $this->vendedor_model->get($producto->vendedor_id);
            $vendedor_cliente= $this->cliente_model->get($vendedor->cliente_id);
            
            $localizacion = $this->localizacion_model->get_by("usuario_id", $vendedor_cliente->usuario_id);

            if ($localizacion->pais_id != null) {
                $res = $this->pais_model->get($localizacion->pais_id);
                $localizacion->pais = $res->nombre;
            }
            if ($localizacion->provincia_id != null) {
                $res = $this->provincia_model->get($localizacion->provincia_id);
                $localizacion->provincia = $res->nombre;
            }
            if ($localizacion->poblacion_id != null) {
                $res = $this->poblacion_model->get($localizacion->poblacion_id);
                $localizacion->poblacion = $res->nombre;
            }
            
            /**
             * Usuario Loggedin
             */
            if ($this->authentication->is_loggedin()) {
                $this->visita_model->nueva_visita_producto($producto->id);
                $user_id = $this->authentication->read('identifier');
                $cliente = $this->cliente_model->get_by("usuario_id", $user_id);
                $producto_tarifa = $this->producto_model->get_tarifas_from_producto($producto->id, $cliente->id);
                if ($producto_tarifa) {
                    $tarifa = (float) $producto_tarifa->nuevo_costo;
                } else {
                    $tarifa = 0;
                }
                $producto_oferta = $this->producto_model->get_ofertas_from_producto($producto->id);
                if ($producto_oferta) {
                    $oferta = (float) $producto_oferta->nuevo_costo;
                    $params=array('oferta_general_id'=>$producto_oferta->grupo_o_tarifa_id);                    
                    $restricciones=$this->oferta_general_model->get_requisitos($params,0,2);
                    if($restricciones["total"]>0){
                        $oferta_id = $producto_oferta->grupo_o_tarifa_id;
                    }else{
                        $oferta_id = false;
                    }
                    
                    $fecha_finaliza=date("d-m-Y",strtotime($producto_oferta->fecha_finaliza));
                } else {
                    $oferta = 0;
                    $oferta_id= false;
                    $fecha_finaliza="";
                }


                $params = array(
                    "vendedor_id" => $producto->vendedor_id,
                    "cliente_id" => $cliente->id,
                    "excluir_producto_id" => array($producto->id)
                );
                $otros_productos = $this->producto_model->get_site_search($params, 4, 0, "p.fecha_insertado", "desc");
                if ($otros_productos["total"] > 0) {
                    $prods = $otros_productos["productos"];
                } else {
                    $prods = false;
                }

                $params2 = array(
                    "cliente_id" => $cliente->id,
                    "categoria_id" => $producto->categoria_id,
                    "excluir_producto_id" => array($producto->id)
                );
                $otros_productos_categoria = $this->producto_model->get_site_search($params2, 4, 0, "p.fecha_insertado", "desc");
                if ($otros_productos_categoria["total"] > 0) {
                    $prods2 = $otros_productos_categoria["productos"];
                } else {
                    $prods2 = false;
                }

                $cc = $this->cliente_model->get($vendedor->cliente_id);
                $son_contactos = $this->invitacion_model->son_contactos($user_id, $cc->usuario_id);
                
                $invitacion = $this->invitacion_model->invitacion_existe($user_id, $cc->usuario_id);

                $data = array(
                    "producto" => $producto,
                    "producto_imagen" => $producto_imagen,
                    "producto_imagenes" => $producto_imagenes,
                    "tarifa" => $tarifa,
                    "oferta" => $oferta,
                    "oferta_id"=>$oferta_id,
                    "otros_productos" => $prods,
                    "otros_productos_categoria" => $prods2,                    
                    "vendedor"=>$vendedor,
                    "son_contactos" => $son_contactos,
                    "invitacion" => $invitacion,
                    "fecha_finaliza"=>$fecha_finaliza,
                    "localizacion" => $localizacion,                    
                        );
                $this->template->load_view('home/producto/ficha', $data);
            } else {
            /**
             * Anonimo
             */    
                
                if ($producto->mostrar_producto == 1) {
                    $params = array(
                        "vendedor_id" => $producto->vendedor_id,
                        "excluir_producto_id" => array($producto->id)
                    );
                    $otros_productos = $this->producto_model->get_site_search($params, 4, 0, "p.fecha_insertado", "desc");
                    if ($otros_productos["total"] > 0) {
                        $prods = $otros_productos["productos"];
                    } else {
                        $prods = false;
                    }

                    $params = array(
                        "categoria_id" => $producto->categoria_id,
                        "excluir_producto_id" => array($producto->id)
                    );
                    $otros_productos_categoria = $this->producto_model->get_site_search($params, 4, 0, "p.fecha_insertado", "desc");
                    if ($otros_productos_categoria["total"] > 0) {
                        $prods2 = $otros_productos_categoria["productos"];
                    } else {
                        $prods2 = false;
                    }

                    $data = array(
                        "producto" => $producto,
                        "producto_imagen" => $producto_imagen,
                        "producto_imagenes" => $producto_imagenes,
                        "tarifa" => 0,
                        "oferta" => 0,
                        "oferta_id"=>false,
                        "otros_productos" => $prods,
                        "otros_productos_categoria" => $prods2,
                        "vendedor"=>$vendedor,
                        "invitacion" => true,
                        "son_contactos" => true,
                        "son_contactos" => false,
                        "localizacion" => $localizacion,
                        );
                    $this->template->load_view('home/producto/ficha', $data);
                } else {
                    show_404();
                }
            }
        } else {
            show_404();
        }
    }

    /**
     * 
     * @param type $categorias
     * @return string|boolean
     */
    private function _build_categorias_searchparams($categorias) {
        if (!empty($categorias)) {
            $html = "";
            foreach ($categorias as $categoria) {
                $texto = truncate_simple(fix_category_text($categoria['nombre']), 32);
                if (isset($categoria['subcategorias'])) {
                    $html.='<li class="seleccion_categoria">';
                    $html.='<a href="" data-id="' . $categoria['id'] . '">' . $texto;
                    if ($categoria['padre_id'] == '0') {
                        $html.='<span class="caret arrow"></span></a>';
                    } else {
                        $html.='<span class="caret arrow"></span></a>';
                    }

                    $res_html = $this->_build_categorias_searchparams($categoria['subcategorias']);
                    $html.='<ul class="nav nav-pills nav-stacked nav-sub-level">';
                    $html.=$res_html;
                    $html.='</ul>';
                } else {
                    $html.='<li class="seleccion_categoria final">';
                    $html.='<a href="" data-id="' . $categoria['id'] . '">' . $texto;
                    $html.='</a>';
                }
                $html.='</li>';
            }
            return $html;
        } else {
            return false;
        }
    }

    public function enviar_mensaje($producto_id) {
        if ($this->authentication->is_loggedin()) {
            $formValues = $this->input->post();
            $user_id = $this->authentication->read('identifier');
            $identidad = $this->usuario_model->get_full_identidad($user_id);
            $producto = $this->producto_model->get($producto_id);

            if ($formValues !== false) {
                $asunto = $this->input->post("asunto");
                $mensaje = $this->input->post("mensaje");
                $vendedor_cliente = $this->cliente_model->get($producto->cliente_id);
                $vendedor_usuario = $this->usuario_model->get($vendedor_cliente->usuario_id);

                if ($this->invitacion_model->son_contactos($vendedor_cliente->usuario_id, $user_id)) {
                    if ($this->config->item('emails_enabled')) {
                        $this->load->library('email');
                        $this->email->from($this->config->item('site_info_email'), 'Mercabarato.com');
                        $this->email->to($vendedor_usuario->email);
                        $this->email->subject('Tienes un nuevo mensaje');
                        $data_email = array("asunto" => $asunto, "mensaje" => $mensaje);
                        $this->email->message($this->load->view('home/emails/enviar_mensaje', $data_email, true));
                        $this->email->send();
                    }
                }
            } else {
                $this->template->set_title('Mercabarato - Busca y Compara');
                $data = array("producto" => $producto);                
                $this->template->load_view('home/producto/enviar_mensaje', $data);
            }
        } else {
            redirect('');
        }
    }
    
    public function ver_oferta_requisitos($oferta_general_id){
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Busca y Compara');            

            $oferta_general = $this->oferta_general_model->get($oferta_general_id);
            // TODO : Validar que yo pueda acceder a esta
            if ($oferta_general) {
                $requisitos=$this->requisito_visitas_model->get_many_by("oferta_general_id",$oferta_general_id);
                if($requisitos){
                    $productos_array=array();
                    foreach($requisitos as $requisito){
                        $productos_array[]=$this->producto_model->get_pp_by($requisito->producto_id);
                    }
                }
                //$this->template->add_js('modules/home/infocompras_seguros.js');
                $this->template->load_view('home/producto/oferta_restriccion', array("productos"=>$productos_array));
            } else {
                redirect('');
            }
        } else {
            redirect('');
        }
    }

}
