<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url() . '/css/bootstrap.min.css' ?>">
    <link rel="stylesheet" href="<?php echo base_url() . '/css/institute-inner.css' ?>">

   
    <title>Tute Distribution</title>
     <style>
      /* download icon styling */
      
      .nav-header {
        display: flex;
        justify-content: space-between;
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

<body class="institute-inner">
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
                <h1 class="text-center display-4" style="font-size:2.6rem">Tute Distribution Summary</h1>
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

                <?php echo form_open('welcome/tute_distribution_summary') ?>
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
                            
                              // select subject if previously selected
                              if (!empty($pclass_id) && $pclass_id == $class->ID) {
                                echo "<option value='$class_id' selected>$class_name</option>";
                              } else {
                                echo "<option value='$class_id'>$class_name</option>";
                              }
                              
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
                              <!-- selected branch -->
                              <option value="HED" <?php echo (isset($branch) && $branch == 'HED') ? 'selected' : ''; ?>>Head Office</option>
                              <option value="BAT" <?php echo (isset($branch) && $branch == 'BAT') ? 'selected' : ''; ?>>Battaramulla</option>
                              <option value="PEL" <?php echo (isset($branch) && $branch == 'PEL') ? 'selected' : ''; ?>>Pellawatta</option>
                              <option value="HRI" <?php echo (isset($branch) && $branch == 'HRI') ? 'selected' : ''; ?>>Hripitiya</option>
                              <option value="MAH" <?php echo (isset($branch) && $branch == 'MAH') ? 'selected' : ''; ?>>Maharagama</option>
                              <option value="MAT" <?php echo (isset($branch) && $branch == 'MAT') ? 'selected' : ''; ?>>Mattegoda</option>
                              <option value="DIY" <?php echo (isset($branch) && $branch == 'DIY') ? 'selected' : ''; ?>>Diyagama</option>
                            </select>
                        </td>
                        <td> 
                          <div class="form-group">
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
                          </div>
                        </td>
                        <td colspan="2">
                          <div class="form-group">
                          <br>
                            <input class="btn btn-danger btn-block mt-2" type="submit" name="submit" value="SEARCH">
                          </div>
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
<!-- nav header -->
<div class="nav-header">
  <nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
       <?php 

       if(isset($students) && is_array($students)){
          // loop through students array and get index name 
          $active = 'active';
          foreach ($students as $subjectName => $subjectsdata) {
           // print subject name as tab
           echo "<button class='nav-link {$active}' id='nav-{$subjectName}-tab' data-toggle='tab' data-target='#nav-{$subjectName}' type='button' role='tab' >{$subjectName}</button>";
                        $active = '';
          }
       }
        ?>
                    
    </div>
  </nav>
   <button class="download-btn btn btn-outline-success btn-sm" onclick="downloadStudentInfo()"> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/> </svg>
                     Student Info</button>
</div>

<!-- end of nav header -->


                
<!-- Tab Content start -->
  <?php
    if (isset($students) && is_array($students)) {
      ?>

        <div class="tab-content" id="nav-tabContent">
              <?php 
              // loop through students array and get index name 
              $paneactive = 'show active';
              foreach ($students as $subjectName => $subjectsdata) {
                echo "<div class='tab-pane fade {$paneactive}' id='nav-{$subjectName}' role='tabpanel' aria-labelledby='nav-{$subjectName}-tab'>";
                $paneactive = '';
              ?>
                <?php
                // pass form clicked submit button value to confirm submit function

                $attributes = array(
                    'class' => 'tute-distribution-form',
                    'data-add-url' => site_url('welcome/tute_distribution_add_ajax'),
                    'data-delete-url' => site_url('welcome/tute_distribution_delete_ajax')
                );
               
                echo form_open('welcome/tute_distribution_summary', $attributes);
                // print_r($subjectsdata);
                // if (isset($subjectsdata) && is_array($subjectsdata)) { 
                if (true) {
                  ?>
              
                <?php 
                // echo $subject_name; 
                ?></h2>


              <?php

                if (isset($students[$subjectName]) && is_array($students[$subjectName])) {
                // check array lenght
                // print_r($students[$subjectName]);
                $arrayLength = count($students[$subjectName]);
                // create loop to get is_active status count
                $activeCount = 0;
                $inactiveCount = 0;
               
                  for ($i = 0; $i < $arrayLength; $i++) {
                      if ($students[$subjectName][$i]->is_active == 1) {
                          $activeCount++;
                      } else {
                          $inactiveCount++;
                      }
                  }
                
              ?>
      <div class="row justify-content-md-center"> 
            <div class="col-4 col-md-3 px-1">
                  <div class="card text-white bg-primary mb-3" >
                    <div class="card-header">Registration </div>
                      <div class="card-body py-1">
                        <h3 class="card-title"><?php 
                        echo $arrayLength; ?>
                        </h3>
                        <!-- <p class="card-text">Register Student Count.</p> -->
                      </div>
                    </div>
                  
            </div> 

                <div class="col-4 col-md-3 px-1 ">
                  <div class="card text-white bg-primary mb-3" >
                    <div class="card-header">Dropouts </div>
                      <div class="card-body py-1">
                        <h3 class="card-title"><?php 
                        echo $inactiveCount; 
                        ?></h3>
                        <!-- <p class="card-text">Inactive Student Count.</p> -->
                      
                  </div>
                </div> 
                </div>





                <div class="col-4 col-md-3 px-1">
                  <div class="card text-white bg-danger mb-3" >
                    <div class="card-header"> Percentage </div>
                      <div class="card-body py-1">
                        <h3 class="card-title"><?php 
                        echo $arrayLength > 0 ? round(($inactiveCount / $arrayLength) * 100, 2) : 0; 
                        ?>%</h3>
                        <!-- <p class="card-text">Dropout Percentage.</p> -->
                      
                  </div>
                </div> 
                </div>


               



    </div>

<?php } ?>


<div> 
      

      


                <input type="hidden" class="form-control" value="<?php echo $pclass_id; ?>" name="selectclassid">
                <input type="hidden" class="form-control" value="<?php echo $pclass_name; ?>" name="selectclassname">  
                <input type="hidden" class="form-control" value="<?php echo $subject_id[$subjectName]; ?>" name="selectsubjectid">
                <input type="hidden" class="form-control" value="<?php echo $subject_name[$subjectName]; ?>" name="selectsubjectname">
                <input type="hidden" value="<?php echo (int) $pclass_id; ?>" name="class_id">
                <input type="hidden" value="<?php echo (int) $subject_id[$subjectName]; ?>" name="subject_id">
                <input type="hidden" value="<?php echo html_escape($branch); ?>" name="branch">
                <input type="hidden" value="<?php echo (int) $selected_academic_year; ?>" name="session_id">
                <input type="hidden" name="academicyear" value="<?php echo (int) $selected_academic_year; ?>">
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
                
                  <div class="form-row py-2 bg-light">
                    <div class="col-sm-12 my-2 my-md-0 my-sm-2 col-md-4">
                      <!-- show only current month in the calender  -->
                      
                      <label for="calendar-<?php echo (int) $subject_id[$subjectName]; ?>">Distribution date</label>
                      <input type="date" id="calendar-<?php echo (int) $subject_id[$subjectName]; ?>"
                             class="form-control mb-2 distribution-date" name="attendancedate"
                             min="<?php echo date('Y-m-01'); ?>" max="<?php echo html_escape($currentDate); ?>"
                             value="<?php echo html_escape($currentDate); ?>">
                      <small class="form-text text-muted">Choose a date in this month. Use the tute selector in each student's row to assign immediately.</small>
                    </div>
                   <div class="col-sm-12 col-md-8">
                    <?php 
                    $staff_name = '';
                    $last_added_date = '';
                    if (isset($subjectsdata) && is_array($subjectsdata)) {
                        foreach ($subjectsdata as $student) {
                                // check if student marks already exists
                                  $attendances = $student->attendance_history;
                                if (isset($attendances) && is_array($attendances)) {
                                  
                                    foreach ($attendances as $attendance) {
                                      // loop through attendance and get the last added staff name and date
                                      foreach($attendance as $att){
                                        
                                        if($last_added_date == '' || strtotime($att->created_at) > strtotime($last_added_date)){
                                          $last_added_date = $att->created_at;
                                          // check if staff name is exists
                             
                                          if(isset($att->staff_name) && !empty($att->staff_name)){
                                           
                                            $staff_name = $att->staff_name;
                                          }else{  
                                            $staff_name = 'Unknown';
                                          }
                                        }
                                      }
                                      // // style staff name bold and put nice label before staff name
                                      
                                      //   break;
                                        
                                    }
                                    
                                }
                                  
                                  // break;
                        }
                    }

                      echo '<span class="badge badge-danger" style="font-weight: bold;"> Last Update By: ' . html_escape($staff_name) . '</span>';
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
                            <th scope="col" style="position: sticky; left: 0; background: #ff3939; z-index: 1;">Student Name</th>
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

                    if (isset($subjectsdata) && is_array($subjectsdata)) {
                        foreach ($subjectsdata as $student) {
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

                            $current_month_records = isset($attendances[$currentMonth]) && is_array($attendances[$currentMonth])
                                ? $attendances[$currentMonth]
                                : array();
                            $student_tutes = isset($tutes[$subjectName]) && is_array($tutes[$subjectName])
                                ? $tutes[$subjectName]
                                : array();
                            $current_month_markup = '<div class="student-tute-list" data-student-id="' . (int) $student->ID . '">';
                            foreach ($current_month_records as $status) {
                                $assigned_tute_id = (int) $status->tute_number;
                                if ($assigned_tute_id < 1) {
                                    continue;
                                }

                                $assigned_tute_title = 'Unknown tute';
                                foreach ($student_tutes as $available_tute) {
                                    if ((int) $available_tute->ID === $assigned_tute_id) {
                                        $assigned_tute_title = $available_tute->title;
                                        break;
                                    }
                                }

                                $current_month_markup .= '<div class="student-tute-item d-flex align-items-center mb-1" data-record-id="' .
                                    (int) $status->ID . '" data-tute-id="' . $assigned_tute_id . '" data-class-date="' .
                                    html_escape($status->class_date) . '"><span class="badge badge-success text-wrap">' .
                                    html_escape($assigned_tute_title) . ' - ' .
                                    html_escape(date('Y-m-d', strtotime($status->class_date))) . '</span>' .
                                    '<button type="button" class="btn btn-sm btn-link text-danger student-tute-delete ml-1" ' .
                                    'aria-label="Remove ' . html_escape($assigned_tute_title) . ' assigned on ' .
                                    html_escape($status->class_date) . '" title="Remove tute">&times;</button></div>';
                            }
                            $current_month_markup .= '</div><select class="form-control form-control-sm student-tute-select mt-2" ' .
                                'data-student-id="' . (int) $student->ID . '"><option value="">Assign a tute...</option>';
                            foreach ($student_tutes as $available_tute) {
                                $current_month_markup .= '<option value="' . (int) $available_tute->ID . '">' .
                                    html_escape($available_tute->title) . '</option>';
                            }
                            $current_month_markup .= '</select><small class="d-block student-tute-status" role="status" aria-live="polite"></small>';


                            
                            if (isset($attendances) && is_array($attendances)) {

                               

                               
                                foreach ($months as $month) {
                                    $found = false;
                                    foreach ($attendances as $key =>  $attendace) {
                                        if ($key == $month) {
                                            echo "<td>";
                                            // echo $payment->label;
                                            // echo "<br>";
                                           
                                            // print_r($attendace);
                                            // Show per-student tutes in the active month.
                                            // if($month){ //uncheck if you need to enable to updaate previous months attendance
                                            if($month == $currentMonth){
                                                echo $current_month_markup;
                                            }else{
                                                foreach ($attendace as $status) {
                                                    $historical_tute_title = 'No tute assigned';
                                                    foreach ($student_tutes as $available_tute) {
                                                        if ((int) $available_tute->ID === (int) $status->tute_number) {
                                                            $historical_tute_title = $available_tute->title;
                                                            break;
                                                        }
                                                    }

                                                    echo '<div class="mb-1"><span class="badge ' .
                                                        ((int) $status->tute_number > 0 ? 'badge-success' : 'badge-secondary') .
                                                        ' text-wrap">' . html_escape($historical_tute_title) . ' - ' .
                                                        html_escape(date('Y-m-d', strtotime($status->class_date))) .
                                                        '</span></div>';
                                                }
                                            
                                            }
                                            
                                            echo "</td>";
                                            $found = true;
                                            // break; // Exit the inner loop once a match is found
                                        }
                                      
                                    }
                                  
                                    if (!$found && $month != $currentMonth) {
                                        echo "<td>";
                                          // echo "<input type='checkbox' value='P' id='new_attendace_".$student->ID."' name='new_attendace_".$student->ID."'>";
                                          // echo "<label class='form-check-label mx-1' for='new_attendace_".$student->ID."'>Present(New)</label>";
                                           echo "Yet to be enable";
                                        echo "</td>";
                                        // $found = false;

                                        
                                    } elseif (!$found && $month == $currentMonth) {
                                        echo "<td>" . $current_month_markup . "</td>";
                                    }
                                }
                               
                                
                        
                            }else{
                                // if no payments found for student display empty cells for each month
                                foreach ($months as $month) {
                                  if($month == $currentMonth){
                                    echo "<td>" . $current_month_markup . "</td>";
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
                }

                        ?>

                    </tbody>
                    </table>

               <?php }
               echo form_close();
                ?>
            </div>
     

</div>
<?php } ?>
<?php } ?>
            <!-- end of tab content -->
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
    message = 'Are you sure you want to assign new tute distribution?';
    formEl = document.querySelector('.tab-pane.active form'); // Get Active form
    
    let activeTab = document.querySelector('.tab-pane.active');

    // Get form inside active tab
    let formE2 = activeTab.querySelector('form');

    // Get hidden field inside THAT form
    let hiddenInput = formE2.querySelector('#btnsubmit');

    // Set value
    hiddenInput.value = action;

// ✅ CHECK DATE EXIST
    if (action === 'Add New Attendance') {
        if (isDateAlreadyExists()) {
            alert("One or more selected tutes are already assigned on this date. Choose different tutes or update the existing distribution.");
            return false;
        }
    }

    // formE1.querySelector('.btnsubmit').value = action;
    // btnEl = btnSubmit;
  } else if (action === 'Update Old Attendance') {
    message = 'Are you sure you want to update previous tutes distribution?';
    formEl = document.querySelector('.tab-pane.active form'); // Get Active form
    let activeTab = document.querySelector('.tab-pane.active');

    // Get form inside active tab
    let formE2 = activeTab.querySelector('form');

    // Get hidden field inside THAT form
    let hiddenInput = formE2.querySelector('#btnsubmit');

    // Set value
    hiddenInput.value = action;
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
<script>
document.querySelectorAll('.tute-distribution-form').forEach(function (form) {
    const dateInput = form.querySelector('.distribution-date');

    function refreshStudentOptions() {
        const selectedDate = dateInput.value;
        form.querySelectorAll('.student-tute-select').forEach(function (select) {
            const studentList = form.querySelector(
                '.student-tute-list[data-student-id="' + select.dataset.studentId + '"]'
            );
            const assignedTutes = new Set();
            if (studentList) {
                studentList.querySelectorAll('.student-tute-item').forEach(function (item) {
                    if (item.dataset.classDate === selectedDate) {
                        assignedTutes.add(item.dataset.tuteId);
                    }
                });
            }

            Array.from(select.options).forEach(function (option) {
                if (option.value !== '') {
                    option.hidden = assignedTutes.has(option.value);
                    option.disabled = option.hidden;
                }
            });
            select.value = '';
        });
    }

    async function postAjax(url, values) {
        const payload = new FormData();
        ['class_id', 'subject_id', 'session_id', 'branch'].forEach(function (name) {
            const input = form.querySelector('input[name="' + name + '"]');
            if (input) payload.set(name, input.value);
        });
        Object.keys(values).forEach(function (key) {
            payload.set(key, values[key]);
        });

        const response = await fetch(url, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: payload,
            credentials: 'same-origin'
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'The tute request could not be completed.');
        }
        return result;
    }

    dateInput.addEventListener('change', refreshStudentOptions);
    form.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    form.addEventListener('change', async function (event) {
        const select = event.target.closest('.student-tute-select');
        if (!select || !select.value) return;

        const studentList = form.querySelector(
            '.student-tute-list[data-student-id="' + select.dataset.studentId + '"]'
        );
        const status = select.parentElement.querySelector('.student-tute-status');
        if (!dateInput.value || !studentList) {
            select.value = '';
            return;
        }

        const tuteId = select.value;
        select.disabled = true;
        status.textContent = 'Saving...';
        try {
            const result = await postAjax(form.dataset.addUrl, {
                student_id: select.dataset.studentId,
                tute_id: tuteId,
                class_date: dateInput.value
            });
            const item = document.createElement('div');
            item.className = 'student-tute-item d-flex align-items-center mb-1';
            item.dataset.recordId = result.record.id;
            item.dataset.tuteId = result.record.tute_id;
            item.dataset.classDate = result.record.class_date;

            const badge = document.createElement('span');
            badge.className = 'badge badge-success text-wrap';
            badge.textContent = result.record.tute_title + ' - ' + result.record.display_date;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-link text-danger student-tute-delete ml-1';
            remove.setAttribute('aria-label', 'Remove ' + result.record.tute_title + ' assigned on ' + result.record.display_date);
            remove.title = 'Remove tute';
            remove.textContent = '×';

            item.appendChild(badge);
            item.appendChild(remove);
            studentList.appendChild(item);
            status.textContent = 'Tute assigned.';
            refreshStudentOptions();
        } catch (error) {
            status.textContent = error.message;
            window.alert(error.message);
            select.value = '';
        } finally {
            select.disabled = false;
        }
    });

    form.addEventListener('click', async function (event) {
        const remove = event.target.closest('.student-tute-delete');
        if (!remove) return;

        const item = remove.closest('.student-tute-item');
        const studentList = remove.closest('.student-tute-list');
        if (!item || !studentList || !window.confirm('Remove this tute assignment?')) return;

        const status = studentList.parentElement.querySelector('.student-tute-status');
        remove.disabled = true;
        status.textContent = 'Removing...';
        try {
            await postAjax(form.dataset.deleteUrl, {record_id: item.dataset.recordId});
            item.remove();
            status.textContent = 'Tute assignment removed.';
            refreshStudentOptions();
        } catch (error) {
            status.textContent = error.message;
            window.alert(error.message);
            remove.disabled = false;
        }
    });

    refreshStudentOptions();
});
</script>
</html>