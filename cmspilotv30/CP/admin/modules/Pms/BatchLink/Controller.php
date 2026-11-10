<?
class CP_Admin_Modules_Pms_BatchLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    /**
     *
     */
    function getBatchValueForDropDown() {
        return $this->model->getBatchValueForDropDown();
    }

    /**
     *
     */
    function getBatchValueForDropDownPvt() {
        return $this->model->getBatchValueForDropDownPvt();
    }

    /**
     *
     */
    function getBatchValueForDropDownReport() {
        return $this->model->getBatchValueForDropDownReport();
    }

    /**
     *
     */
    function getBatchValueForDropDownFromBatchTransfer() {
        return $this->model->getBatchValueForDropDownFromBatchTransfer();
    }
}