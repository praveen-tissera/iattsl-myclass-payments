<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mark extends CI_Controller {

    public function __construct() {
		parent::__construct();
        $this->load->helper('form');
        $this->load->library('form_validation');
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
        $grades = $this->Mark_model->get_classes();
        $data['grades'] = $grades;
        // print_r($data);
        $this->load->view('marks/class_selection',$data);
    }

    public function subjects(){
        $success = $this->session->flashdata('success');
        $error = $this->session->flashdata('error');
        // get form data input name grade
        $class_detail = $this->input->post('class');
        $class_array = explode('*', $class_detail);
        $class_id = $class_array[0];
        $class_name = $class_array[1];
         $subjects = $this->Mark_model->get_subjecs($class_id);
        $data['subjects'] = $subjects;
        $data['class_detail'] = $class_detail;
    
       $this->load->view('marks/subject_selection',$data);
        // print_r($grades);
    }

    public function students(){
        $success = $this->session->flashdata('success');
        $error = $this->session->flashdata('error');
        // get form data input name grade
        $class_id = $this->input->post('selectclassid');
        $class_name = $this->input->post('selectclass');
        $subject_detail = $this->input->post('subject');
        $subject_array = explode('*', $subject_detail);
        $subject_id = $subject_array[0];
        $subject_name = $subject_array[1];
        // print_r($subject_id);
        $session_id = 3;  
        $term = 'Term 1';   
         $students = $this->Mark_model->get_students($subject_id, $session_id);
        //  print_r($students);
         if($students == 0){
            $data['message'] = "No results found";
            $this->load->view('marks/class_selection',$data);
            
         }else{
            
            $data['students'] = $students;
            $data['class_id'] = $class_id;
            $data['class_name'] = $class_name;
            $data['subject_id'] = $subject_id;
            $data['subject_name'] = $subject_name;
            // print_r($data);
            $data['students'] = $this->Mark_model->get_student_marks($subject_id, $session_id, $term, $students);
            $this->load->view('marks/student_selection',$data);
         }
    }

    public function studentsViewAfterSubmit($selectclassid, $selectclass, $selectsubjectid, $selectsubject){
       
        $success = $this->session->flashdata('success');
        $error = $this->session->flashdata('error');
        // get form data input name grade
        $class_id = $selectclassid;
        $class_name = urldecode($selectclass);
        $subject_id = $selectsubjectid;
        $subject_name = urldecode($selectsubject);
       
       
        // print_r($subject_id);
        $session_id = 3; 
        $term = 'Term 1';   
         $students = $this->Mark_model->get_students($subject_id, $session_id);
        //  print_r($students);
         if($students == 0){
            $data['message'] = "No results found";
            $this->load->view('marks/class_selection',$data);
            
         }else{
            if($success){
                $data['success'] = "Student Marks Added Successfully";
            }
            if($error){
                $data['error'] = $error;
            }
           
            $data['students'] = $students;
            $data['class_id'] = $class_id;
            $data['class_name'] = $class_name;
            $data['subject_id'] = $subject_id;
            $data['subject_name'] = $subject_name;
            // print_r($students);
            // create foreach loop for students array and get eache sudent marks and update the array use get_student_marks model
            $data['students'] = $this->Mark_model->get_student_marks($subject_id, $session_id, $term, $students);
          
            // print_r($data);
            $this->load->view('marks/student_selection',$data);
         }
    }


    public function studentsSubmit(){
        echo "Loading........................";
        date_default_timezone_set('Asia/Colombo');
        $success = $this->session->flashdata('success');
        $error = $this->session->flashdata('error');
         $selectclassid = $this->input->post('selectclassid');
        $selectclass = $this->input->post('selectclass');
        $selectsubjectid = $this->input->post('selectsubjectid');
        $selectsubject = $this->input->post('selectsubject');
        

        // print_r($_POST);
        $subject_id = $this->input->post('selectsubjectid');
        $session_id = 3; 
        $students = $this->Mark_model->get_students($subject_id, $session_id);
        // print_r($students);
            foreach ($students as $student) {
                $part1 = $this->input->post('part1_'.$student->ID);
                $part2 = $this->input->post('part2_'.$student->ID);
                $total = $this->input->post('total_'.$student->ID);
                $link = $this->input->post('link_'.$student->ID);
                // print_r($part1);
                // print_r($part2);
                // print_r($total);
                // print_r($link);
                $data[] = array(
                    'student_id' => $student->ID,
                    'admission_number' => $student->admission_number,
                    'name' => $student->name,
                    'term' => 'Term 1',
                    'class_id' => $selectclassid,
                    'class_name' => $selectclass,
                    'subject_id' => $subject_id,
                    'subject_name' => $selectsubject,
                    'session_id' => $session_id,
                    'part1' => $part1,
                    'part2' => $part2,
                    'total' => $total,
                    'link' => $link,
                    'created_at' => date('Y-m-d H:i:s')
                    
                );
                
            }
            // print_r($data);
             $students_marks_insert = $this->Mark_model->insert_students_marks($data);
            if($students_marks_insert > 0){
                $this->session->set_flashdata('success', 'Marks Added Successfully');
                redirect('mark/studentsViewAfterSubmit/'.$selectclassid.'/'.$selectclass.'/'.$selectsubjectid.'/'.$selectsubject);
            }else{
                $this->session->set_flashdata('error', 'Error on Marks Adding');
                 redirect('mark/studentsViewAfterSubmit/'.$selectclassid.'/'.$selectclass.'/'.$selectsubjectid.'/'.$selectsubject);
            }
        
    }
        
}
       
    


