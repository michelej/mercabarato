<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Vendedor extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     *  Afiliarse - Paso 1 
     */
    public function view_afiliarse() {
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Anuncios y subastas');
            $user_id = $this->authentication->read('identifier');
            $cliente = $this->cliente_model->get_by("usuario_id", $user_id);

            if (!$this->cliente_model->es_vendedor($cliente->id)) {
                $this->session->unset_userdata('afiliacion_cliente');
                $this->session->unset_userdata('afiliacion_vendedor');

                $cliente_es_vendedor = $this->cliente_model->es_vendedor($cliente->id);
                $html_options = $this->load->view('home/partials/panel_opciones', array("es_vendedor" => $cliente_es_vendedor), true);
                $this->template->add_js('modules/home/perfil.js');
                $this->template->load_view('home/vendedor/afiliarse', array("cliente" => $cliente, "html_options" => $html_options));
            } else {
                redirect('usuario/perfil');
            }
        } else {
            redirect('');
        }
    }

    /**
     *  Afiliarse - Recibe Paso 1
     */
    public function cliente_a_vendedor() {
        $formValues = $this->input->post();

        if ($formValues !== false) {
            $user_id = $this->authentication->read('identifier');
            $cliente = $this->cliente_model->get_by("usuario_id", $user_id);

            $data = array(
                "cliente_id" => $cliente->id,
                "nombre" => $this->input->post('nombre_empresa'),
                "descripcion" => $this->input->post('descripcion'),
                "actividad" => $this->input->post('actividad'),
                "sitio_web" => $this->input->post('sitio_web'),
                "habilitado" => 0
            );

            $data_cliente = array(
                "direccion" => $this->input->post('direccion'),
                "telefono_fijo" => $this->input->post('telefono_fijo'),
                "telefono_movil" => $this->input->post('telefono_movil')
            );

            $this->session->unset_userdata('afiliacion_cliente');
            $this->session->unset_userdata('afiliacion_vendedor');

            $this->session->set_userdata(array(
                'afiliacion_cliente' => $data_cliente,
                'afiliacion_vendedor' => $data,
            ));

            redirect('usuario/afiliacion-paso2');
        } else {
            redirect('usuario/perfil');
        }
    }

    /**
     *  Afiliarse - Paso 2
     */
    public function view_seleccionar_paquete() {
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Anuncios y subastas');
            $user_id = $this->authentication->read('identifier');
            $cliente = $this->cliente_model->get_by("usuario_id", $user_id);

            if (!$this->cliente_model->es_vendedor($cliente->id)) {
                $paquetes = $this->paquete_model->get_paquetes();
                $cliente_es_vendedor = $this->cliente_model->es_vendedor($cliente->id);

                $html_options = $this->load->view('home/partials/panel_opciones', array("es_vendedor" => $cliente_es_vendedor), true);
                $this->template->add_js('modules/home/perfil.js');
                $this->template->load_view('home/vendedor/seleccion_paquete', array("cliente" => $cliente, "paquetes" => $paquetes, "html_options" => $html_options));
            } else {
                redirect('usuario/perfil');
            }
        } else {
            redirect('');
        }
    }

    /**
     *  Afiliarse - Recibe Paso 2
     * @param type $paquete_id
     */
    public function submit_afiliacion($paquete_id) {
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Anuncios y subastas');

            if ($this->paquete_model->validar_paquete($paquete_id)) {
                $user_id = $this->authentication->read('identifier');
                $cliente = $this->cliente_model->get_by("usuario_id", $user_id);

                $data_cliente = $this->session->userdata('afiliacion_cliente');
                $data_vendedor = $this->session->userdata('afiliacion_vendedor');
                $this->cliente_model->update($cliente->id, $data_cliente);

                $vendedor = $this->vendedor_model->get_by("cliente_id", $cliente->id);
                if (!$vendedor) {
                    $vendedor_id = $this->vendedor_model->insert($data_vendedor);
                } else {
                    $this->vendedor_model->update($vendedor->id, $data_vendedor);
                    $vendedor_id = $vendedor->id;
                }


                $this->session->unset_userdata('afiliacion_cliente');
                $this->session->unset_userdata('afiliacion_vendedor');

                $paquete = $this->paquete_model->get($paquete_id);
                $data = array(
                    "vendedor_id" => $vendedor_id,
                    "nombre_paquete" => $paquete->nombre,
                    "duracion_paquete" => $paquete->duracion,
                    "fecha_comprado" => date("Y-m-d"),
                    "fecha_terminar" => null,
                    "fecha_aprobado" => null,
                    "referencia" => "",
                    "limite_productos" => $paquete->limite_productos,
                    "limite_anuncios" => $paquete->limite_anuncios,
                    "monto_a_cancelar" => $paquete->costo,
                    "aprobado" => 0
                );
                // TODO: (NUEVA AFILIACION) Enviar correo a mercabarato con la informacion de compra y enviarle un correo al email del cliente
                $this->vendedor_paquete_model->insert($data);
                redirect('usuario/completado');
            } else {
                redirect('usuario/afiliacion-paso2');
            }
        } else {
            redirect('');
        }
    }

    /**
     * Afiliarse - Completado
     */
    public function view_completado() {
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Anuncios y subastas');
            $user_id = $this->authentication->read('identifier');
            $cliente = $this->cliente_model->get_by("usuario_id", $user_id);

            $cliente_es_vendedor = $this->cliente_model->es_vendedor($cliente->id);
            $html_options = $this->load->view('home/partials/panel_opciones', array("es_vendedor" => $cliente_es_vendedor), true);
            $this->template->load_view('home/vendedor/completado', array("cliente" => $cliente, "html_options" => $html_options));
        } else {
            redirect('');
        }
    }

    public function ir_panel_vendedor() {
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Anuncios y subastas');
            $user_id = $this->authentication->read('identifier');
            $cliente = $this->cliente_model->get_by("usuario_id", $user_id);

            if ($this->cliente_model->es_vendedor($cliente->id)) {
                $vendedor = $this->vendedor_model->get_by("cliente_id", $cliente->id);

                $html_options = $this->load->view('home/partials/panel_opciones', array("es_vendedor" => true), true);

                $data = array(
                    "cliente" => $cliente,
                    "vendedor" => $vendedor,
                    "html_options" => $html_options
                );
                $this->template->add_js('modules/home/perfil.js');
                $this->template->load_view('home/vendedor/pre_panel', $data);
            } else {
                redirect('usuario/perfil');
            }
        } else {
            redirect('');
        }
    }

    public function mis_paquetes() {
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Anuncios y subastas');
            $user_id = $this->authentication->read('identifier');
            $cliente = $this->cliente_model->get_by("usuario_id", $user_id);

            if ($this->cliente_model->es_vendedor($cliente->id)) {
                $vendedor = $this->vendedor_model->get_by("cliente_id", $cliente->id);
                $vendedor_paquetes = $this->vendedor_paquete_model->get_paquetes_por_vendedor($vendedor->id);

                $html_options = $this->load->view('home/partials/panel_opciones', array("es_vendedor" => true), true);

                $data = array(
                    "cliente" => $cliente,
                    "vendedor" => $vendedor,
                    "vendedor_paquetes" => $vendedor_paquetes,
                    "html_options" => $html_options
                );
                $this->template->add_js('modules/home/perfil.js');
                $this->template->load_view('home/vendedor/paquetes', $data);
            } else {
                redirect('usuario/perfil');
            }
        } else {
            redirect('');
        }
    }

    public function comprar_paquetes() {
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Anuncios y subastas');
            $user_id = $this->authentication->read('identifier');
            $cliente = $this->cliente_model->get_by("usuario_id", $user_id);

            if ($this->cliente_model->es_vendedor($cliente->id)) {
                $vendedor = $this->vendedor_model->get_by("cliente_id", $cliente->id);
                $paquetes = $this->paquete_model->get_paquetes();

                $cliente_es_vendedor = $this->cliente_model->es_vendedor($cliente->id);
                $html_options = $this->load->view('home/partials/panel_opciones', array("es_vendedor" => $cliente_es_vendedor), true);

                $puede_comprar = $this->vendedor_model->verificar_disponibilidad($vendedor->id);

                $data = array(
                    "html_options" => $html_options,
                    "cliente" => $cliente,
                    "paquetes" => $paquetes,
                    "puede_comprar" => $puede_comprar
                );
                $this->template->add_js('modules/home/perfil.js');
                $this->template->load_view('home/vendedor/compra_paquete', $data);
            } else {
                redirect('usuario/perfil');
            }
        } else {
            redirect('');
        }
    }

    public function submit_comprar_paquetes($paquete_id) {
        if ($this->authentication->is_loggedin()) {
            $this->template->set_title('Mercabarato - Anuncios y subastas');

            if ($this->paquete_model->validar_paquete($paquete_id)) {
                $user_id = $this->authentication->read('identifier');
                $cliente = $this->cliente_model->get_by("usuario_id", $user_id);
                $vendedor = $this->vendedor_model->get_by("cliente_id", $cliente->id);

                $paquete = $this->paquete_model->get($paquete_id);
                $data = array(
                    "vendedor_id" => $vendedor->id,
                    "nombre_paquete" => $paquete->nombre,
                    "duracion_paquete" => $paquete->duracion,
                    "fecha_comprado" => date("Y-m-d"),
                    "fecha_terminar" => null,
                    "fecha_aprobado" => null,
                    "referencia" => "",
                    "limite_productos" => $paquete->limite_productos,
                    "limite_anuncios" => $paquete->limite_anuncios,
                    "monto_a_cancelar" => $paquete->costo,
                    "aprobado" => 0
                );
                $result = $this->vendedor_model->verificar_disponibilidad($vendedor->id);
                if ($result) {
                    // TODO: (NUEVA COMPRA DE PAQUETE) Enviar correo a mercabarato con la informacion de compra y enviarle un correo al email del cliente                                                
                    $this->vendedor_paquete_model->insert($data);
                }
                redirect('usuario/mis-paquetes');
            } else {
                redirect('usuario/perfil');
            }
        } else {
            redirect('');
        }
    }

    /*     
     * 
     */

    public function view_buscador() {
        $this->template->set_title('Mercabarato - Anuncios y subastas');
        $this->template->add_js('modules/home/vendedores_listado.js');
        $paises=$this->pais_model->get_all();
        $data=array("paises"=>$paises);
        
        $this->template->load_view('home/vendedores/listado',$data);
    }

    /**
     * 
     */
    public function ajax_get_listado_resultados() {
        //$this->show_profiler();
        $formValues = $this->input->post();

        $params = array();
        if ($formValues !== false) {
            if ($this->input->post('search_query') != "") {
                $params["nombre"] = $this->input->post('search_query');
                $params["descripcion"] = $this->input->post('search_query');
            }
            if ($this->input->post('pais') != "0") {
                $params["pais"] = $this->input->post('pais');                
            }
            if ($this->input->post('provincia') != "0") {
                $params["provincia"] = $this->input->post('provincia');                
            }
            if ($this->input->post('poblacion') != "0") {
                $params["poblacion"] = $this->input->post('poblacion');                
            }

            $pagina = $this->input->post('pagina');
        } else {
            $pagina = 1;
        }

        //$limit = $this->config->item("principal_default_per_page");
        $limit = 5;
        $offset = $limit * ($pagina - 1);
        $vendedores_array = $this->vendedor_model->get_site_search($params, $limit, $offset);
        $flt = (float) ($vendedores_array["total"] / $limit);
        $ent = (int) ($vendedores_array["total"] / $limit);
        if ($flt > $ent || $flt < $ent) {
            $paginas = $ent + 1;
        } else {
            $paginas = $ent;
        }

        if ($vendedores_array["total"] == 0) {
            $vendedores_array["vendedores"] = array();
        }

        $search_params = array(
            "anterior" => (($pagina - 1) < 1) ? -1 : ($pagina - 1),
            "siguiente" => (($pagina + 1) > $paginas) ? -1 : ($pagina + 1),
            "pagina" => $pagina,
            "total_paginas" => $paginas,
            "por_pagina" => $limit,
            "total" => $vendedores_array["total"],
            "hasta" => ($pagina * $limit < $vendedores_array["total"]) ? $pagina * $limit : $vendedores_array["total"],
            "desde" => (($pagina * $limit) - $limit) + 1);
        $pagination = build_paginacion($search_params);

        $data = array(
            "vendedores" => $vendedores_array["vendedores"],
            "pagination" => $pagination);

        $this->template->load_view('home/vendedores/tabla_resultados', $data);
    }

    
     public function ver_vendedor($id) {
        $this->template->set_title('Mercabarato - Anuncios y subastas');
        $vendedor = $this->vendedor_model->get_vendedor($id);
        $localizacion = $this->localizacion_model->get_by("usuario_id",$vendedor->usuario_id);              
        
        if($localizacion->pais_id!=null){
            $res=$this->pais_model->get($localizacion->pais_id);
            $localizacion->pais=$res->nombre;
        }
        if($localizacion->provincia_id!=null){
            $res=$this->provincia_model->get($localizacion->provincia_id);
            $localizacion->provincia=$res->nombre;
        }
        if($localizacion->poblacion_id!=null){
            $res=$this->poblacion_model->get($localizacion->poblacion_id);
            $localizacion->poblacion=$res->nombre;
        }
        
        $vendedor_image = false;
        
        
        $data = array(
            "vendedor" => $vendedor,
            "vendedor_image"=>$vendedor_image,
            "localizacion" => $localizacion);        
        
        $this->template->load_view('home/vendedores/ficha', $data);
    }
}
