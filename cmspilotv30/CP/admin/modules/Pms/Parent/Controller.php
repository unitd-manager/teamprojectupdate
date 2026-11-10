<?
class CP_Admin_Modules_Pms_Parent_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    /**
     *
     */
    function getGenerateDda() {
        return $this->model->getGenerateDda();
    }

    /**
     *
     */
    function getParentTransferFormSubmitLatest() {
        return $this->model->getParentTransferFormSubmitLatest();
    }

    /**
     *
     */
    function getCheckNoOfStudentForParent() {
        return $this->model->getCheckNoOfStudentForParent();
    }
}