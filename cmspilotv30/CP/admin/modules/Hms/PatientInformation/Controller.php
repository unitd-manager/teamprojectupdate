<?
class CP_Admin_Modules_Hms_PatientInformation_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getUpdateCompanyDetails() {
        return $this->model->getUpdateCompanyDetails();
    }

    function getCompanyNameJSON() {
        return $this->view->getCompanyNameJSON();
    }

}