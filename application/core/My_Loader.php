<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
#[\AllowDynamicProperties]
class My_Loader extends CI_loader
{
    public function template_login($data)
    {
        if (isset($_COOKIE['setlogin'])) {
            $this->login_cookie($data);
        } else {
            $this->login_normal($data);
        }
    }

    private function login_normal($data)
    {
        $this->view('element/login', $data);
    }

    private function login_cookie($data)
    {
        $this->view('element/login_remember', $data);
    }

    public function template_homepage()
    {
        $data = infocategory($cat = '');

        $this->view('element/head', $data);

        // add by mYa on Feb 8, 2022, give portal access by table setup
        $id = $this->session->userdata('id_user');
        $access['portal'] = $this->module_model->getPortalAccessbyId($id);

        $this->view('element/modal');
        $this->view('element/home', $access);
        $this->view('element/footer', $data);
    }

    function template_header()
    {
        $this->view('element/head');
        $this->view('element/header');
        // $this->view('element/footer');
    }

    function template_footer()
    {
        $data = infocategory($cat = '');
        $this->view('element/footer', $data);
    }

    function template_main_header($cat, $data, $url_active)
    {
        if ($url_active) {
            $data['menu_active'] = $url_active['menu'];
        } else {
            $data['menu_active'] = "";
        }

        $info_cat = infocategory($cat);
        $data['cat_title'] = $info_cat['title'];
        $data['cat'] = $cat;
        $data['url_active'] = $url_active;

        $this->view('element/header', $data);
    }

    public function template_page($cat, $url_active,  $el)
    {
        $data = infocategory($cat);
        $data['el'] = $el;
        $data['cat'] = $cat;


        // var_dump($el);
        // exit;


        $admin = $this->session->userdata('admin');
        $id = $this->session->userdata('id_user');
        // $url = "home/template";
        // $this->view($url, $data);

        if (!$url_active) {
            // add by mYA on Feb 3, 2022.. Jika portal Employee Self Service. Bedakan home dashboardnya
            $menu = $this->uri->segment(2);

            switch ($cat) {
                case 'doc':
                    //$url = "home/defaultTemplate";
                    $url = "home/template";
                    break;
            }

            $this->view($url, $data);
        } else {
            $data['menu'] = $url_active['menu'];
            $data['submenu'] = $url_active['submenu'];
            $data['module'] = $url_active['module'];

            $data['submenu_title'] = $url_active['submenu_title'];
            $data['module_title'] = $url_active['module_title'];
            $data['module_id'] = $url_active['module_id'];




            $id_user = $this->session->userdata('id_user');

            if ($admin) {
                $data['is_create'] = '1';
                $data['is_update'] = '1';
                $data['is_delete'] = '1';
            } else {
                $permit = $this->hakakses_model->user_permission($id_user, $url_active['module_id'], $admin);
                // $data['is_create'] = $permit['is_create'];
                // $data['is_update'] = $permit['is_update'];
                // $data['is_delete'] = $permit['is_delete'];

                $data['is_create'] = '0';
                $data['is_update'] = '0';
                $data['is_delete'] = '0';
                
            }
            //template.php pengganti index.php untuk setiap panggilan dari menu


            $url = "$cat/$data[menu]/$data[submenu]/$data[module]/template";

            // var_dump($url);
            // exit;
            $this->view($url, $data);
        }
    }

    function template_footer_page($cat, $url_active,  $el)
    {
        $data = infocategory($cat);
        $data['el'] = $el;
        $this->view('element/footer_page', $data);
    }

    function template_main_header_cpanel($cat, $url_active, $data)
    {
        //if( $url_active ){ $data['menu_active'] = $url_active['menu']; } else { $data['menu_active'] = ""; }

        //$info_cat = infocategory($cat);
        //$data['cat_title'] = $info_cat['title'];
        $data['cat'] = $cat;
        $data['url_active'] = $url_active;

        $this->view('element/header', $data);
    }

    /*
    function template_main_header_cpanel($cat, $url_active)
    {
        //if( $url_active ){ $data['menu_active'] = $url_active['menu']; } else { $data['menu_active'] = ""; }

        //$info_cat = infocategory($cat);
        //$data['cat_title'] = $info_cat['title'];
        $data['cat'] = $cat;
        $data['url_active'] = $url_active;

        $this->view('element/header_cpanel', $data);
    }*/

    public function template_page_cpanel($cat, $url_active)
    {

        $data = infocategory($cat);

        $admin = $this->session->userdata('admin');

        if (!$url_active) {
            $menu = $this->uri->segment(2);
            if ($menu == 'home') {
                $url = "home/template";
            } else {
                $url = "home/changepassword";
            }

            $this->view($url, $data);
        } else {
            $stitle = "";
            switch ($url_active) {
                case 'access':
                    $stitle = 'User & Akses';
                    break;
                case 'module':
                    $stitle = 'Menu & Module';
                    break;
                case 'workflow':
                    $stitle = 'Workflow';
                    break;
            }

            $data['menu'] = 'control';
            $data['submenu'] = $url_active;
            $data['module'] = $url_active;

            $data['submenu_title'] = "Setting";
            $data['module_title'] = $stitle;
            $data['module_id'] = "";

            //template.php pengganti index.php untuk setiap panggilan dari menu
            $url = "$cat/$url_active/template";
            $this->view($url, $data);
        }
    }

    public function api_error_page($err_message)
    {
        $data['err_message'] = $err_message;
        $this->view('element/api_error_page', $data);
    }
}
