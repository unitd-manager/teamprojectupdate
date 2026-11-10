<?
class CP_Admin_Modules_EnggCrm_Opportunity_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getConfirmedQuoteIDJSON() {
        return $this->model->getConfirmedQuoteIDJSON();
    }

    /**
    */
    function getAddQuoteForm() {
        return $this->view->getAddQuoteForm();
    }

    /**
    */
    function getAddQuoteFormSubmit() {
        return $this->model->getAddQuoteFormSubmit();
    }

    /**
    */
    function getAddLineItemForQuoteForm() {
        return $this->view->getAddLineItemForQuoteForm();
    }

    /**
    */
    function getAddLineItemForQuoteFormSubmit() {
        return $this->model->getAddLineItemForQuoteFormSubmit();
    }

    /**
    */
    function getEditLineItem() {
        return $this->view->getEditLineItem();
    }

    /**getEditLineItemSubmit
    */
    function getEditLineItemSubmit() {
        return $this->model->getEditLineItemSubmit();
    }

    /**
    */
    function getEditForQuote() {
        return $this->view->getEditForQuote();
    }
    /**
    */
    function getEditForQuoteSubmit() {
        return $this->model->getEditForQuoteSubmit();
    }

    /**
    */

    function getDeleteLineItem(){
        return $this->model->getDeleteLineItem();
    }

    /**
    */

    function getDeleteAddQuote(){
        return $this->model->getDeleteAddQuote();
    }

    /**
    */

    function getPrintLinkForPdf(){
        return $this->view->getPrintLinkForPdf();
    }

    /**
    */
    function getDuplicateQuote(){
        return $this->model->getDuplicateQuote();
    }

    /**
    */
    function getAddQuoteFormListView(){
        return $this->view->getAddQuoteFormListView();
    }

    /**
    */
    function getAddMultipleLineItem(){
        return $this->view->getAddMultipleLineItem();
    }

    /**
    */
    function getAddLineItemRecord(){
        return $this->view->getAddLineItemRecord();
    }

    /**
    */
    function getAddMultipleLineItemSubmit(){
        return $this->model->getAddMultipleLineItemSubmit();
    }

    /**
    */
    function getAddManpowerQuote(){
        return $this->view->getAddManpowerQuote();
    }

    /**
    */
    function getAddManpowerQuoteFormSubmit(){
        return $this->model->getAddManpowerQuoteFormSubmit();
    }

  /**
    */
    function getPrintLinkForManpowerPdf(){
        return $this->view->getPrintLinkForManpowerPdf();
    }

}