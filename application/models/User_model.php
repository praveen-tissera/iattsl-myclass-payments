<?php 

class User_model extends CI_Model{
    public function set_attendance($data){
       
        $condition = "student_id='{$data['student_id']}' && course_name='{$data['course_name']}'&& grade_id='{$data['grade_id']}'&& attend_date='{$data['attend_date']}'";
        $query = $this->db->select('*')
        ->where($condition)
        ->get('wp_wlsm_attendance');

        // echo $query->num_rows();
        if($query->num_rows() == 1){
            $result = $query->result();
         
            $data['try_count']= $result[0]->try_count+1;
                $condition ="attendance_id  ='{$result[0]->attendance_id}'";
                $this->db->set('try_count', $data['try_count']);
                $this->db->where($condition);

            $this->db->update('wp_wlsm_attendance');
            if($this->db->affected_rows() == 1){
                return(1);
            }else if($this->db->affected_rows() == 0){
                return(0);
            }else{
                return(-1);
            }

        }else{
            $data['try_count']= 1;
            $this->db->insert('wp_wlsm_attendance', $data);
            if($this->db->affected_rows() == 1){
                return(1);
            }else if($this->db->affected_rows() == 0){
                return(0);
            }else{
                return(-1);
            }
        }
        
        return true;
    }

    public function get_usreData($data){
        
        // SELECT * FROM `wp_wlsm_student_records` WHERE admission_number = 'G07/21-013' AND session_id='4';
        // SELECT * FROM `wp_wlsm_sections` WHERE ID="7";
        // SELECT * FROM `wp_wlsm_payments` WHERE student_record_id ='254';
        // session table wp_wlsm_sessions
        $condition = "admission_number='{$data}' && session_id='3'";
        $query = $this->db->select('*')
        ->where($condition)
        ->get('wp_wlsm_student_records');
        
        if($query->num_rows() == 1){
            $profileData = $query->result();
            // print_r($profileData[0]->ID);
            // get course Name:
                    $condition = "ID='{$profileData[0]->section_id}'";
                    $query_class = $this->db->select('*')
                    ->where($condition)
                    ->get('wp_wlsm_sections');
                    if($query->num_rows() == 1){
                        // print_r($query_class->result());
                        $courseName = $query_class->result()[0]->label;
                        // GET Grade 1. get ID 2. get grade label

                        // SELECT wp_wlsm_classes.ID, wp_wlsm_classes.label FROM wp_wlsm_classes INNER JOIN wp_wlsm_class_school ON wp_wlsm_classes.ID=wp_wlsm_class_school.class_id WHERE wp_wlsm_class_school.ID = 7;

                            $query_gradeID = $this->db->query("SELECT wp_wlsm_classes.ID, wp_wlsm_classes.label FROM wp_wlsm_classes INNER JOIN wp_wlsm_class_school ON wp_wlsm_classes.ID=wp_wlsm_class_school.class_id WHERE wp_wlsm_class_school.ID = {$query_class->result()[0]->class_school_id}");
                            $grade = $query_gradeID->result();
                        //    print_r($grade);

                        // GET invoices FROM wp_wlsm_invoice
                        // SELECT * FROM `wp_wlsm_invoice` WHERE student_record_id ='254' ORDER BY invoice_id DESC;
                        
                        
                            $condition = "student_record_id='{$profileData[0]->ID}'";
                            $query_invoicehistory = $this->db->select('*')
                            ->where($condition)
                            ->order_by("invoice_number", "desc")
                            ->get('wp_wlsm_invoices');
                            // print_r($query_paymenthistory->result());
                            $paymentHistory = $query_invoicehistory->result();
                            // print_r($invoiceHistory);
                        // GET payment records
                            $condition = "student_record_id='{$profileData[0]->ID}'";
                            $query_paymenthistory = $this->db->select('*')
                            ->where($condition)
                            ->order_by("invoice_id", "desc")
                            ->get('wp_wlsm_payments');
                            // print_r($query_paymenthistory->result());
                            $paymentCompletion = $query_paymenthistory->result();

                    }



                    
                    $result_attendance = [];  
                    // $condition = "student_id='{$profileData[0]->ID}' && course_name='{$courseName}'&& grade_id='{$grade[0]->ID}'";
                    // $query = $this->db->select('*')
                    // ->where($condition)
                    // ->order_by("attend_date", "desc")
                    // ->get('wp_wlsm_attendance'); 
                                
                                        // echo $query->num_rows();
                        // if($query->num_rows() > 0){
                        //     $result_attendance = $query->result();
                            
                        // }else{
                        //     $result_attendance = [];  
                        // }

                        $data =  array ('profile'=> $profileData,
                        'course'=>$courseName,
                        'grade'=> $grade,
                        'payment'=>$paymentHistory,
                        'payment_completion'=>$paymentCompletion,
                        'attendance'=>$result_attendance
                    );
                   
                    return $data;

        }else{
            return(0);
        }
        // print_r($query->result());
    }

