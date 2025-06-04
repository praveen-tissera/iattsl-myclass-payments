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
    input[type=text], input[type=number], input[type=email], input[type=password], input[type=url],[type=date], select, option, .from-control {
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
    input[type=text], input[type=number], input[type=email], input[type=password], input[type=url], select, option {
      border: 1px solid #ccc;
      text-align: left;
      padding: 5px;
    }
    /* change input field border radius */
    input[type=text], input[type=number], input[type=email], input[type=password],input[type=url], select, option {
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
     <li class="nav-item ">
      <a class="nav-link" target="_blank" href="<?php echo base_url(); ?>index.php/welcome/income">Income Summary <span class="sr-only">(current)</span></a>
     </li>
     <li class="nav-item active">
      <a class="nav-link"  href="<?php echo base_url(); ?>index.php/mark/">Enter Marks <span class="sr-only">(current)</span></a>
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
                <h1 class="text-center display-4" style="font-size:3rem">Submit Marks</h1>
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

                
                <table class="table table-borderless">
                    <tr>
                        <td>
                        <?php
                         date_default_timezone_set('Asia/Colombo');
                            $currentDate = date("Y-m-d"); // Format: YYYY-MM-DD
                             
                        ?>
                        <div class="form-group">
                          <label for="selectclass">Selected Class</label>
                          <input type="hidden" class="form-control" value="<?php echo $class_id; ?>" name="selectclassid">
                          <input type="text" class="form-control" id="selectclass" readonly value="<?php echo $class_name; ?>" name="selectclass">
                        </div>
                        <div class="form-group">
                          <label for="selectsubject">Selected Subject</label>
                          <input type="hidden" class="form-control" value="<?php echo $subject_id; ?>" name="selectsubjectid">
                          <input type="text" class="form-control" id="selectsubject" readonly value="<?php echo $subject_name; ?>" name="selectsubject">
                        </div>

                         

                        </td>
                    </tr>

                 
                </table>
                
                
            </div>

        </div>
        <div class="row">
            <div class="col">
              
              <?php 
              $attributes = array('id' => 'studentMarkList');
              echo form_open('mark/studentsSubmit', $attributes) ?>
              
              <input class="btn btn-primary btn-block" type="submit" name="btnsubmit" value="Submit" onclick="changeButtonText()" id="submitBtn">
                <input type="hidden" class="form-control" value="<?php echo $class_id; ?>" name="selectclassid">
                <input type="hidden" class="form-control" value="<?php echo $class_name; ?>" name="selectclass">  
                <input type="hidden" class="form-control" value="<?php echo $subject_id; ?>" name="selectsubjectid">
                <input type="hidden" class="form-control" value="<?php echo $subject_name; ?>" name="selectsubject">
                
              <br>
              <div class="row">
              <?php 
              $termtest_date = false;
              $practicaltest_date = false;
              date_default_timezone_set('Asia/Colombo');
              $currentDate = date("Y-m-d");
                      

              
              ?>
              </div>
              <br>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Student ID</th>
                            <th scope="col">Student Name</th>
                            <th scope="col">Status</th>
                            <th scope="col">Part 01</th>
                            <th scope="col">Part 02</th>
                            <th scope="col">Total</th>
                             <th scope="col">Grade</th>
                              <th scope="col">Practical Test Grade</th>
                              <th scope="col">Written Exam Date</th>
                              <th scope="col">Practical Exam Date</th>
                            <th scope="col">Paper Link</th>
                             
                            

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                      
                        $i = 1;
                        $termtest_date = false;
                        foreach ($students as $student) {
                            echo "<tr>";
                            echo "<td>" . $i . "</td>";
                            echo "<td>" . $student->admission_number . "</td>";
                            echo "<td>" . $student->name . "</td>";
                            if ($student->is_active == 1) {
                                echo "<td><span class='dot-active'></span></td>";
                            } else {
                                echo "<td><span class='dot-inactive'></span></td>";
                            }
                            echo "<td>";
                            if(isset($student->marks) && $student->marks != null){
                                echo "<input  type='number' class='input1 form-control' name='part1_{$student->ID}'  min='0' max='100' value='{$student->marks->part1}'>";
                            }else{
                            echo "<input  type='number' class='input1 form-control' name='part1_{$student->ID}'  min='0' max='100'>";
                            }
                            echo "</td>";
                            echo "<td>";
                            if(isset($student->marks) && $student->marks != null){
                                echo "<input  type='number' class=' input2 form-control' name='part2_{$student->ID}'  min='0' max='100' value='{$student->marks->part2}'>";
                            }else{
                            echo "<input type='number' class=' input2 form-control' name='part2_{$student->ID}'  min='0' max='100'>";
                            }
                            echo "</td>";
                            echo "<td>";
                            if(isset($student->marks) && $student->marks != null){
                                echo "<input  type='number' class='totalTrigger form-control' name='total_{$student->ID}'  min='0' max='100' value='{$student->marks->total}'>";
                            }else{
                            echo "<input  type='number' class='totalTrigger form-control' name='total_{$student->ID}'  min='0' max='100' >";
                            }
                            echo "</td>";
                            echo "<td>";
                           if(isset($student->marks) && $student->marks != null){
                                if($student->marks->total >= 75){
                                    echo "<p class='gradeVal'>A</p>";
                                }else if($student->marks->total >= 65){
                                    echo "<p class='gradeVal'>B</p>";
                                }else if($student->marks->total >= 55){
                                    echo "<p class='gradeVal'>C</p>";
                                }else if($student->marks->total >= 40){
                                    echo "<p class='gradeVal'>S</p>";
                                }else if($student->marks->total > 0){
                                    echo "<p class='gradeVal'>F</p>";
                                }else{
                                    echo "<p class='gradeVal'>N/A</p>";
                                }
                           }else{
                           
                            echo "<p class='gradeVal'></p>";
                          }
                            echo "</td>";
                            echo "<td>";
                         
                            // create a select dropdown for practical test grade
                            echo "<select class='form-control' name='practical_test_{$student->ID}'>";
                            echo "<option value=''>Select Grade</option>";
                             echo "<option value='AB' " . (isset($student->marks) && $student->marks->practical_test == 'AB' ? 'selected' : '') . ">AB</option>";
                            echo "<option value='A' " . (isset($student->marks) && $student->marks->practical_test == 'A' ? 'selected' : '') . ">A</option>";
                            echo "<option value='B' " . (isset($student->marks) && $student->marks->practical_test == 'B' ? 'selected' : '') . ">B</option>";
                            echo "<option value='C' " . (isset($student->marks) && $student->marks->practical_test == 'C' ? 'selected' : '') . ">C</option>";
                            echo "<option value='S' " . (isset($student->marks) && $student->marks->practical_test == 'S' ? 'selected' : '') . ">S</option>";
                            echo "<option value='F' " . (isset($student->marks) && $student->marks->practical_test == 'W' ? 'selected' : '') . ">W</option>";
                            echo "</select>";
                          
                            echo "</td>";
                            echo "<td>";
                          
                            // show term_test_date date if exists or current date
                            if(isset($student->marks) && $student->marks != null && $student->marks->term_test_date != null){
                                echo "<input type='date' class='form-control' name='term_test_date_{$student->ID}' value='{$student->marks->term_test_date}'>";
                            }else{
                                echo "<input type='date' class='form-control' name='term_test_date_{$student->ID}' value='{$currentDate}'>";
                            }

                            echo "</td>";

                            echo "<td>";
                            // show practical_test_date date if exists or current date  
                            if(isset($student->marks) && $student->marks != null && $student->marks->practical_test_date != null){
                                echo "<input type='date' class='form-control' name='practical_test_date_{$student->ID}' value='{$student->marks->practical_test_date}'>";
                            }else{
                                echo "<input type='date' class='form-control' name='practical_test_date_{$student->ID}' value='{$currentDate}'>";
                            }
                            echo "</td>";
                             echo "<td>";
                            if(isset($student->marks) && $student->marks != null){
                                echo "<input type='url'  class='form-control' name='link_{$student->ID}'  min='0' max='100' value='{$student->marks->link}'>";
                              ?>
                              <?php if($student->marks->link != null){ ?>
                              <a href="#" onclick="openSmallWindow(`<?php echo $student->marks->link; ?>`); return false;">Open</a>
                              <?php } ?>
                              <?php
                            }else{
                            echo "<input type='url' class='form-control' name='link_{$student->ID}'  min='0' max='100' >";
                            }
                            echo "</td>";
                            echo "</tr>";
                            
                            $i++;
                        }
                        ?>
                    </tbody>
                </table>
              <?php echo form_close(); ?>
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