<?php

namespace App\Controllers;

use App\Traits\Renderable; 
use App\Models\User; 
use App\Controllers\BaseController;

class RegistrationController extends BaseController
{
    use Renderable; 

    public function showRegistrationForm()
    {
        $template = 'registration'; 
        $data = [
            'title' => 'Registration',
        ];
        $this->render($template, $data);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $complete_name = trim($_POST['complete_name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            $errors = [];
            if (empty($complete_name)) {
                $errors[] = 'Complete name is required.';
            }
            if (empty($email)) {
                $errors[] = 'Email address is required.';
            }
            if (empty($password) || empty($confirmPassword)) {
                $errors[] = 'Password and confirmation are required.';
            } else {
                if (strlen($password) < 8) {
                    $errors[] = 'Password must be at least 8 characters.';
                }
                if (!preg_match('/[0-9]/', $password)) {
                    $errors[] = 'Password must contain at least one numeric character.';
                }
                if (!preg_match('/[a-zA-Z]/', $password)) {
                    $errors[] = 'Password must contain at least one non-numeric character.';
                }
                if (!preg_match('/[!@#$%^&*()\-+]/', $password)) {
                    $errors[] = 'Password must contain at least one special character like (!@#$%^&*-+).';
                }
                if ($password !== $confirmPassword) {
                    $errors[] = 'Passwords do not match.';
                }
            }

            if (!empty($errors)) {
                $template = 'registration'; 
                $data = [
                    'title' => 'Registration',
                    'errors' => $errors,
                ];
                $this->render($template, $data);
                return;
            }

            $user = new User();
            $user->fill([
                'email' => $email,
                'complete_name' => $complete_name,
                'password' => $password, 
            ]);

            if ($user->save($user->toArray())) {
                $template = 'success'; 
                $data = [
                    'message' => 'Successful Registration',
                    'link' => '/login', 
                ];
                $this->render($template, $data);
            } else {
                $template = 'registration'; 
                $data = [
                    'title' => 'Registration',
                    'errors' => ['Registration failed. Please try again.'],
                ];
                $this->render($template, $data);
            }
        }
    }
}