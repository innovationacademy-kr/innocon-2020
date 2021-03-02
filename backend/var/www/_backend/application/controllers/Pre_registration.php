<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
CREATE TABLE `tPre_registration` (
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
class Pre_registration extends CI_Controller
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

    private function _excel()
    {
        $this->output->enable_profiler(FALSE);
        $this->_perm(false, ['staff']);

        $begin = date('Y-m-d', strtotime($this->uri->rsegment(3, date('Y-m-d', strtotime('-8 days')))));
        if (substr($begin, 0, 10) == '1970-01-01') {
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
            '메일' => 'string',
            '뉴스레터신청' => 'YYYY-MM-DD HH:MM:SS',
            '이름' => 'string',
            '연락처' => 'string',
            '소속' => 'string',
            '관심분야' => 'text',
            '유입경로' => 'text',
            '질문' => 'string',
            '가입이메일발송' => 'YYYY-MM-DD HH:MM:SS',
            '신청아이피' => 'string',
            '모바일여부' => 'string',
            '브라우저' => 'string',
            '운영체제' => 'string',
        ];

        $wExcel = new Ellumilel\ExcelWriter();
        $wExcel->writeSheetHeader('사전등록_' . $begin . '_' . $finish, $header);
        $wExcel->setAuthor('이노컴');

        foreach ($result['body']['rows'] as $k => &$row) {
            $row->comment = preg_replace('/(http|https)?:\/\/([\S]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $row->comment);
            // $row->idx = $result['count'] - $k - $params['limit'][0];
            $wExcel->writeSheetRow('사전등록_' . $begin . '_' . $finish, [
                $row->created_at,
                $row->email,
                $row->subscribe,
                $row->infomation['name'],
                $row->infomation['phone'],
                $row->infomation['organization'],
                implode(',', $row->interests['label']) . ((in_array('기타', $row->interests['label'])) ? '[' . $row->interests['etc'] . ']' : ''),
                implode(',', $row->funnel['label']) . ((in_array('기타', $row->funnel['label'])) ? '[' . $row->funnel['etc'] . ']' : ''),
                $row->comment,
                $row->email_sending,
                $row->created_info['ip'],
                $row->created_info['mobile'],
                $row->created_info['browser'],
                $row->created_info['platform'],
            ]);
        }
        header('Content-Disposition: inline; filename="사전등록_' . $begin . '_' . $finish . '.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $wExcel->writeToStdOut(false);
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

    public function index($referer = false, $params = [
        'where' => [
            'remove' => 'N',
        ], 'limit' => [0, 20], 'order_by' => 'id desc'
    ])
    {
        // $this->_perm($referer, ['staff', 'user']);
        if ($this->uri->segment(1) == 'admin') {

            if ($this->input->get('page') && is_numeric($this->input->get('page')) && floor($this->input->get('page')) > 0 && isset($params['limit'])) {
                $params['limit'][0] = (floor($this->input->get('page')) - 1) * $params['limit'][1];
            }

            $this->result['status'] = 200;

            foreach (['interests', 'funnel', 'infomation'] as $idx) {
                if ($this->input->get($idx) && is_array($this->input->get($idx))) {
                    foreach ($this->input->get($idx) as $k => $v) {
                        if (is_string($v)) {
                            if (strlen(trim($v)) > 0) {
                                $params['where']['JSON_EXTRACT( ' . $idx . ', \'$.' . $k . '\' ) LIKE'] = '%' . trim($v) . '%';
                            }
                        }
                    }
                }
            }
            foreach(['email','subscribe'] as $idx) {
                if ($this->input->get($idx)) {
                    $params['where'][$idx] = $this->input->get($idx);
                }
            }
            $result = $this->crud->read('tPre_registration', $params, false);
            foreach ($result['rows'] as $k => &$row) {
                if (isset($params['limit'])) {
                    $row->idx = $result['count'] - $k - $params['limit'][0];
                }
            }
            $this->result['body'] = $result;
            if ($referer == '_api') {
                return $this->result;
            } else {
                $this->result['body']['paging'] = $this->ghost->paging($params['limit'][0], $params['limit'][1], $this->result['body']['count']);
                $this->result['body']['get'] = $this->input->get();
                unset($this->result['body']['get']['page']);
                $this->result['body']['paging']['href'] = '/' . $this->uri->uri_string() . '?' . http_build_query($this->result['body']['get']);

                $this->result['msg'] = null;
                return $this->minify->parse(strtolower($this->path . '/' . $this->uri->uri_string() . '/index.html'), $this->result);
            }
        } else {
            redirect(str_replace('index', null, $this->uri->uri_string()) . '/regist');
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

            $result = $this->crud->read('tPre_registration', $params);
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
        if ($this->session->tempdata('pre_registration')) {
            redirect('/pop_pre_registration_error');
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
            'required|is_unique[tPre_registration.email]|valid_email',
            [
                'is_unique' => '이미 등록된 이메일입니다.'
            ]
        );
        // $this->form_validation->set_rules('email', '이메일', 'required|valid_email');
        $this->form_validation->set_rules('subscribe', '구독', 'required');
        $this->form_validation->set_rules('infomation[name]', '성명', 'required');
        // $this->form_validation->set_rules('infomation[organization]', '소속단체', 'required');
        $this->form_validation->set_rules('interests[label]', '관심분야', 'required|min_length[1]');
        $this->form_validation->set_rules('funnel[label]', '유입경로', 'required|min_length[1]');
        $this->form_validation->set_rules('info_agreement', '개인정보 활용 동의서', 'required');
        if (isset($form['interests']) && isset($form['interests']) && in_array('기타', $form['interests']['label'])) {
            $this->form_validation->set_rules('interests[etc]', '관심분야 기타내용', 'required');
        }
        if (isset($form['funnel']) && isset($form['funnel']['label']) && in_array('기타', $form['funnel']['label'])) {
            $this->form_validation->set_rules('funnel[etc]', '유입경로 기타내용', 'required');
        }

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
            $form['info_agreement'] = date('Y-m-d H:i:s');
            if ($form['subscribe'] == 1) {
                $form['subscribe'] = date('Y-m-d H:i:s');
            } else {
                unset($form['subscribe']);
            }

            $form['created_info'] = [
                'ip'   => $this->ghost->remote_ip(), 'platform'   => $this->agent->platform(), 'browser'  => $this->agent->browser(), 'mobile'   => $this->agent->mobile()
            ];

            //메일도 보낸다
            $config['useragent'] = '';
            $config['protocol'] = "smtp";
            $config['smtp_host'] = "ssl://smtp.gmail.com";
            $config['smtp_port'] = "465";
            $config['smtp_user'] = "2020innocon@gmail.com";
            $config['smtp_pass'] = "innocon1208@";
            $config['charset'] = "utf-8";
            $config['mailtype'] = "html";
            $config['crlf'] = "\r\n";
            $config['newline'] = "\r\n";
            $config['bcc_batch_mode'] = FALSE;
            $config['bcc_batch_size'] = 200;
            // $config['smtp_crypto']  = 'ssl';   //can be 'ssl' or 'tls' for example
            $config['smtp_timeout'] = '30';    //in seconds 
            $this->load->library('email', $config);

            $this->email->from('2020innocon@gmail.com', 'INNO-CON 사무국');
            $this->email->to([$form['email']]);
            // $this->email->reply_to('my-email@gmail.com', 'Explendid Videos');
            $this->email->subject('이노베이션 등록신청 완료 안내');
            $this->email->message($this->load->view('pre_registration_mail.html', $form, true));
            if ($this->email->send()) {
                $form['email_sending'] = date('Y-m-d H:i:s');
            }

            $result = $this->crud->insert('tPre_registration', $form);

            $this->session->set_tempdata('pre_registration', true, 10 * 60);
            if ($referer == '_api') {
                $this->result['body'] = $result;
                return $this->result;
            } else {
                redirect($this->uri->uri_string() . '_done');
            }
        }
    }

    public function remove($referer = false)
    {
        $this->_perm($referer, ['staff']);
        $where = [
            'remove' => 'N', 'id' => $this->input->get('id')
        ];
        $this->crud->update('tPre_registration', $where, ['remove' => 'Y']);
        if ($referer == '_api') {
            return $this->result;
        } else {
            redirect(str_replace('remove', null, $this->uri->uri_string()));
        }
    }
}
