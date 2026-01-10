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
     
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
          Income Summary
        </a>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="<?php echo base_url(); ?>index.php/welcome/income">Day Payments Summary </a>
          <a class="dropdown-item" href="<?php echo base_url(); ?>index.php/welcome/adminincome">Overall Payment Summary</a>
          
        </div>
     </li>
     <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
          Student Payment Summary
        </a>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="<?php echo base_url(); ?>index.php/welcome/paymenthistory">Physical Payments Summary </a>
          <a class="dropdown-item" href="<?php echo base_url(); ?>index.php/online/onlinepaymenthistory">Online Payments Summary</a>
          
        </div>
     </li>

     <li class="nav-item">
      <a class="nav-link"  href="<?php echo base_url(); ?>index.php/welcome/attendanceview">Enter Attendance <span class="sr-only">(current)</span></a>
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
    <li class="nav-item">
        <?php  
        // session check and if true show logout button else show login button
        if(!$this->session->userdata('logged_in')) {
            echo '<a class="nav-link" href="' . base_url() . 'index.php/guest/loginview">Login</a>';
        } else {  
           // create nice back hyperlink with bootstrap design
           
            // echo '<a class=" mx-4 nav-link btn btn-sm badge-dark" href="' . base_url() . 'index.php/guest/loginview">Logout</a>';
             echo '<a class="rounded-pill mx-4 nav-link btn btn-sm badge-dark" href="' . base_url() . 'index.php/guest/loginview">';
             echo $this->session->userdata('user_name') . '<span class=" mx-1 badge badge-light rounded-pill"> Logout</span></a>';

           
        }
        ?>
        
      </li>
    </ul>
  </div>
</nav>