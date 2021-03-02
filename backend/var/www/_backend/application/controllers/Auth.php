<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
CREATE TABLE `tUser_history` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(10) UNSIGNED NOT NULL,
	`created_at` DATETIME(6) NOT NULL,
	`created_ip` CHAR(15) NULL DEFAULT '' COLLATE 'utf8_bin',
	`platform` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`browser` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`mobile` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `id` (`id`) USING BTREE
)
COLLATE='utf8_bin'
ENGINE=InnoDB;
*/
class Auth extends CI_Controller
{
    private $result = [
        'status' => 200, 'msg' => [], 'body' => []
    ];
    public function __construct()
    {
        parent::__construct();
        $this->load->library('ghost');
        $this->load->model('crud');
        $this->load->library('user_agent');
        $this->load->library('minify');
    }

    public function _remap($method)
    {
        //요청별 처리 형태 -- 시작
        switch ($this->input->request_headers()['Content-Type']) {
            case 'application/json':
                break;
            case 'application/script':
                break;
                //html
            default:
                if (method_exists($this, $method)) {
                    $body['main'] = $this->{$method}();
                } else {
                    show_404();
                }
                $this->minify->full($body);
                break;
        }
        //요청별 처리 형태 -- 끝
    }

    public function check()
    {
        $headers = $this->input->request_headers('token');
        if (isset($headers['Token']) && $this->session->userdata('token') == $headers['Token']) {
            $this->result['status'] = 200;
            $this->result['body'] = $this->session->userdata('user');
        }
        else {
            $this->result['status'] = 401;
            $this->result['msg'] = [
                'uid' => '로그인 되지 않았습니다.'
            ];
        }
        return $this->result;
    }

    public function login()
    {
        if($this->session->userdata('user')) {
            redirect('/');
        }
        $this->load->library('form_validation');
        $form = $this->input->post();
        if($this->input->is_ajax_request() && $this->input->method() == 'post' && $form == null) {
            $form = json_decode($this->input->raw_input_stream, true);
        }
        $this->form_validation->set_data($form);

        $this->form_validation->set_rules('uid', '아이디', 'required|trim|min_length[4]');
        $this->form_validation->set_rules('passwd', '암호', 'required|min_length[4]');
        if ($this->form_validation->run() == FALSE) {
            $this->result['status'] = 400;
            $this->result['msg'] = $this->form_validation->error_array();

            return $this->minify->parse(strtolower('mockup/' . __CLASS__ . '/' . __FUNCTION__ . '.html'), $this->result);
        } else {
            $passwd = $form['passwd'];
            unset($form['passwd']);
            $form['use'] = 'Y';
            $form['withdraw'] = 'N';
            
            $tmp = $this->crud->read('tUsers', ['where' => $form]);
            if ($tmp['count'] == 0) {
                $this->result['status'] = 400;
                $this->result['msg'] = [
                    'uid' => '아이디가 일치하지 않습니다.'
                ];
                return $this->minify->parse(strtolower('mockup/' . __CLASS__ . '/' . __FUNCTION__ . '.html'), $this->result);
            }else if ($tmp['rows'][0]->passwd != $passwd) {
                $this->result['status'] = 400;
                $this->result['msg'] = [
                    'uid' => '암호가 일치하지 않습니다.'
                ];
                return $this->minify->parse(strtolower('mockup/' . __CLASS__ . '/' . __FUNCTION__ . '.html'), $this->result);
            } else {
                $this->result['body']['token'] = md5($tmp['rows'][0]->uid . '|' . $tmp['rows'][0]->passwd);
                $this->session->set_userdata('token', $this->result['body']['token']);
                $user = $tmp['rows'][0];
                unset($user->passwd);
                $this->session->set_userdata('user', $tmp['rows'][0]);

                $time = explode(".", microtime(true));
                $history = [
                    'created_at' => date('Y-m-d H:i:s') . '.' . $time[1]
                    ,'created_ip'   => $this->ghost->remote_ip()
                    ,'platform'   => $this->agent->platform()
                    ,'browser'  => $this->agent->browser()
                    ,'mobile'   => $this->agent->mobile()
                    ,'user_id'  => $tmp['rows'][0]->id
                ];
                $this->crud->insert('tUser_history', $history);

                if ($this->input->is_ajax_request()) {
                    $this->ghost->rest_json([]);
                } else {
                    redirect('/');
                }
            }
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('user');
        redirect('/');
    }

    public function regist()
    {
        $this->load->library('form_validation');
        if ($this->input->is_ajax_request()) {
            $form = json_decode($this->input->raw_input_stream, true);
        } else {
            $form = $this->input->post();
        }
        $this->form_validation->set_data($form);

        $this->form_validation->set_rules('uid', '아이디', 'required|is_unique[tUsers.uid]');
        $this->form_validation->set_rules('passwd', '암호', 'required|differs[uid]');
        $this->form_validation->set_rules('passwd_confirm', '암호확인', 'required|matches[passwd]');
        if ($this->form_validation->run() == FALSE) {
            $this->result['status'] = 403;
            $this->result['msg'] = $this->form_validation->error_array();
            return $this->minify->parse(strtolower('mockup/' . __CLASS__ . '/' . __FUNCTION__ . '.html'), $this->result);
        } else {
            $form['infomation'] = [
                'created_at' => date('Y-m-d H:i:s')
                ,'created_ip'   => $this->ghost->remote_ip()
                ,'platform'   => $this->agent->platform()
                ,'browser'  => $this->agent->browser()
                ,'mobile'   => $this->agent->mobile()
            ];
            $form['nick'] = $form['uid'];
            $form['name'] = $form['uid'];
            $user = $this->crud->insert('tUsers', $form);
            $this->result['body'] = $user;
        }
        // return $this->result;
    }

    public function withdraw()
    {
        $form = json_decode($this->input->raw_input_stream, true);

        $this->load->library('form_validation');
        $this->form_validation->set_data($form);

        $this->form_validation->set_rules('uid', '아이디', 'required');
        $this->form_validation->set_rules('passwd', '암호', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->result['status'] = 403;
            $this->result['msg'] = $this->form_validation->error_array();
        } else {
        }
        return $this->result;
    }

    // public function api()
    // {
    //     $method = '_' . $this->uri->rsegment(3);
    //     if(method_exists($this, $method)) {
    //         $this->ghost->rest_json($this->{$method}());
    //     }
    //     else {
    //         show_error('접근이 불가합니다');
    //     }
    // }

    // public function script()
    // {
    //     $call = strtolower(__CLASS__);
    //     switch ($this->uri->rsegment(3)) {
    //         case 'join':
    //         case 'signup':
    //             $result = $this->script->define('_' . $call, [
    //                 'regist' => $this->script->post($call . '/regist', [])
    //             ]);
    //             break;
    //         case 'check':
    //             $result = $this->script->define('_' . $call, [
    //                 'check' => $this->script->get($call . '/check', [
    //                     'token' => 'localStorage["token"]'
    //                 ])
    //             ]);
    //         break;
    //         default:
    //             $result = $this->script->define('_' . $call, [
    //                 'login' => $this->script->post($call . '/login', [])
    //             ]);
    //             break;
    //     }
    //     $this->output->set_output($result);
    // }
}
