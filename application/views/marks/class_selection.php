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
     input[type=text], input[type=number], input[type=email], input[type=password], input[type=url], select, option, .from-control {
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
     <li class="nav-item active">
      <a class="nav-link" target="_blank" href="<?php echo base_url(); ?>index.php/welcome/income">Income Summary <span class="sr-only">(current)</span></a>
     </li>
     <li class="nav-item active">
      <a class="nav-link" href="<?php echo base_url(); ?>index.php/mark/">Enter Marks <span class="sr-only">(current)</span></a>
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
                <h1 class="text-center display-4" style="font-size:3rem">Select Grade</h1>
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

                <?php echo form_open('mark/subjects') ?>
                <table class="table table-borderless">
                    <tr>
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
                    </tr>

                    <tr>
                        <td>
                            <input class="btn btn-primary btn-block" type="submit" name="submit" value="NEXT">
                        </td>
                    </tr>
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
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
</script>
</html>