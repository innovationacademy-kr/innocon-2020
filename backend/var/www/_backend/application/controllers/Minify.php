<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Minify extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'redis', 'backup' => 'file'));
        $this->load->library('ghost');
    }

    public function script()
    {
        $file = substr($this->uri->ruri_string(), 13);
        $this->output->set_header('Content-type: text/javascript');
        if (is_file($_SERVER['DOCUMENT_ROOT'] . $file)) {
            $js = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $file);
            
            $this->output->set_output($this->ghost->js_pack($js));
        } else {
            $this->output->set_output('/* 스크립트가 존재하지 않습니다 */');
        }
    }

    public function style()
    {
        $file = substr($this->uri->ruri_string(), 12);
        $this->output->set_header('Content-type: text/css');

        if (is_file($_SERVER['DOCUMENT_ROOT'] . $file)) {
            $str = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $file);
            if (!$this->input->get('debug')) {
                $this->output->set_header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 5)) . ' GMT'); // 유효기한
                $this->output->set_header("Cache-Control: max-age=" . (60 * 5)); // 캐시 최대 길이 (초 단위)
                $this->output->set_header("Pragma: public");
                $packer = $this->cache->get('minify|style' . str_replace('/', '|', substr($file, 0, -4)));
                if (!$packer) {
                    $packer = WebSharks\CssMinifier\Core::compress($str);
                    $this->cache->save('minify|style' . str_replace('/', '|', substr($file, 0, -4)), $packer, 60 * 10);
                }
            } else {
                $packer = $str;
            }
            $this->output->set_output($packer);
        } else {
            $this->output->set_output('/* 스타일이 존재하지 않습니다 ' . $file . '*/');
        }
    }
}
