<?
class CP_Admin_Modules_ManPower_Agent_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getContactByCompanyJSON(){
        return $this->model->getContactByCompanyJSON();
    }

    function getCandidateCountrySubmit(){
        return $this->model->getCandidateCountrySubmit();
    }

    function getAgentDocumentSubmit(){
        $this->model->getAgentDocumentSubmit();
    }

    function getCandidatePassSubmit(){
        return $this->model->getCandidatePassSubmit();
    }

    function getPrintAgentContract(){
        return $this->model->getPrintAgentContract();
    }

    /*function getSendAgentLoginDetailsEmail(){
        return $this->model->getSendAgentLoginDetailsEmail();
    }*/
}