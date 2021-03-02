<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sms extends CI_Controller
{
    private $sender = ['010','4949','8249'];
    public function __construct()
    {
        parent::__construct();
        $this->load->library('ghost');
        $this->load->library('sms_l');

        $this->load->model('crud');
        $this->load->driver('cache', array('adapter' => 'redis', 'backup' => 'file'));
    }

    public function _remap($method)
    {
        if (method_exists($this, $method)) {
            $this->{$method}();
        } else {
            show_404();
        }
    }
    public function remained()
    {
        if ($this->input->is_cli_request()) {
            $this->cache->save('remained', $this->sms_l->remained(), 60 * 10);
        } else {
            $result['body']['count'] =  $this->cache->get('remained');
            $this->ghost->rest_json($result);
        }
    }

    public function send()
    {
        if ($this->input->is_cli_request()) {
            $form = [
                //발신자번호 cafe24에 등록된 번호여야 한다.
                'sphone1'       => $this->sender[0], //필수
                'sphone2'       => $this->sender[1], //필수
                'sphone3'       => $this->sender[2], //필수

                'rphone'        => '010-4832-5987', //수신자번호 - 필수 (-)포함
                'smsType'       => '', //LMS일 경우에만  L 이라고 붙인다.
                'title'         => '안녕하세요. 명진자동차입니다.', //제목 - LMS일경우

                'destination'   => '010-4832-5987|홍길동', //이름삽입번호 - 메세지 가공을 위해
                'msg'           => '테스트 입니다.', //필수 SMS = 90 byte 이하, LMS = 2,000 byte 이하 까지 입력
                'testflag'      => 'Y', //전송테스트용
                // 'testflag'      => '', //전송테스트용
                'nointeractive' => '1', //성공시 대화 상자(alert)를 사용 하지 않게 합니다.

                //현재 사용하지 않음
                'rdate'         => '',
                'rtime'         => '',
                'returnurl'     => '', //이동할 URL
                'repeatFlag'    => '', //반복 설정	반복 설정을 원하는 경우 : Y
                'repeatNum'     => '', //반복 횟수	1~10회 가능.
                'repeatTime'    => '', //반복 시간	15분 이상부터 가능.
            ];
            $result = $this->sms_l->send($form);
            $this->cache->save('remained', $result['count'], 60 * 10);
            // var_dump($result);
        }
    }

    public function reservation()
    {
        $where = [
            // 'created_at >=' => date('Y-m-d H:i:s', strtotime('-1 days'))
            'created_at >=' => date('Y-m-d H:i:s', strtotime('-1 min'))
            ,'type' => 'auto'
        ];
        $reservate = $this->crud->read('tSms', [
            'select' => '*, (select personal_info from tMaintenance where id = maintenance_id) as personal_info',
            'where' => $where, 'group_by' => 'maintenance_id', 'order_by' => 'created_at desc, id desc'
        ]);
        // var_dump($reservate);
        if($reservate['count'] > 0) {
            foreach($reservate['rows'] as $row) {
                $personal_info = json_decode($row->personal_info, true);
                $rdate = '';
                $rtime = '';
                if(date('G') >= 21) {
                    $rdate = date('Y-m-d', strtotime('+1 days'));
                    $rtime = '08:00:00';
                }
                else if(date('G') < 8) {
                    $rdate = date('Y-m-d');
                    $rtime = '08:00:00';
                }
                $form = [
                    //발신자번호 cafe24에 등록된 번호여야 한다.
                    'sphone1'       => $this->sender[0], //필수
                    'sphone2'       => $this->sender[1], //필수
                    'sphone3'       => $this->sender[2], //필수

                    // 'rphone'        => '010-3320-3113', //수신자번호 - 필수 (-)포함
                    'rphone'        => $personal_info['phone'], //수신자번호 - 필수 (-)포함
                    'smsType'       => '', //LMS일 경우에만  L 이라고 붙인다.
                    'title'         => '안녕하세요. 명진자동차입니다.', //제목 - LMS일경우

                    'destination'   => $personal_info['phone'] . '|' . $personal_info['name'], //이름삽입번호 - 메세지 가공을 위해
                    'msg'           => $row->msg, //필수 SMS = 90 byte 이하, LMS = 2,000 byte 이하 까지 입력
                    // 'testflag'      => 'Y', //전송테스트용
                    'testflag'      => '', //실제용
                    'nointeractive' => '1', //성공시 대화 상자(alert)를 사용 하지 않게 합니다.

                    //현재 사용하지 않음
                    'rdate'         => $rdate,
                    'rtime'         => $rtime,
                    'returnurl'     => '', //이동할 URL
                    'repeatFlag'    => '', //반복 설정	반복 설정을 원하는 경우 : Y
                    'repeatNum'     => '', //반복 횟수	1~10회 가능.
                    'repeatTime'    => '', //반복 시간	15분 이상부터 가능.
                ];
                $result = $this->sms_l->send($form);
                $this->ghost->update('tSms', ['id' => $row->id], ['result' => $result['alert']]);
                $this->cache->save('remained', $result['count'], 60 * 10);
            }
        }
    }

    public function script()
    {
        $this->ghost->load_script();
    }
}
