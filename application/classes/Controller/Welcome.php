<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller_Template {

    public $template = 'site';

    public function action_index() {

        // Load the user information
      //  $user = Auth::instance()->get_user();
         $config = array(
                'author'   => 'Shanmugan',
                'title'    => 'Test',
                'subject'  => 'Pdf',
                'name'     => Text::random().'.pdf', // name file pdf
        );
        
        $name = "Shan";
        View_PDF::factory('welcome/info', $config)
                ->set("name", $name)
        ->render();

        $this->template->content = View::factory('welcome/info')
                ->bind('name', $name);

        // if a user is not logged in, redirect to login page
//        if (!$user) {
//            $this->redirect('welcome/login');
//        }
    }
    
    public function action_send_email() {
        
        $email = "shan@gmail.com";
        $code = "ASDF123";
        $user_id = 10;
        $url = "http://www.google.com";
          $message = View::factory('template/mail/signup')
                ->bind('email', $email)
                ->bind('registration_code', $code)
                ->bind('user_id', $user_id)
                ->bind('url', $url);
        $mail = array(
            'subject' => 'Welcome to KRCFM',
            'body' => $message,
            'from' => array('info@my-schedule.net' => 'KRCFM'),
            'to' => "shanmu.grs24@gmail.com"
        );
        
        Email::send('default', $mail['subject'], $mail['body'], $mail['from'], $mail['to'], 'text/html');
    }

    public function action_create() {
        if (HTTP_Request::POST == $this->request->method()) {
            try {
                $date = new DateTime();

                $user = ORM::factory('User');
                $user->email = $this->request->post('email');
                $user->password = $this->request->post('password');
                $user->is_active = 1;
                $user->created_at = $date->format('Y-m-d H:i:s');
                $user->updated_at = $date->format('Y-m-d H:i:s');
                $user->save();

                // Grant user login role
                $user->add('roles', ORM::factory('Role', array('name' => 'agent')));

                // Reset values so form is not sticky
                $_POST = array();

                // Set success message
                $message = "You have added user '{$user->email}' to the database";
            } catch (ORM_Validation_Exception $e) {

                // Set failure message
                $message = 'There were errors, please see form below.';

                // Set errors using custom messages
                $errors = $e->errors('models');
            }
        }
        $this->template->content = View::factory('user/create')
                ->bind('errors', $errors)
                ->bind('message', $message);
    }

    public function action_login() {
        $this->template->content = View::factory('user/login')
                ->bind('message', $message);

        if (HTTP_Request::POST == $this->request->method()) {
            // Attempt to login user
            $remember = array_key_exists('remember', $this->request->post()) ? (bool) $this->request->post('remember') : FALSE;
            $user = Auth::instance()->login($this->request->post('username'), $this->request->post('password'), $remember);

            // If successful, redirect user
            if ($user) {
                $this->redirect('welcome/index');
            } else {
                $message = 'Login failed';
            }
        }
    }

    public function action_logout() {
        // Log user out
        Auth::instance()->logout();

        // Redirect to login page
        $this->redirect('welcome/login');
    }

}

// End Welcome
