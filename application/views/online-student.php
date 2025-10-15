<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url() . '/css/bootstrap.min.css' ?>">

   
    <title>Student Class Link Generator</title>
  <style>
    body{
      font-size: .9rem;
    }
   

     /* change input field font size */
    input[type=radio],input[type=text], input[type=number], input[type=email], input[type=password], select {
      font-size: .6rem;
    }
    span{
      font-size: .6rem;
    }
   
    /* change table font size */
        table {
          font-size: .6rem;
        }
        /* change button font size */
        button,input[type=submit] {
          font-size: .6rem;
        }
        /* change table cell font size */
        th, td {
          font-size: .6rem;
        }
      @media (min-width: 768px) {
        input[type=radio],input[type=text], input[type=number], input[type=email], input[type=password], select {
           font-size: .8rem;
         } 

          body,td,th{
            font-size: .7rem;
          }

      }

      @media (min-width: 992px) {
        input[type=radio],input[type=text], input[type=number], input[type=email], input[type=password], select {
           font-size: .9rem;
         } 
         body,td,th{
            font-size: .9rem;
          }
      }
   
    /* change input field border */
    input[type=text], input[type=number], input[type=email], input[type=password], select {
      border: 1px solid #ccc;
      text-align: left;
      padding: 5px;
    }
    /* change input field border radius */
    input[type=text], input[type=number], input[type=email], input[type=password], select {
      border-radius: 4px;
    }

    .dot-active{
      height: 15px;
      width: 15px;
      background-color: #32CD32;
      border-radius: 50%;
      display: inline-block;
    }
    .dot-inactive{
      height: 15px;
      width: 15px;
      background-color: #800000;
      border-radius: 50%;
      display: inline-block;
    }
  </style>

</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary ">
  <a class="navbar-brand" href="#">
    <img class="img-fluid" style="width:80px" src="https://iattsl.edu.lk/wp-content/uploads/2025/03/IATTLS_LOGO.jpg" alt="Logo">
  </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item active">
        <a class="nav-link" href="<?php echo base_url(); ?>">Enter Payments <span class="sr-only">(current)</span></a>
      </li>
     <li class="nav-item">
      <a class="nav-link" href="<?php echo base_url(); ?>index.php/welcome/income">Income Summary</a>
     </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
          Payment Summary
        </a>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="<?php echo base_url(); ?>index.php/welcome/paymenthistory">Physical Payments Summary </a>
          <a class="dropdown-item" href="<?php echo base_url(); ?>index.php/online/onlinepaymenthistory">Online Payments Summary</a>
          
        </div>
     </li>
     <li class="nav-item">
      <a class="nav-link" href="<?php echo base_url(); ?>index.php/mark/">Enter Marks <span class="sr-only">(current)</span></a>
     </li>
      <li class="nav-item ">
      <a class="nav-link" href="<?php echo base_url(); ?>index.php/mark/paper">Enter Paper Class Marks </a>
     </li>
     <li class="nav-item">
      <a class="nav-link" target="_blank" href="https://iattsl.edu.lk/iattslstudent">Student Report Card</a>
     </li>
    </ul>
  </div>
</nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <?php
                if (isset($success)) {
                    echo "<div class='alert alert-success'>";
                    echo $success;
                    echo "</div>";
                }
                if (isset($error)) {
                    echo "<div class='alert alert-danger'>";
                    echo $error;
                    echo "</div>";
                }

                ?>
                <h1 class="text-center display-4 mt-3" style="font-size:3rem">Please  Enter ONLINE Student ID </h1>
                <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
                <?php
                  if(isset($message)){
                    echo "<div class='alert alert-info'>";
                    echo $message;
                    echo "</div>";
                  }
                
                ?>
                <?php 
                
                if (isset($error_message_display)) {
                  echo '<div class="alert alert-danger" role="alert">';
                  echo $error_message_display;
                  echo '</div>';
                }
                if (isset($success_message_display)) {
                  echo '<div class="alert alert-success" role="alert">';
                  echo $success_message_display;
                  echo '</div>';
                }
                ?>

                <?php echo form_open('online/idValidator') ?>
                <table class="table table-borderless">
                    <tr>
                        <td>
                          <?php 
                         
                            if (!empty($student_data[0]['profile']->admission_number)) {
                              $student_id_array = explode('/', $student_data[0]['profile']->admission_number);
                             
                              if($student_id_array[0] == 'ONL'){
                                // remove ending number after hyphen
                                $student_id = str_replace('ONL/', '', $student_data[0]['profile']->admission_number);
                                $student_id_without_ending_number  = substr($student_id, 0, strrpos($student_id, '-'));
                              }else{
                                $student_id_without_ending_number = end($student_id_array);
                              }
                                
                              echo "<input class='form-control' type='text' name='studentid' placeholder='24-999' value='{$student_id_without_ending_number}'>";
                            }else{
                             echo '<input class="form-control" type="text" name="studentid" placeholder="24-999">';
                            }

                          
                          ?>
                          <br>
                         
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="center" id="PEL" value="PEL"> <label class="form-check-label" for="PEL">PELAWATTA</label> 
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="center" id="MAH" value="MAH" > <label class="form-check-label" for="MAH">MAHARAGAMA</label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="center" id="HRI" value="HRI" > <label class="form-check-label" for="HRI">HRIPITIYA</label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="center" id="BAT" value="BAT" > <label class="form-check-label" for="BAT">BATTARAMULLA</label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="center" id="ONL" value="ONL" > <label class="form-check-label" for="ONL">ONLINE</label>
                          </div>

                            <?php 
                            if (!empty($student_data[0]['profile']->admission_number)) {
                              $student_id_array = explode('/', $student_data[0]['profile']->admission_number);
                              $student_branch = $student_id_array[0];
                              // base on student_brach place checked on radio btn
                              if($student_branch == 'PEL'){
                                echo '<script>document.getElementById("PEL").checked = true;</script>';
                              }elseif($student_branch == 'MAH'){
                                echo '<script>document.getElementById("MAH").checked = true;</script>';
                              }elseif($student_branch == 'HRI'){
                                echo '<script>document.getElementById("HRI").checked = true;</script>';
                              }elseif($student_branch == 'BAT'){
                                echo '<script>document.getElementById("BAT").checked = true;</script>';
                              }elseif($student_branch == 'ONL'){
                                echo '<script>document.getElementById("ONL").checked = true;</script>';
                              }
                              else{
                                echo '<script>document.getElementById("PEL").checked = true;</script>';
                              }
                            }else{
                              echo '<script>document.getElementById("PEL").checked = true;</script>';
                            }
                          
                          ?>

                        </td>
                    </tr>

                    <tr>
                        <td>
                            <input class="btn btn-primary btn-block" type="submit" name="submit" value="Submit">
                        </td>
                    </tr>
                </table>
                <?php echo form_close(); ?>
                <div class="alert alert-warning text-center mx-3" role="alert">
                  !! Forgot class ID - Contact ADMIN  (076 435 4111)
                </div>

            </div>

        </div>
        <?php 
          if(isset($student_data[0]['profile'])){
            echo "<hr>";
        ?>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Profile Status: 
                    <!-- <?php echo  ($student_data['profile'][0]->is_active == 1)? '<span class="dot-active"></span> Active' : '<span class="dot-inactive"></span> Inactive'; ?> -->
                    </h5>
               
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-12 col-md-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Student Profile</h5>
                  <p class="card-text">Name: <?php echo $student_data[0]['profile']->name;?></p>
                  <p class="card-text">Course : <?php echo $student_data[0]['course']; ?>  <?php echo $student_data[0]['grade'][0]->label; ?></p>
                <?php
                $student_id_array = explode('/', $student_data[0]['profile']->admission_number);
               if($student_id_array[0] == 'ONL'){
                                // remove ending number after hyphen
                                // $student_id = str_replace('ONL/', '', $student_data[0]['profile']->admission_number);
                                $student_id_without_ending_number  = substr($student_data[0]['profile']->admission_number, 0, strrpos($student_data[0]['profile']->admission_number, '-'));
                              }else{
                                $student_id_without_ending_number = $student_data[0]['profile']->admission_number;;
                              }
                                
                ?>


                  <p class="card-text">Class/Course Registration# : <?php echo $student_id_without_ending_number; ?> </p>
                  
                </div>
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 mt-2">
              <div class="card">
                <div class="card-body px-0 px-sm-0 px-md-2">
                  <div class="cart-title">Payment History</div>

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <?php 
                        $i=0;
                        foreach ($student_data as $key => $student_tab) {
                           
                           if($i==0){
                             echo '<li class="nav-item" role="presentation">';
                              echo "<button class='nav-link active' id='{$student_tab['course']}-tab' data-toggle='tab' data-target='#{$student_tab['course']}' type='button' role='tab' aria-controls='home' aria-selected='true'>";
                              echo $student_tab['course'];
                              
                              echo "</button>";
                            echo '</li>';
                            $i++;
                          }else{
                            echo '<li class="nav-item" role="presentation">';
                              echo "<button class='nav-link' id='{$student_tab['course']}-tab' data-toggle='tab' data-target='#{$student_tab['course']}' type='button' role='tab' aria-controls='home' aria-selected='true'>";
                              echo $student_tab['course'];
                              
                              echo "</button>";
                            echo '</li>';
                          }
                         
                        }
                        ?>
                    </ul>
                          
                          
                           <?php 
                              // print_r($student_data);
                          $j=0;
                          echo '<div class="tab-content" id="myTabContent">';
                          foreach ($student_data as $key => $student_detail) {
                            if($j==0){
                              
                              echo "<div class='tab-pane fade show active' id='{$student_detail['course']}'>";
                              

                                     $payments = $student_detail['payment'];
                              // print_r($payments);
                              
                                echo "<table class='table table-sm table-striped'>";
                                      echo "<tr class='bg-success text-white' >";
                                        echo "<th >";
                                          echo "Invoice #";
                                        echo "</th>";
                                        echo "<th>";
                                          echo "Paid Month";
                                        echo "</th>";
                                        echo "<th>";
                                        echo "Invoice Amount";
                                      echo "</th>";
                                        echo "<th style='min-width:60px'>";
                                          echo "Paid Amount";
                                        echo "</th>";
                                        echo "<th style='min-width:60px'>";
                                          echo "Note";
                                        echo "</th>";
                                        echo "<th>";
                                        echo "Last updated";
                                      echo "</th>";
                                        echo "<th>";
                                          echo "Status";
                                        echo "</th>";
                                        echo "<th>";
                                          echo "Action";
                                        echo "</th>";
                                      
                                      echo "</tr>";
                                      foreach ($student_detail['payment'] as $key => $payment) {
                                        if($payment->status == 'paid') {
                                          echo form_open('online/pdfgenerator');
                                          echo "<tr>";
                                          echo "<td>";
                                            echo $payment->invoice_number;
                                          echo "</td>";
                                          echo "<td>";
                                            echo $payment->label;
                                          echo "</td>";
                                          echo "<td>";
                                          echo $payment->amount;
                                        echo "</td>";
                                        
                                          echo "<td>";
                                          $invoiceIds = array_map(function($payment) {
                                            return $payment->invoice_id;
                                        }, $student_detail['payment_completion']);
                                        
                                        $index = array_search($payment->ID, $invoiceIds);
                                        if($index !== false) {
                                          echo $student_detail['payment_completion'][$index]->amount;
                                        }else{
                                          echo $payment->amount;
                                        }

                                          echo "</td>";
                                          echo "<td>";
                                            

                                                 echo '<span class="badge badge-light text-wrap">';
                                                  echo $student_detail['payment_completion'][$index]->note;
                                                 echo '</span>';
                                              echo "</td>";
                                          echo "<td>";
                                          // if paid return receipt payment date
                                          // echo $student_detail['payment_completion'][$index]->created_at;
                                          $originalTime = $student_detail['payment_completion'][$index]->created_at;
                                          $timestamp = strtotime($originalTime);
                                          $newTime = date("y-m-d h:i A", $timestamp);
                                          echo $newTime;
                                          // echo (empty($payment->updated_at))? $payment->created_at : $payment->updated_at;
                                          
                                      
                                        echo "</td>";
                                          echo "<td>";
                                          echo $payment->status;
                                        echo "</td>";
                                        echo "<td>";
                                        
                                        echo "<input type='hidden' name='grade' value='{$student_detail['grade'][0]->label}'>";
                                        echo "<input type='hidden' name='invoiceid' value='{$payment->invoice_number}'>";
                                        echo "<input type='submit' class='btn btn-info btn-sm mr-1' value='Save' />";
                                      if(empty($student_detail['profile']->phone)){
                                        echo "<a class='disabled pt-0 pb-0 btn btn-success btn-sm' href='https://wa.me/{$student_detail['profile']->phone}?text={$payment->label}%20Approved'>Send</a>";
                                      }else{
                                        echo "<a class='mt-1 mt-md-0 pt-0 pb-1 btn btn-success btn-sm' href='https://wa.me/{$student_detail['profile']->phone}?text={$payment->label}%20Approved'>";
                                        echo "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-whatsapp' viewBox='0 0 16 16'>
                                            <path d='M13.601 2.326A7.95 7.95 0 0 0 8.06.008a7.936 7.936 0 0 0-5.63 2.326A7.987 7.987 0 0 0 .37 8.06a7.96 7.96 0 0 0 1.169 4.019L.012 15.59l3.613-1.154a7.964 7.964 0 0 0 4.035 1.169h.004c2.11 0 4.102-.822 5.63-2.326a7.95 7.95 0 0 0 2.326-5.63c-.003-2.11-.825-4.102-2.326-5.63zM8.06 14.92h-.002a6.92 6.92 0 0 1-3.52-1.026l-.252-.15-2.144 .685 .72-2.09-.164-.27A6.92 6.92 0 0 1 .98 8.06c0-3.83 3.11-6.94 6.94-6.94a6.92 6.92 0 0 1 4.9 2.03c1.33 1.33 2.03 3.09 2.03 4.9a6.92 6.92 0 0 1-2.03 4.9c-1.33 1.33-3.09 2.03-4.9 2.03zm3-4.173c-.163-.081-.97-.479-1.12-.534-.15-.056-.26-.081-.37 .081s-.426 .534-.523 .644c-.097 .111-.195 .125-.358 .044-.163-.08-.687-.252-1.31-.808-.484-.43-.81-.961-.905-1.124-.095-.163-.01-.25 .07-.33 .072-.071 .163-.195 .244-.293 .081-.098 .108-.163 .163-.271 .054-.108 .027-.203-.014-.284-.04-.08-.37-.888-.508-1.216-.134-.319-.27-.276-.37-.276h-.317c-.108 0-.284 .04-.433 .195s-.567 .554-.567 1.352c0 .798 .58 1.57 .661 1.678 .081 .108 1.143 1.743 2.767 2.443 1.625 .7 1.625 .467 1.92 .439 .296-.027 1.02-.416 1.163-.818 .144-.403 .144-.747 .101-.818s-.163-.108-.326-.189z'/>
                                          </svg>";
                                        echo "</a>";
                                      }
                                      

                                        
                                        echo "</td>";
                                        echo "</tr>";
                                        echo form_close();
                                        }
                                        else if($payment->status == 'unpaid'){
                                          echo form_open('online/insertPayment' , ['onsubmit' => 'return confirmSubmit();']);
                                              echo "<tr>";
                                              echo "<td>";
                                              echo "<input type='hidden' name='student_id' value='{$student_detail['profile']->admission_number}'>";

                                            
                                              echo "<input type='hidden' name='invoice_id' value='$payment->ID'>";

                                                echo $payment->invoice_number;
                                              echo "</td>";
                                              echo "<td>";
                                              echo "<input type='hidden' name='label' value='$payment->label'>";
                                                echo $payment->label;
                                              echo "</td>";
                                              echo "<td>";
                                            
                                                echo $payment->amount;
                                              echo "</td>";
                                              
                                              echo "<td>";
                                              echo "<input type='text' class='form-control' name='amount' value='$payment->amount'>";

                                              echo "<input type='hidden' name='student_record_id' value='$payment->student_record_id'>";
                                              echo "</td>";
                                              
                                              echo "<td>";
                                            
                                                 echo "<input type='text' name='note' class='form-control' placeholder='Enter Note'>";
                                              echo "</td>";
                                              echo "<td>";
                                              if(empty($payment->updated_at)){
                                                // echo $payment->created_at;

                                                 $originalTime =$payment->created_at;
                                                  $timestamp = strtotime($originalTime);
                                                  $newTime = date("y-m-d h:i A", $timestamp);
                                                  echo $newTime;

                                              }else{
                                                // echo $payment->updated_at;
                                                $originalTime = $payment->updated_at;
                                                  $timestamp = strtotime($originalTime);
                                                  $newTime = date("y-m-d h:i A", $timestamp);
                                                  echo $newTime;
                                              }
                                            echo "</td>";
                                              echo "<td>";
                                              echo $payment->status;
                                            echo "</td>";
                                            echo "<td>";
                                            echo "<input type='submit' class='btn btn-danger btn-sm' value='Pay' />";
                                            echo "</td>";
                                            echo "</tr>";
                                          echo form_close();
                                        }
                                      }
                              echo "</table>";












                              echo "</div>";
                              $j++;
                            }else{
                               echo "<div class='tab-pane fade' id='{$student_detail['course']}'>";
                              

                                 $payments = $student_detail['payment'];
                              // print_r($payments);
                              
                                echo "<table class='table table-sm table-striped'>";
                                      echo "<tr class='bg-success text-white' >";
                                        echo "<th >";
                                          echo "Invoice Number";
                                        echo "</th>";
                                        echo "<th>";
                                          echo "Paid Month";
                                        echo "</th>";
                                        echo "<th>";
                                        echo "Invoice Amount";
                                      echo "</th>";
                                        echo "<th style='min-width:60px'>";
                                          echo "Paid Amount";
                                        echo "</th>";
                                        echo "<th style='min-width:60px'>";
                                          echo "Note";
                                        echo "</th>";
                                        echo "<th>";
                                        echo "Last updated";
                                      echo "</th>";
                                        echo "<th>";
                                          echo "Status";
                                        echo "</th>";
                                        echo "<th>";
                                          echo "Action";
                                        echo "</th>";
                                      
                                      echo "</tr>";
                                      foreach ($student_detail['payment'] as $key => $payment) {
                                        if($payment->status == 'paid') {
                                          echo form_open('online/pdfgenerator');
                                          echo "<tr>";
                                          echo "<td>";
                                            echo $payment->invoice_number;
                                          echo "</td>";
                                          echo "<td>";
                                            echo $payment->label;
                                          echo "</td>";
                                          echo "<td>";
                                          echo $payment->amount;
                                        echo "</td>";
                                         
                                          echo "<td>";
                                       
                                          $invoiceIds = array_map(function($payment) {
                                            return $payment->invoice_id;
                                        }, $student_detail['payment_completion']);
                                        
                                        $index = array_search($payment->ID, $invoiceIds);
                                        if($index !== false) {
                                          echo $student_detail['payment_completion'][$index]->amount;
                                        }else{
                                          echo $payment->amount;
                                        }

                                          echo "</td>";
                                          echo "<td>";
                                            

                                                 echo '<span class="badge badge-light text-wrap">';
                                                  echo $student_detail['payment_completion'][$index]->note;
                                                 echo '</span>';
                                              echo "</td>";
                                          
                                          echo "<td>";
                                          // if paid return receipt payment date
                                          echo $student_detail['payment_completion'][$index]->created_at;
                                          // echo (empty($payment->updated_at))? $payment->created_at : $payment->updated_at;
                                          
                                      
                                        echo "</td>";
                                          echo "<td>";
                                          echo $payment->status;
                                        echo "</td>";
                                        echo "<td>";
                                        
                                        echo "<input type='hidden' name='grade' value='{$student_detail['grade'][0]->label}'>";
                                        echo "<input type='hidden' name='invoiceid' value='{$payment->invoice_number}'>";
                                        echo "<input type='submit' class='btn btn-info btn-sm mr-1' value='Save' />";
                                      if(empty($student_detail['profile']->phone)){
                                        echo "<a class='disabled pt-0 pb-0 btn btn-success btn-sm' href='https://wa.me/{$student_detail['profile']->phone}?text={$payment->label}%20Approved'>Send</a>";
                                      }else{
                                        echo "<a class='mt-1 mt-md-0 pt-0 pb-1 btn btn-success btn-sm' href='https://wa.me/{$student_detail['profile']->phone}?text={$payment->label}%20Approved'>";
                                        echo "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-whatsapp' viewBox='0 0 16 16'>
                                            <path d='M13.601 2.326A7.95 7.95 0 0 0 8.06.008a7.936 7.936 0 0 0-5.63 2.326A7.987 7.987 0 0 0 .37 8.06a7.96 7.96 0 0 0 1.169 4.019L.012 15.59l3.613-1.154a7.964 7.964 0 0 0 4.035 1.169h.004c2.11 0 4.102-.822 5.63-2.326a7.95 7.95 0 0 0 2.326-5.63c-.003-2.11-.825-4.102-2.326-5.63zM8.06 14.92h-.002a6.92 6.92 0 0 1-3.52-1.026l-.252-.15-2.144 .685 .72-2.09-.164-.27A6.92 6.92 0 0 1 .98 8.06c0-3.83 3.11-6.94 6.94-6.94a6.92 6.92 0 0 1 4.9 2.03c1.33 1.33 2.03 3.09 2.03 4.9a6.92 6.92 0 0 1-2.03 4.9c-1.33 1.33-3.09 2.03-4.9 2.03zm3-4.173c-.163-.081-.97-.479-1.12-.534-.15-.056-.26-.081-.37 .081s-.426 .534-.523 .644c-.097 .111-.195 .125-.358 .044-.163-.08-.687-.252-1.31-.808-.484-.43-.81-.961-.905-1.124-.095-.163-.01-.25 .07-.33 .072-.071 .163-.195 .244-.293 .081-.098 .108-.163 .163-.271 .054-.108 .027-.203-.014-.284-.04-.08-.37-.888-.508-1.216-.134-.319-.27-.276-.37-.276h-.317c-.108 0-.284 .04-.433 .195s-.567 .554-.567 1.352c0 .798 .58 1.57 .661 1.678 .081 .108 1.143 1.743 2.767 2.443 1.625 .7 1.625 .467 1.92 .439 .296-.027 1.02-.416 1.163-.818 .144-.403 .144-.747 .101-.818s-.163-.108-.326-.189z'/>
                                          </svg>";
                                        // 
                                        echo "</a>";
                                      }
                                      

                                        
                                        echo "</td>";
                                        echo "</tr>";
                                        echo form_close();
                                        }
                                        else if($payment->status == 'unpaid'){
                                          echo form_open('online/insertPayment' , ['onsubmit' => 'return confirmSubmit();']);
                                              echo "<tr>";
                                              echo "<td>";
                                              echo "<input type='hidden' name='student_id' value='{$student_detail['profile']->admission_number}'>";

                                            
                                              echo "<input type='hidden' name='invoice_id' value='$payment->ID'>";

                                                echo $payment->invoice_number;
                                              echo "</td>";
                                              echo "<td>";
                                              echo "<input type='hidden' name='label' value='$payment->label'>";
                                                echo $payment->label;
                                              echo "</td>";
                                              echo "<td>";
                                            
                                                echo $payment->amount;
                                              echo "</td>";
                                              echo "<td>";
                                              echo "<input type='text' class='form-control' name='amount' value='$payment->amount'>";

                                              echo "<input type='hidden' name='student_record_id' value='$payment->student_record_id'>";
                                              echo "</td>";
                                               echo "<td>";
                                            
                                                 echo "<input type='text' name='note' class='form-control' placeholder='Enter Note'>";
                                              echo "</td>";
                                              echo "<td>";
                                              if(empty($payment->updated_at)){
                                                echo $payment->created_at;
                                              }else{
                                                echo $payment->updated_at;
                                              }
                                            echo "</td>";
                                              echo "<td>";
                                              echo $payment->status;
                                            echo "</td>";
                                            echo "<td>";
                                            echo "<input type='submit' class='btn btn-danger btn-sm' value='Pay' />";
                                            echo "</td>";
                                            echo "</tr>";
                                          echo form_close();
                                        }
                                      }
                              echo "</table>";






                               echo "</div>";
                            }
                                  

                          }
                          echo '</div>';



                               
                          // ksort($payments );
                          // print_r($payments);
                          ?>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-12 col-md-12 mt-2">
              <div class="card">
                <div class="card-body">
                  <div class="cart-title">Attendance</div>
                  <?php 
                  // $attendance = $student_data['attendance'];
                  // if(count($attendance) != 0){
                  //   echo "<table class='table table-sm table-striped'>";
                  //   echo "<tr class='bg-success text-white' >";
                  //    echo "<th >";
                  //      echo "Attend Date";
                  //    echo "</th>";
                    
                  //   echo "</tr>";
                  //   echo "</table>";
                  //   $current_month = date('m');
                  //   foreach ($student_data['attendance'] as $key => $attend) {
                  //    if($current_month == date('m',strtotime($attend->attend_date)) ){
                  //     echo "<span class='badge badge-primary mx-1' title='".date('D-m-Y',strtotime($attend->attend_date))."'>". date('d-M-Y',strtotime($attend->attend_date)). "</span>";
                  //    }else{
                  //     echo '<hr class="my-1">';
                  //     echo "<span class='badge badge-warning mx-1' title='".date('D-m-Y',strtotime($attend->attend_date))."'>". date('d-M-Y',strtotime($attend->attend_date)). "</span>";
                  //    }
                       
                    
                     
                  //   }
                    
                  // }else{

                  //   echo "<span class='badge badge-danger mx-1'>No Attendance Found</span>";
                  // }
                 
                 
                
                  ?>
                </div>
              </div>
            </div>
        </div>

        <?php } ?>
    </div>
    <div class="container-fluid my-3">
      <div class="row">
        <div class="col text-center">
        Copyright © 2025 - IATTSL. All Rights Reserved
        </div>
      </div>
    </div>
</body>
<script>
  
 function confirmSubmit() {
    return confirm("Are you sure you want to update the payment?");
  }

</script>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
</html>