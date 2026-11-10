<?
class CP_Admin_Modules_EnterpriseIms_PaymentSummary_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSetInvoiceCodeForSession(){
        return $this->view->getSetInvoiceCodeForSession();
    }

    /**
     *
     */
    function getMakePaymentForParentForm(){
        return $this->view->getMakePaymentForParentForm();
    }

    /**
     *
     */
    function getGenerateReceiptForParentFormSubmit(){
        return $this->model->getGenerateReceiptForParentFormSubmit();
    }
}