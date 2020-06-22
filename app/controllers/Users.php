<?php
  class Users extends Controller{
    public function __construct(){
      $this->userModel = $this->model('User');
    }

    public function index(){
      redirect('welcome');
    }

    public function register(){
      // Check if logged in
      if($this->isLoggedIn()){
        redirect('posts');
      }

      // Check if POST
      if($_SERVER['REQUEST_METHOD'] == 'POST'){
        // Sanitize POST
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $data = [
          'name' => trim($_POST['name']),
          'email' => trim($_POST['email']),
          'password' => trim($_POST['password']),
          'confirm_password' => trim($_POST['confirm_password']),
          'email_verified' => 'n',
          'name_err' => '',
          'email_err' => '',
          'password_err' => '',
          'confirm_password_err' => ''
        ];

        // Validate email
        if(empty($data['email'])){
            $data['email_err'] = 'Please enter an email';
            // Validate name
            if(empty($data['name'])){
              $data['name_err'] = 'Please enter a name';
            }
        } else{
          // Check Email
          if($this->userModel->findUserByEmail($data['email'])){
            $data['email_err'] = 'Email is already taken.';
          }
        }

        // Validate password
        if(empty($data['password'])){
          $password_err = 'Please enter a password.';     
        } elseif(strlen($data['password']) < 6){
          $data['password_err'] = 'Password must have atleast 6 characters.';
        }

        // Validate confirm password
        if(empty($data['confirm_password'])){
          $data['confirm_password_err'] = 'Please confirm password.';     
        } else{
            if($data['password'] != $data['confirm_password']){
                $data['confirm_password_err'] = 'Password do not match.';
            }
        }
         
        // Make sure errors are empty
        if(empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])){
          // SUCCESS - Proceed to insert

          // Hash Password
          $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
          // Make sure / is not included in the hashed password so it can be parsed through the URL
          $data['password'] = str_replace("/","A",$data['password']);

          //Execute
          if($this->userModel->register($data)){
            // Send email and redirect to login

            $emailBody = "<h3>To register for Shareposts click the link below</h3>
            <h2><a href=" . URLROOT . "/users/confirmRegistration/" . $data['email'] . "/" . $data['password'] . ">Click here to register for Shareposts</a></h2><BR>";

            if (mail($data['email'], "Welcome to the sharepost community",$emailBody,"MIME-Version: 1.0\r\nContent-type: text/html; charset=charset=ISO-8859-1\r\nFrom: Do not reply")) {
               flash('register_success', 'Click the link in the email we sent you and then login');
            } else {
               flash('register_success', 'Email did not work. Try again!');
            }
            redirect('users/login');
          } else {
            die('Something went wrong');
          }
                    
        } else {
          // Load View
          $this->view('users/register', $data);
        }
      } else {
        // IF NOT A POST REQUEST

        // Init data
        $data = [
          'name' => '',
          'email' => '',
          'password' => '',
          'confirm_password' => '',
          'name_err' => '',
          'email_err' => '',
          'password_err' => '',
          'confirm_password_err' => ''
        ];

        // Load View
        $this->view('users/register', $data);
      }
    }

    // Confirm registration when email link is clicked on
    public function confirmRegistration($email = "XXX",$password = "ZZZ"){

      if($this->userModel->confirmRegistration($email,$password)) {
         flash('verify_success', 'You have verified your details, you may now login');
         redirect('users/login');
      } else if ($this->userModel->findUserByEmail($email)) {
           flash('verify_success', 'You have already verified you details');
           redirect('users/login');
      } else {
        flash('verify_success', 'Your details were not found on our system');
        redirect('users/register');    
      }
      
    }

    public function login(){
      // Check if logged in
      if($this->isLoggedIn()){
        redirect('posts');
      }

      // Check if POST
      if($_SERVER['REQUEST_METHOD'] == 'POST'){
        // Sanitize POST
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        $data = [       
          'email' => trim($_POST['email']),
          'password' => trim($_POST['password']),        
          'email_err' => '',
          'password_err' => '',
          'email_verified_err' => ''      
        ];

        // Check for email
        if(empty($data['email'])){
          $data['email_err'] = 'Please enter email.';
        }

        // Check for name
        if(empty($data['name'])){
          $data['name_err'] = 'Please enter name.';
        }

        // Check for user
        if($this->userModel->findUserByEmail($data['email'])){
          // User Found
        } else {
          // No User
          $data['email_err'] = 'This email is not registered.';
        }

        // Check user as verified by their email account    
        $verifyEmail = $this->userModel->verifyEmail($data['email']); 
        if(isset($verifyEmail)) {
         // print_r($verifyEmail);die();
           if($verifyEmail->email_verified == 'y'){
             // User verified by email
           } else if($verifyEmail->email_verified == 'n') {
             // User not verified by email
             $data['email_verified_err'] = 'verify your details by following the link on the email we sent you.';
           }
        }


        // Make sure errors are empty
        if(empty($data['email_err']) && empty($data['password_err']) && empty($data['email_verified_err']) ){

          // Check and set logged in user
          $loggedInUser = $this->userModel->login($data['email'], $data['password']);

          if($loggedInUser){
            // User Authenticated!
            $this->createUserSession($loggedInUser);
           
          } else {
            $data['password_err'] = 'Password incorrect.';
            // Load View
            $this->view('users/login', $data);
          }
           
        } else {
          // Load View
          $this->view('users/login', $data);
        }

      } else {
        // If NOT a POST

        // Init data
        $data = [
          'email' => '',
          'password' => '',
          'email_err' => '',
          'password_err' => '',
        ];

        // Load View
        $this->view('users/login', $data);
      }
    }

    // Create Session With User Info
    public function createUserSession($user){
      $_SESSION['user_id'] = $user->id;
      $_SESSION['user_email'] = $user->email; 
      $_SESSION['user_name'] = $user->name;
      redirect('posts');
    }

    // Logout & Destroy Session
    public function logout(){
      unset($_SESSION['user_id']);
      unset($_SESSION['user_email']);
      unset($_SESSION['user_name']);
      session_destroy();
      redirect('users/login');
    }

    // Check Logged In
    public function isLoggedIn(){
      if(isset($_SESSION['user_id'])){
        return true;
      } else {
        return false;
      }
    }
  }