<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_profile_model extends CI_Model
{
    public function get_password_hash($staff_id)
    {
        return $this->db
            ->select('user_pass')
            ->where('ID', $staff_id)
            ->get('wp_users')
            ->row();
    }

    public function update_password_hash($staff_id, $password_hash)
    {
        $this->db
            ->where('ID', $staff_id)
            ->update('wp_users', array('user_pass' => $password_hash));

        return $this->db->affected_rows() === 1;
    }
}
