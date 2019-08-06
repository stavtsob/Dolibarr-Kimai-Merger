<?php
    class Task_model extends CI_Model
    {
        public function __construct()
        {
            $this->load->database();
        }
        public function insertTask($data)
        {
            $this->db->insert('task', $data);            
        }
        public function getTasks()
        {
            $query = $this->db->get('task');
            return $query->result_array();
        }
        public function getTaskByKimaiID($id)
        {
            $query = $this->db->get_where('task', array('kimai_id'=>$id));
            return $query->row_array();
        }
        public function getTaskByDoliID($id)
        {
            $query = $this->db->get_where('task', array('doli_id'=>$id));
            return $query->row_array();
        }
        public function deleteTaskByDoliID($id)
        {
            $this->db->where('doli_id',$id);
            $this->db->delete('task');
        }
        public function wipeTasks()
        {
            $this->db->empty_table('task');
        }
    }
?>