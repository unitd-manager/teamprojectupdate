<?
class CP_Admin_Modules_Pms_Batch_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getValidateAttendanceDate() {
        return $this->model->getValidateAttendanceDate();
    }

    function getDeleteAttendance() {
        return $this->model->getDeleteAttendance();
    }

    function getStudentGradeRecords() {
        return $this->view->getStudentGradeRecords();
    }

    function getDeleteStudentGrade() {
        return $this->model->getDeleteStudentGrade();
    }

    function getNewAssessmentSurah() {
        return $this->view->getNewAssessmentSurah();
    }

    function getAddNewIqraAssessmentToSession() {
        return $this->model->getAddNewIqraAssessmentToSession();
    }

    function getAddNewIqraTypeAssessmentToSession() {
        return $this->model->getAddNewIqraTypeAssessmentToSession();
    }

    function getEditAssessmentSurah() {
        return $this->view->getEditAssessmentSurah();
    }

    function getAttendanceRecords() {
        return $this->view->getAttendanceRecords();
    }

    function getEnrollContactFromWaitingListSubmit() {
        return $this->model->getEnrollContactFromWaitingListSubmit();
    }

    function getCancelContactFromWaitingList() {
        return $this->model->getCancelContactFromWaitingList();
    }

    function getUpdateRemarksInStudentGradeIqra() {
        return $this->model->getUpdateRemarksInStudentGradeIqra();
    }

    function getAddStudentToStudentGradeTable() {
        return $this->model->getAddStudentToStudentGradeTable();
    }

    function getStudentGradePortalDisplay() {
        return $this->view->getStudentGradePortalDisplay();
    }

    function getUpdateRemarksInStudentGrade() {
        return $this->model->getUpdateRemarksInStudentGrade();
    }
}