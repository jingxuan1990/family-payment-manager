<?php

/**
 * class is used to display user's information
 * @author andy
 *
 */
class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'view'));
        $this->load->model('User_model', 'user');
        $this->load->model('Record_model', 'record');
    }
    
    /**
     * user logout
     */
    public function  logout()
    {
//     unset the application's session
       $this->session->sess_destroy();
       redirect('/login');
    }
    
    /**
     * get user's information
     * @param string $username -- user's name
     */
    public function  info($username)
    {
        $session_username  = $this->session->userdata('username');
        if ($username !== $session_username)
        {
            redirect('login');
        }
        render($this, 'Home', 'content/personal.phtml', array("active"=>3));
    }
    
    /**
     * add a record of the current logined user
     */  
    public function add_record()
    {
        $money    = $this->input->post('money', true);
        $desc     = $this->input->post('desc', true);
        $user_id  = $this->getCurrentUserId();
        $log_time = date('Y-m-d H:i:s');
        $count = $this->record->add_record(array('user_id'=>$user_id, 'money'=>$money, 'desc'=>$desc, 'log_time'=>$log_time));
        if($count){
            $this->writeJson(array('status'=>true, 'message'=>'添加记录成功！'));
        }else {
            $this->writeJson(array('status'=>false, 'message'=>'添加记录失败！'));
        }
    }
    
    /**
     * update the curent logined user's password
     */
    public function update_password()
    {
        $password     = $this->input->post('password', true);
        $old_password = $this->input->post('old_password', true);
        $username     = $this->session->userdata('username');
        
        if ($this->user->updatePassword($username, $password, $old_password)){
            $this->writeJson(array('status'=>true, 'message'=>'密码修改成功！'));
        }else{
            $this->writeJson(array('status'=>false, 'message'=>'旧密码不正确！'));
        }
    }
    
    /**
     * get the current user's all of record
     */
    public function  get_all_records()
    {
        $data['records'] = $this->record->get_all_records();
        $data['active']    = 1;
        
        render($this, 'Home', 'content/record.phtml', $data);
    }
    
    /**
     *  count++ or count--
     * @param number $flag -- add or sub
     */
    public function  add_or_sub_count($flag = 1)
    {
        $count = $this->get_user_count();
        if($flag || (!$flag && $count > 0)){
            $count = $this->user->add_or_sub_count($flag);
        }
        $this->writeJson(array("status"=>true, "count"=>$count));
    }
    
    /**
     * get user's total count
     * @return integer $count -- user's count
     */
    public function  get_user_count()
    {
        return $this->user->get_user_count();
    }
    
    /**
     * delete a record from the current logined user
     * @param integer $record_id -- the deleted record's id
     */
    public function delete_record($record_id)
    {
       if ($this->record->delete_record($record_id))
       {
           $this->writeJson(array("status"=>true));
       }else 
       {
           $this->writeJson(array("status"=>false));
       }
    }
    
    /**
     * method is used to display the result for all the user
     */
    public function  result()
    {
        $data['user_result']  = $this->record->get_all_user_total(); // user's result 
        $data['total']        = $this->record->get_total()->total; // the total of money
        $total_count          = $this->user->get_total_count()->total_count; // the number of eating
        $users                = $this->user->get_all_user_count();  // get the variable count of all the user
        $data['pay_result']   = array(); // the result of spend moeny 
        $data['final_result'] = array();
        if ($total_count) {
            foreach ($users as $user)
            {
                $username               = $user->username;
                $user_count             = $user->count ? $user->count : 0;
                $user_pay               = round(($user_count / $total_count) * $data['total'], 2);
                $data['pay_result'][]   = array('username'=>$username, 'user_pay'=>$user_pay, 'user_count' => $user_count);
                $user_total_money       = $this->record->get_user_total($user->id)->total;
                $result_money           = $user_total_money - $user_pay;
                $data['final_result'][] = array('username'=>$username, 'result_total' =>$result_money);
            }
        }
        
        $data['active']    = 2;
        render($this, 'Home', 'content/result.phtml', $data);
    }
}