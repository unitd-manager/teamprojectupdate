<?
class CP_Admin_Modules_EnterpriseIms_Parent_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getParentTransferForm() {
        return $this->view->getParentTransferForm();
    }

    /**
     *
     */
    function getParentTransferFormSubmit() {
        return $this->model->getParentTransferFormSubmit();
    }
}