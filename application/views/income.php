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
  </style>

</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light " style="background-color: #BEADFA;">
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
      <a class="nav-link" target="_blank" href="<?php echo base_url(); ?>index.php/welcome/income"">Income Summary <span class="sr-only">(current)</span></a>
     </li>
     <li class="nav-item">
      <a class="nav-link" target="_blank" href="https://techlab.lk/reportcard/index.php">My Report Card</a>
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

                <?php echo form_open('welcome/incomesummary') ?>
                <table class="table table-borderless">
                    <tr>
                        <td>
                        <?php
                         date_default_timezone_set('Asia/Colombo');
                            $currentDate = date("Y-m-d"); // Format: YYYY-MM-DD
                        ?>

                        <input class="form-control" type="date" name="incomedate" value="<?php echo $currentDate; ?>">
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
        ?>
          <div class="col-12 col-sm-4 col-md-4 mt-2">
                        <div class="card text-white bg-primary">
                        <div class="card-header strong">Income as of <?php echo $date_select; ?></div>
                          <div class="card-body">
                            <div class="cart-title">
                            <?php
                             if($income_summary != 0){
                              $total_income = 0;
                              foreach ($income_summary as $key => $payment) {
                                $total_income += $payment->invoice_payable;
                              }
                              $formattedAmount = number_format($total_income, 2, '.', ',');

                              echo '<h2> LKR '. $formattedAmount . '<h2>';
                             }
                            
                            ?>
                            
                            </div>
                            <?php 

                            ?>
                          </div>
                        </div>
            </div>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 mt-2">
              <div class="card">
                <div class="card-body">
                  <div class="cart-title">Payment Summary : Date <?php echo (isset($date_select)) ? $date_select : ''; ?></div>
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
                  foreach ($income_summary as $key => $payment) {
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
                  // ksort($payments );
                  // print_r($payments);
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
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
</html>