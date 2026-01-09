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
                <h1 class="text-center display-4" style="font-size:2.6rem">Student Payment Summary</h1>
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
                $attributes = array('id' => 'studentMarkList');
               
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

                <?php
                 foreach ($students as $student) {
                            // check if student marks already exists
                            $payments = $student->payment_history;
                              if (isset($payments) && is_array($payments)) {

                               foreach ($payments as $headerpaymenttype) {
                                 if(stripos($headerpaymenttype->label, 'Installment') !== false){
                                   $headerpapertype = 'installment';
                                   break;
                                 }else{
                                   $headerpapertype = 'monthly';
                                 }

                               }
                              }
                  }
                          
                ?>
                <div class="nav-header">
                <button class="download-btn btn btn-outline-success btn-sm" onclick="downloadStudentInfo()"> 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/> </svg>
                     Student Info</button>
                </div>

                   <table class="table table-bordered table-striped">
                    <thead>
                      <?php if(isset($headerpapertype) && $headerpapertype == 'monthly'){ ?> 
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID</th>
                            <th scope="col" style="position: sticky; left: 0; background: #f2f2f2; z-index: 1;">Student Name</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'January') ?  'bg-warning fix-col' : ''; ?>">JAN</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'February') ?  'bg-warning fix-col' : ''; ?>" >FEB</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'March') ?  'bg-warning fix-col' : ''; ?>">MAR</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'April') ?  'bg-warning fix-col' : ''; ?>">APR</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'May') ?  'bg-warning fix-col' : ''; ?>">MAY</th>
                            <th scope="col" class = "<?php echo ($currentMonth == 'June') ?  'bg-warning fix-col' : ''; ?>">JUN</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'July') ?  'bg-warning fix-col' : ''; ?>">JUL</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'August') ?  'bg-warning fix-col' : ''; ?>">AUG</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'September') ?  'bg-warning fix-col' : ''; ?>">SEP</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'October') ?  'bg-warning fix-col' : ''; ?>">OCT</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'November') ?  'bg-warning fix-col' : ''; ?>">NOV</th>
                            <th scope="col" class = "<?php echo($currentMonth == 'December') ?  'bg-warning fix-col' : ''; ?>">DEC</th>
                        
                            

                        </tr>
                      <?php } else if(isset($headerpapertype) &&$headerpapertype == 'installment'){ ?>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID</th>
                            <th scope="col" style="position: sticky; left: 0; background: #f2f2f2; z-index: 1;">Student Name</th>
                            <th scope="col" class = "<?php echo($currentMonth) ?  'bg-warning fix-col' : ''; ?>">Inst 1</th>
                            <th scope="col" class = "<?php echo ($currentMonth ) ?  'bg-warning fix-col' : ''; ?>" >Inst 2</th>
                            <th scope="col" class = "<?php echo ($currentMonth) ?  'bg-warning fix-col' : ''; ?>">Inst 3</th>
                            <th scope="col" class = "<?php echo($currentMonth ) ?  'bg-warning fix-col' : ''; ?>">Inst 4</th>
                            <th scope="col" class = "<?php echo($currentMonth ) ?  'bg-warning fix-col' : ''; ?>">Inst 5</th>
                            <th scope="col" class = "<?php echo ($currentMonth ) ?  'bg-warning fix-col' : ''; ?>">Inst 6</th>
                            <th scope="col" class = "<?php echo($currentMonth ) ?  'bg-warning fix-col' : ''; ?>">Inst 7</th>
                            <th scope="col" class = "<?php echo($currentMonth ) ?  'bg-warning fix-col' : ''; ?>">Inst 8</th>
                            <th scope="col" class = "<?php echo($currentMonth ) ?  'bg-warning fix-col' : ''; ?>">Inst 9</th>
                            <th scope="col" class = "<?php echo($currentMonth ) ?  'bg-warning fix-col' : ''; ?>">Inst 10</th>
                            <th scope="col" class = "<?php echo($currentMonth ) ?  'bg-warning fix-col' : ''; ?>">Inst 11</th>
                            <th scope="col" class = "<?php echo($currentMonth) ?  'bg-warning fix-col' : ''; ?>">Inst 12</th>
                             <th scope="col" class = "<?php echo($currentMonth) ?  'bg-warning fix-col' : ''; ?>">Inst 13</th>
                             <th scope="col" class = "<?php echo($currentMonth) ?  'bg-warning fix-col' : ''; ?>">Inst 14</th>
                           
                        
                            

                        </tr>
                      <?php } ?>
                    </thead>
                    <tbody>
                      <?php
                      
                        $i = 1;
                        $months = [
                                      "January", "February", "March", "April", "May", "June",
                                      "July", "August", "September", "October", "November", "December"
                                  ];

                           $installments = [
                                      "Installment 1", "Installment 2", "Installment 3", "Installment 4", "Installment 5", "Installment 6",
                                      "Installment 7", "Installment 8", "Installment 9", "Installment 10", "Installment 11", "Installment 12","Installment 13", "Installment 14"
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
                                      
                            echo "<span class='copy-icon' title='Copy Email' value='$student->email' onclick='copyEmail(this)'>Email📋</span>";
                            echo "</td>";



                            
                            if (isset($payments) && is_array($payments)) {

                               foreach ($payments as $checkpaymenttype) {
                                 if(stripos($checkpaymenttype->label, 'Installment') !== false){
                                   $papertype = 'installment';
                                   break;
                                 }else{
                                   $papertype = 'monthly';
                                 }

                               }

                               if($papertype == 'monthly'){
                                foreach ($months as $month) {
                                    $found = false;
                                    foreach ($payments as $payment) {
                                        if (stripos($payment->label, $month) !== false) {
                                            echo "<td>";
                                            // echo $payment->label;
                                            // echo "<br>";
                                           
                                            if($payment->status == 'paid'){
                                              $studentid = explode('/', $student->admission_number);
                                              echo "<a href='" . base_url() . "index.php/online/idValidator/{$studentid[1]}/{$branch}/{$selected_academic_year}'>";
                                                echo "<span class='badge badge-success' title='$payment->amount'> Paid ($payment->invoice_number)</span>";
                                              echo "</a>";
                                            }else if($payment->status == 'unpaid'){
                                              $studentid = explode('/', $student->admission_number);
                                                echo "<a class='badge badge-danger' href='" . base_url() . "index.php/online/idValidator/{$studentid[1]}/{$branch}/{$selected_academic_year}'> Unpaid <span class='badge badge-light'>$payment->amount</span> </a>";
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
                               }else if($papertype == 'installment'){
                                foreach ($installments as $installment) {
                                    $found = false;
                                    foreach ($payments as $payment) {
                                        if (stripos($payment->label, $installment) !== false) {
                                            echo "<td>";
                                            // echo $payment->label;
                                            // echo "<br>";
                                           
                                            if($payment->status == 'paid'){
                                              $studentid = explode('/', $student->admission_number);
                                              echo "<a href='" . base_url() . "index.php/online/idValidator/{$studentid[1]}/{$branch}/{$selected_academic_year}'>";
                                                echo "<span class='badge badge-success' title='$payment->amount'> Paid ($payment->invoice_number)</span>";
                                              echo "</a>";
                                            }else if($payment->status == 'unpaid'){
                                              $studentid = explode('/', $student->admission_number);
                                                echo "<a class='badge badge-danger' href='" . base_url() . "index.php/online/idValidator/{$studentid[1]}/{$branch}/{$selected_academic_year}'> Unpaid <span class='badge badge-light'>$payment->amount</span> </a>";
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
</html>