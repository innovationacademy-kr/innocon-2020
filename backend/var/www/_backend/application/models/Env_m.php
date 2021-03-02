<?php
class Env_m extends CI_Model
{
    protected $tbl = 'tEnv';
    private $CI;

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->model('crud');
        $this->CI->load->driver('cache');
    }
    public function read($params = [])
    {
        // $idx = 'env' . md5(json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $idx = 'env';
        $tmp = $this->crud->read($this->tbl, $params);

        foreach ($tmp['rows'] as $row) {
            $result['rows'][$row->code] = (array) $row;
        }
        return $result;
    }
    
    public function update($where, $params)
    {
        return $this->crud->update($this->tbl, $where, $params);
    }
}
