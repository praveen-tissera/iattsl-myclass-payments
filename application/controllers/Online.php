<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Online extends CI_Controller {

    public function __construct() {
		parent::__construct();
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->model('Online_User_model');
        $this->load->model('Mark_model');
        $this->load->library('session');
        //load url library
		$this->load->helper('url');
        date_default_timezone_set("Asia/colombo");
	}
    public function index(){
        $success = $this->session->flashdata('success');
		$error = $this->session->flashdata('error');
        $data = [];
        if (!empty($success)) {
            $data['success'] = $success;
        }
        if (!empty($error)) {
            $data['error'] = $error;
        }
        // $this->user_model->get_usreData();
        $this->load->view('online-student',$data);
    }
     public function idValidator($student_id=0, $branch='PEL'){
       
        $success = $this->session->userdata('success_message_display');
      

        if ($student_id == 0) {
            
            $this->form_validation->set_rules('studentid', 'Class ID', 'required');
            if ($this->form_validation->run() == FALSE){
                
                $this->load->view('online-student');
            }
            else{
                if(isset($_POST['center'])){
                     $student_id = $_POST['center'].'/'.trim($_POST['studentid']);
                        if($_POST['center'] == 'ONL'){
                            $session_id = 4;
                        }else{
                            $session_id = 3;
                        }
                     
                }
                
               
                $result = $this->Online_User_model->get_usreData($student_id,$session_id);

                if($result == 0){
                    $data['message'] = "No results found";
                    $this->load->view('online-student',$data);
                }else{
                    $data['student_data'] = $result;
                    // print_r($data);
                    $this->load->view('online-student',$data);
                }
                
            }
        }

       else{  
        
        $student_id = $branch.'/'.$student_id;       
       
            if($branch == 'ONL'){
                $session_id = 4;
            }else{
                $session_id = 3;
            }
        $result = $this->Online_User_model->get_usreData($student_id,$session_id);
        if($result == 0){
            $data['message'] = "No results found";
            $this->load->view('online-student',$data);
        }else{
            $data['student_data'] = $result;
            $data['message'] = "Updated successfully";
            $this->load->view('online-student',$data);
        }


        }
        
    }

    public function attendnce(){
       
        $data = array(
            'admission_id' =>$_POST['admission'],
            'student_id' =>$_POST['studentid'],
            'course_name' => $_POST['course'],
            'grade_id'=>$_POST['gradeid'],
            'grade_lable'=>$_POST['gradelable'],
            'attend_date_time' => date('Y-m-d H:i:s'),
            'attend_date' => date('Y-m-d'),
        );
        
        $url = $_POST['classlink'];
        $result = $this->Online_User_model->set_attendance($data);
        if($result == 1){
             header("Location:".$url);
        }else if($result == 0){

        }else{
            $this->session->set_flashdata('error','Please try again');
            redirect('welcome/index');
        }
       
    }

    public function payment_details($invoice_id, $amount, $label, $student_record_id){
        // create arrya of data pass from url
        $data = array(
            'invoice_id' => $invoice_id,
            'amount' => urldecode($amount),
            'label' =>urldecode($label),
           'student_record_id' => $student_record_id,
        );
     print_r($data);
       
    }
    // crete public function to call insert_payment method in user_model file 
    public function insertPayment(){
        $this->form_validation->set_rules('amount', 'Amount', 'required');
        $this->form_validation->set_rules('invoice_id', 'Receipt Number', 'required');
        $this->form_validation->set_rules('label', 'Paid Month', 'required');
        $this->form_validation->set_rules('student_id', 'Student ID', 'required');
        if ($this->form_validation->run() == FALSE){
            $this->load->view('online_student');
        }
        else{
            date_default_timezone_set('Asia/Colombo');
            $currentDate = date('Y-m-d H:i:s');
            $data = array(
                'ID' => NULL,
                'amount' => $_POST['amount'],
                'payment_method' => 'cash',
                'transaction_id' => '',
                'note' => '',
                'invoice_label' => $_POST['label'],
                'invoice_payable' => $_POST['amount'],
                'invoice_id' => $_POST['invoice_id'],
                'student_record_id' => $_POST['student_record_id'],
                'school_id' => '1',
                'created_at' => $currentDate,
                );

                // print_r($data);
            $result = $this->Online_User_model->insert_payment($data);
            // $result = 2;
            if($result > 1){
                // echo "success";
                // $this->session->set_flashdata('success_message_display', 'Payment Updated sucessfully');
                $student_id = $_POST['student_id'];
                // split $student_id using '/'
                $student_id_array = explode('/', $student_id);
                // get last element of array
                $student_id = end($student_id_array);
                // get student branch from $student_if_array's first element
                $student_branch = $student_id_array[0];
                            if($student_branch == 'ONL'){
                                // remove ending number after hyphen
                                $student_id = str_replace('ONL/', '', $student_id);
                                $student_id_without_ending_number  = substr($student_id, 0, strrpos($student_id, '-'));
                              }else{
                                $student_id_without_ending_number = end($student_id_array);
                            }
                           
                        
                $this->session->set_userdata('success_message_display', 'Payment Updated sucessfully');
               
                redirect('/online/idValidator/'.$student_id_without_ending_number.'/'.$student_branch);

            } else if($result == 0){
                // echo "error";
                $student_id = $_POST['student_id'];
                // split $student_id using '/'
                $student_id_array = explode('/', $student_id);
                // get last element of array
                $student_id = end($student_id_array);
                $student_branch = $student_id_array[0];
                            if($student_branch == 'ONL'){
                                // remove ending number after hyphen
                                $student_id = str_replace('ONL/', '', $student_data[0]['profile']->admission_number);
                                $student_id_without_ending_number  = substr($student_id, 0, strrpos($student_id, '-'));
                              }else{
                                $student_id_without_ending_number = end($student_id_array);
                            }


                
                $this->session->set_userdata('error_message_display', 'Error on Payment Updated ');

                redirect('/online/idValidator/'.$student_id);
            } else{
                // echo "default";
                $student_id = $_POST['student_id'];
                // split $student_id using '/'
                $student_id_array = explode('/', $student_id);
                // get last element of array
                $student_id = end($student_id_array);

                $this->session->set_userdata('error_message_display', 'Error on Payment Updated ');

                redirect('/online/idValidator/'.$student_id);
            }
        }

        
    }

    public function pdfgenerator(){
        $result = $this->Online_User_model->get_payment_detail($_POST['invoiceid']);
        $admission = $result[1]->admission_number;
        // retrive branch code MAH/25-090
        $branch_code = explode('/', $admission);
        $branch_code = $branch_code[0];
        switch($branch_code){
            case 'MAH':
                $branch_name = 'Maharagama Center';
                break;
            case 'PEL':
                $branch_name = 'Pelawatta Center';
                break;
            case 'BAT':
                $branch_name = 'Battaramulla Center';
                break;
            case 'MAT':
                $branch_name = 'Mattegoda Center';
                break;
            case 'HRI':
                $branch_name = 'Hiripitiya Center';
                break;
           default:
                $branch_name = 'Unknown Center';
                break;
        }
        if($result[2]->invoice_payable < $result[0]->amount){
            $result[0]->status = 'Partially paid';
        }
        $html =  "<table border='0' style='margin-top:40px;width:100%;border:0px black solid;border-collapse: collapse;'><thead ><tr><th style='padding:10px;text-align:left;font-size:18px;'> IATT|SL $branch_name : {$result[0]->label}</th><th scope='col' style='text-align:right;font-size:16px'>  Invoice No: {$result[0]->invoice_number} </th></tr></thead><tbody></table><hr><table border='1' style='margin-top:10px;width:100%;border:1px black solid;border-collapse: collapse;font-family:arial;'><thead class='bg-primary'><tr><td scope='col' style='padding:10px;'>Invoice Title</td><td scope='col' style='padding:10px;'>{$result[0]->label}</td></tr><tr><td scope='col' style='padding:10px;'>Student Name</td><td scope='col' style='padding:10px;'>{$result[1]->name}</td></tr><tr><td scope='col' style='padding:10px;'>Student ID</td><td scope='col' style='padding:10px;'>{$result[1]->admission_number}</td></tr><tr><td scope='col' style='padding:10px;'>Receipt Number</td><td scope='col' style='padding:10px;'>{$result[2]->receipt_number}</td></tr></tr><tr><td scope='col' style='padding:10px;'>Date Issued</td><td scope='col' style='padding:10px;'>{$result[2]->created_at}</td></tr></tr><tr><td scope='col' style='padding:10px;'>Student Grade</td><td scope='col' style='padding:10px;'>{$_POST['grade']}</td></tr></tr><tr><td scope='col' style='padding:10px;'>Amount (in LKR) </td><td scope='col' style='padding:10px;'>{$result[2]->invoice_payable}</td></tr></tr><tr><td scope='col' style='padding:10px;'>Payment Status</td><td scope='col' style='padding:10px;'>{$result[0]->status}</td></tr></thead><tbody></table><table border='0' style='margin-top:20px;width:100%;border:0px black solid;border-collapse: collapse;'><thead ><tr><th style='padding:10px;text-align:right;font-size:14px;'> Phone: +94 74 080 7306 | +94 76 435 4111 Email: info@iattsl.edu.lk</th></tr></thead><tbody></table>";
        $this->load->library('pdf');
		$this->dompdf->loadHtml($html);
		$this->dompdf->setPaper('A4', 'portrait');
		$this->dompdf->render();
		$this->dompdf->stream("{$result[2]->receipt_number}", array("Attachment" => 1));
    }


    public function income(){
        $this->load->view('income');
    }
    public function adminincome(){
        $this->load->view('adminincome');
    }

    public function incomesummary(){
        date_default_timezone_set('Asia/Colombo');
         if(empty($this->input->post('filteroptions'))){
            
            redirect('welcome/income');
        }
        // print_r($_POST);
        if($this->input->post('filteroptions') == 'week'){
            $datedata[] = date('Y-m-d');
            $datedata[] = date('Y-m-d', strtotime('-6 days'));
            $date_range_string = $datedata[1] . ' - ' . $datedata[0];
        }elseif($this->input->post('filteroptions') == 'month'){
            $datedata[] = date('Y-m-d');
            // Get the first day of the current month
            $datedata[] = date('Y-m-01');
            $date_range_string = $datedata[1] . ' - ' . $datedata[0];
        }elseif($this->input->post('filteroptions') == 'custome'){
            $datedata = $this->input->post('incomedate');
            $date_range_string = $datedata;
        }

        $result = $this->Online_User_model->get_payments( $datedata);
        
            $data['income_summary'] = $result;
            $data['date_select'] = $date_range_string;
            // print_r($data);
            $this->load->view('income',$data);
        
    }
    public function adminincomesummary(){
        date_default_timezone_set('Asia/Colombo');
        // print_r($_POST);
        if(empty($this->input->post('filteroptions'))){
            
            redirect('welcome/adminincome');
        }
        if($this->input->post('filteroptions') == 'week'){
            $datedata[] = date('Y-m-d');
            $datedata[] = date('Y-m-d', strtotime('-6 days'));
            $date_range_string = $datedata[1] . ' - ' . $datedata[0];
        }elseif($this->input->post('filteroptions') == 'month'){
            $datedata[] = date('Y-m-d');
            // Get the first day of the current month
            $datedata[] = date('Y-m-01');
            $date_range_string = $datedata[1] . ' - ' . $datedata[0];
        }elseif($this->input->post('filteroptions') == 'lastmonth'){
            // get last month date range
            $datedata[] = date('Y-m-d', strtotime('last day of last month'));
            $datedata[] = date('Y-m-d', strtotime('first day of last month'));
            
            // $datedata[] = date('Y-m-d', strtotime('-1 month'));
            $date_range_string = $datedata[1] . ' - ' . $datedata[0];
        }elseif($this->input->post('filteroptions') == 'custome'){
            $datedata = $this->input->post('incomedate');
            $date_range_string = $datedata;
        }

        $result = $this->Online_User_model->get_payments( $datedata);
        
            $data['income_summary'] = $result;
            $data['date_select'] = $date_range_string;
            // print_r($data);
            $this->load->view('adminincome',$data);
        
    }


    // payment history
        public function paymenthistory(){
        
        $success = $this->session->flashdata('success');
		$error = $this->session->flashdata('error');
        $data = [];
        if (!empty($success)) {
            $data['success'] = $success;
        }
        if (!empty($error)) {
            $data['error'] = $error;
        }
        $grades = $this->Mark_model->get_classes();
        $data['grades'] = $grades;
        // print_r($data);
           
            $this->load->view('student_payment',$data);   
             
        
       
    }



    public function gradewisepaymentSumamry(){
        $success = $this->session->flashdata('success');
        $error = $this->session->flashdata('error');
        // get form data input name grade
        // $date = $this->input->post('date');
        $branch = $this->input->post('branch');
        $class_detail = $this->input->post('class');
       
        $session_id = 3;  
        $class_array = explode('*', $class_detail);
        $class_id = $class_array[0];
        $class_name = $class_array[1];

         $subjects = $this->Mark_model->get_subjecs($class_id);
        //  print_r($subjects);
        //  check whether ICT lable eixists in subjects array and return ID index value
        $ict_subject_id = 0;
        $subject_name;
        foreach($subjects as $subject){
            if($subject->label == 'ICT'){
                $ict_subject_id = $subject->ID;
                $subject_name = $subject->label;
                break;
            }
            if($subject->label == $branch){
                $ict_subject_id = $subject->ID;
                $subject_name = $subject->label;
                break;
            }
        }
        $grades = $this->Mark_model->get_classes();
        $data['grades'] = $grades;
        if($ict_subject_id == 0){
            $data['message'] = "No Student Found for this class";
            $this->load->view('student_payment',$data);
        }else{
         $students = $this->Online_User_model->get_students_by_branch($ict_subject_id, $session_id , $branch);
        // print_r($students);

      
        
        $data['students'] = $students;
        // $data['date'] = $date;
        $data['branch'] = $branch;
        $data['pclass_id'] = $class_id;
        $data['pclass_name'] = $class_name;
        $data['subject_id'] = $ict_subject_id;
        $data['subject_name'] = $subject_name;
       
        $this->load->view('student_payment',$data);   
        
        }
    }

}