    // methos to insert new payment
    public function insert_payment($data){
        // Set the desired timezone
        // date_default_timezone_set('Asia/Colombo');
        // $currentDate = date('Y-m-d H:i:s');
        // $data = array(
        //     'ID' => NULL,
        //     'amount' => '1200.00',
        //     'payment_method' => 'cash',
        //     'transaction_id' => '',
        //     'note' => '',
        //     'invoice_label' => 'Class Fee - February (2024)',
        //     'invoice_payable' => '1200.00',
        //     'invoice_id' => 125,
        //     'student_record_id' => 106,
        //     'school_id' => '1',
        //     'created_at' => $currentDate,
        //     );

        $this->db->trans_begin();
        $this->db->insert('wp_wlsm_payments', $data);
        

        if( $this->db->trans_status() === FALSE ){
            echo "Error";
            $this->db->trans_rollback();
            return(0);
        }else{
            echo "inserted need to update receipt number";
            // after recorde inserted need to update receipt_number coloumn with new inserted id
            $insert_id = $this->db->insert_id();
            // modify insert_id variable with leading zeroes before number upto 6 digits
            $receipt_number = str_pad($insert_id, 6, '0', STR_PAD_LEFT);
        
            $this->db->set('receipt_number', $receipt_number);
            $this->db->where('ID', $insert_id);
            $this->db->update('wp_wlsm_payments');
            if( $this->db->trans_status() === FALSE ){
                echo "Error";
                $this->db->trans_rollback();
                return(0);
            }else{
                echo "payment inserted and receipt number updated";
                // update status to paid in wp_wlsm_invoices table
                $this->db->set('status', 'paid');
                $this->db->where('ID', $data['invoice_id']);
                $this->db->update('wp_wlsm_invoices');
                if( $this->db->trans_status() === FALSE ){
                    echo "Error";
                    $this->db->trans_rollback();
                    return(0);
                } else{
                    echo "status updated";
                    $this->db->trans_commit();
                    return $insert_id;
                }
                
            }

           
           
        }  

    }

    // method to get payment details by invoice id
    public function get_payment_detail($data){
        //cerate variable
        $condition = "invoice_number ='{$data}'";
                    $query = $this->db->select('*')
                    ->where($condition)
                    ->get('wp_wlsm_invoices');
                    $result = [];
                    if($query->num_rows() == 1){
                        $invoiceData = $query->result();
                        // print_r($invoiceData[0]);
                        $result[] = $invoiceData[0];
                        // get student details
                        $condition = "ID ='{$invoiceData[0]->student_record_id}'";
                        $query_std_data = $this->db->select('*')
                        ->where($condition)
                        ->get('wp_wlsm_student_records');
                        if($query_std_data->num_rows() == 1){
                            $studentData = $query_std_data->result();
                            // print_r($studentData[0]);
                            $result[] = $studentData[0];
                            //get payment receipt details
                            $condition = "invoice_id ='{$invoiceData[0]->ID}'";
                            $query_std_receipt = $this->db->select('*')
                            ->where($condition)
                            ->get('wp_wlsm_payments');
                            if($query_std_receipt->num_rows() == 1){
                                $studentReceipt = $query_std_receipt->result();
                                // print_r($studentReceipt[0]);
                                $result[] = $studentReceipt[0];

                            }
                        }

                        return $result;
                    }else{
                        return(0);
                    }
                    
                    

    }




