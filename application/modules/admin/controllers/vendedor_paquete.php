<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Vendedor_paquete extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->authentication->is_loggedin()) {
            redirect('admin/login');
        } else {
            if (!$this->authentication->user_is_admin()) {
                redirect('admin/sin_permiso');
            }
        }
    }

    /**
     *  Listado
     */
    public function view_listado_por_activar() {
        $this->template->set_title("Panel de Administracion - Mercabarato.com");
        $this->template->add_js("modules/admin/vendedor_paquete_listado.js");
        $this->template->load_view('admin/vendedor_paquete/listado_por_activar');
    }

    /**
     * 
     * @param type $id
     */
    public function aprobar($id) {
        $vendedor_paquete=$this->vendedor_paquete_model->get($id);        
        $this->vendedor_paquete_model->aprobar_paquete($id);
        $this->vendedor_model->habilitar_vendedor($vendedor_paquete->vendedor_id);
        $this->session->set_flashdata('success', 'El paquete ha sido aprobado y el Vendedor habilitado.');
        redirect('admin/vendedor_paquetes/listado_por_activar');
    }

    /**
     *  AJAX Productos / Listado
     */
    public function ajax_get_listado_resultados() {
        $formValues = $this->input->post();

        $params = array();
        if ($formValues !== false) {
            if ($this->input->post('nombre') != "") {
                $params["nombre_empresa"] = $this->input->post('nombre');
            }
            if ($this->input->post('email') != "") {
                $params["email"] = $this->input->post('email');
            }
            if ($this->input->post('sitioweb') != "") {
                $params["sitioweb"] = $this->input->post('sitioweb');
            }
            if ($this->input->post('actividad') != "No Especificada") {
                $params["actividad"] = $this->input->post('actividad');
            }
            $pagina = $this->input->post('pagina');
        } else {
            $pagina = 1;
        }

        $limit = $this->config->item("admin_default_per_page");
        $offset = $limit * ($pagina - 1);
        $vendedor_paquetes_array = $this->vendedor_paquete_model->get_admin_search($params, $limit, $offset);
        $flt = (float) ($vendedor_paquetes_array["total"] / $limit);
        $ent = (int) ($vendedor_paquetes_array["total"] / $limit);
        if ($flt > $ent || $flt < $ent) {
            $paginas = $ent + 1;
        } else {
            $paginas = $ent;
        }
        // TODO: Falta testear mas

        if ($vendedor_paquetes_array["total"] == 0) {
            $vendedor_paquetes_array["vendedor_paquetes"] = array();
            // TODO: Resultados vacio
        }
        $data = array(
            "vendedor_paquetes" => $vendedor_paquetes_array["vendedor_paquetes"],
            "search_params" => array(
                "anterior" => (($pagina - 1) < 1) ? -1 : ($pagina - 1),
                "siguiente" => (($pagina + 1) > $paginas) ? -1 : ($pagina + 1),
                "pagina" => $pagina,
                "total_paginas" => $paginas,
                "por_pagina" => $limit,
                "total" => $vendedor_paquetes_array["total"],
                "hasta" => ($pagina * $limit < $vendedor_paquetes_array["total"]) ? $pagina * $limit : $vendedor_paquetes_array["total"],
                "desde" => (($pagina * $limit) - $limit) + 1));

        $this->template->load_view('admin/vendedor_paquete/tabla_resultados', $data);
    }

    public function ajax_get_paquete_info() {
        if ($this->input->is_ajax_request()) {
            $formValues = $this->input->post();
            if ($formValues !== false) {
                $vendedor_paquete_id = $this->input->post("vendedor_paquete_id");
                $vendedor_paquete = $this->vendedor_paquete_model->get($vendedor_paquete_id);
                $paquete = $this->paquete_model->get($vendedor_paquete->paquete_id);
                $vendedor = $this->vendedor_model->get($vendedor_paquete->vendedor_id);
                $cliente = $this->cliente_model->get($vendedor->cliente_id);
                $usuario = $this->usuario_model->get($cliente->usuario_id);
                
                $html='<table class="table table-bordered table-hover table-striped">';
                $html.='<thead>';
                $html.='<tr>';                
                $html.='<th style="width: 15%">Vendedor / Empresa</th>';
                $html.='<th style="width: 15%">Email</th>';
                $html.='<th style="width: 15%">Monto a Cancelar</th>';
                $html.='<th style="width: 15%">Paquete</th>';                                
                $html.='</tr>';
                $html.='</thead>';
                $html.='<tbody>';            
                $html.='<tr>';                    
                $html.='<td>'.$vendedor->nombre.'</td>';
                $html.='<td>'.$usuario->email.'</td>';
                $html.='<td>'.$vendedor_paquete->monto_a_cancelar .' '.$this->config->item('money_sign').'</td>';
                $html.='<td>'.$paquete->nombre.'</td>';
                $html.='</tr>';            
                $html.='</tbody>';
                $html.='</table>';                
                
                echo $html;
            }
        }
    }

}