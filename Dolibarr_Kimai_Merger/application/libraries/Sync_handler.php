<?php
    class Sync_handler
    {
        private $ci;
        private $settings;
        public function __construct()
        {
            $this->ci =& get_instance();
            $this->ci->load->helper('url');
            // load models
            $this->ci->load->model('timesheet_model');
            $this->ci->load->model('customer_model');
            $this->ci->load->model('project_model');
            $this->ci->load->model('task_model');
            $this->ci->load->model('user_model');
            // load libraries
            $this->ci->load->library('api_handler');
            //load timetracker's config
            $this->ci->config->load('tt_config', TRUE);
            $this->settings = $this->ci->config->item('tt_config');
        }
        


        public function getEditedTimesheets()
        {
            $registered_timesheets = $this->ci->timesheet_model->getTimesheets();
            $kimai_timesheets = $this->ci->api_handler->callKimaiAPI('GET','timesheets?user=all');
            $ids = array();
            foreach($registered_timesheets as $r_timesheet)
            {
                foreach($kimai_timesheets as $k_timesheet)
                {
                    if($k_timesheet['id'] == $r_timesheet['kimai_id'])
                    {
                        // formatting the kimai date to compare
                        $kimai_date = date_create($k_timesheet['begin']);
                        $date = date_format($kimai_date,'Y-m-d H:i:s');
                        //flags to detect differences between timetracker's database and kimai.
                        $flag1 = $k_timesheet['activity'] == $r_timesheet['activity_id'];
                        $flag2 = $k_timesheet['project'] == $r_timesheet['project_id'];
                        $flag3 = $date == $r_timesheet['start'];
                        $flag4 = $k_timesheet['duration'] == $r_timesheet['duration'];

                        if(!($flag1 && $flag2 && $flag3 && $flag4))
                        {
                            array_push($ids,$r_timesheet['kimai_id']);
                        }
                        break;
                    }
                    
                }
            }
            return $ids;
        }
        public function syncUsers()
        {
            $doli_users = $this->ci->api_handler->callDOLAPI("GET", "users?sortfield=t.rowid&sortorder=ASC");
            $kimai_users = $this->ci->api_handler->callKimaiAPI("GET","users?visible=3");
            foreach($doli_users as $doli_user)
            {
                $db_user = $this->ci->user_model->getUserByDoliID($doli_user['id']);
                //if user doesnt exist in timetrackers database
                if(empty($db_user))
                {
                    $flag = false; //check if user exists in kimai
                    foreach($kimai_users as $kimai_user)
                    {
                        if($kimai_user['username'] == $doli_user['login'])
                        {
                            $flag =true;
                            $token = md5($doli_user['login']);
                            $data = array(
                                'doli_id' => $doli_user['id'],
                                'kimai_id' => $kimai_user['id'],
                                'token' => $token,
                                'username' => $doli_user['login']
                            );
                            $this->ci->user_model->insertUser($data);
                            break;
                        }
                    }
                    if(!$flag)
                    {
                        // We want to create the user from dolibarr to kimai
                        // Because Kimai API doesnt support [POST] Request to create
                        // A new user, we do it via database.
                        $options = [
                            'cost' => 13,
                          ];
                        // Kimai uses BCRYPT to encrypt the passwords
                        // 
                        $plain_password = $this->settings['default_user_password'];
                        $password = password_hash($plain_password, PASSWORD_BCRYPT, $options);
                        $data_for_kimai = array(
                            'username' => $doli_user['login'],
                            'username_canonical' => $doli_user['login'],
                            'email' => $doli_user['email'],
                            'email_canonical' => $doli_user['email'],
                            'password' => $password,
                            'enabled' => 1,
                            'alias' => $doli_user['firstname'] .' '. $doli_user['lastname'],
                            'registration_date' => date('Y-m-d H:i:s'),
                            'roles' => 'a:0:{}'
                        );
                        //insert to kimai database
                        $this->ci->user_model->insertUserToKimai($data_for_kimai);
                        //insert to timetrackers db
                        $token = md5($doli_user['login']);
                        $data = array(
                            'doli_id' => $doli_user['id'],
                            'kimai_id' => $kimai_user['id'],
                            'token' => $token,
                            'username' => $doli_user['login']
                        );
                    }
                }
            }
        }
        public function syncCustomers()
        {
            // dolibarr customers
            $doli_customers = $this->ci->api_handler->callDOLAPI('GET', 'thirdparties?mode=1');
            $kimai_customers = $this->ci->api_handler->callKimaiAPI('GET', 'customers');
            // customers in TimeTracker's database
            $db_customers = $this->ci->customer_model->getCustomers();
            $count =0; // count synced customers
            foreach($doli_customers as $doli_customer)
            {
                //if not in in timetracker's database
                $db_customer = $this->ci->customer_model->getCustomerByDoliID($doli_customer['id']);
                if(empty($db_customers))
                {
                    $token = md5("tk: ".$doli_customer['id']);
                    $body =  '{
                        "name": "'.$doli_customer['name'].'",
                        "number": "0",
                        "comment": "'.$token.'",
                        "company": "'.$doli_customer['name'].'",
                        "contact": "null",
                        "address": "'.$doli_customer['address'].'",
                        "country": "'.$doli_customer['country_code'].'",
                        "currency": "'.$doli_customer['multicurrency_code'].'",
                        "phone": "'.$doli_customer['phone'].'",
                        "fax": "'.$doli_customer['fax'].'",
                        "mobile": "'.$doli_customer['phone'].'",
                        "email": "'.$doli_customer['email'].'",
                        "homepage": "'.$doli_customer['url'].'",
                        "timezone": "UTC",
                        "color": "blue",
                        "fixedRate": 0,
                        "hourlyRate": 0,
                        "visible": true
                      }';
                    //register customer to kimai 
                    $kimai_customer = $this->ci->api_handler->callKimaiAPI('POST','customers',$body);
                    //register customer to timetracker's database
                    $data = array(
                       'doli_id' => $doli_customer['id'],
                       'kimai_id' => $kimai_customer['id'],
                       'token' => $token
                    );
                    $this->ci->customer_model->insertCustomer($data);
                    $count = $count +1;
                }
                else
                {
                    //We want to handle any missed or wrong record in TimeTracker's database.
                    //check if customer exists in kimai 
                    foreach($kimai_customers as $kimai_customer)
                    {
                        if($kimai_customer['name'] == $doli_customer['name'])
                        {
                            //check if our database has the correct kimai id for the customer.
                            if($db_customer['kimai_id'] != $kimai_customer['id'])
                            {
                                $this->ci->customer_model->deleteCustomerByDoliID($doli_customer['id']);
                                $token = md5("tk: ".$doli_customer['id']);
                                //register customer to timetracker's database
                                $data = array(
                                'doli_id' => $doli_customer['id'],
                                'kimai_id' => $kimai_customer['id'],
                                'token' => $token
                                );
                                $this->ci->customer_model->insertCustomer($data);
                                $count = $count +1;
                            }
                            break;
                        }
                    }
                }
            }
            return $count;
        }
        public function syncProjects()
        {
            $db_customers = $this->ci->customer_model->getCustomers();
            $doli_projects = $this->ci->api_handler->callDOLAPI('GET','projects?sortfield=t.rowid');
            $kimai_projects = $this->ci->api_handler->callKimaiAPI('GET','projects');
            $count = 0; // count synced projects
            foreach($db_customers as $db_customer)
            {
                foreach($doli_projects as $doli_project)
                {
                    if($doli_project['socid'] == $db_customer['doli_id'])
                    {
                        $db_project = $this->ci->project_model->getProjectByDoliID($doli_project['id']);
                        // if project is not registered in our database.
                        if(empty($db_project))
                        {
                            $token = md5("tk: " .$doli_project['id']);
                            $body = '{
                                "name": "'.$doli_project['title'].'",
                                "comment": "'.$token.'",
                                "orderNumber": "",
                                "customer": '.intval($db_customer['kimai_id']).',
                                "color": "red",
                                "fixedRate": 0,
                                "hourlyRate": 0,
                                "visible": true
                              }';
                            // insert project to kimai
                            $kimai_project = $this->ci->api_handler->callKimaiAPI('POST','projects',$body);
                            // insert project to timetracker's database
                            $data = array(
                                'doli_id' => $doli_project['id'],
                                'kimai_id' => $kimai_project['id'],
                                'token' => $token
                            );
                            $this->ci->project_model->insertProject($data);
                            $count=$count+1;
                        }
                        else
                        {
                            // We want to handle any missed or wrong record in TimeTracker's database.
                            // check if project exists in kimai 
                            foreach($kimai_projects as $kimai_project)
                            {
                                if($kimai_project['name'] == $doli_project['title'])
                                {
                                    if($db_project['kimai_id'] != $kimai_project['id'])
                                    {
                                        $this->ci->project_model->deleteProjectByDoliID($doli_project['id']);

                                        $token = md5("tk: " .$doli_project['id']);
                                        //insert project to timetracker's database
                                        $data = array(
                                            'doli_id' => $doli_project['id'],
                                            'kimai_id' => $kimai_project['id'],
                                            'token' => $token
                                        );
                                        $this->ci->project_model->insertProject($data);
                                        $count=$count+1;
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            return $count;
        }
        public function syncTasks()
        {
            $db_projects = $this->ci->project_model->getProjects();
            $doli_tasks = $this->ci->api_handler->callDOLAPI("GET","tasks?sortfield=t.rowid");
            $kimai_activities = $this->ci->api_handler->callKimaiAPI("GET","activities");
            $count=0;
            foreach($db_projects as $db_project)
            {
                foreach($doli_tasks as $doli_task)
                {
                    if($doli_task['fk_project'] == $db_project['doli_id'])
                    {
                        $db_task = $this->ci->task_model->getTaskByDoliID($doli_task['id']);
                        if(empty($db_task))
                        {
                            $token = md5("tk: ".$doli_task['id']);
                            $body = '{
                                "name": "'.$doli_task['label'].'",
                                "comment": "'.$token.'",
                                "project": '.intval($db_project['kimai_id']).',
                                "color": "yellow",
                                "fixedRate": 0,
                                "hourlyRate": 0,
                                "visible": true
                              }';
                              // insert task to kimai activities
                              $kimai_task = $this->ci->api_handler->callKimaiAPI('POST','activities',$body);
                              // insert task to timetracker's database
                              $data = array(
                                  'doli_id'=>$doli_task['id'],
                                  'kimai_id'=>$kimai_task['id'],
                                  'token'=>$token
                              );
                              $this->ci->task_model->insertTask($data);
                              $count=$count+1;
                        }
                        else
                        {
                            foreach($kimai_activities as $kimai_activity)
                            {
                                if($kimai_activity['name'] == $doli_task['label'])
                                {
                                    if($db_task['kimai_id'] != $kimai_activity['id'])
                                    {
                                        // Delete record with wrong Kimai Id
                                        $this->ci->task_model->deleteTaskByDoliID($doli_task['id']);
                                        $token = md5("tk: ".$doli_task['id']);
                                        // insert task to timetracker's database
                                        $data = array(
                                            'doli_id'=>$doli_task['id'],
                                            'kimai_id'=>$kimai_activity['id'],
                                            'token'=>$token
                                        );
                                        $this->ci->task_model->insertTask($data);
                                        $count=$count+1;
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            return $count;
        }
        public function syncTimesheets()
        {
            $kimai_timesheets = $this->ci->api_handler->callKimaiAPI("GET", "timesheets?user=all&active=0");
            $count = 0;
            foreach($kimai_timesheets as $kimai_timesheet)
            {
                $db_task = $this->ci->task_model->getTaskByKimaiID($kimai_timesheet['activity']);
                $db_project = $this->ci->project_model->getProjectByKimaiID($kimai_timesheet['project']);
                $db_user = $this->ci->user_model->getUserByKimaiID($kimai_timesheet['user']);
                $db_timesheet = $this->ci->timesheet_model->getByKimaiId($kimai_timesheet['id']);
                if(empty($db_timesheet))
                {
                    $kimai_date = date_create($kimai_timesheet['begin']);
                    $date = date_format($kimai_date,'Y-m-d H:i:s');
                    $body = '{
                        "date": "'.$date.'",
                        "duration": '.$kimai_timesheet['duration'].',
                        "user_id": '.$db_user['doli_id'].',
                        "note": ""
                    }';
                    // update dolibarr task
                    $this->ci->api_handler->callDOLAPI("POST","tasks/".$db_task['doli_id']."/addtimespent",$body);
                    // insert data to local table
                    $data = array(
                        'kimai_id'=>$kimai_timesheet['id'],
                        'task_id'=>$db_task['doli_id'],
                        'activity_id'=>$kimai_timesheet['activity'],
                        'project_id'=>$kimai_timesheet['project'],
                        'duration'=>$kimai_timesheet['duration'],
                        'user_id'=>$kimai_timesheet['user'],
                        'start'=>$date
                    );
                    $this->ci->timesheet_model->insert_timesheet($data);
                    //increase counter of synced timesheets
                    $count = $count+1;
                }
            }
            return $count;
        }
        // this function can transfer the timesheets no matter what.
        // but its very time-inefficient and too complicated.
        // It makes use only of the Dolibarr & Kimai API
        /*
        public function syncTimesheetsWithBruteForce() 
        {
            
            $doli_projects = $this->api_handler->callDOLAPI("GET","projects?sortfield=t.rowid&sortorder=ASC");
            $doli_users = $this->api_handler->callDOLAPI("GET", "users?sortfield=t.rowid&sortorder=ASC");

            $kimai_timesheets = $this->api_handler->callKimaiAPI("GET", "timesheets?user=all&exported=0&active=0");
            $kimai_projects = $this->api_handler->callKimaiAPI("GET", "projects");
            $kimai_users = $this->api_handler->callKimaiAPI("GET","users");
            // Count timesheets that have been transfered
            $count = 0;
            // Loop through kimai_timesheets > kimai_projects > kimai_activities > update dolibarr & kimai
            foreach($kimai_timesheets as $timesheet)
            {
                foreach($kimai_projects as $kimai_project)
                {
                    if($kimai_project['id'] == $timesheet['project'])
                    {
                        // Get activities only for the current project
                        $kimai_activities = $this->api_handler->callKimaiAPI("GET","activities?project=" .$kimai_project['id']);
                        foreach($doli_projects as $doli_project)
                        {
                            if($doli_project["title"] == $kimai_project["name"])
                            {
                                $doli_tasks = $this->api_handler->callDOLAPI("GET","tasks?sortfield=t.rowid&sortorder=ASC");
                                //
                                foreach($kimai_activities as $kimai_activity)
                                {
                                    if($kimai_activity['id'] == $timesheet['activity'])
                                    {    
                                        foreach($doli_tasks as $doli_task)
                                        {
                                            if($doli_task["label"] == $kimai_activity["name"] && $doli_task["fk_project"] == $doli_project["id"])
                                            { 
                                                // get dolibarr user id
                                                $id = 0;
                                                foreach($kimai_users as $kimai_user)
                                                {
                                                    if($kimai_user['id'] == $timesheet['user'])
                                                    {
                                                        foreach($doli_users as $doli_user)
                                                        {
                                                            if($kimai_user['username'] == $doli_user['login'])
                                                            {
                                                                $id = intval($doli_user['id']);
                                                                break;
                                                            }
                                                        }
                                                        break;
                                                    }
                                                }       
                                                // date format                                        
                                                $kimai_date = date_create($timesheet['begin']);
                                                $date = date_format($kimai_date,'Y-m-d H:i:s');
                                                // request body
                                                $body = '{
                                                    "date": "'.$date.'",
                                                    "duration": '.$timesheet['duration'].',
                                                    "user_id": '.$id.',
                                                    "note": "string"
                                                }';
                                                // update dolibarr task
                                                $this->api_handler->callDOLAPI("POST","tasks/".$doli_task['id']."/addtimespent",$body);
                                                // export kimai timesheet
                                                $this->api_handler->callKimaiAPI("PATCH", "timesheets/".$timesheet['id']."/export");
                                                // insert data to local table
                                                $data = array(
                                                    'kimai_id'=>$timesheet['id'],
                                                    'task_id'=>$doli_task['id'],
                                                    'activity_id'=>$kimai_activity['id'],
                                                    'project_id'=>$kimai_project['id'],
                                                    'duration'=>$timesheet['duration'],
                                                    'user_id'=>$timesheet['user'],
                                                    'start'=>$date
                                                );
                                                $this->timesheet_model->insert_timesheet($data);
                                                // increase counter of synced timesheets
                                                $count = $count+1;
                                                break;
                                            }
                                        }
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }
            return $count;
        }
        */
    }
?>
