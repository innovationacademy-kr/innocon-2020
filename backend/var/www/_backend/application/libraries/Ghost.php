<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ghost
{

    protected $SALT = 'max@9won.kr';
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        // $this->CI->load->driver('cache');
        // $this->CI->load->model('crud');
    }

    public function js_pack($str)
    {
        if (!$this->CI->input->get('debug')) {
            $this->CI->output->set_header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 5)) . ' GMT'); // 유효기한
            $this->CI->output->set_header("Cache-Control: max-age=" . (60 * 5)); // 캐시 최대 길이 (초 단위)
            $this->CI->output->set_header("Pragma: public");
            // $packer = new Tholu\Packer\Packer($str, 'Numeric', false, true, false);
            foreach ([62, 10, 0] as $k) {
                // foreach ([10] as $k) {
                $packer = new Tholu\Packer\Packer($str, $k, false, false, true);
                $str = $packer->pack();
            }
            // $packed_js = '(new Function(atob("' . base64_encode($str) . '")))();';
            $packed_js = $str;
        } else {
            $packed_js = $str;
        }
        return $packed_js;
    }
    
    public function load_script()
    {
        $script = APPPATH . 'views/' . str_replace('/script', null, $this->CI->uri->ruri_string()) . '.js';
        $this->CI->output->set_header('Content-type: text/javascript; charset=utf-8');
        if (is_file($script)) {
            $result = file_get_contents($script);
            $this->CI->output->set_output($this->js_pack($result));
        } else {
            $this->CI->output->set_output('console.log("Not Found ' . str_replace('/script', null, $this->CI->uri->ruri_string()) . '.js' . '");');
        }
    }

    public function rest_json($arr)
    {
        $json = [
            'status' => 200, 'msg' => null, 'body' => []
        ];
        $result = array_merge($json, $arr);
        $this->CI->output->set_header('Content-type: application/json; charset=utf-8');
        http_response_code($result['status']);
        $this->CI->output->set_status_header($result['status']);
        $this->CI->output->set_output(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_IGNORE));
    }

    public function remote_ip()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    public function paging($offset = 0, $per_page = 10, $total = 1, $link = 3)
    {
        $result = [
            'total' => ceil($total / $per_page),
            'current' => ($offset / $per_page) + 1,
            'first' => 1,
            'last' => ceil($total / $per_page)
        ];
        if ($result['total'] == 0) {
            $result['total'] = 1;
            $result['last'] = 1;
        }
        if ($result['current'] - $link > 0) {
            if ($result['current'] + $link + 1 >= $result['total']) {
                $begin = $result['total'] - ($link * 2) - 1;
                $finish = $result['total'];
            } else {
                $begin = $result['current'] - $link;
                $finish =  $result['current'] + $link + 1;
            }
        } else {
            $begin = 1;
            $finish = $link * 2 + 1;
        }

        for ($i = $begin; $i <= $finish && $i <= $result['total']; $i++) {
            $result['nav'][] = $i;
        }

        return $result;
    }

    public function auth($type = 'check')
    {
        switch ($type) {
            case 'check':
                $result = [
                    'status' => 401, 'msg' => [
                        'uid' => '로그인이 되지 않았습니다'
                    ]
                ];
                if ($this->CI->input->request_headers()['Token'] && $this->CI->session->userdata('token') != $this->CI->input->request_headers()['Token']) {
                    return $result;
                } else {
                    $result['status'] = 200;
                    $result['msg'] = [];
                    return $result;
                }
                break;
        }
    }
}
