<?
class CP_Admin_Modules_Edukloud_Batch_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getBulkUpdateEvaluate() {
        return $this->view->getBulkUpdateEvaluate();
    }

    function getBulkUpdateEvaluateSubmit() {
        return $this->model->getBulkUpdateEvaluateSubmit();
    }

    function getTakeAttendance() {
        return $this->view->getTakeAttendance();
    }

    function getTakeAttendanceSubmit() {
        return $this->model->getTakeAttendanceSubmit();
    }

    function getStudentFeedback() {
        return $this->view->getStudentFeedback();
    }

    function getStudentFeedbackSubmit() {
        return $this->model->getStudentFeedbackSubmit();
    }

    function getPrintVoucher() {
        return $this->model->getPrintVoucher();
    }

    function getPrintAttendance() {
        return $this->fns->getPrintAttendance();
    }

    function getPrintAttendanceExcell() {
        return $this->fns->getPrintAttendanceExcell();
    }

    function getEditAttendance() {
        return $this->view->getEditAttendance();
    }

    function getEditAttendanceSubmit() {
        return $this->model->getEditAttendanceSubmit();
    }

    function getStudentGrade() {
        return $this->view->getStudentGrade();
    }

    function getStudentGradeSubmit() {
        return $this->model->getStudentGradeSubmit();
    }

    function getEditStudentGrade() {
        return $this->view->getEditStudentGrade();
    }

    function getEditStudentGradeSubmit() {
        return $this->model->getEditStudentGradeSubmit();
    }

    function getEditStudentFeedback() {
        return $this->view->getEditStudentFeedback();
    }

    function getEditStudentFeedbackSubmit() {
        return $this->model->getEditStudentFeedbackSubmit();
    }

    function getUpdateGrade() {
        return $this->model->getUpdateGrade();
    }

    function getUpdateStudentResult() {
        return $this->model->getUpdateStudentResult();
    }

    function getFeedbackQuestions() {
        return $this->view->getFeedbackQuestions();
    }
}