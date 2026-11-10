<?
class CP_Admin_Modules_AgileIms_Batch_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getBulkUpdateEvaluate() {
        return $this->view->getBulkUpdateEvaluate();
    }

    function getBulkUpdateEvaluateSubmit() {
        return $this->model->getBulkUpdateEvaluateSubmit();
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

    function getUpdateGrade() {
        return $this->model->getUpdateGrade();
    }

    function getUpdateStudentResult() {
        return $this->model->getUpdateStudentResult();
    }

    function getAlertBatchChangesForm() {
        return $this->view->getAlertBatchChangesForm();
    }

    function getAlertBatchChangesFormSubmit() {
        return $this->model->getAlertBatchChangesFormSubmit();
    }

    /*function getGenerateContractToTeacherForm() {
        return $this->view->getGenerateContractToTeacherForm();
    }*/

    function getGenerateContractToTeacherWord(){
        return $this->model->getGenerateContractToTeacherWord();
    }

    function getCreateInstallmentDates(){
        return $this->view->getCreateInstallmentDates();
    }

    function getCreateInstallmentDatesSubmit() {
        return $this->model->getCreateInstallmentDatesSubmit();
    }

    function getEditInstallmentDate() {
        return $this->view->getEditInstallmentDate();
    }

    function getEditInstallmentDateSubmit() {
        return $this->model->getEditInstallmentDateSubmit();
    }

    function getDeleteInstallmentDate() {
        return $this->model->getDeleteInstallmentDate();
    }

    function getPrintEducationalInformation() {
        return $this->model->getPrintEducationalInformation();
    }

    function getPrintAssessmentSummary() {
        return $this->model->getPrintAssessmentSummary();
    }

    function getPrintMom() {
        return $this->model->getPrintMom();
    }

}