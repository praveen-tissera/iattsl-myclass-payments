<?php 

class Mark_model extends CI_Model{
//  get all the data in table call wp_wlsm_classes
    public function get_classes(){
        $this->db->select('*');
        $this->db->from('wp_wlsm_classes');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_subjecs($class_id){
    //    create join select ID from wp_wlsm_class_school table where class_id =$calss_id and filter from wp_wlsm_sections table
        $this->db->select('wp_wlsm_class_school.ID, wp_wlsm_sections.ID, wp_wlsm_sections.label');
        $this->db->from('wp_wlsm_class_school');
        $this->db->join('wp_wlsm_sections', 'wp_wlsm_class_school.ID = wp_wlsm_sections.class_school_id');
        $this->db->where('wp_wlsm_class_school.class_id', $class_id);
        $query = $this->db->get();
        return $query->result();
    }
    
    public function get_students($subject_id, $session_id){
        // get all the data in table call wp_wlsm_student_records filter by section_id and session_id
        $condition = "section_id ='{$subject_id}' AND session_id = '{$session_id}'";
                    $query = $this->db->select('*')
                    ->where($condition)
                    ->get('wp_wlsm_student_records');
        if($query->num_rows() > 0){
            return $query->result();
        }else{
            return 0;
        }
    }

    public function insert_students_marks($data){
        // insert data into wp_wlsm_student_records table
        // check if record already exists baseed on following condition
        // Student_id, subject_id, session_id,term if exists update the record else insert

        $this->db->trans_begin();
        foreach($data as $key => $value){
             $this->db->select('*');
            $this->db->from('wp_wlsm_student_marks_iattsl');
            $this->db->where('student_id', $value['student_id']);
            $this->db->where('subject_id', $value['subject_id']);
            $this->db->where('session_id', $value['session_id']);
            $this->db->where('term', $value['term']);
            $query = $this->db->get();
            if($query->num_rows() > 0){
                // update the record
                $this->db->where('student_id', $value['student_id']);
                $this->db->where('subject_id', $value['subject_id']);
                $this->db->where('session_id', $value['session_id']);
                $this->db->where('term', $value['term']);
                $this->db->update('wp_wlsm_student_marks_iattsl', $value);
                if( $this->db->trans_status() === FALSE ){
                    $this->db->trans_rollback();
                    return(0);
                }else{
                    $this->db->trans_commit();
                }
                // return 1;
            }else{
                // insert the record
                $this->db->insert('wp_wlsm_student_marks_iattsl', $value);
                if( $this->db->trans_status() === FALSE ){
                    $this->db->trans_rollback();
                    return(0);
                }else{
                    $this->db->trans_commit();
                }
                // return 2;
            }
           
        }
        return(1);
        
    }

    public function get_student_marks($subject_id, $session_id, $term, $students){
        // create foreach loop for students array and get eache sudent marks and update the array use get_student_marks model
        $student_marks = array();
        foreach($students as $key=>$student){
            $student_id = $student->ID;
            $condition = "student_id ='{$student->ID}' AND subject_id = '{$subject_id}' AND session_id = '{$session_id}' AND term = '{$term}'";
                    $query = $this->db->select('*')
                    ->where($condition)
                    ->get('wp_wlsm_student_marks_iattsl');
            if($query->num_rows() > 0){
                // print_r($query->result());
                $students[$key]->marks = $query->result()[0];
            }
        }
        // print_r($students);
        return $students;

        // get all the data in table call wp_wlsm_student_marks_iattsl filter by ,student_id, subject_id and session_id
        
        // if($query->num_rows() > 0){
        //     return $query->result();
        // }else{
        //     return 0;
        // }
    }

    // create a function to get all students from wp_wlsm_student_records base on subject_id and session_id and admission_number start with 'BAT', 'PEL', 'MAT'
    public function get_students_by_branch($subject_id, $session_id, $branch){  
        $this->db->select('*');
        $this->db->from('wp_wlsm_student_records');
        $this->db->where('section_id', $subject_id);
        $this->db->where('session_id', $session_id);
        $this->db->like('admission_number', $branch, 'after'); // 'after' means it will match the beginning of the string
        $query = $this->db->get();
        //PRINT query string
        // echo $this->db->last_query();

        
        if($query->num_rows() > 0){
            // crete loop to get each student previous marks from wp_wlsm_student_paper_marks_iattsl table filter by student_id, subject_id, session_id
            foreach($query->result() as $key => $student){
                $condition = "student_id ='{$student->enrollment_number}' AND subject_id = '{$subject_id}' AND session_id = '{$session_id}'";
                $marks_query = $this->db->select('*')
                                        ->where($condition)
                                        ->get('wp_wlsm_student_paper_marks_iattsl');
                if($marks_query->num_rows() > 0){
                    $query->result()[$key]->marks = $marks_query->result();
                }else{
                    $query->result()[$key]->marks = null;
                }
            }
            return $query->result();
        }else{
            return 0;
        }
    }

    // insert paper class marks to wp_wlsm_student_paper_marks_iattsl table
    public function insert_paper_marks($data){
        // print_r($data);

        // insert data into wp_wlsm_student_paper_marks_iattsl table
        // check if record already exists baseed on following condition
        // paper_date, Student_id, subject_id, session_id, if exists update the record else insert
        $this->db->trans_begin();
        foreach($data as $key => $value){
             $this->db->select('*');
            $this->db->from('wp_wlsm_student_paper_marks_iattsl');
            $this->db->where('paper_date', $value['paper_date']);
            $this->db->where('subject_id', $value['subject_id']);
            $this->db->where('session_id', $value['session_id']);
            $this->db->where('student_id', $value['student_id']);
           
            $query = $this->db->get();
            // print_r($this->db->last_query());
                    echo "<br>";
                if($query->num_rows() > 0){
                // update the record
                    $this->db->where('paper_date', $value['paper_date']);
                    $this->db->where('subject_id', $value['subject_id']);
                    $this->db->where('session_id', $value['session_id']);
                    $this->db->where('student_id', $value['student_id']);
                    $this->db->update('wp_wlsm_student_paper_marks_iattsl', $value);
                    
                    if( $this->db->trans_status() === FALSE ){
                        $this->db->trans_rollback();
                        return(0);
                    }else{
                        $this->db->trans_commit();
                    }
                    
                }else{
                    // insert the record
                    $this->db->insert('wp_wlsm_student_paper_marks_iattsl', $value);
                    if( $this->db->trans_status() === FALSE ){
                        $this->db->trans_rollback();
                        return(0);
                    }else{
                        $this->db->trans_commit();
                    }
                    
                }

        }
        return 2;
    }

}
