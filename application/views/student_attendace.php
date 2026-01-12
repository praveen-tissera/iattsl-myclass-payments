<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url() . '/css/bootstrap.min.css' ?>">

   
    <title>Attendace Summary</title>
     <style>
      /* download icon styling */
      
      .nav-header {
        display: flex;
        justify-content: end;
        margin-bottom: 10px;
      }



    </style>
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

       /* copy admission num */
    .admission-row {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
    }
    .copy-icon {
      margin-left: 8px;
      cursor: pointer;
      color: blue;
    }
  </style>

</head>

<body>
 <!-- php include menu_admin.php file -->
    <?php 

       // check session user_role and include menu_admin.php
    if($this->session->userdata('user_role') == 'administrator'){
         $this->load->view('includesui/menu_admin');
    }elseif($this->session->userdata('user_role') == 'teacher'){
       
        $this->load->view('includesui/menu_teacher');
    }elseif($this->session->userdata('user_role') == 'cordinator'){
       
        $this->load->view('includesui/menu_cordinator');
    }
    
    
    ?>
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
                <h1 class="text-center display-4" style="font-size:2.6rem">Student Attendace Summary</h1>
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

                <?php echo form_open('welcome/gradewiseattendaceSumamry') ?>
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
                              <option value="DIY">Diyagama</option>
                            </select>
                        </td>
                        <td> 
                          <label for="branch">Select Academic Year</label>
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
                        <td colspan="2">
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
                         date_default_timezone_set('Asia/Colombo');
                            $currentDate = date("Y-m-d"); // Format: YYYY-MM-DD
                        ?>
                <?php
                // pass form clicked submit button value to confirm submit function

                $attributes = array('id' => 'studentMarkList');
               
                echo form_open('welcome/attendacesubmit', $attributes); 
                if (isset($students) && is_array($students)) { 
                  ?>
                <h2 class="text-center">Payment Summary for  <?php echo $branch; ?> <?php echo urldecode($pclass_name); ?> - <?php echo $subject_name; ?></h2>
                <input type="hidden" class="form-control" value="<?php echo $pclass_id; ?>" name="selectclassid">
                <input type="hidden" class="form-control" value="<?php echo $pclass_name; ?>" name="selectclassname">  
                <input type="hidden" class="form-control" value="<?php echo $subject_id; ?>" name="selectsubjectid">
                <input type="hidden" class="form-control" value="<?php echo $subject_name; ?>" name="selectsubjectname">


                  <input type="hidden" class="form-control" value="<?php echo $branch; ?>" name="branch">
                  <input type="hidden" name="academicyear" value="<?php echo $selected_academic_year; ?>">
                  <input type="hidden" name="btnsubmit" id="btnsubmit">
<!-- 
                <input class="btn btn-primary btn-block mb-2" type="submit" name="btnsubmit" value="Submit" onclick="changeButtonText()" id="submitBtn"> -->
                <?php
                // current month in Jan, Feb, Mar format
               
                  $currentMonth = date("m");
                  $monthNum = intval($currentMonth);
                  $dateObj   = DateTime::createFromFormat('!m', $monthNum);
                  $currentMonth = $dateObj->format('M');
             
                ?>

                <?php
                    
                ?>
                <div class="nav-header">
                  
                <button class="download-btn btn btn-outline-success btn-sm" onclick="downloadStudentInfo()"> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/> </svg>
                     Student Info</button>

                </div>
                  <div class="form-row py-2 bg-light">
                    <div class="col-sm-12 my-2 my-md-0 my-sm-2 col-md-4">
                      <!-- show only current month in the calender  -->
                      
                      <input type="date" id="calendar" class="form-control mb-2" name="attendancedate" value="<?php echo $currentDate; ?>">
                      <small id="datemsg" class="form-text text-muted mt-0 mb-3">Select class date to add new attendance</small>

                    </div>
                    <div class="col-sm-12 col-md-2">
                      <!-- create submit button -->
                    <!-- <input class="btn btn-primary btn-block mb-2" type="submit" id="submitBtn" name="btnsubmit" value="Add New Attendace" onclick="confirmSubmit(event,this.value)"> -->
                   
                    <button  class=" btn btn-primary btn-block  btn-sm  my-md-0" id="submitBtn" type="submit" onclick="confirmSubmit(event, 'Add New Attendance')" value="Add New Attendance">Add New Attendance</button>

                    </div>
                    <div class="col col-sm-12 col-md-2">
                    <!-- <input class="btn btn-secondary btn-block mb-2" id="updateBtn" type="submit" name="btnsubmit" value="Update Old Attendace" onclick="confirmSubmit(event,this.value)"> -->
                    <button class="btn btn-secondary btn-block btn-sm mb-2" id="updateBtn" type="submit" onclick="confirmSubmit(event, 'Update Old Attendance')" value="Update Old Attendance">Update Old Attendance</button>

                   </div>
                   <div class="col-sm-12 col-md-4 text-md-right text-sm-center">
                    <?php 
                    $staff_name = '';
                    $last_added_date = '';
                    foreach ($students as $student) {
                            // check if student marks already exists
                              $attendances = $student->attendance_history;
                            if (isset($attendances) && is_array($attendances)) {
                              
                                foreach ($attendances as $attendance) {
                                  // loop through attendance and get the last added staff name and date
                                  foreach($attendance as $att){
                                    
                                    if($last_added_date == '' || strtotime($att->created_at) > strtotime($last_added_date)){
                                      $last_added_date = $att->created_at;
                                      $staff_name = $att->staff_name;
                                    }
                                  }
                                  // // style staff name bold and put nice label before staff name
                                  
                                  //   break;
                                    
                                }
                                 
                            }
                              
                              // break;
                    }

                      echo '<span class="badge badge-danger" style="font-weight: bold;"> Last Update By: ' . $staff_name . '</span>';
                                    echo '<br>';
                                    echo '<span class="badge badge-secondary" style="font-weight: bold;"> Last Update Date: ';
                                    // get date and time on Y-m-d format and H:i:s format am pm

                                    $last_added_date = date("Y-m-d h:i:s A", strtotime($last_added_date));
                                    echo $last_added_date;
                                    echo '</span>';
                                   
                  
                  ?>
                   </div>
                </div>
                    <table class="table table-striped table-bordered table-hover" style="table-layout: fixed;">
                    <thead>
                      
                    
                <colgroup>
                  <col style="width: 27px;">           <!-- # -->
                  <col style="width: 75px;">           <!-- ID -->
                  <col style="width: 110px;">          <!-- Student Name (sticky) -->
                  <!-- 12 months (equal widths) -->
                  <col span="12" style="width: 111px;"> <!-- JAN..DEC -->
                </colgroup>
                <thead class="table-primary">

                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID</th>
                            <th scope="col" style="position: sticky; left: 0; background: #f2f2f2; z-index: 1;">Student Name</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'Jan') ?  'bg-warning fix-col' : ''; ?>">JAN</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'Feb') ?  'bg-warning fix-col' : ''; ?>" >FEB</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'Mar') ?  'bg-warning fix-col' : ''; ?>">MAR</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'Apr') ?  'bg-warning fix-col' : ''; ?>">APR</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'May') ?  'bg-warning fix-col' : ''; ?>">MAY</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'Jun') ?  'bg-warning fix-col' : ''; ?>">JUN</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'Jul') ?  'bg-warning fix-col' : ''; ?>">JUL</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'Aug') ?  'bg-warning fix-col' : ''; ?>">AUG</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'Sep') ?  'bg-warning fix-col' : ''; ?>">SEP</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'Oct') ?  'bg-warning fix-col' : ''; ?>">OCT</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'Nov') ?  'bg-warning fix-col' : ''; ?>">NOV</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'Dec') ?  'bg-warning fix-col' : ''; ?>">DEC</th>
                        
                            

                        </tr>
                      
                 </thead>       
                     
                    </thead>
                    <tbody>
                      <?php
                      // print_r($students);
                        $i = 1;
                        $months = [
                                      "Jan", "Feb", "Mar", "Apr", "May", "Jun",
                                      "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                                  ];

                           $installments = [
                                      "Installment 1", "Installment 2", "Installment 3", "Installment 4", "Installment 5", "Installment 6",
                                      "Installment 7", "Installment 8", "Installment 9", "Installment 10", "Installment 11", "Installment 12","Installment 13", "Installment 14"
                                  ];


                        foreach ($students as $student) {
                            // check if student marks already exists
                            $attendances = $student->attendance_history;
                            $part1 = null;
                            $part2 = null;
                            $total = null;
                            $papertype = null;
                            $paperlink = null;
                           
                           
                           echo "<tr>";
                            echo "<td >" . $i . "</td>";
                            echo "<td>";
                            echo "<input type='hidden' name='student_id[]' value='" . $student->ID . "'>";
                            echo "<span class='admission'>" .$student->admission_number. "</span>";
                            echo "<span class='copy-icon' onclick='copyCode(this)'>📋</span>";
                            echo "</td>";
                            echo "<td style='position: sticky; left: 0; background: #f2f2f2; z-index: 1;'>";
                             // if is_active is 1 show green dot else red dot
                                      if($student->is_active == 1){
                                        echo " <span class='dot-active' title='Active'></span>";    
                                      }else{
                                        echo " <span class='dot-inactive' title='Inactive'></span>";
                                      }
                                      echo " <span class='align-top'>" . $student->name . "</span>";
                                      
                            // echo "<span class='copy-icon' title='Copy Email' value='$student->email' onclick='copyEmail(this)'>Email📋</span>";
                            echo "</td>";



                            
                            if (isset($attendances) && is_array($attendances)) {

                               

                               
                                foreach ($months as $month) {
                                    $found = false;
                                    foreach ($attendances as $key =>  $attendace) {
                                        if ($key == $month) {
                                            echo "<td>";
                                            // echo $payment->label;
                                            // echo "<br>";
                                           
                                            // print_r($attendace);
                                            // if currentmonth is equal to month set checkbox to checked 
                                            if($month == $currentMonth){
                                                foreach($attendace as $date => $status){
                                                  
                                                  if($status->attendace == 'P'){
                                                    echo "<div class='form-group form-check'>";

                                                    echo "<input class='form-check-input' type='checkbox' checked id='old_attendace_".$student->ID."_".$status->class_date."' value='P' name='old_attendace_".$student->ID."_".$status->class_date."'>";

                                                    echo '<label for="old_attendace_'.$student->ID.'_'.$status->class_date.'" class="form-check-label  badge badge-success">'.$status->class_date."</label>";
                                                    
                                                    echo "</div>";


                                                    // echo "<span class='mx-1 dot-active' title='Present'></span> &nbsp;<br> ";
                                                  }else if($status->attendace == 'AB'){
                                                     echo "<div class='form-group form-check'>";

                                                    echo "<input class='form-check-input' type='checkbox' id='old_attendace_".$student->ID."_".$status->class_date."' name='old_attendace_".$student->ID."_".$status->class_date."' >";

                                                    echo '<label for="old_attendace_'.$student->ID.'_'.$status->class_date.'" class="form-check-label  badge badge-danger">'.$status->class_date."</label>";
                                                    // echo "<span class='mx-1 dot-inactive'  title='Absent'></span> &nbsp;<br> ";

                                                    echo "</div>";
                                                  }
                                                
                                                }
                                              echo "<hr> <input type='checkbox' id='new_attendace_".$student->ID."' value='P' name='new_attendace_".$student->ID."'>";
                                              
                                              echo "<label class='form-check-label mx-1' for='new_attendace_".$student->ID."'>Present(New)</label>";
                                            }else{
                                              foreach($attendace as $date => $status){
                                              echo $status->class_date.": ";
                                              if($status->attendace == 'P'){
                                                echo "<span class='mx-1 dot-active' title='Present(New)'></span> &nbsp;<br> ";
                                              }else if($status->attendace == 'AB'){
                                                echo "<span class='mx-1 dot-inactive' title='Absent'></span> &nbsp;<br> ";
                                              }
                                            }
                                            
                                            }
                                            
                                            echo "</td>";
                                            $found = true;
                                            // break; // Exit the inner loop once a match is found
                                        }
                                      
                                    }
                                  
                                    if (!$found) {
                                        echo "<td>";
                                          // echo "<input type='checkbox' value='P' id='new_attendace_".$student->ID."' name='new_attendace_".$student->ID."'>";
                                          // echo "<label class='form-check-label mx-1' for='new_attendace_".$student->ID."'>Present(New)</label>";
                                           echo "Yet to be enable";
                                        echo "</td>";
                                        $found = false;
                                    }
                                }
                               
                                
                        
                            }else{
                                // if no payments found for student display empty cells for each month
                                foreach ($months as $month) {
                                  if($month == $currentMonth){
                                    echo "<td>";
                                      echo "<input type='checkbox' value='P' id='new_attendace_".$student->ID."' name='new_attendace_".$student->ID."'>";
                                      echo "<label class='form-check-label mx-1' for='new_attendace_".$student->ID."'>Present(New)</label>";
                                    echo "</td>";
                                  } else {
                                    echo "<td>";
                                      echo "Yet to be enable";
                                    echo "</td>";
                                  }
                                }
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
    function copyCode(icon) {
      const fullText = icon.previousElementSibling.textContent;
      const code = fullText.split('/')[1]; // Extracts 24-011
      navigator.clipboard.writeText(code).then(() => {
        icon.textContent = "✅"; // Visual feedback
        setTimeout(() => icon.textContent = "📋", 1000);
      });
    }

     function copyEmail(icon) {
      // retrive value attribute
      const iconValue = icon.getAttribute('value');
    
      
      navigator.clipboard.writeText(iconValue).then(() => {
        icon.textContent = "✅"; // Visual feedback
        setTimeout(() => icon.textContent = "Email📋", 1000);
      });
    }
  </script>
<script>
function openSmallWindow(url) {
    window.open(url, '_blank', 'width=700,height=800,resizable=yes,scrollbars=yes');
}

// function changeButtonText() {
//     var btn = document.getElementById("submitBtn");
//     btn.value = "Processing...";
//     btn.disabled = true; // Optional: Disable button to prevent multiple clicks
//     document.getElementById("studentMarkList").submit();
// }

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

    <script>
function downloadStudentInfo() {
    const rows = document.querySelectorAll("table tr");
    let csvContent = "Admission Number,Name,Email\n";

    rows.forEach(row => {
        const admissionSpan = row.querySelector(".admission");
        const nameCell = row.cells[2];
        const emailSpan = nameCell ? nameCell.querySelector(".copy-icon[title='Copy Email']") : null;

        if (admissionSpan && nameCell && emailSpan) {
            const admissionNumber = admissionSpan.textContent.trim();
            const name = nameCell.childNodes[0].textContent.trim();
            const email = emailSpan.getAttribute("value").trim();

            csvContent += `"${admissionNumber}","${name}","${email}"\n`;
        }
    });

    // Create a downloadable link
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "student_info.csv";
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}





</script>

<script>
  // calender control
    // Get today's date
    const today = new Date();

    // Calculate first and last day of the current month
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    // Set min and max attributes on the input
    const calendar = document.getElementById('calendar');
    calendar.min = formatDate(firstDay);
   Date(lastDay);


 // Disable future dates — set max to today
  calendar.max = formatDate(today);

  // (Optional) Set a default value within the allowed range
  const todayStr = formatDate(today);
  calendar.value = (todayStr >= calendar.min && todayStr <= calendar.max)
    ? todayStr
    : calendar.min;

</script>
<script>
  function confirmSubmit(e,action) {

    
    // Always stop the default submit first
      if (e && typeof e.preventDefault === 'function') {
        e.preventDefault();
      }

    // get element by name attribute submitBtn

    var btnUpdate = document.getElementById("updateBtn");
    var btnSubmit = document.getElementById("submitBtn");
    
// Decide message and target form based on action
  let message = '';
  let formEl = null;
  let btnEl = null;

  if (action === 'Add New Attendance') {
    message = 'Are you sure you want to add new attendance?';
    formEl = document.getElementById('studentMarkList'); // FORM, not button
    document.getElementById('btnsubmit').value = action;
    btnEl = btnSubmit;
  } else if (action === 'Update Old Attendance') {
    message = 'Are you sure you want to update old attendance?';
    formEl = document.getElementById('studentMarkList'); // FORM, not button
    document.getElementById('btnsubmit').value = action;
    btnEl = btnUpdate;
  } else {
    // Unknown action; do nothing
    return false;
  }

  // Show confirmation
  const ok = window.confirm(message);

  if (ok) {
    // Disable only after confirmation
    if (btnEl) {
      btnEl.disabled = true;
      btnEl.value = 'Processing...'; // for <input type="submit">; ignored on <button>
      btnEl.textContent = 'Processing...'; // for <button>
    }
    // Submit the corresponding FORM
    if (formEl && typeof formEl.submit === 'function') {
      formEl.submit();
    }
    return true;
  } else {
    // User cancelled: ensure buttons remain enabled
    if (btnSubmit) btnSubmit.disabled = false;
    if (btnUpdate) btnUpdate.disabled = false;
    return false;
  }

  }
</script>
</html>