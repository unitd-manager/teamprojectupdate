<?
class CP_Admin_Modules_EnterpriseIms_BatchLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    function getBatchValueForDropDown() {
        return $this->model->getBatchValueForDropDown();
    }

    /**
     *
     */
    function getBatchValueForDropDownPvt() {
        return $this->model->getBatchValueForDropDownPvt();
    }
}