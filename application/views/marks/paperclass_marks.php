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
<nav class="navbar navbar-expand-lg navbar-dark bg-primary " style="background-color: #BEADFA;">
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
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
          Payment Summary
        </a>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="<?php echo base_url(); ?>index.php/welcome/paymenthistory">Physical Payments Summary</a>
          <a class="dropdown-item" href="<?php echo base_url(); ?>index.php/online/onlinepaymenthistory">Online Payments Summary</a>
          
        </div>
     </li>
     <li class="nav-item ">
      <a class="nav-link" href="<?php echo base_url(); ?>index.php/mark/">Enter Marks </a>
     </li>
     <li class="nav-item active">
      <a class="nav-link" href="<?php echo base_url(); ?>index.php/mark/paper">Enter Paper Class Marks <span class="sr-only">(current)</span></a>
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
                <h1 class="text-center display-4" style="font-size:3rem">Paper Class Marks</h1>
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

                <?php echo form_open('mark/paperStudents') ?>
                <table class="table table-borderless">
                    <tr>
                      <td>
                        <?php
                         date_default_timezone_set('Asia/Colombo');
                            $currentDate = date("Y-m-d"); // Format: YYYY-MM-DD
                            
                        ?>
                        <!-- create date form group set to current date -->
                        <div class="form-group">
                          <label for="date">Date</label>
                          <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                      </td>
                        <td>
                        

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
                            <input class="btn btn-danger btn-block mt-2" type="submit" name="submit" value="NEXT">
                        </td>
                    </tr>
                </table>
                <?php echo form_close(); ?>
                <hr>
            </div>

        </div>
        <div class="row">
            <div class="col">
                <?php
                $attributes = array('id' => 'studentMarkList');
                echo form_open('mark/paperMarksSubmit', $attributes);
                if (isset($students) && is_array($students)) { 
                  ?>
                <h2 class="text-center">Enter Marks for <?php echo $branch; ?> <?php echo urldecode($pclass_name); ?> - <?php echo $subject_name; ?> Date: <?php echo $date; ?></h2>
                <input type="hidden" class="form-control" value="<?php echo $pclass_id; ?>" name="selectclassid">
                <input type="hidden" class="form-control" value="<?php echo $pclass_name; ?>" name="selectclassname">  
                <input type="hidden" class="form-control" value="<?php echo $subject_id; ?>" name="selectsubjectid">
                <input type="hidden" class="form-control" value="<?php echo $subject_name; ?>" name="selectsubjectname">

                  <input type="hidden" class="form-control" value="<?php echo $date; ?>" name="date">
                  <input type="hidden" class="form-control" value="<?php echo $branch; ?>" name="branch">


                <input class="btn btn-primary btn-block mb-2" type="submit" name="btnsubmit" value="Submit" onclick="changeButtonText()" id="submitBtn">
                   <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Student ID</th>
                            <th scope="col">Student Name</th>
                            <th scope="col">Paper Type</th>
                            <th scope="col">Part 01</th>
                            <th scope="col">Part 02</th>
                            <th scope="col">Total</th>
                             <th scope="col">Grade</th>

                            <th scope="col">Paper Link</th>
                             
                            

                        </tr>
                    </thead>
                    <tbody>
                      <?php
                      
                        $i = 1;
                        foreach ($students as $student) {
                            // check if student marks already exists
                            $marks = $student->marks;
                            $part1 = null;
                            $part2 = null;
                            $total = null;
                            $papertype = null;
                            $paperlink = null;
                           
                            if (isset($marks) && is_array($marks)) {
                                // if marks exists then set the values to the input fields
                             
                                foreach ($marks as $mark) {
                                  if($mark->paper_date == $date){
                                    $part1 = $mark->part1;
                                    $part2 = $mark->part2;
                                    $total = $mark->total;
                                    $papertype = $mark->paper_type;
                                    $paperlink = $mark->link;
                                }
                            }
                          }
                           echo "<tr>";
                            echo "<td>" . $i . "</td>";
                            echo "<td>" . $student->admission_number . "</td>";
                            echo "<td>" . $student->name . "</td>";
                            echo "<td>";
                            echo "<input  type='text' class='form-control' name='papertype_{$student->ID}' value='{$papertype}' >";
                            ?>


                          <!-- Button trigger modal -->
<button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#prevMarksModal<?php echo $student->ID; ?>">
  Previous Marks
</button>

<!-- Modal -->
<div class="modal fade" id="prevMarksModal<?php echo $student->ID; ?>" tabindex="-1"  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" ><?php echo $student->name; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Paper Date</th>
              <th scope="col">Paper Type</th>
              <th scope="col">Part 01</th>
              <th scope="col">Part 02</th>
              <th scope="col">Total</th>
              <th scope="col">Paper Link</th>


            </tr>
          </thead>
          <tbody>
            <?php
            if (isset($marks) && is_array($marks)) {
                $j = 1;
                foreach ($marks as $mark) {
                    echo "<tr>";
                    echo "<td>" . $j . "</td>";
                    echo "<td>" . $mark->paper_date . "</td>";
                    echo "<td>" . $mark->paper_type . "</td>";
                    echo "<td>" . $mark->part1 . "</td>";
                    echo "<td>" . $mark->part2 . "</td>";
                    echo "<td>" . $mark->total . "</td>";
                    echo "<td>";
                     if(!empty($mark->link)) { ?>
                                <a href="#" onclick="openSmallWindow(`<?php echo $mark->link; ?>`); return false;">Open</a>
                           <?php } 
                    echo "</td>";
                    echo "</tr>";
                    $j++;
                }
            } else {
                echo "<tr><td colspan='7'>No previous marks found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

      </div>
    </div>
  </div>
</div>



                            <?php
                            echo "</td>";
                            echo "<td>";
                            echo "<input type='number' class='input1 form-control' name='part1_{$student->ID}'  min='0' max='100' value='{$part1}' >";
                            echo "</td>";
                            echo "<td>";
                            echo "<input type='number' class='input2 form-control' name='part2_{$student->ID}' min='0' max='100' value='{$part2}' >";
                            echo "</td>";
                            echo "<td>";
                            echo "<input type='number' class='totalTrigger form-control' name='total_{$student->ID}' min='0' max='100' value='{$total}'>";
                            echo "</td>";
                            echo "<td>";
                           if(isset($total) && $total != null){
                                if($total >= 75){
                                    echo "<p class='gradeVal'>A</p>";
                                }else if($total >= 65){
                                    echo "<p class='gradeVal'>B</p>";
                                }else if($total >= 55){
                                    echo "<p class='gradeVal'>C</p>";
                                }else if($total >= 40){
                                    echo "<p class='gradeVal'>S</p>";
                                }else if($total > 0){
                                    echo "<p class='gradeVal'>F</p>";
                                }else{
                                    echo "<p class='gradeVal'>N/A</p>";
                                }
                           }else{
                           
                            echo "<p class='gradeVal'></p>";
                          }
                            echo "</td>";
                            echo "<td>";
                            echo "<input type='text' class='form-control' name='paperlink_{$student->ID}' placeholder='Enter Paper Link' value='{$paperlink}' >";
                            if(!empty($paperlink)) { ?>
                                <a href="#" onclick="openSmallWindow(`<?php echo $paperlink; ?>`); return false;">Open</a>
                           <?php } 
                            echo "</td>";
                            echo "</tr>";
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