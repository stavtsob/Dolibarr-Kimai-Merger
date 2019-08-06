<?php
    class User_model extends CI_Model
    {
        public function __construct()
        {
            $this->load->database('default', TRUE);
        }
        public function insertUser($data)
        {
            $this->db->insert('user', $data);            
        }
        public function insertUserToKimai($data)
        {
            $kimai_db = $this->load->database('kimai', TRUE);
            $kimai_db->insert('kimai2_users', $data);
        }
        public function getUsers()
        {
            $query = $this->db->get('user');
            return $query->result_array();
        }
        public function getUserByKimaiID($id)
        {
            $query = $this->db->get_where('user', array('kimai_id'=>$id));
            return $query->row_array();
        }
        public function getUserByDoliID($id)
        {
            $query = $this->db->get_where('user', array('doli_id'=>$id));
            return $query->row_array();
        }
        public function wipeUsers()
        {
            $this->db->empty_table('user');
        }
    }
?>