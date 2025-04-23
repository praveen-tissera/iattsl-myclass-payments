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

    public function get_payments($date){
        // $this->db->where('DATE(created_at)', $date);
        // $query = $this->db->get('wp_wlsm_payments');



        $this->db->select('wp_wlsm_payments.*, wp_wlsm_student_records.name,wp_wlsm_student_records.admission_number');
        $this->db->from('wp_wlsm_payments');
        $this->db->join('wp_wlsm_student_records', 'wp_wlsm_payments.student_record_id = wp_wlsm_student_records.ID');
        $this->db->where('DATE(wp_wlsm_payments.created_at)', $date);




        $query = $this->db->get();

        $lastQuery = $this->db->last_query();
        echo $lastQuery;
        
        if($query->num_rows()>0){
            $result = $query->result();
            return $result;

            // print_r($result);
        }elseif($query->num_rows() == 0){
            return(0);
        }
        

    }

}
