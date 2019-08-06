<?php
class Restore_associations
{
    private $ci;
    public function __construct()
    {
        $this->ci =& get_instance();
        // load models
        $this->ci->load->model('timesheet_model');
        $this->ci->load->model('customer_model');
        $this->ci->load->model('project_model');
        $this->ci->load->model('task_model');
        $this->ci->load->model('user_model');
        // load libraries
        $this->ci->load->library('api_handler');
    }

    public function associateUsers()
    {
        $api =& $this->ci->api_handler;
        try
        {
            $doli_users = $api->callDOLAPI('GET', 'users?sortfield=t.rowid');
            $kimai_users = $api->callKimaiAPI('GET', 'users?visible=3');
        }
        catch(Exception $e)
        {
            return false; // send error signal
        }
        //clear the timetracker's customer associations
        $this->ci->user_model->wipeUsers();
        //remake customer associations between dolibarr and kimai
        foreach($doli_users as $doli_user)
        {
            foreach($kimai_users as $kimai_user)
            {
                if($doli_user['login'] == $kimai_user['username'])
                {
                    $token = md5('tk: '.$doli_user['id']);
                    $data = array(
                        'doli_id'=>$doli_user['id'],
                        'kimai_id'=>$kimai_user['id'],
                        'token'=>$token
                    );
                    $this->ci->user_model->insertUser($data);
                    break;
                } 
            }
        }
        return true; //return succes signal
    }
    public function associateCustomers()
    {
        $api =& $this->ci->api_handler;
        try
        {
            $doli_customers = $api->callDOLAPI('GET', 'thirdparties?mode=1');
            $kimai_customers = $api->callKimaiAPI('GET', 'customers');
        }
        catch(Exception $e)
        {
            return false; //return failure signal
        }
        //clear the timetracker's customer associations
        $this->ci->customer_model->wipeCustomers();
        //remake customer associations between dolibarr and kimai
        foreach($doli_customers as $doli_customer)
        {
            foreach($kimai_customers as $kimai_customer)
            {
                if($doli_customer['name'] == $kimai_customer['name'])
                {
                    $token = md5('tk: '.$doli_customer['id']);
                    $data = array(
                        'doli_id'=>$doli_customer['id'],
                        'kimai_id'=>$kimai_customer['id'],
                        'token'=>$token
                    );
                    $this->ci->customer_model->insertCustomer($data);
                    break;
                } 
            }
        }
        return true; //return success signal
    }
    public function associateProjects()
    {
        $api =& $this->ci->api_handler;
        try
        {
            $doli_projects = $api->callDOLAPI('GET', 'projects?sortfield=t.rowid');
            $kimai_projects = $api->callKimaiAPI('GET', 'projects');
        }
        catch(Exception $e)
        {
            return false; //return failure signal
        }
        //clear the timetracker's project associations
        $this->ci->project_model->wipeProjects();
        //remake project associations between dolibarr and kimai
        foreach($doli_projects as $doli_project)
        {
            foreach($kimai_projects as $kimai_project)
            {
                if($doli_project['title'] == $kimai_project['name'])
                {
                    $token = md5('tk: '.$doli_project['id']);
                    $data = array(
                        'doli_id'=>$doli_project['id'],
                        'kimai_id'=>$kimai_project['id'],
                        'token'=>$token
                    );
                    $this->ci->project_model->insertProject($data);
                    break;
                } 
            }
        }
        return true;//return success signal
    }
    public function associateTasks()
    {
        $api =& $this->ci->api_handler;
        try
        {
            $doli_tasks = $api->callDOLAPI('GET', 'tasks?sortfield=t.rowid');
            $kimai_activities = $api->callKimaiAPI('GET', 'activities');
        }
        catch(Exception $e)
        {
            return false; // return failure signal
        }
        //clear the timetracker's task associations
        $this->ci->task_model->wipeTasks();
        //remake task associations between dolibarr and kimai
        foreach($doli_tasks as $doli_task)
        {
            foreach($kimai_activities as $kimai_activity)
            {
                if($doli_task['label'] == $kimai_activity['name'])
                {
                    $token = md5('tk: '.$doli_task['id']);
                    $data = array(
                        'doli_id'=>$doli_task['id'],
                        'kimai_id'=>$kimai_activity['id'],
                        'token'=>$token
                    );
                    $this->ci->task_model->insertTask($data);
                    break;
                } 
            }
        }
        return true; //return succes signal
    }
    public function restore()
    {
        
        $flag1=$this->associateUsers();
        $flag2=$this->associateCustomers();
        $flag3=$this->associateProjects();
        $flag4=$this->associateTasks();
        if($flag1 && $flag2 && $flag3 && $flag4)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
}
?>