<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Main extends MY_Controller {

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

    public function index() {
        redirect('admin/dashboard');
    }

    public function dashboard() {
        $this->template->set_title("Panel de Administracion - Mercabarato.com");
        $this->template->load_view('admin/dashboard/index');
    }

}