<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Time_controller extends CI_Controller
    {
        private $settings;

        public function __construct()
        {
            parent::__construct();
            $this->load->helper('url_helper');
            // load libraries
            $this->load->library('api_handler');
            $this->load->library('sync_handler');
            //setup timetrackers config
            $this->config->load('tt_config', TRUE);
            $this->settings = $this->config->item('tt_config');
        }
        public function index()
        {
            $data['title'] = 'Dolibar & Kimai Merger';
            $data['console_log'] = '';
            $api =& $this->api_handler;
            try
            {
                // sync timesheets using Dolibarr API & Kimai API
                // on page load.
                if($this->settings['transfer_on_load'])
                {
                    
                    $data['synced_customers'] = $this->sync_handler->syncCustomers();
                    $data['synced_projects'] = $this->sync_handler->syncProjects();
                    $data['synced_tasks'] = $this->sync_handler->syncTasks();
                    $this->sync_handler->syncUsers();
                    $data['synced_timesheets'] = $this->sync_handler->syncTimesheets();
                    $data['edited_timesheets'] = $this->sync_handler->getEditedTimesheets();
                }
                $this->load->view('templates/header', $data);
                $this->load->view('timetracker/main', $data);
                $this->load->view('templates/footer');
            }
            catch(Exception $e)
            {
                $data['heading'] = "Whoops... Error.";
                $data['message'] = 'Cant reach server';
                $data['console_log'] = $e->getMessage();
                $this->load->view('errors/html/error_404.php', $data);
                $this->load->view('templates/footer');
            }
            
        }
    }
?>