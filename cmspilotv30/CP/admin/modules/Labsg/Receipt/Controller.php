<?
class CP_Admin_Modules_Labsg_Receipt_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    /**
     *
     */
    function getCancelReceiptForm() {
        return $this->view->getCancelReceiptForm();
    }

    /**
     *
     */
    function getCancelReceiptFormSubmit() {
        return $this->model->getCancelReceiptFormSubmit();
    }

    /**
     *
     */
    function getReceiptDetails() {
        return $this->view->getReceiptDetails();
    }
}