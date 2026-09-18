<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class QrScanner extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper('url');
         $this->load->model('Online_User_model');
        date_default_timezone_set("Asia/Colombo");

        // Redirect to login if user is not logged in
        if (!$this->is_logged_in()) {
            redirect('guest/loginview');
        }
    }

    /**
     * Check if user is logged in
     */
    public function is_logged_in() {
        return $this->session->userdata('logged_in') === TRUE;
    }

    /**
     * Index page - loads the QR Scanner View
     */
    public function index() {
        $data = array(
            'title' => 'QR Code Scanner - MyClass',
            'user_name' => $this->session->userdata('user_name'),
            'user_role' => $this->session->userdata('user_role')
        );
        $this->load->view('qr_scanner', $data);
    }

    /**
     * Handle AJAX POST request containing the scanned QR code
     */
    public function process_scan() {
        // Retrieve the scanned code from POST data
        $scanned_code = $this->input->post('qr_code');
        // $scanned_code = "HED/26-113-1/6"; // For testing purposes, you can hardcode a value here
        if (empty($scanned_code)) {
            $response = array(
                'status' => 'error',
                'message' => 'No QR code payload received.'
            );
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        // Clean/Trim the scanned code
        $scanned_code = trim($scanned_code);

        // Print to PHP System Log / error_log (as requested by user)
        error_log("----------------------------------------");
        error_log("QR CODE SCANNED AT " . date('Y-m-d H:i:s'));
        error_log("Scanned Content: " . $scanned_code);
        error_log("By User: " . $this->session->userdata('user_name') . " (ID: " . $this->session->userdata('user_id') . ")");
        error_log("----------------------------------------");

        // Also print/echo in output buffer if needed, but return JSON for AJAX response
       
        // number received from QR code is in the format: HED/26-113-1/6
        $parts = explode('/', $scanned_code);
        if (count($parts) !== 3) {
            $response = array(
                'status' => 'error',
                'message' => 'Invalid QR code format. Expected format: student_id/branch/session_id'
            );
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }else{
            $student_branch = $parts[0];
            $student_id = explode('-', $parts[1]);
            $student_id_without_ending_number = $student_id[0].'-'.$student_id[1];
            $session_id = $parts[2];
            $redirect_url = site_url(
                'online/idValidator/' .
                $student_id_without_ending_number . '/' .
                $student_branch . '/' .
                $session_id
            );
                // get payment related data from the database based on the scanned QR code
                $std_id = $parts[0].'/'.$student_id_without_ending_number;
                $result = $this->Online_User_model->get_usreData($std_id,$session_id);
                
                $activesubjects = [];
            foreach($result as $row){
                $latestPayment = null; // Initialize latestPayment variable
                $isPaidForCurrentMonth = false; // Default to not paid
                
                if($row['profile']->is_active == 1){
                    $studentName = $row['profile']->name;
            //    if if array payment_completion is not empty
            if(!empty($row['payment_completion'])) {
                usort($row['payment_completion'], function ($a, $b) {
                    
                    if ($a->invoice_id != $b->invoice_id) {
                        return $b->invoice_id - $a->invoice_id; // Higher invoice_id first
                    }
                });

                $latestPayment = $row['payment_completion'][0];
                // $lastPayment = $row['last_payment'];

                $invoiceLabel = $latestPayment->invoice_label; // Class Fee - June (2026)

                // Extract month and year
                if (preg_match('/Class Fee - ([A-Za-z]+) \((\d{4})\)/', $invoiceLabel, $matches)) {

                    $invoiceMonth = $matches[1]; // June
                    $invoiceYear  = $matches[2]; // 2026

                    $paidDate = DateTime::createFromFormat('F Y',$invoiceMonth . ' ' . $invoiceYear);

                  
                    if ($paidDate) {
                        $paidMonth = $paidDate->format('Y-m');
                        $currentMonth = date('Y-m');
                        $isPaidForCurrentMonth = ($paidMonth >= $currentMonth);
                    }
                    

                    
                }
            }
                $activesubjects[] = array(
                    'current_month_payment_status' => $isPaidForCurrentMonth ? 'Paid' : 'Unpaid',
                    'course_name' => $row['course'],
                    'last_payment' => $latestPayment,
                );
                }
            }
           
                $response = array(
            'status' => 'success',
            'message' => 'QR Code printed in controller successfully.',
            'scanned_code' => $scanned_code,
            'timestamp' => date('Y-m-d H:i:s'),
            'redirect_url' => $redirect_url,
            'studentname' => $studentName,
            'activesubjects' => $activesubjects
        ); 

        
         return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));




            // redirect to the idValidator method in the online controller with the extracted parameters
            //  redirect('/online/idValidator/'.$student_id_without_ending_number.'/'.$student_branch.'/'.$session_id);
            
        }
       

        
    }
    


    
}
