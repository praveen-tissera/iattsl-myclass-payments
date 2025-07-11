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
    .fix-col{
      position: sticky; top: 0; background: #f8f8f8; z-index: 2; padding: 8px; border: 1px solid #ccc;
    }
    body{
      font-size: .9rem;
    }
    /* change input field font size */
      button[type=button],input[type=date],input[type=text], input[type=number], input[type=email], input[type=password], input[type=url], select, option, .from-control {
      font-size: .6rem !important;
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
     <li class="nav-item">
      <a class="nav-link" target="_blank" href="<?php echo base_url(); ?>index.php/welcome/income">Income Summary </a>
     </li>
     <li class="nav-item active">
      <a class="nav-link" href="<?php echo base_url(); ?>index.php/welcome/paymenthistory">Payment Summary <span class="sr-only">(current)</span></a>
     </li>
     <li class="nav-item ">
      <a class="nav-link" href="<?php echo base_url(); ?>index.php/mark/">Enter Marks </a>
     </li>
     <li class="nav-item">
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
                <h1 class="text-center display-4" style="font-size:3rem">Student Payment Summary</h1>
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

                <?php echo form_open('welcome/gradewisepaymentSumamry') ?>
                <table class="table table-borderless">
                    <tr>
                      <!-- <td> -->
                        
                        <!-- create date form group set to current date -->
                        <!-- <div class="form-group">
                          <label for="date">Date</label>
                          <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div> -->

                      <!-- </td> -->
                        <td>
                        <?php
                         date_default_timezone_set('Asia/Colombo');
                            $currentDate = date("Y-m-d"); // Format: YYYY-MM-DD
                            
                        ?>

                        <div class="form-group">
                          <label for="class">Select Class</label>
                          <select class="form-control" id="class" name="class" required>
               
                            <?php 
                            // create loop to get all data from classes array
                            
                            foreach($grades as $class){
                              // get class name
                              $class_name = $class->label;
                              // get class id
                              $class_id = $class->ID.'*'.$class->label;
                            
                              
                              echo "<option value='$class_id'>$class_name</option>";
                            }
                            ?>
                            
                          </select>
                        </div>
                         

                        </td>
                        <td>
                          <!-- create dropdown listing 3 branches names are Battaramull Pellawatta and Mattegoda -->
                          <div class="form-group">
                            <label for="branch">Select Branch</label>
                            <select class="form-control" id="branch" name="branch" required>
                              <option value="BAT">Battaramulla</option>
                              <option value="PEL">Pellawatta</option>
                              <option value="HRI">Hripitiya</option>
                              <option value="MAH">Maharagama</option>
                              <option value="MAT">Mattegoda</option>
                            </select>
                        </td>
                        <td>
                          <br>
                            <input class="btn btn-danger btn-block mt-2" type="submit" name="submit" value="SEARCH">
                        </td>
                    </tr>
                </table>
                <?php echo form_close(); ?>
                <hr>
            </div>

        </div>
        <div class="row">
            <div class="col table-responsive" style="overflow-x: auto; max-width: 100%; height: 450px; overflow-y: auto;">
                <?php
                $attributes = array('id' => 'studentMarkList');
                echo form_open('mark/paperMarksSubmit', $attributes);
                if (isset($students) && is_array($students)) { 
                  ?>
                <h2 class="text-center">Payment Summary for  <?php echo $branch; ?> <?php echo urldecode($pclass_name); ?> - <?php echo $subject_name; ?></h2>
                <input type="hidden" class="form-control" value="<?php echo $pclass_id; ?>" name="selectclassid">
                <input type="hidden" class="form-control" value="<?php echo $pclass_name; ?>" name="selectclassname">  
                <input type="hidden" class="form-control" value="<?php echo $subject_id; ?>" name="selectsubjectid">
                <input type="hidden" class="form-control" value="<?php echo $subject_name; ?>" name="selectsubjectname">


                  <input type="hidden" class="form-control" value="<?php echo $branch; ?>" name="branch">

<!-- 
                <input class="btn btn-primary btn-block mb-2" type="submit" name="btnsubmit" value="Submit" onclick="changeButtonText()" id="submitBtn"> -->
                <?php
                  $currentMonth = date("F");
             
                ?>
                   <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Student ID</th>
                            <th scope="col" style="position: sticky; left: 0; background: #f2f2f2; z-index: 1;">Student Name</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'January') ?  'bg-warning fix-col' : ''; ?>">JAN</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'February') ?  'bg-warning' : ''; ?>" >FEB</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'March') ?  'bg-warning' : ''; ?>">MAR</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'April') ?  'bg-warning' : ''; ?>">APR</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'May') ?  'bg-warning' : ''; ?>">MAY</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'June') ?  'bg-warning fix-col' : ''; ?>">JUN</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'July') ?  'bg-warning' : ''; ?>">JUL</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'August') ?  'bg-warning' : ''; ?>">AUG</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'September') ?  'bg-warning' : ''; ?>">SEP</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'October') ?  'bg-warning' : ''; ?>">OCT</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'November') ?  'bg-warning' : ''; ?>">NOV</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'December') ?  'bg-warning' : ''; ?>">DEC</th>
                        
                            

                        </tr>
                    </thead>
                    <tbody>
                      <?php
                      
                        $i = 1;
                        $months = [
                                      "January", "February", "March", "April", "May", "June",
                                      "July", "August", "September", "October", "November", "December"
                                  ];

                        foreach ($students as $student) {
                            // check if student marks already exists
                            $payments = $student->payment_history;
                            $part1 = null;
                            $part2 = null;
                            $total = null;
                            $papertype = null;
                            $paperlink = null;
                           
                           
                           echo "<tr>";
                            echo "<td >" . $i . "</td>";
                            echo "<td>" . $student->admission_number . "</td>";
                            echo "<td style='position: sticky; left: 0; background: #f2f2f2; z-index: 1;'>" . $student->name . "</td>";
                            if (isset($payments) && is_array($payments)) {
                                foreach ($months as $month) {
                                    $found = false;
                                    foreach ($payments as $payment) {
                                        if (stripos($payment->label, $month) !== false) {
                                            echo "<td>";
                                            // echo $payment->label;
                                            // echo "<br>";
                                           
                                            if($payment->status == 'paid'){
                                                echo "<span class='badge badge-success'> Paid</span>";
                                            }else if($payment->status == 'unpaid'){
                                              $studentid = explode('/', $student->admission_number);
                                                echo "<a class='badge badge-danger' href='" . base_url() . "index.php/welcome/idValidator/{$studentid[1]}/{$branch}'> Unpaid </a>";
                                            }
                                            echo "</td>";
                                            $found = true;
                                            break; // Exit the inner loop once a match is found
                                        }
                                    }
                                    if (!$found) {
                                        echo "<td> Not Found </td>"; // If no payment found for the month, display empty cell
                                    }
                                }
                                // foreach ($student->payment_history as $payment) {
                                //     if (stripos($payment->label, $months) !== false) {
                                //       echo "<td>";
                                //         echo $payment->label;
                                //         echo "<br>";
                                //         echo $payment->status;
                                //       echo "</td>";
                                //     }


                                // }
                            }
                          echo "</tr>";
                            ?>


                            <?php
                           
                           
                            $i++;

                        }

                        ?>

                    </tbody>
                    </table>

               <?php }
               echo form_close();
                ?>
            </div>
        </div>
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
function openSmallWindow(url) {
    window.open(url, '_blank', 'width=700,height=800,resizable=yes,scrollbars=yes');
}

