<?
class CP_Admin_Modules_ManPower_Candidate_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getContactByCompanyJSON(){
        return $this->model->getContactByCompanyJSON();
    }

    function getCandidateDocumentSubmit(){
        $this->model->getCandidateDocumentSubmit();
    }

    function getPrintCandidateDocument(){
        $this->model->getPrintCandidateDocument();
    }

    function getPrintCandidateResumeAsPdf(){
        $this->model->getPrintCandidateResumeAsPdf();
    }

    function getPrintNoDuePdf(){
        $this->model->getPrintNoDuePdf();
    }

    function getPrintNoDueWord(){
        $this->model->getPrintNoDueWord();
    }

    function getPrintDeclarationPdf(){
        $this->model->getPrintDeclarationPdf();
    }

    function getPrintDeclarationWord(){
        $this->model->getPrintDeclarationWord();
    }

    function getCandidateCommentForm(){
        return $this->view->getCandidateCommentForm();
    }

    function getAddCommentFormSubmit(){
        return $this->model->getAddCommentFormSubmit();
    }

    function getViewComment(){
        return $this->view->getViewComment();
    }

    function getSendMessageToStaffByAgent(){
        return $this->view->getSendMessageToStaffByAgent();
    }

    function getSendMessageToStaffByAgentSubmit(){
        return $this->model->getSendMessageToStaffByAgentSubmit();
    }

    function getPrintPdfByDropDown(){
        return $this->model->getPrintPdfByDropDown();
    }

    function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

    function getValueByValuelistJSON() {
        return $this->model->getValueByValuelistJSON();
    }

    function getAddPositionCandidate() {
        return $this->model->getAddPositionCandidate();
    }

    function getDeletePositionCandidate() {
        return $this->model->getDeletePositionCandidate();
    }

    function getConvertDocumentsIntoText(){
        return $this->view->getConvertDocumentsIntoText();
    }
}