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

     public function paper(){
        
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
           
            $this->load->view('marks/paperclass_marks',$data);   
             
        
       
    }

    public function paperStudents(){
        $success = $this->session->flashdata('success');
        $error = $this->session->flashdata('error');
        // get form data input name grade
        $date = $this->input->post('date');
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
            $this->load->view('marks/paperclass_marks',$data);
        }else{
         $students = $this->Mark_model->get_students_by_branch($ict_subject_id, $session_id , $branch);
        // print_r($students);

      
        
        $data['students'] = $students;
        $data['date'] = $date;
        $data['branch'] = $branch;
        $data['pclass_id'] = $class_id;
        $data['pclass_name'] = $class_name;
        $data['subject_id'] = $ict_subject_id;
        $data['subject_name'] = $subject_name;
        // print_r($data);
           
            $this->load->view('marks/paperclass_marks',$data);   
        // $data['subjects'] = $subjects;
        // $data['class_detail'] = $class_detail;
             
        //     $this->load->view('marks/paper_subject_selection',$data);
        
        // print_r($grades);
        }
    }

    public function paperMarksSubmit() {
        echo "Loading........................";
        date_default_timezone_set('Asia/Colombo');
        $success = $this->session->flashdata('success');
        $error = $this->session->flashdata('error');

        $pclass_id = $this->input->post('selectclassid');
        $pclass_name = $this->input->post('selectclassname');
        $subject_id = $this->input->post('selectsubjectid');
        $subject_name = $this->input->post('selectsubjectname');
        $date = $this->input->post('date');
         $branch = $this->input->post('branch');

        // get students by branch
        $session_id = 3; 
        $students = $this->Mark_model->get_students_by_branch($subject_id, $session_id ,$branch);
        // print_r($students);
            foreach ($students as $student) {
                $papertype = $this->input->post('papertype_'.$student->ID);
                $part1 = $this->input->post('part1_'.$student->ID);
                $part2 = $this->input->post('part2_'.$student->ID);
                $total = $this->input->post('total_'.$student->ID);
                $paperlink = $this->input->post('paperlink_'.$student->ID);
                // print_r($marks);
                // print_r($part2);
                // print_r($total);
                // print_r($link);
                $data[] = array(
                    'student_id' => $student->ID,
                    'admission_number' => $student->admission_number,
                    'name' => $student->name,
                    'branch' => explode('/', $student->admission_number)[0],
                    'paper_date' => $date,
                    'paper_type' => $papertype,
                    'class_id' => $pclass_id,
                    'class_name' => $pclass_name,
                    'subject_id' => $subject_id,
                    'subject_name' => $subject_name,
                    'session_id' => 3,
                    'part1' => $part1,
                    'part2' => $part2,
                    'total' => $total,
                    'link' => $paperlink,                   
                    'created_at' => date('Y-m-d H:i:s')
                    
                );
                
            }
            // print_r($data);
             $students_marks_insert = $this->Mark_model->insert_paper_marks($data);
            if($students_marks_insert > 0){
            //     $this->session->set_flashdata('success', 'Marks Added Successfully');
                redirect('mark/paperStudentsAfterSubmit/'.$pclass_id.'/'.$pclass_name.'/'.$subject_name.'/'.$date.'/'.$subject_id.'/'.$session_id.'/'.$branch.'/success');
            }else{
                $this->session->set_flashdata('error', 'Error on Marks Adding');
                 redirect('mark/paperStudentsAfterSubmit/'.$pclass_id.'/'.$pclass_name.'/'.$subject_name.'/'.$date.'/'.$subject_id.'/'.$session_id.'/'.$branch.'/error');
            }
           
    }

        public function paperStudentsAfterSubmit($class_id,$class_name,$subject_name,$date,$ict_subject_id, $session_id , $branch, $status = null){
        $success = $this->session->flashdata('success');
        $error = $this->session->flashdata('error');
        // get form data input name grade
        // $date = $this->input->post('date');
        // $branch = $this->input->post('branch');
        // $class_detail = $this->input->post('class');
       
        $session_id = 3;  
        // $class_array = explode('*', $class_detail);
        // $class_id = $class_array[0];
        // $class_name = $class_array[1];

        //  $subjects = $this->Mark_model->get_subjecs($class_id);
        // //  print_r($subjects);
        // //  check whether ICT lable eixists in subjects array and return ID index value
        // $ict_subject_id = 0;
        // $subject_name;
        // foreach($subjects as $subject){
        //     if($subject->label == 'ICT'){
        //         $ict_subject_id = $subject->ID;
        //         $subject_name = $subject->label;
        //         break;
        //     }
        // }
        
        
         $students = $this->Mark_model->get_students_by_branch($ict_subject_id, $session_id , $branch);
        // print_r($students);

        $grades = $this->Mark_model->get_classes();
        $data['grades'] = $grades;
        $data['students'] = $students;
        $data['date'] = $date;
        $data['branch'] = $branch;
        $data['pclass_id'] = $class_id;
        $data['pclass_name'] = $class_name;
        $data['subject_id'] = $ict_subject_id;
        $data['subject_name'] = $subject_name;
        if($status == 'success'){
            $data['success'] = "Paper Marks Added Successfully";
        }elseif($status == 'error'){
            $data['error'] = "Error on Paper Marks Adding";
        }
        // print_r($data);
           
            $this->load->view('marks/paperclass_marks',$data);   
        // $data['subjects'] = $subjects;
        // $data['class_detail'] = $class_detail;
             
        //     $this->load->view('marks/paper_subject_selection',$data);
        
        // print_r($grades);
        
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
                $practical_test_grade = $this->input->post('practical_test_'.$student->ID);
                $term_test_date = $this->input->post('term_test_date_'.$student->ID);
                $practical_test_date = $this->input->post('practical_test_date_'.$student->ID);
                $link = $this->input->post('link_'.$student->ID);
                $branch = explode('/', $student->admission_number)[0];
                // print_r($part1);
                // print_r($part2);
                // print_r($total);
                // print_r($link);
                $data[] = array(
                    'student_id' => $student->ID,
                    'admission_number' => $student->admission_number,
                    'name' => $student->name,
                    'branch' => $branch,
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
                    'practical_test' => $practical_test_grade,
                    'term_test_date' => $term_test_date,
                    'practical_test_date' => $practical_test_date,
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
       
    