    // get student course and grade deatils by provided admission number
    public function get_student_course_grade($data,$session_id){    
        
        
        // SELECT * FROM `wp_wlsm_student_records` WHERE admission_number = 'G07/21-013' AND session_id='4';
        // SELECT * FROM `wp_wlsm_sections` WHERE ID="7";
        // SELECT * FROM `wp_wlsm_payments` WHERE student_record_id ='254';
        // session table wp_wlsm_sessions
        $condition = "admission_number='{$data}' && session_id=$session_id";
        $query = $this->db->select('*')
        ->where($condition)
        ->get('wp_wlsm_student_records');
        
        if($query->num_rows() == 1){
            $profileData = $query->result();
            // print_r($profileData[0]->ID);
            // get course Name:
                    $condition = "ID='{$profileData[0]->section_id}'";
                    $query_class = $this->db->select('*')
                    ->where($condition)
                    ->get('wp_wlsm_sections');
                    if($query->num_rows() == 1){
                        // print_r($query_class->result());
                        $courseName = $query_class->result()[0]->label;
                        // GET Grade 1. get ID 2. get grade label

                        // SELECT wp_wlsm_classes.ID, wp_wlsm_classes.label FROM wp_wlsm_classes INNER JOIN wp_wlsm_class_school ON wp_wlsm_classes.ID=wp_wlsm_class_school.class_id WHERE wp_wlsm_class_school.ID = 7;

                            $query_gradeID = $this->db->query("SELECT wp_wlsm_classes.ID, wp_wlsm_classes.label FROM wp_wlsm_classes INNER JOIN wp_wlsm_class_school ON wp_wlsm_classes.ID=wp_wlsm_class_school.class_id WHERE wp_wlsm_class_school.ID = {$query_class->result()[0]->class_school_id}");
                            $grade = $query_gradeID->result();
                        //    print_r($grade);

                        // GET invoices FROM wp_wlsm_invoice
                        // SELECT * FROM `wp_wlsm_invoice` WHERE student_record_id ='254' ORDER BY invoice_id DESC;
                        
                        
                            // $condition = "student_record_id='{$profileData[0]->ID}'";
                            // $query_invoicehistory = $this->db->select('*')
                            // ->where($condition)
                            // ->order_by("invoice_number", "desc")
                            // ->get('wp_wlsm_invoices');
                           
                            // $paymentHistory = $query_invoicehistory->result();


                            // print_r($invoiceHistory);
                        // GET payment records
                            // $condition = "student_record_id='{$profileData[0]->ID}'";
                            // $query_paymenthistory = $this->db->select('*')
                            // ->where($condition)
                            // ->order_by("invoice_id", "desc")
                            // ->get('wp_wlsm_payments');
                            
                            // $paymentCompletion = $query_paymenthistory->result();

                    }



                    
                    $result_attendance = [];  
                    // $condition = "student_id='{$profileData[0]->ID}' && course_name='{$courseName}'&& grade_id='{$grade[0]->ID}'";
                    // $query = $this->db->select('*')
                    // ->where($condition)
                    // ->order_by("attend_date", "desc")
                    // ->get('wp_wlsm_attendance'); 
                                
                                        // echo $query->num_rows();
                        // if($query->num_rows() > 0){
                        //     $result_attendance = $query->result();
                            
                        // }else{
                        //     $result_attendance = [];  
                        // }

                        $data =  array ('profile'=> $profileData,
                        'course'=>$courseName,
                        'grade'=> $grade,
                        // 'payment'=>$paymentHistory,
                        // 'payment_completion'=>$paymentCompletion,
                        'attendance'=>$result_attendance
                    );
                   
                    return $data;

        }else{
            return(0);
        }
        // print_r($query->result());
    }

