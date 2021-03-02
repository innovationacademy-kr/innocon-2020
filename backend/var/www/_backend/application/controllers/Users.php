<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
CREATE TABLE `tUsers` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`use` ENUM('Y','N') NOT NULL DEFAULT 'Y' COLLATE 'utf8_bin',
	`withdraw` ENUM('Y','N') NOT NULL DEFAULT 'N' COLLATE 'utf8_bin',
	`perm` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`uid` CHAR(128) NOT NULL COLLATE 'utf8_bin',
	`passwd` BLOB NOT NULL,
	`nick` CHAR(128) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`name` CHAR(128) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`infomation` JSON NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `id` (`id`) USING BTREE,
	INDEX `uid` (`uid`, `use`, `withdraw`) USING BTREE
)
COLLATE='utf8_bin'
ENGINE=InnoDB
*/
class Users extends CI_Controller
{
    private $result = [
        'status' => 200, 'msg' => [], 'body' => []
    ];
    public function __construct()
    {
        parent::__construct();
        // $this->output->enable_profiler(TRUE);
        $this->load->library('ghost');
        $this->load->library('auth_l');
        $this->load->library('minify');
        $this->load->model('crud');
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
        $params = [
            'where' => [
                'use' => 'Y'
            ]
            ,'limit' => [0, 20]
        ];
        if($this->input->get('page') && is_numeric($this->input->get('page')) && floor($this->input->get('page')) > 0) {
            $params['limit'][0] = (floor($this->input->get('page')) - 1) * $params['limit'][1];
        }

        if($this->uri->segment(1) != 'admin') {
            $param['id'] = $this->session->userdata('user')->id;
        }
        else {
            foreach ($this->input->get() as $k => $v) {
                if (strlen(trim($v)) > 0 && !in_array($k, ['page'])) {
                    $params['where'][$k] = trim($v);
                }
            }
        }
        $this->result['body'] = $this->crud->read('tUsers', $params);
        foreach($this->result['body']['rows'] as $k => &$row) {
            $row->idx = $this->result['body']['count'] - $k - $params['limit'][0];
        }
        $this->result['body']['paging'] = $this->ghost->paging($params['limit'][0], $params['limit'][1], $this->result['body']['count']);
        $this->result['body']['paging']['href'] = '/' . $this->uri->uri_string() . '?' . http_build_query($params['where']);
        // var_dump($this->result['paging']);
        return $this->minify->parse(strtolower('mockup/admin/' . strtolower(__CLASS__) . '/' . __FUNCTION__ . '.html'), $this->result);
    }

    public function detail()
    {
        # code...
    }
}
