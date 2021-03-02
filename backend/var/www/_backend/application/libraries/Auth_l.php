<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_l
{

    protected $SALT = 'max@9won.kr';
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->driver('cache');
    }

    public function user()
    {
        $user = $this->CI->session->userdata('user');
        return $user;
    }

    public function destroy()
    {
        $this->CI->session->unset_userdata(['user','token']);
    }

    public function perm($perm = [])
    {
        if($this->user()) {
            if(in_array($this->user()->perm, $perm)) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }
}