function changeButtonText() {
    var btn = document.getElementById("submitBtn");
    btn.value = "Processing...";
    btn.disabled = true; // Optional: Disable button to prevent multiple clicks
    document.getElementById("studentMarkList").submit();
}

</script>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>

<script>
        $(document).ready(function() {
            $(".totalTrigger").focus(function() {

                var row = $(this).closest("tr");
                var val1 = row.find(".input1").val();
                var val2 = row.find(".input2").val();
                console.log(val1, val2);
                if (val1 !== "" && val2 !== "") {
                    
                    var total = parseFloat(val1) + parseFloat(val2);

                    row.find(".totalTrigger").val(total);
                    
                    // check total and set grade base of criteria
                    if (total >= 75) {
                        row.find(".gradeVal").text('A');
                    } else if (total >= 65) {
                        row.find(".gradeVal").text('B');
                    } else if (total >= 55) {
                        row.find(".gradeVal").text('C');
                    } else if (total >= 40) {
                        row.find(".gradeVal").text('S');
                    } else {
                        row.find(".gradeVal").text('F');
                    }
                }else {
                    var totalOnly = row.find(".totalTrigger").val();
                    if (totalOnly !== "") {
                        // check total and set grade base of criteria
                        if (parseFloat(totalOnly) >= 75) {
                            row.find(".gradeVal").text('A');
                        } else if (parseFloat(totalOnly) >= 65) {
                            row.find(".gradeVal").text('B');
                        } else if (parseFloat(totalOnly) >= 55) {
                            row.find(".gradeVal").text('C');
                        } else if (parseFloat(totalOnly) >= 40) {
                            row.find(".gradeVal").text('S');
                        } else {
                            row.find(".gradeVal").text('F');
                        }
                    }else{
                      row.find(".gradeVal").text('');
                    }
                    
                }







                
                // if (val1 !== "" && val2 !== "") {
                //     $("#total").val(parseFloat(val1) + parseFloat(val2));
                //     // check total and set grade base of criteria
                //     if (parseFloat(val1) + parseFloat(val2) >= 75) {
                //         $("#gradeVal").text('A');
                //     } else if (parseFloat(val1) + parseFloat(val2) >= 65) {
                //         $("#gradeVal").text('B');
                //     }else if (parseFloat(val1) + parseFloat(val2) >= 55) {
                //         $("#gradeVal").text('C');
                //     }else if (parseFloat(val1) + parseFloat(val2) >= 45) {
                //         $("#gradeVal").text('B');
                //     }else {
                //         $("#gradeVal").text('F');
                //     }

                // }
            });
        });
    </script>
</html>