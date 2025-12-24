<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url() . '/css/bootstrap.min.css' ?>">

   
    <title>Income Summary</title>
  <style>
    body{
      font-size: .9rem;
    }
    /* change input field font size */
    input[type=text], input[type=number], input[type=email], input[type=password], select {
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
    .PEL{
      background-color:rgb(255, 12, 12) !important;
    }
    .BAT{
      background-color:rgb(33, 184, 60) !important;
    }
    .MAT{
      background-color: #3357FF !important;
    }
    .MAH{ 
      background-color: #FF33A1 !important;
    } 
    .HRI{
      background-color: #FF8C33 !important;
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
      <li class="nav-item ">
        <a class="nav-link" href="<?php echo base_url(); ?>">Enter Payments </a>
      </li>
     <li class="nav-item active">
      <a class="nav-link"  href="<?php echo base_url(); ?>index.php/welcome/income">Income Summary <span class="sr-only">(current)</span></a>
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
      <a class="nav-link"  href="<?php echo base_url(); ?>index.php/mark/">Enter Marks <span class="sr-only">(current)</span></a>
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
                <h1 class="text-center display-4" style="font-size:3rem">Select Date</h1>
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

                <?php echo form_open('welcome/adminincomesummary') ?>
                <?php
                         date_default_timezone_set('Asia/Colombo');
                            $currentDate = date("Y-m-d"); // Format: YYYY-MM-DD
                        ?>
                <table class="table table-borderless">
                    <tr>
                        
                    
                    <td>
                      <input type="date" class="form-control" name="start_incomedate" value="<?php echo $currentDate; ?>">
                       <div class="btn-group btn-group-toggle mt-2" data-toggle="buttons">
                          <label class="btn btn-sm btn-secondary active">
                            <input type="radio" name="filteroptions" id="option1" value="custome" checked> Custom
                          </label>
                          <label class="btn btn-sm btn-secondary">
                            <input type="radio" name="filteroptions" value="week" id="option2"> 07 Days
                          </label>
                          <label class="btn btn-sm btn-secondary">
                            <input type="radio" name="filteroptions" value="month" id="option3"> This Month
                          </label>
                          <label class="btn btn-sm btn-secondary">
                            <input type="radio" name="filteroptions" value="lastmonth" id="option3"> Last Month
                          </label>
                        </div>
                    </td>
                    
                    <td>
                        

                       <div class="input-group">
                          <input type="date" class="form-control" name="end_incomedate" value="<?php echo $currentDate; ?>">

                          <!-- <div class="input-group-append">
                            <div class="btn-group" role="group" aria-label="Class Mode">
                              <label class="btn btn-outline-secondary">
                                2025 IATTSL Pelawatta  
                                <input type="radio" name="class_mode" value="3" autocomplete="off" checked> Physical
                              </label>
                               25/26 Iattsl EDEX/CAM ONLINE  
                              <label class="btn btn-outline-secondary">
                                <input type="radio" name="class_mode" value="4" autocomplete="off"> Online
                              </label>
                            </div>
                          </div> -->
                        </div>

                       
                          <?php 
                            // if (!empty($student_data['profile'][0]->admission_number)) {
                            //   $student_id_array = explode('/', $student_data['profile'][0]->admission_number);
                            //   $student_id = end($student_id_array);

                            //   echo "<input class='form-control' type='date' name='studentid' placeholder='24-999' value='{$student_id}'>";
                            // }else{
                            //  echo '<input class="form-control" type="text" name="studentid" placeholder="24-999">';
                            // }

                          
                          ?>
                           

                        </td>
                        <td>
                          <select class="form-control" name="academicyear">
                            <?php 
                              foreach ($academicyear as $year) {
                                if (!empty($selected_academic_year) && $selected_academic_year == $year->ID) {
                                  echo "<option value='{$year->ID}' selected>{$year->label}</option>";
                                } else {
                                  echo "<option value='{$year->ID}'>{$year->label}</option>";
                                }
                              }
                            ?>
                          </select>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <input class="btn btn-primary btn-block" type="submit" name="submit" value="Submit">
                        </td>
                    </tr>
                </table>
                <?php echo form_close(); ?>
                
            </div>

        </div>

        <?php 
          if(isset($date_select)){
            echo "<hr>";

             
            //  create array of branches names PEL, BAT, MAT, MAH,HRI
            $branch_list = array('ONL','PEL', 'BAT', 'MAT', 'MAH', 'HRI');
            // create a loop through $income_summary arrya and crate seperate arrays for each branch
            $brancewise_income_summary = array();
            if(isset($income_summary) && !empty($income_summary)) {
              // $income_summary is an array of objects
              // print_r($income_summary);
            foreach ($branch_list as $branch) {
              // $income_summary[$branch] = array();
               $total_income = 0;
              foreach ($income_summary as $key => $payment) {
                $student_admission = explode('/', $payment->admission_number);
                if($student_admission[0] == $branch){
                  $brancewise_income_summary[$branch][] = $payment;

                }
                  
              }
            }
            // print_r($brancewise_income_summary);
          }
        ?>
        <div class="row">
            <?php
            foreach ($branch_list as $branch) {
              foreach($brancewise_income_summary as $key => $payments){
                if($key == $branch){
            ?>
            <div class="col-6 col-sm-3 col-md-3 mt-2">
                        <div class="card text-white bg-primary <?php echo $branch; ?>">
                        <div class="card-header strong"><?php  echo $branch; ?> Income as of <?php echo $date_select; ?></div>
                          <div class="card-body">
                            <div class="cart-title">
                            <?php
                             if($income_summary != 0){
                              $total_income = 0;
                              foreach ($payments as $key => $payment) {
                                $total_income += $payment->invoice_payable;
                              }
                              $formattedAmount = number_format($total_income, 2, '.', ',');

                              echo '<h4> LKR '. $formattedAmount . '<h4>';
                             }
                            
                            ?>
                            
                            </div>
                            <?php 

                            ?>
                          </div>
                        </div>
            </div>
            <?php } } } ?>
        </div>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 mt-2">
              <div class="card">
                <div class="card-body">
                  <div class="cart-title mb-1">Payment Summary : Date <?php echo (isset($date_select)) ? $date_select : ''; ?></div>
                  <?php
                  if(isset($income_summary) && empty($income_summary)){
                     echo "<div class='alert alert-warning'>";
                    echo "No income records found for the selected date.";
                    echo "</div>";
                  } else {
                   
                  }   
                  ?>  
                  <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <?php
                    $tab_active = 'active';
                    foreach ($branch_list as $branch) {
                      foreach($brancewise_income_summary as $key => $payments){
                        if($key == $branch){
                    ?>

                  
                  <li class="nav-item" role="presentation">
                    <button class=" mr-1 nav-link border-0 <?php echo $tab_active; ?>" id="pills-<?php echo $branch; ?>-tab" data-toggle="pill" data-target="#pills-<?php echo $branch; ?>" type="button" role="tab" aria-controls="pills-<?php echo $branch; ?>" aria-selected="true"><?php echo $branch; ?></button>
                  </li>

                  <?php
                  $tab_active = '';
                  ?>
                
                    <?php 
                                    }
                                  }
                                }
                    ?>
        </ul>
        <div class="tab-content" id="pills-tabContent">
            <?php
            $tab_active = 'active';

            foreach ($branch_list as $branch) {
              foreach($brancewise_income_summary as $key => $payments){
                if($key == $branch){
            ?>


            <div class="tab-pane fade show <?php echo $tab_active; ?>" id="pills-<?php echo $branch; ?>" role="tabpanel" aria-labelledby="pills-<?php echo $branch; ?>-tab">
                            <!-- show payment table base on branch -->
                            
          <?php 
                 
                //  print_r($income_summary);
                 echo "<table class='table table-sm table-striped'>";
                    echo "<tr class='bg-success text-white' >";
                    echo "<th >";
                        echo "#";
                      echo "</th>";
                      echo "<th >";
                        echo "Recepit Number";
                      echo "</th>";
                      echo "<th>";
                      echo "Student ID";
                    echo "</th>";
                    echo "<th>";
                      echo "Course";
                    echo "</th>";
                    echo "<th>";
                    echo "Student Name";
                  echo "</th>";
                      echo "<th>";
                        echo "Paid Month";
                      echo "</th>";
                      echo "<th>";
                      echo "Invoice Amount";
                    echo "</th>";
                    echo "<th>";
                    echo "Created Date";
                  echo "</th>";
                      echo "<th>";
                      echo "Last updated";
                    echo "</th>";
                    
                 echo "</tr>";
                 if($income_summary == 0){
                  echo "<tr>";
                  echo "<td colspan='7'>";
                    echo "No records found";
                  echo "</td>";
                  echo "</tr>";
                 }else{
                  $rowNumber = 1;
                  // print_r($income_summary);
                  foreach ($payments as $key => $payment) {
                    echo "<tr>";
                      echo "<td>";
                      echo $rowNumber;
                        $rowNumber += 1;
                      echo "</td>";
                      echo "<td>";
                        echo $payment->receipt_number;
                      echo "</td>";
                      echo "<td>";
                      echo $payment->admission_number;
                      echo "</td>";
                      echo "<td>";
                      echo $payment->course . ' | ' .$payment->grade; 
                      echo "</td>";
                      echo "<td>";
                      echo $payment->name;
                      echo "</td>";
                      echo "<td>";
                      echo $payment->invoice_label;
                      echo "</td>";
                      echo "<td>";
                      echo $payment->invoice_payable;
                      echo "</td>";
                      echo "<td>";
                      echo $payment->created_at;
                      echo "</td>";
                      echo "<td>";
                      echo $payment->updated_at;
                      echo "</td>";
  
                    echo "</tr>";
                  
                   }
                 }
                
                 echo "</table>";

                  ?>
                  <!-- end of payment table -->




                </div>
                  <?php 
                   $tab_active = '';
                   ?>
<?php 
                }
              }
            } 
?>
        </div>
                  
                  
                 
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
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
</html>