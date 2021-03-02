<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
CREATE TABLE `tEnv` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`label` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`ko` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`en` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`sort` INT(11) NULL DEFAULT '0',
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8_bin'
ENGINE=InnoDB;
*/
class Environment extends CI_Controller
{
    private $result = [
        'status' => 200, 'msg' => [], 'body' => []
    ];
    public function __construct()
    {
        parent::__construct();
        // $this->output->enable_profiler(TRUE);
        $this->load->library('ghost');
        $this->load->library('minify');
        $this->load->model('env_m');
    }

    public function _remap($method)
    {
        switch ($this->input->request_headers()['Content-Type']) {
            case 'application/json':
                break;
            case 'application/script':
                break;
                //html
            default:
                if (method_exists($this, $method)) {
                    $body['main'] = $this->{$method}();
                    $this->minify->full($body);
                } else {
                    show_404();
                }
                break;
        }
    }

    public function index()
    {
        $this->load->library('form_validation');
        if ($this->input->is_ajax_request()) {
            $form = json_decode($this->input->raw_input_stream, true);
        } else {
            $form = $this->input->post();
        }
        $this->form_validation->set_data($form);

        $this->form_validation->set_rules('company[ko]', '회사명', 'required|trim');
        $this->form_validation->set_rules('tel[ko]', '대표번호', 'required|trim');
        $this->form_validation->set_rules('business_id[ko]', '사업자(법인)등록번호', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->result['status'] = 400;
            $this->result['msg'] = $this->form_validation->error_array();

            $this->result['body'] = $this->env_m->read([
                'order_by' => [
                    'sort' => 'desc'
                ]
            ]);
            return $this->minify->parse(strtolower('mockup/admin/' . __CLASS__ . '/' . __FUNCTION__ . '.html'), $this->result);
        } else {
            foreach ($form as $idx => $row) {
                $this->env_m->update(['code' => $idx], $row);
            }

            redirect($this->uri->uri_string());
        }
    }
}
