<?php 

namespace App\Controllers;

use App\Models\Question;
use App\Models\UserAnswer;

class ExamController extends BaseController
{
    public function registrationForm()
    {
        $this->initializeSession();

        return $this->render('registration-form');
    }

    public function register()
    {
        $this->initializeSession();
        $data = $_POST;

        # Save the registration to database
        $_SESSION['user_id'] = 1;
        $_SESSION['complete_name'] = 'complete_name';
        $_SESSION['email'] = $data['email'];

        return $this->render('pre-exam', $data);
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
            // Initialize session variables
            $_SESSION['item_number'] = $item_number;
            $_SESSION['answers'] = [false];
        } else {
            $item_number = $_SESSION['item_number'];
        }

        $data = $_POST;
        $questionObj = new Question();
        $question = $questionObj->getQuestion($item_number);

        // if there are no more questions, save the answers
        if (is_null($question) || !$question) {
            $user_id = $_SESSION['user_id'];
            $json_answers = json_encode($_SESSION['answers']);

            error_log('FINISHED EXAM, SAVING ANSWERS');
            error_log('USER ID = ' . $user_id);
            error_log('ANSWERS = ' . $json_answers);

            $userAnswerObj = new UserAnswer();
            $userAnswerObj->save(
                $user_id,
                $json_answers
            );
            $score = $questionObj->computeScore($_SESSION['answers']);
            $items = $questionObj->getTotalQuestions();
            $userAnswerObj->saveAttempt($user_id, $items, $score);

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
            $question['user_answer'] = $answers[$question['item_number']];
        }
        $data['total_score'] = $questionObj->computeScore($_SESSION['answers']);
        $data['question_items'] = $questionObj->getTotalQuestions();

        session_destroy();

        return $this->render('result', $data);
    }
}
