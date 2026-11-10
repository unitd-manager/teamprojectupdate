<?
class CP_Admin_Modules_AgileIms_Feedback_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getStudentFeedback() {
        return $this->view->getStudentFeedback();
    }

    /**
     *
     */
    function getStudentFeedbackSubmit() {
        return $this->model->getStudentFeedbackSubmit();
    }

    /**
     *
     */
    function getFeedbackQuestions() {
        return $this->view->getFeedbackQuestions();
    }

    /**
     *
     */
    function getEditStudentFeedback() {
        return $this->view->getEditStudentFeedback();
    }

    /**
     *
     */
    function getEditStudentFeedbackSubmit() {
        return $this->model->getEditStudentFeedbackSubmit();
    }
}