    public function get_payments($date, $session_id){
        // $this->db->where('DATE(created_at)', $date);
        // $query = $this->db->get('wp_wlsm_payments');

        
        

        $this->db->select('wp_wlsm_payments.*, wp_wlsm_student_records.name,wp_wlsm_student_records.admission_number');
        $this->db->from('wp_wlsm_payments');
        $this->db->join('wp_wlsm_student_records', 'wp_wlsm_payments.student_record_id = wp_wlsm_student_records.ID');
          // Filter by session_id
        $this->db->where('wp_wlsm_student_records.session_id', $session_id);
        if (is_array($date) && count($date) == 2) {
            $this->db->where('DATE(wp_wlsm_payments.created_at) >=', $date[1]);
            $this->db->where('DATE(wp_wlsm_payments.created_at) <=', $date[0]);
        } else {
            $this->db->where('DATE(wp_wlsm_payments.created_at)', $date);
        }




        $query = $this->db->get();

        // $lastQuery = $this->db->last_query();
       
        
        if($query->num_rows()>0){
            $result = $query->result();
            
            // create loop to retrive admission_number form $result array object and take number 25-170 from BAT/25-170
            foreach($result as $key => $payment){
                // echo $payment->admission_number;
                $admission_parts = explode('/', $payment->admission_number);
                // print_r($admission_parts[1]);
                
                // call function get_student_course_grade on top on this same model
                $student_course_grade = $this->get_student_course_grade($payment->admission_number,$session_id);
                $result[$key]->course = $student_course_grade['course'];
                $result[$key]->grade = $student_course_grade['grade'][0]->label;
                // print_r($student_course_grade);
            }
            // print_r($result);
            return $result;
        }elseif($query->num_rows() == 0){
            return(0);
        }
        

    }

