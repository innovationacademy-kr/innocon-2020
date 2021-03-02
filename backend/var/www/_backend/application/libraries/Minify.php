<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Wa72\HtmlPrettymin\PrettyMin;

class Minify
{
    protected $CI;

    private $twig;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('ghost');
        $this->CI->load->library('auth_l');
        $this->CI->load->model('env_m');
        // $this->CI->load->driver('cache');
        $loader = new \Twig\Loader\FilesystemLoader(VIEWPATH);
        $env = [
            // 'cache' => APPPATH . 'cache',
            'cache' => false,
            'optimizations' => 1,
            'debug'    => false,
            'autoreload'    => true
        ];
        $this->twig = new \Twig\Environment($loader, $env);
    }

    public function parse($file, $param)
    {
        $env = $this->CI->env_m->read();
        $param['env'] = $env['rows'];
        $param['user'] = $this->CI->auth_l->user();
        if(is_file(VIEWPATH . '/' . $file)) {
            return $this->twig->render($file, $param);
        }
        else {
            show_404();
        }
    }

    public function html($str)
    {
        libxml_use_internal_errors(true);
        $pm = new PrettyMin();
        $pm->load($str);
        libxml_clear_errors();
        if (!$this->CI->input->get('debug')) {
            $pm->minify();
        } else {
            $pm->indent();
        }

        return html_entity_decode(preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", $pm->saveHtml()), null, 'UTF-8');
    }

    public function ugilfy($str)
    {
        if (!$this->CI->input->get('debug')) {
            $this->CI->output->set_header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 5)) . ' GMT'); // 유효기한
            $this->CI->output->set_header("Cache-Control: max-age=" . (60 * 5)); // 캐시 최대 길이 (초 단위)
            $this->CI->output->set_header("Pragma: public");
            foreach ([62, 10, 0] as $k) {
            // foreach ([10] as $k) {
                $packer = new Tholu\Packer\Packer($str, $k, true, false, true);
                $str = $packer->pack();
            }
                
            // $str = (new Tholu\Packer\Packer($str, 10, true, false, false))->pack();

            $packed_js = $str;
            // $packed_js = '(new Function(atob("' . base64_encode($str) . '")))();';
        } else {
            $packed_js = $str;
        }
        return $packed_js;
    }

    public function head($str, $meta_arr)
    {
        libxml_use_internal_errors(true);
        $html = new DOMDocument();
        $html->loadHTML($str);
        //head 재설정
        //타이틀
        foreach($meta_arr as $tag => $ele) {
            switch($tag) {
                case 'title':
                    if ($html->getElementsByTagName('title')->length > 0) {
                        $html->getElementsByTagName('title')->item(0)->nodeValue = $ele;
                    }
                break;
                case 'meta':
                    foreach($ele as $meta) {
                        if (isset($meta['name'])) {
                            $find = false;
                            foreach ($html->getElementsByTagName('meta') as $key => $row) {
                                if ($row->getAttribute('name') == $meta['name']) {
                                    $html->getElementsByTagName('meta')->item($key)->setAttribute('content', $meta['content']);
                                    $find = true;
                                }
                            }
                            if (!$find) {
                                $dom = $html->createElement('meta');
                                $html->getElementsByTagName('head')->item(0)->appendChild($dom);
                                $dom->setAttribute('name', $meta['name']);
                                $dom->setAttribute('content', $meta['content']);
                            }
                        }

                        if (isset($meta['property'])) {
                            $find = false;
                            foreach ($html->getElementsByTagName('meta') as $key => $row) {
                                if ($row->getAttribute('property') == $meta['property']) {
                                    $html->getElementsByTagName('meta')->item($key)->setAttribute('content', $meta['content']);
                                    $find = true;
                                }
                            }
                            if (!$find) {
                                $dom = $html->createElement('meta');
                                $html->getElementsByTagName('head')->item(0)->appendChild($dom);
                                $dom->setAttribute('property', $meta['property']);
                                $dom->setAttribute('content', $meta['content']);
                            }
                        }
                    }
                break;
            }
        }
        return $html->saveHTML();
    }


    // public function file_attach($html)
    // {
    //     return preg_replace_callback('/(\{\{([a-zA-Z\/0-9_]+)\}\})/m', function ($matches) {
    //         if ($matches[2] == 'MAIN') {
    //             $result = $this->main;
    //         } else if (is_file(VIEWPATH . $matches[2] . '.html')) {
    //             $result = $this->file_attach($this->twig->render($matches[2] . '.html', $this->CI->env_m->read()));
    //         } else {
    //             $result = '<p>Not Found ' . $matches[2] . '.html</p>';
    //         }
    //         return $result;
    //     }, $html);
    // }

    public function full($body)
    {
        //head 내용 확인
        preg_match('/<!--HEAD(([\s\S])*?)HEAD-->/m', $body['main'], $tmp);
        if(isset($tmp[1])) {
            $head = json_decode($tmp[1], true);
        }
        
        $env = $this->CI->env_m->read();
        $body['env'] = $env['rows'];

        $body['user'] = $this->CI->auth_l->user();

        if($this->CI->uri->segment(1) == 'admin') {
            $html = $this->twig->render('frame/admin.html', $body);
        }
        else {
            $html = $this->twig->render('frame/full.html', $body);
        }

        //head에 삽입
        if (isset($head) && count($head) > 0) {
            $html = $this->head($html, $head);
        }

        // $html = $this->file_attach($html);

        //스크립트
        /*
        $html = preg_replace_callback('/<script?\w+((\s+\w+(\s*=\s*(?:\"(.*?)\"|\'(.*?)\'|[^\'">\s]+))?)+\s*|\s*)?>*<\/script>/m', function ($matches) {
            $url = parse_url($matches[4]);
            if (isset($url['scheme'])) {
                $result = '<script src="' . $matches[4] . '"></script>';
            } else {
                if (is_file($_SERVER['DOCUMENT_ROOT'] . $url['path'])) {
                    $result = '<script src="/minify/script' . $url['path'] . '?' . ((isset($url['query'])) ? $url['query'] : null) .  (($this->CI->input->get('debug') == 1) ? '&debug=1' : null) . '"></script>';
                } else {
                    $result = '<script src="' . $url['path'] . '?' . ((isset($url['query'])) ? $url['query'] : null) .  (($this->CI->input->get('debug') == 1) ? '&debug=1' : null) . '"></script>';
                }
            }
            return $result;
        }, $html);
        */
        //스타일
        /*
        $html = preg_replace_callback('/<link?\w+((\s+\w+(\s*=\s*(?:\"(.*?)\"|\'(.*?)\'|[^\'">\s]+))?)+\s*|\s*)?>*>/m', function ($matches) {
            if (is_file($_SERVER['DOCUMENT_ROOT'] . $matches[count($matches) - 1])) {
                $result = '<link rel="stylesheet" href="/minify/style' . $matches[count($matches) - 1] . (($this->CI->input->get('debug') == 1) ? '?debug=1' : null) . '">';
            } else {
                $result = $matches[0];
            }
            return $result;
        }, $html);
        */

        $this->CI->output->set_output($this->html($html) );
    }
}