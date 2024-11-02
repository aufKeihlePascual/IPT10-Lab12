<?php 

namespace App\Controllers;

use App\Models\Question;
use App\Models\UserAnswer;
use App\Models\User;
use \PDO;
use Fpdf\Fpdf;

require 'vendor/autoload.php';

class ExamController extends BaseController
{
    public function loginForm()
    {
        $this->initializeSession();

        return $this->render('login-form');
    }

    public function registrationForm()
    {
        $this->initializeSession();

        return $this->render('registration-form');
    }

    public function register()
    {
        $this->initializeSession();
        $data = $_POST;

        $user = new User();
        $result = $user->save($data);

        if ($result['row_count'] > 0) {
           
            $_SESSION['user_id'] = $result['last_insert_id']; 
            $_SESSION['complete_name'] = $data['complete_name'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['password'] = $data['password'];
    
            return $this->render('login-form', $data);

        }
    }

    public function login(){
        $this->initializeSession();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST;

        $user = new User();
        
        #User Verification
        if ($user->verifyAccess($data['email'], $data['password'])) {
            $sql = "SELECT id, complete_name, email FROM users WHERE email = :email";
            $statement = $user->getDatabaseConnection()->prepare($sql);
            $statement->execute(['email' => $data['email']]);
            $userData = $statement->fetch(PDO::FETCH_ASSOC);
            
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['complete_name'] = $userData['complete_name'];
            $_SESSION['email'] = $userData['email'];

            $templateData = [
                'complete_name' => $userData['complete_name'],
                'email' => $userData['email'],
            ];

            return $this->render('pre-exam', $templateData); 
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            return $this->render('login'); 
        }
    }

    
    return $this->render('login'); 
    }

    public function exam()
    {
        
        $this->initializeSession();
        $item_number = 1;

        if (isset($_POST['item_number']) && isset($_POST['answer'])) {
            array_push($_SESSION['answers'], $_POST['answer']);
            $_SESSION['item_number'] = $_POST['item_number'] + 1;
        }

        if (!isset($_SESSION['item_number'])) {
            $_SESSION['item_number'] = $item_number;
            $_SESSION['answers'] = [false];
        } else {
            $item_number = $_SESSION['item_number'];
        }

        $data = $_POST;
        $questionObj = new Question();
        $question = $questionObj->getQuestion($item_number);

        #save answers if no more questions
        if (is_null($question) || !$question) {
            $user_id = $_SESSION['user_id'];
            $json_answers = json_encode($_SESSION['answers']);

            error_log('FINISHED EXAM, SAVING ANSWERS');
            error_log('USER ID = ' . $user_id);
            error_log('ANSWERS = ' . $json_answers);

            $userAnswerObj = new UserAnswer();
            $score = $questionObj->computeScore($_SESSION['answers']);
            $items = $questionObj->getTotalQuestions();
            $attempt_Id = $userAnswerObj->saveAttempt($user_id, $items, $score);
            $userAnswerObj->save(
                $user_id,
                $json_answers,
                $attempt_Id
            );
            

            header("Location: /result");
            exit;
        }

        $question['choices'] = json_decode($question['choices']);

        return $this->render('exam', $question);
    }

    public function result()
    {
        $this->initializeSession();
        $data = $_SESSION;
        
        $questionObj = new Question();
        $data['questions'] = $questionObj->getAllQuestions();
        $answers = $_SESSION['answers'];
        foreach ($data['questions'] as &$question) {
            $question['choices'] = json_decode($question['choices']);
            $question['user_answer'] = $answers[$question['item_number']] ?? null;
        }
        $data['total_score'] = $questionObj->computeScore($_SESSION['answers']);
        $data['question_items'] = $questionObj->getTotalQuestions();

        $userAnswerObj = new UserAnswer();
        $user_id = $_SESSION['user_id'];
        
        $latestAttempt = $userAnswerObj->getLatestAttemptByUserId($user_id);
        
        if ($latestAttempt) {
            $data['attempt_id'] = $latestAttempt['attempt_id'];
        } else {
            $data['attempt_id'] = null; 
        }

        session_destroy();

        return $this->render('result', $data);
    }

    public function displayExamAttempts()
    {
        $examAttemptModel = new UserAnswer();
        
        $attempts = $examAttemptModel->getAllExamAttempts();
        
        return $this->render('exam-attempts', ['attempts' => $attempts]);
    }

    public function exportToPDF($attempt_id)
    {
        $obj = new UserAnswer();
        $data = $obj->exportData($attempt_id);
    
        // Create an instance of FPDF
        $pdf = new FPDF();
        $pdf->AddPage();
    
        // Set document title with larger, bold font
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(0, 51, 102); // Dark blue color for the title
        $pdf->Cell(190, 15, 'Examinee Attempt Details', 0, 1, 'C');
        $pdf->Ln(10);
    
        // Section: Examinee Information
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(255, 255, 255); // White color for header text
        $pdf->SetFillColor(0, 51, 102); // Dark blue background for headers
        $pdf->Cell(190, 10, 'Examinee Information', 0, 1, 'C', true);
        $pdf->Ln(5);
    
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Black for body text
        $pdf->Cell(50, 10, 'Name:', 0, 0);
        $pdf->Cell(100, 10, $data['examinee_name'], 0, 1);
        $pdf->Cell(50, 10, 'Email:', 0, 0);
        $pdf->Cell(100, 10, $data['examinee_email'], 0, 1);
        $pdf->Cell(50, 10, 'Attempt Date:', 0, 0);
        $pdf->Cell(100, 10, $data['attempt_date'], 0, 1);
        $pdf->Cell(50, 10, 'Exam Items:', 0, 0);
        $pdf->Cell(100, 10, $data['exam_items'], 0, 1);
        $pdf->Cell(50, 10, 'Exam Score:', 0, 0);
        $pdf->Cell(100, 10, $data['exam_score'], 0, 1);
        $pdf->Ln(10);
    
        // Section: Answers Submitted
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFillColor(0, 51, 102);
        $pdf->Cell(190, 10, 'Answers Submitted', 0, 1, 'C', true);
        $pdf->Ln(5);
    
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 10, 'Answers: ' . $data['answers']);
        $pdf->Cell(50, 10, 'Date Answered:', 0, 0);
        $pdf->Cell(100, 10, $data['date_answered'], 0, 1);
        $pdf->Ln(10);
    
        // Output the PDF as a download
        $pdf->Output('D', 'examinee_attempt_' . $attempt_id . '.pdf');
    }

    public function logout() {
        $this->initializeSession();
    
        session_unset();
        session_destroy();
        
        header("Location: /");
        exit();
    }
    
}