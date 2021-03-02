<?php
class Crud extends CI_Model
{
    protected $SALT = 'max@9won.kr';
    private $CI;

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->library('ghost');
        $this->CI->load->driver('cache');
    }

    public function insert($tbl = null, $values = [])
    {
        if ($this->db->table_exists($tbl)) {
            $fields = $this->meta($tbl);
            foreach ($fields as $field) {
                if ($field->name == 'created_ip') {
                    $values[$field->name] = $this->ghost->remote_ip();
                }
            }
            foreach ($values as $k => $v) {
                if (!in_array($k, array_keys($fields))) {
                    unset($values[$k]);
                } else {
                    switch ($fields[$k]->type) {
                        case 'json':
                            $this->db->set($k, json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE));
                            break;
                        case 'blob':
                            $this->db->set($k, 'AES_ENCRYPT("' . $v . '", "' . md5($this->SALT) . '")', FALSE);
                            break;
                        default:
                            $this->db->set($k, $v);
                            break;
                    }
                }
            }
            $this->db->insert($tbl);
            $id = $this->db->insert_id();
            return $this->read($tbl, ['where' => ['id' => $id]]);
        } else {
            return [];
        }
    }

    public function update($tbl = null, $where, $values = [])
    {
        if ($this->db->table_exists($tbl)) {
            $fields = $this->meta($tbl);
            foreach ($values as $k => $v) {
                if (!in_array($k, array_keys($fields))) {
                    unset($values[$k]);
                } else {
                    switch ($fields[$k]->type) {
                        case 'json':
                            if (is_string($v) && strpos($v, '`') !== false) {
                                $this->db->set($k, $v, false);
                            } else {
                                $this->db->set($k, json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE));
                            }
                            break;
                        case 'blob':
                            $this->db->set($k, 'AES_ENCRYPT("' . $v . '", "' . md5($this->SALT) . '")', FALSE);
                            break;
                        default:
                            if(strpos($v, '`') !== false) {
                                $this->db->set($k, $v, false);
                            }
                            else {
                                $this->db->set($k, $v);
                            }
                            break;
                    }
                }
            }

            //where
            if (isset($where)) {
                foreach ($where as $k => $v) {
                    $this->db->where($k, $v);
                }
            }
            $this->db->update($tbl);
            // echo $this->db->last_query();
            return $this->read($tbl, ['where' => $where], false);
        } else {
            return [];
        }
    }


    public function read($tbl, $param = [], $cache = true)
    {
        $result = [
            'rows' => [], 'count' => 0
        ];
        if ($this->db->table_exists($tbl)) {
            $fields = $this->meta($tbl);

            if (isset($param['select'])) {
                $this->db->select($param['select'], false);
            } else {
                foreach ($fields as $field) {
                    switch ($field->type) {
                        case 'blob':
                            $this->db->select('AES_DECRYPT(`' . $field->name . '`, "' . md5($this->SALT) . '") as `' . $field->name . '`', FALSE);
                            break;
                        default:
                            $this->db->select($field->name);
                            break;
                    }
                }
            }

            //where
            if (isset($param['where'])) {
                foreach ($param['where'] as $k => $v) {
                    if (strpos($k, ' ') !== false) {
                        $this->db->where($k, '"' . $v . '"', false);
                    } else if(in_array($k, array_keys($fields))) {
                        $this->db->where($k, $v);
                    }
                }
            }
            //order_by
            if (isset($param['order_by'])) {
                if (is_array($param['order_by'])) {
                    foreach ($param['order_by'] as $k => $v) {
                        $this->db->order_by($k, $v);
                    }
                } else {
                    $this->db->order_by($param['order_by']);
                }
            }

            $sql = $this->db->get_compiled_select($tbl);
            // echo $sql;
            //group_by
            if (isset($param['group_by'])) {
                if (is_array($param['group_by'])) {
                    foreach ($param['group_by'] as $k => $v) {
                        $this->db->group_by($k, $v);
                    }
                } else {
                    $this->db->group_by($param['group_by']);
                }
            }

            $sql = $this->db->get_compiled_select('(' . $sql . ') as a');

            if($cache) {
                $result = $this->cache->get(md5(json_encode($param)));
            }
            else {
                $result = false;
            }
            if(!$result) {
                $query = $this->db->query("SELECT count(a.`id`) as `cnt` FROM (${sql}) AS a");
                $result['count'] = $query->row(0)->cnt;
    
                //limit
                if (isset($param['limit'])) {
                    $this->db->limit($param['limit'][1], $param['limit'][0]);
                }
    
    
                $query = $this->db->get("(${sql}) AS a");
                $result['rows'] = $query->result();
    
                foreach ($fields as $field) {
                    switch ($field->type) {
                        case 'json':
                            foreach ($result['rows'] as &$row) {
                                if (isset($row->{$field->name})) {
                                    $row->{$field->name} = json_decode($row->{$field->name}, true);
                                }
                            }
                            break;
                    }
                }
                $this->cache->save(md5(json_encode($param)), $result, 60);
            }
            if (ENVIRONMENT == 'development') {
             //   $result['sql'] = $sql;
            }
        } else {
        }
        return $result;
    }


    public function meta($tbl)
    {
        $result = [];
        foreach ($this->db->field_data($tbl) as $field) {
            $result[$field->name] = $field;
        }
        return $result;
    }

}
