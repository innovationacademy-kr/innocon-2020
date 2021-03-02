<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mockup extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $this->output->enable_profiler(TRUE);
        $this->load->library('ghost');
        $this->load->library('minify');
    }

    public function _remap($method)
    {
        $file = $this->uri->ruri_string();
        if(is_file(VIEWPATH . $file . '.html')) {
            $body['main'] = $this->minify->parse($file . '.html', []);
            
            $this->minify->full($body);
        }
        else  if(is_file(VIEWPATH . $file . '/index.html')) {
            $body['main'] = $this->minify->parse($file . '/index.html', []);

            $this->minify->full($body);
        }
        else {
            show_404();
        }
    }

}