<?
class CP_Admin_Modules_ManPower_Opportunity_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getConfirmedQuoteIDJSON() {
        return $this->model->getConfirmedQuoteIDJSON();
    }

    /**
     *
     */
    function getConvertOppToProject() {
        return $this->model->getConvertOppToProject();
    }

    /**
     *
     */
    function getPopulateCandidatePassport() {
        return $this->model->getPopulateCandidatePassport();
    }

    /**
     *
     */
    function getPopulateRelatedAgent() {
        return $this->model->getPopulateRelatedAgent();
    }

    /**
     *
     */
    function getSendMailToAgentForm() {
        return $this->view->getSendMailToAgentForm();
    }
    /**
     *
     */
    function getSendMailToAgent() {
        return $this->model->getSendMailToAgent();
    }
    /**
     *
     */
    function getSendMailToAgentFormSubmit() {
        return $this->model->getSendMailToAgentFormSubmit();
    }
    /**
     *
     */
    function getSendMessageInBackground() {
        return $this->model->getSendMessageInBackground();
    }

    function getPrintCandidateContract(){
        $this->model->getPrintCandidateContract();
    }

    function getPrintCandidateResumeAsPdf() {
        $modObj = getCPModuleObj('manPower_candidate');
        return $modObj->model->getPrintCandidateResumeAsPdf();
    }

    function getDuplicate(){
        $this->model->getDuplicate();
    }

    function getShowCandidateDetails(){
        return $this->view->getShowCandidateDetails();
    }

    function getOpportunityCandidateDisplay(){
        return $this->view->getOpportunityCandidateDisplay();
    }

    function getCandidatMembers(){
        return $this->view->getCandidatMembers();
    }

    function getAddCandidate() {
        $modObj = getCPModuleObj('manPower_candidateLink');
        return $modObj->view->getAddCandidate();
    }

    function getAddCandidateFormSubmit() {
        $modObj = getCPModuleObj('manPower_candidateLink');
        return $modObj->model->getAddCandidateFormSubmit();
    }

    function getDeleteCandidateRecord() {
        $modObj = getCPModuleObj('manPower_candidateLink');
        return $modObj->model->getDeleteCandidateRecord();
    }

    function getEditCandidate() {
        $modObj = getCPModuleObj('manPower_candidateLink');
        return $modObj->view->getEditCandidate();
    }

    function getEditCandidateFormSubmit() {
        $modObj = getCPModuleObj('manPower_candidateLink');
        return $modObj->model->getEditCandidateFormSubmit();
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
}