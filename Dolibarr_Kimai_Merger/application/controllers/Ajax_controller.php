<?php
    class Ajax_controller extends CI_Controller
    {
        public function __construct()
        {
            parent::__construct();
            $this->load->helper('url_helper');
            $this->load->library('sync_handler');
            $this->load->library('restore_associations');
            $this->config->load('tt_config', TRUE);
        }
        public function ajax_restore()
        {
            $flag = $this->restore_associations->restore();
            if($flag)
            {
                echo '200 OK';
            }
            else
            {
                echo '500 Internal Server Error';
            }
        }
        public function ajax_sync()
        {
            if($this->settings['sync_users'])
            {
                $this->sync_handler->syncUsers();
            }
            $data['synced_customers'] = $this->sync_handler->syncCustomers();
            $data['synced_projects'] = $this->sync_handler->syncProjects();
            $data['synced_tasks'] = $this->sync_handler->syncTasks();
            $data['synced_timesheets'] = $this->sync_handler->syncTimesheets();
            $data['edited_timesheets'] = $this->sync_handler->getEditedTimesheets();
            echo json_encode($data);
        }
    }
?>