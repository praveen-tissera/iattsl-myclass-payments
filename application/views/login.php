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
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
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
                <h1 class="text-center display-4" style="font-size:3rem">Please  Enter Student Class ID </h1>
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

                <?php echo form_open('welcome/idValidator') ?>
                <table class="table table-borderless">
                    <tr>
                        <td>
                          <?php 
                         
                            if (!empty($student_data['profile'][0]->admission_number)) {
                              $student_id_array = explode('/', $student_data['profile'][0]->admission_number);
                              $student_id = end($student_id_array);

                              echo "<input class='form-control' type='text' name='studentid' placeholder='24-999' value='{$student_id}'>";
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

                            <?php 
                            if (!empty($student_data['profile'][0]->admission_number)) {
                              $student_id_array = explode('/', $student_data['profile'][0]->admission_number);
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
          if(isset($student_data['profile'])){
            echo "<hr>";
        ?>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Profile Status: <?php echo  ($student_data['profile'][0]->is_active == 1)? '<span class="dot-active"></span> Active' : '<span class="dot-inactive"></span> Inactive'; ?></h5>
                  <?php 
                  if($student_data['profile'][0]->is_active == 1){
                    $classLinks = array(
                      'ICT'=> array(
                        'Grade 6' => "https://teams.microsoft.com/l/meetup-join/19%3a0474bd04e5d4468c801efc0e1e88f393%40thread.tacv2/1675186039125?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 7' => "https://teams.microsoft.com/l/meetup-join/19%3abdf78068f9d043f6abecdb1fec43b319%40thread.tacv2/1675733009028?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 8' => "https://teams.microsoft.com/l/meetup-join/19%3a59b07ffdb7c04f8f8c1c1c7e006d55c1%40thread.tacv2/1675572699061?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 9' => "https://teams.microsoft.com/l/meetup-join/19%3ad4671fe5103a4ade89c07f61c433c609%40thread.tacv2/1675650687386?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 10' => "https://teams.microsoft.com/l/meetup-join/19%3aaf6f06cab6ff4a228950a9356aee0e1d%40thread.tacv2/1675276024535?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 11' => "https://teams.microsoft.com/l/meetup-join/19%3ae617bea26db3498f99187b18a43ac30e%40thread.tacv2/1675209215336?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        
                      ),
                      'Science'=> array(
                        'Grade 6' => "https://teams.microsoft.com/l/meetup-join/19%3abaf00f4a664f4fe5b3b1b610edcb5da4%40thread.tacv2/1678780575404?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 7' => "https://teams.microsoft.com/l/meetup-join/19%3a249300bfc10b4ad99f66285c5d473f7b%40thread.tacv2/1678780854925?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 8' => "https://teams.microsoft.com/l/meetup-join/19%3a1aa518d35e814bf1b7fee6f3608dca51%40thread.tacv2/1678780928279?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 9' => "https://teams.microsoft.com/l/meetup-join/19%3a03447e0d65614d0bb5ca63e2f22400f9%40thread.tacv2/1678781067259?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                      ),
                      'Maths'=> array(
                        'Grade 6' => "https://teams.microsoft.com/l/meetup-join/19%3a9d258dcb37274eec94f4c5a709b5eab7%40thread.tacv2/1678777726993?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 7' => "https://teams.microsoft.com/l/meetup-join/19%3ad3495b86037d4600bed6d3ba7776c013%40thread.tacv2/1678778071305?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 8' => "https://teams.microsoft.com/l/meetup-join/19:e41d75e7c5f9425a83568ee9cd3c7f22@thread.tacv2/1678778315991?context=%7B%22Tid%22:%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22,%22Oid%22:%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7D",
                        'Grade 9' => "https://teams.microsoft.com/l/meetup-join/19%3acb5957caa3134f9bbd58515e2f79ef7c%40thread.tacv2/1678780165129?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                      ),
                      'English'=> array(
                        'Grade 6' => "https://teams.microsoft.com/l/meetup-join/19%3ae311e3ed5c744e07b81bad0d24e43522%40thread.tacv2/1690046163023?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 7' => "https://teams.microsoft.com/l/meetup-join/19%3a874948c8346e4c6c9235f9f52b35eb34%40thread.tacv2/1690046361538?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 8' => "https://teams.microsoft.com/l/meetup-join/19%3a8966bf4ea327402f8272fe753623fecd%40thread.tacv2/1690046482887?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 9' => "https://teams.microsoft.com/l/meetup-join/19%3a69c3cd49b6f34d6cb656358791144823%40thread.tacv2/1690046552724?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                      ),
                      'Tamil'=> array(
                        'Grade 6' => "https://teams.microsoft.com/l/meetup-join/19%3a81352b05cb714d478ac45c460a9601b8%40thread.tacv2/1690046724142?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 7' => "https://teams.microsoft.com/l/meetup-join/19%3a08488d91c6ce4ad3aeabf659627c4425%40thread.tacv2/1690046786909?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 8' => "https://teams.microsoft.com/l/meetup-join/19%3aa2d92bd460a548fd8457f9a12b7a8f50%40thread.tacv2/1690046851219?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 9' => "https://teams.microsoft.com/l/meetup-join/19%3a526ed630b08d421984b928ef4e4483ed%40thread.tacv2/1690046913625?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                      ),
                      'History'=> array(
                        'Grade 6' => "https://teams.microsoft.com/l/meetup-join/19%3ae77fd640b196426e8459d2e9baec1bd3%40thread.tacv2/1690047288471?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 7' => "https://teams.microsoft.com/l/meetup-join/19%3a33ffe9f6d7ba44a691f73a7fa8e99b97%40thread.tacv2/1690047357023?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 8' => "https://teams.microsoft.com/l/meetup-join/19%3a447a7d0b51494e369e67b622bae50508%40thread.tacv2/1690119211019?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 9' => "https://teams.microsoft.com/l/meetup-join/19%3acac1dd34466d4119b44b36a32dbbe848%40thread.tacv2/1690047472616?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                      ),
                      'Health'=> array(
                        'Grade 6' => "https://teams.microsoft.com/l/meetup-join/19%3a71f1fad03f164034b6bb75cd0ee9cbba%40thread.tacv2/1690047012921?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 7' => "https://teams.microsoft.com/l/meetup-join/19%3ac246247a4fe74e1caf69e4747349f768%40thread.tacv2/1690047069962?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 8' => "https://teams.microsoft.com/l/meetup-join/19%3abc92d5e01598438bb5d939ddd58f9ed3%40thread.tacv2/1690047137931?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                        'Grade 9' => "https://teams.microsoft.com/l/meetup-join/19%3a41568810d6494c929d7283078da1183d%40thread.tacv2/1690047194289?context=%7b%22Tid%22%3a%22f3c632dc-15a9-4d0e-86ce-95adfd69e8b3%22%2c%22Oid%22%3a%226b3e1f6a-f9b9-44d8-b2c9-9b931436fbc0%22%7d",
                      ),
                      'ICTJ'=> array(
                        'Grade 6' => "",
                      
                      ),

                    );
                    foreach ($classLinks as $course => $grades) {
                      if($course == $student_data['course']){
                        foreach ($grades as $grade => $link) {
                          if($grade == $student_data['grade'][0]->label ){
                            $classLink = $link;
                          }
                        }
                      }
                    }
                   
                 
                    // echo $deepUrl = base_url().'login/attendnce/'.$student_data['profile'][0]->ID.'/'. $student_data['course'].'/'.$student_data['grade'][0]->ID .'/'. $student_data['grade'][0]->label.'/'.base64_encode($classLink);
                    echo form_open('welcome/attendnce');
                    // echo "<input type='hidden' name='admission' value='{$student_data['profile'][0]->admission_number}'>";
                    // echo "<input type='hidden' name='studentid' value='{$student_data['profile'][0]->ID}'>";
                    // echo "<input type='hidden' name='course' value='{$student_data['course']}'>";
                    // echo "<input type='hidden' name='gradeid' value='{$student_data['grade'][0]->ID}'>";
                    // echo "<input type='hidden' name='gradelable' value='{$student_data['grade'][0]->label}'>";
                    // echo "<input type='hidden' name='classlink' value='{$classLink}'>";
                    // echo '<input class="btn btn-primary btn-block" formtarget="_blank" type="submit" name="submit" value="Join Class">';

                    echo form_close();
                  ?>

                 
                  <?php }else {?>
                    <div class="alert alert-danger" role="alert">
                      Your Profile is in <strong>Inactive Mode</strong> Please contact ADMIN (076 435 4111)
                    </div>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-12 col-md-6">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Student Profile</h5>
                  <p class="card-text">Name: <?php echo $student_data['profile'][0]->name;?></p>
                  <p class="card-text">Course : <?php echo $student_data['course']; ?>  <?php echo $student_data['grade'][0]->label; ?></p>
                  <p class="card-text">Class/Course Registration# : <?php echo $student_data['profile'][0]->admission_number; ?> </p>
                  
                </div>
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 mt-2">
              <div class="card">
                <div class="card-body">
                  <div class="cart-title">Payment History</div>
                  <?php 
                  // print_r($student_data);
                  $payments = $student_data['payment'];
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
                 foreach ($student_data['payment'] as $key => $payment) {
                  if($payment->status == 'paid') {
                    echo form_open('welcome/pdfgenerator');
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
                  }, $student_data['payment_completion']);
                  
                  $index = array_search($payment->ID, $invoiceIds);
                  if($index !== false) {
                    echo $student_data['payment_completion'][$index]->amount;
                  }else{
                    echo $payment->amount;
                  }

                    echo "</td>";
                    echo "<td>";
                    // if paid return receipt payment date
                    echo $student_data['payment_completion'][$index]->created_at;
                    // echo (empty($payment->updated_at))? $payment->created_at : $payment->updated_at;
                    
                 
                  echo "</td>";
                    echo "<td>";
                    echo $payment->status;
                  echo "</td>";
                  echo "<td>";
                  
                  echo "<input type='hidden' name='grade' value='{$student_data['grade'][0]->label}'>";
                  echo "<input type='hidden' name='invoiceid' value='{$payment->invoice_number}'>";
                  echo "<input type='submit' class='btn btn-info btn-sm mr-1' value='Download' />";
                 if(empty($student_data['profile'][0]->phone)){
                  echo "<a class='disabled pt-0 pb-0 btn btn-success btn-sm' href='https://wa.me/{$student_data['profile'][0]->phone}?text={$payment->label}%20Approved'>Send</a>";
                 }else{
                  echo "<a class='mt-1 pt-0 pb-0 btn btn-success btn-sm' href='https://wa.me/{$student_data['profile'][0]->phone}?text={$payment->label}%20Approved'>Send</a>";
                 }
                 

                  
                  echo "</td>";
                  echo "</tr>";
                  echo form_close();
                  }
                  else if($payment->status == 'unpaid'){
                    echo form_open('welcome/insertPayment' , ['onsubmit' => 'return confirmSubmit();']);
                        echo "<tr>";
                        echo "<td>";
                        echo "<input type='hidden' name='student_id' value='{$student_data['profile'][0]->admission_number}'>";

                       
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
                      echo "<input type='submit' class='btn btn-danger btn-sm' value='Pay Now' />";
                      echo "</td>";
                      echo "</tr>";
                    echo form_close();
                  }
                 }
                 echo "</table>";
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
                  $attendance = $student_data['attendance'];
                  if(count($attendance) != 0){
                    echo "<table class='table table-sm table-striped'>";
                    echo "<tr class='bg-success text-white' >";
                     echo "<th >";
                       echo "Attend Date";
                     echo "</th>";
                    
                    echo "</tr>";
                    echo "</table>";
                    $current_month = date('m');
                    foreach ($student_data['attendance'] as $key => $attend) {
                     if($current_month == date('m',strtotime($attend->attend_date)) ){
                      echo "<span class='badge badge-primary mx-1' title='".date('D-m-Y',strtotime($attend->attend_date))."'>". date('d-M-Y',strtotime($attend->attend_date)). "</span>";
                     }else{
                      echo '<hr class="my-1">';
                      echo "<span class='badge badge-warning mx-1' title='".date('D-m-Y',strtotime($attend->attend_date))."'>". date('d-M-Y',strtotime($attend->attend_date)). "</span>";
                     }
                       
                    
                     
                    }
                    
                  }else{

                    echo "<span class='badge badge-danger mx-1'>No Attendance Found</span>";
                  }
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
<script>
  
 function confirmSubmit() {
    return confirm("Are you sure you want to update the payment?");
  }

</script>
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
</html>