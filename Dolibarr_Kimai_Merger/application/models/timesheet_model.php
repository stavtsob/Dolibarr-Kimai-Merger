<?php
    class Timesheet_model extends CI_Model
    {
        public function __construct()
        {
            $this->load->database();
        }
        public function insert_timesheet($data_in)
        {
            $token = md5("tk: ".$data_in['kimai_id']);
            $data = $data_in;
            $data['token'] = $token;
            $this->db->insert('merged_timesheet',$data);
        }
        public function getByKimaiId($kimai_id)
        {
            $id = intval($kimai_id);
            $query = $this->db->get_where('merged_timesheet',array('kimai_id'=>$id));
            return $query->row_array();
        }
        public function getTimesheets()
        {
            $query = $this->db->get('merged_timesheet');
            return $query->result_array();
        }
    }
?>