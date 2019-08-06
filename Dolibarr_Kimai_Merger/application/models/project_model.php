<?php
    class Project_model extends CI_Model
    {
        public function __construct()
        {
            $this->load->database();
        }
        public function insertProject($data)
        {
            $this->db->insert('project', $data);            
        }
        public function getProjects()
        {
            $query = $this->db->get('project');
            return $query->result_array();
        }
        public function getProjectByKimaiID($id)
        {
            $query = $this->db->get_where('project', array('kimai_id'=>$id));
            return $query->row_array();
        }
        public function getProjectByDoliID($id)
        {
            $query = $this->db->get_where('project', array('doli_id'=>$id));
            return $query->row_array();
        }
        public function deleteProjectByDoliID($id)
        {
            $this->db->where('doli_id',$id);
            $this->db->delete('project');
        }
        public function wipeProjects()
        {
            $this->db->empty_table('project');
        }
    }
?>