    // get payment summary for each student in the given grade
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
                $condition = "student_record_id  ='{$student->ID}'";
                $marks_query = $this->db->select('*')
                                        ->where($condition)
                                        ->get('wp_wlsm_invoices');
                if($marks_query->num_rows() > 0){
                    $query->result()[$key]->payment_history = $marks_query->result();
                }else{
                    $query->result()[$key]->payment_history = null;
                }
            }
            return $query->result();
        }else{
            return 0;
        }
    }

    // create get_user_by_email_and_password function ,  get user details from wp_users table
    public function get_user_by_email_and_password($email, $password){
        // check from user_login or user_email coloumn


        $condition = "user_email='{$email}' OR user_login='{$email}'";
        $query = $this->db->select('*')
        ->where($condition)
        ->get('wp_users');
        // print_r($this->db->last_query());
        if($query->num_rows() == 1){
            $user = $query->result();
            return $user[0];
        //    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        //    echo $hashed_password;
        //    echo "<br>";
        //    echo $user[0]->user_pass;
        //     // verify password
        //     if (password_verify($hashed_password, $user[0]->user_pass)) {
        //         return $user[0];
        //     } else {
        //         return 0;
        //     }
        }else{
            return 0;
        }
    }



    public function get_students_attendance_by_branch($subject_id, $session_id, $branch){  
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
                // change condition variable to oder by class_date jan to Dec order.


                $condition = "student_record_id  ='{$student->ID}' order by class_date ASC";
                $attendance_query = $this->db->select('*')
                                        ->where($condition)
                                        ->get('wp_wlsm_student_attendance_iattsl');
                if($attendance_query->num_rows() > 0){
                    // group attendace result in to an array which has Jan, Feb, Mar...Dec as keys
                    $attendance_history = [];
                    foreach($attendance_query->result() as $attendance){
                        $month = date('M', strtotime($attendance->class_date));
                        $attendance_history[$month][] = $attendance;

                        // filter user display_name from wp_users table by staff_id
                        $condition = "ID  ='{$attendance->staff_id}'";  
                        $staff_query = $this->db->select('*')
                                                ->where($condition)
                                                ->get('wp_users');
                        if($staff_query->num_rows() == 1){
                            $attendance->staff_name = $staff_query->result()[0]->display_name;
                        }
                    }
                    $query->result()[$key]->attendance_history = $attendance_history;
                }else{
                    $query->result()[$key]->attendance_history = null;
                }
            }
            return $query->result();
        }else{
            return 0;
        }
    }


    public function insert_student_attendace($data){
        // ID
// student_record_id
// class_date
// attendace
// staff_id
// created_at
// create for multiple trasactions use approach using trans_commit and trans_rollback
       

        $attendance_data = array(); 

        $this->db->trans_begin();
        // loop through data array and attendace array to insert each record
        foreach($data['attendace'] as $key => $record){
           
            // print_r($record);
            $attendance_data['student_record_id'] = $key;
            $attendance_data['class_date'] = $data['class_date'];
            $attendance_data['attendace'] = $record;
            $attendance_data['staff_id'] = $data['staff_id'];
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            // print_r($attendance_data);
             $result_attendace = $this->db->insert('wp_wlsm_student_attendance_iattsl', $attendance_data);
            if($this->db->affected_rows() != 1){
                echo "Error";
                $this->db->trans_rollback();
                return(0);
            }
             
        }
        $this->db-> trans_commit();
        return(1);
        // return true;
        // if($this->db->affected_rows() == 1){
        //     return(1);
        // }else if($this->db->affected_rows() == 0){
        //     return(0);
        // }else{
        //     return(-1);
        // }
    }

    // update_student_attendace
    public function update_student_attendace($data,$student_ids){

        // loop through $student_ids and get the value ,  get current month and year in the format of 'YYYY-MM'
        $current_month_year = date('Y-m');
        foreach($student_ids as $student_id){
            // check attendace value from $data array using key old_attendace_{student_id}_{class_date}
            $class_date_filter = $current_month_year;
            // create sql query to filter like this query SELECT * FROM wp_wlsm_student_attendance_iattsl WHERE student_record_id =108 AND class_date LIKE '2025-12%';
            $condition = "student_record_id='{$student_id}' AND class_date LIKE '{$class_date_filter}%'";
            $query = $this->db->select('*')
            ->where($condition)
            ->get('wp_wlsm_student_attendance_iattsl');
            // print_r($this->db->last_query());
            if($query->num_rows() > 0){
                $attendance_records = $query->result();
                // loop through attendance_records and update attendace value as 'AB'

                foreach($attendance_records as $record){
                    $checkbox_key = 'old_attendace_'.$student_id.'_'.$record->class_date;
                   
                    $this->db->set('attendace', 'AB');
                    $this->db->where('ID', $record->ID);
                     $this->db->where('class_date', $record->class_date);
                    $this->db->update('wp_wlsm_student_attendance_iattsl');
                    if($this->db->affected_rows() == -1){
                        echo "Error";
                    }
                }
            }

            

           
        }
            $this->db->trans_begin();
            foreach($data as $key => $record){   
                // print_r($record);
                $this->db->where('student_record_id', $record['student_id']);
                $this->db->where('class_date', $record['class_date']);
                $this->db->update('wp_wlsm_student_attendance_iattsl', array('attendace' => $record['attendace'], 'staff_id' => $record['staff_id'], 'created_at' => date('Y-m-d H:i:s')));

                // print_r($this->db->last_query());
                // echo "<br>";
                // echo "Affected Rows: ".$this->db->affected_rows();
                if($this->db->affected_rows() == -1){
                    echo "Error";
                    $this->db->trans_rollback();
                    return(0);
                }
            }

        
        $this->db->trans_commit();
        return(1);





        // $this->db->where('student_record_id', $data['student_id']);
        // $this->db->where('class_date', $data['class_date']);
        // $this->db->update('wp_wlsm_student_attendance_iattsl', array('attendace' => $data['attendace']));
        // if($this->db->affected_rows() == 1){
        //     return(1);
        // }else if($this->db->affected_rows() == 0){
        //     return(0);
        // }else{
        //     return(-1);
        // }
    }

    // get meta_value from wp_usermeta table where user_id = $user->ID and meta_key = 'wp_capabilities'
    public function get_user_role($user_id){
        $condition = "user_id='{$user_id}' AND meta_key='wp_capabilities'";
        $query = $this->db->select('*')
        ->where($condition)
        ->get('wp_usermeta');
        if($query->num_rows() == 1){
            $user_meta = $query->result();
            return $user_meta[0]->meta_value;
        }else{
            return 0;
        }
    }

}
