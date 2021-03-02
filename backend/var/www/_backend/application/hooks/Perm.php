<?php

class Perm {
    private $CI;
    protected $path = [
        'users'    => ['staff','user']
        ,'environment'    => ['staff']
    ];

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('auth_l');
    }

    public function check() {
        $path = $this->CI->uri->rsegment(1);
        if($this->CI->uri->rsegment(1) != 'mockup' && $this->CI->uri->segment(1) == 'admin') {
            if ($this->CI->session->userdata('user')) {
                if ($this->CI->session->userdata('user')->perm != 'staff') {
                    show_error('No access allowed', 403, 'Permission');
                }
            } else {
                show_error('No access allowed', 403, 'Permission');
            }
        }
        else
        if(isset($this->path[$path])) {
            if($this->CI->session->userdata('user')) {
                if(!in_array($this->CI->session->userdata('user')->perm, $this->path[$path])) {
                    show_error('No access allowed', 403,'Permission'); 
                }
            }
            else {
                redirect('/auth/login');
            }
        }
    }
}