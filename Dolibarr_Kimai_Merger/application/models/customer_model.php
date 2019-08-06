<?php
    class Customer_model extends CI_Model
    {
        public function __construct()
        {
            $this->load->database();
        }
        public function insertCustomer($data)
        {
            $this->db->insert('customer', $data);            
        }
        public function getCustomers()
        {
            $query = $this->db->get('customer');
            return $query->result_array();
        }
        public function getCustomerByKimaiID($id)
        {
            $query = $this->db->get_where('customer', array('kimai_id'=>$id));
            return $query->row_array();
        }
        public function getCustomerByDoliID($id)
        {
            $query = $this->db->get_where('customer', array('doli_id'=>$id));
            return $query->row_array();
        }
        public function deleteCustomerByDoliID($id)
        {
            $this->db->where('doli_id',$id);
            $this->db->delete('customer');
        }
        public function wipeCustomers()
        {
            $this->db->empty_table('customer');
        }
    }
?>