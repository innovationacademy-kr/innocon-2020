<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
CREATE TABLE `tEvent` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`hidden` ENUM('Y','N') NOT NULL DEFAULT 'N' COLLATE 'utf8_bin',
	`remove` ENUM('Y','N') NOT NULL DEFAULT 'N' COLLATE 'utf8_bin',
	`created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
	`email` CHAR(128) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`info_agreement` DATETIME NULL DEFAULT NULL,
	`subscribe` DATETIME NULL DEFAULT NULL,
	`interests` JSON NULL DEFAULT NULL,
	`funnel` JSON NULL DEFAULT NULL,
	`infomation` JSON NULL DEFAULT NULL,
	`comment` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `id` (`id`) USING BTREE
)
COLLATE='utf8_bin'
ENGINE=InnoDB
;
*/

class Event extends CI_Controller
{
    private $result = [
        'status' => 200, 'msg' => [], 'body' => []
    ];
    private $path = 'mockup';
    public function __construct()
    {
        parent::__construct();
        // $this->output->enable_profiler(TRUE);
        $this->load->library('auth_l');
        $this->load->library(['ghost', 'script']);
        $this->load->library('user_agent');
        $this->load->library('minify');
        $this->load->model('crud');
    }

    public function _remap($method)
    {
        if (method_exists($this, '_' . $method)) {
            $this->{'_' . $method}();
        } else if (method_exists($this, $method)) {
            $this->minify->full(['main' => $this->{$method}()]);
        } else {
            $this->minify->full(['main' => $this->index()]);
        }
    }

    private function _api()
    {
        $this->output->enable_profiler(FALSE);
        $method = $this->uri->rsegment(3, 'index');
        if (method_exists($this, $method)) {
            $this->ghost->rest_json($this->{$method}(__FUNCTION__));
        } else {
            $this->ghost->rest_json($this->index(__FUNCTION__));
        }
    }

    private function _script()
    {
        $this->output->enable_profiler(FALSE);
        $method = $this->uri->rsegment(3, 'index');

        $headers = [
            // 'token' => 'localStorage["token"]'
        ];
        switch ($method) {
            case 'regist':
                $func = [
                    'lists'  => $this->script->get(strtolower(__CLASS__), $headers), 'read'  => $this->script->get(strtolower(__CLASS__) . '/read', $headers), 'regist'  => $this->script->post(strtolower(__CLASS__) . '/regist', $headers)
                ];
                break;
            case 'update':
                if (!$this->auth_l->perm(['staff'])) show_error('Nor Allow', 401);
                $func = [
                    'lists'  => $this->script->get(strtolower(__CLASS__), $headers), 'read'  => $this->script->get(
                        strtolower(__CLASS__) . '/read',
                        $headers
                    ), 'update'  => $this->script->post(strtolower(__CLASS__) . '/update', $headers)
                ];
                break;
            case 'remove':
                if (!$this->auth_l->perm(['staff'])) show_error('Nor Allow', 401);
                $func = [
                    'lists'  => $this->script->get(strtolower(__CLASS__), $headers), 'read'  => $this->script->get(strtolower(__CLASS__) . '/read', $headers), 'remove'  => $this->script->post(strtolower(__CLASS__) . '/remove', $headers)
                ];
                break;
            case 'read':
            default:
                if (!$this->auth_l->perm(['staff'])) show_error('Nor Allow', 401);
                $func = [
                    'lists'  => $this->script->get(strtolower(__CLASS__), $headers)
                ];
                break;
        }
        $result = $this->script->define('_' . strtolower(__CLASS__), $func);
        $this->output->set_output($this->minify->ugilfy($result));
    }

    private function _perm($referer = false, $allow = ['staff'])
    {
        if (!$this->auth_l->perm($allow)) {
            $this->result['status'] = 401;
            $this->result['msg'] = '권한이 부족합니다.';
            if ($referer != '_api') {
                show_error('권한이 부족합니다.', 401);
            }
        }
    }

    private function _excel()
    {
        $this->output->enable_profiler(FALSE);
        $this->_perm(false, ['staff']);
        
        $begin = date('Y-m-d', strtotime($this->uri->rsegment(3, date('Y-m-d', strtotime('-8 days')))));
        if(substr($begin, 0, 10) == '1970-01-01') {
            $begin = date('Y-m-d', strtotime('-8 days'));
        }
        $finish = date('Y-m-d', strtotime('+7 days', strtotime($begin)));
        $params = [
            'where' => [
                'remove' => 'N',
                'created_at >=' => $begin . ' 00:00:00',
                'created_at <=' => $finish . ' 23:59:59',
            ], 'order_by' => 'id asc'
        ];
        $result = $this->index('_api', $params);
        $header = [
            '등록일시' => 'YYYY-MM-DD HH:MM:SS',
            '닉네임' => 'string',
            '메일' => 'string',
            '내용' => 'string',
            '신청아이피' => 'string',
            '모바일여부' => 'string',
            '브라우저' => 'string',
            '운영체제' => 'string',
        ];

        $wExcel = new Ellumilel\ExcelWriter();
        $wExcel->writeSheetHeader('이벤트_' . $begin . '_' . $finish, $header);
        $wExcel->setAuthor('이노컴');

        foreach ($result['body']['rows'] as $k => &$row) {
            $row->comment = preg_replace('/(http|https)?:\/\/([\S]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $row->comment);
            // $row->idx = $result['count'] - $k - $params['limit'][0];
            $wExcel->writeSheetRow('이벤트_' . $begin . '_' . $finish, [
                $row->created_at,
                $row->nick,
                $row->email,
                $row->comment,
                $row->created_info['ip'],
                $row->created_info['mobile'],
                $row->created_info['browser'],
                $row->created_info['platform'],
            ]);
        }
        header('Content-Disposition: inline; filename="이벤트_' . $begin . '_' . $finish . '.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $wExcel->writeToStdOut(false);
    }

    public function index_paging()
    {
        return $this->minify->parse(strtolower($this->path . '/' . $this->uri->uri_string() . '.html'), $this->index('_api'));
        
    }

    public function index($referer = false, $params = [
        'where' => [
            'remove' => 'N',
        ], 'limit' => [0, 20], 'order_by' => 'id desc'
    ])
    {
        // $this->_perm($referer, ['staff', 'user']);
        // $params = [
        //     'where' => [
        //         'remove' => 'N',
        //     ], 'limit' => [0, 20], 'order_by' => 'id desc'
        // ];
        if ($this->input->get('page') && is_numeric($this->input->get('page')) && floor($this->input->get('page')) > 0 && isset($params['limit'])) {
            $params['limit'][0] = (floor($this->input->get('page')) - 1) * $params['limit'][1];
        }

        $this->result['status'] = 200;

        foreach (['email', 'nick'] as $idx) {
            if ($this->input->get($idx)) {
                $params['where'][$idx] = $this->input->get($idx);
            }
        }

        $result = $this->crud->read('tEvent', $params, false);
        $this->result['body'] = $result;

        foreach ($result['rows'] as $k => &$row) {
            $row->comment = preg_replace('/(http|https)?:\/\/([\S]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $row->comment);
            if(isset($params['limit'])) {
                $row->idx = $result['count'] - $k - $params['limit'][0];
            }
        }

        $this->result['body']['get'] = $this->input->get();
        unset($this->result['body']['get']['page']);
        if (isset($params['limit'])) {
            $this->result['body']['paging'] = $this->ghost->paging($params['limit'][0], $params['limit'][1], $this->result['body']['count']);
            $this->result['body']['paging']['href'] = '/' . $this->uri->uri_string() . '?' . http_build_query($this->result['body']['get']);
        }

        $this->result['msg'] = null;
        if ($referer == '_api') {
            return $this->result;
        } else {
            return $this->minify->parse(strtolower($this->path . '/' . $this->uri->uri_string() . '/index.html'), $this->result);
        }
    }

    public function read($referer = false)
    {
        if ($this->uri->segment(1) == 'admin') {
            $params = [
                'where' => [
                    'remove' => 'N',
                    'id'    => $this->input->get('id')
                ], 'order_by' => 'id desc'
            ];

            $this->result['status'] = 200;

            $result = $this->crud->read('tEvent', $params);
            $this->result['body'] = $result;
            $this->result['body']['row'] = $result['rows'][0];
            if ($referer == '_api') {
                return $this->result;
            } else {
                $this->result['body']['get'] = $this->input->get();
                unset($this->result['body']['get']['page']);

                $this->result['msg'] = null;
                return $this->minify->parse(strtolower($this->path . '/' . $this->uri->uri_string() . '.html'), $this->result);
            }
        } else {
            redirect(str_replace('read', null, $this->uri->uri_string()));
        }
    }

    public function modify($referer = false)
    {
        $this->_perm($referer, ['staff']);
        if ($referer == '_api') {
            return $this->result;
        } else {
            return $this->minify->parse(strtolower($this->path . '/' . $this->uri->uri_string() . '/index.html'), $this->result);
        }
    }

    public function regist_done($referer = false)
    {
        return $this->minify->parse(strtolower($this->path . '/' . $this->uri->uri_string() . '.html'), $this->result);
    }

    public function regist($referer = false)
    {
        //등록제한
        if ($this->session->tempdata('event')) {
            // redirect('/');
        }
        $this->load->library('form_validation');

        $form = $this->input->post();
        if ($this->input->is_ajax_request() && $this->input->method() == 'post') {
            $form = json_decode($this->input->raw_input_stream, true);
            $this->form_validation->set_data($form);
        }

        $this->form_validation->set_rules(
            'email',
            '이메일',
            'required|is_unique[tEvent.email]|valid_email',
            [
                'is_unique' => '이미 등록된 이메일입니다.'
            ]
        );
        $this->form_validation->set_rules('nick', '닉네임', 'required');
        $this->form_validation->set_rules('comment', '내용', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->result['status'] = 400;
            $this->result['msg'] = $this->form_validation->error_array();
            $this->result['body']['form'] = $form;
            if ($referer == '_api') {
                return $this->result;
            } else {
                return $this->minify->parse(strtolower($this->path . '/' . $this->uri->uri_string() . '.html'), $this->result);
            }
        } else {
            foreach ($form as &$row) {
                if (is_string($row)) {
                    $row = strip_tags($row);
                }
            }
            $form['created_info'] = [
                'ip'   => $this->ghost->remote_ip(), 'platform'   => $this->agent->platform(), 'browser'  => $this->agent->browser(), 'mobile'   => $this->agent->mobile()
            ];

            $result = $this->crud->insert('tEvent', $form);

            $this->session->set_tempdata('event', true, 10 * 60);
            if ($referer == '_api') {
                $this->result['body'] = $result;
                return $this->result;
            } else {
                echo 2;
                // redirect($this->uri->uri_string() . '_done');
            }
        }
    }

    public function remove($referer = false)
    {
        $this->_perm($referer, ['staff']);
        $where = [
            'remove' => 'N', 'id' => $this->input->get('id')
        ];
        $this->crud->update('tEvent', $where, ['remove' => 'Y']);
        if ($referer == '_api') {
            return $this->result;
        } else {
            redirect(str_replace('remove', null, $this->uri->uri_string()));
        }
    }
}
