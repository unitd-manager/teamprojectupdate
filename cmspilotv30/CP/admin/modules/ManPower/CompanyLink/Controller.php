<?
class CP_Admin_Modules_ManPower_CompanyLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    /**
     *
     */
    function getAddNewCompanyForCallRegistryForm() {
        return $this->view->getAddNewCompanyForCallRegistryForm();
    }

    /**
     *
     */
    function getAddNewCompanyForCallRegistryFormSubmit() {
        return $this->model->getAddNewCompanyForCallRegistryFormSubmit();
    }
}