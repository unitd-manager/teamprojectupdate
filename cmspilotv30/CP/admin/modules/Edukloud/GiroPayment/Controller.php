<?
class CP_Admin_Modules_Edukloud_GiroPayment_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
   /**
     *
     */
    function getGiroPaymentSubmit() {
        return $this->model->getGiroPaymentSubmit();
    } 
       
    /**
     *
     */
    function getGenerateDBSTxtFile() {
        return $this->model->getGenerateDBSTxtFile();
    }    

    /**
     *
     */
    function getSetInvoiceCodeForSession(){
        return $this->view->getSetInvoiceCodeForSession();
    }

    /**
     *
     */
    function getSetInvoiceCodeToPrintForSession(){
        return $this->view->getSetInvoiceCodeToPrintForSession();
    }

    /**
     *
     */
    function  getDisplayGiroFailures(){
        return $this->view-> getDisplayGiroFailures();
    }

    /**
     *
     */
    function  getPrintGiroFailures(){
        return $this->view-> getPrintGiroFailures();
    }

    /**
     *
     */
    function  getPrintAllDueInvoiceForMonth(){
        return $this->view-> getPrintAllDueInvoiceForMonth();
    }
}