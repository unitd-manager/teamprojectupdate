<?
class CPL_Admin_Widgets_EnggCrm_ProjectQuoteRenewal_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
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
    function getGenerateOrderRecords(){
        return $this->model->getGenerateOrderRecords();
    }

    /**
    */
    function getCreationModificationQuote3() {
        return $this->model->getCreationModificationQuote3();
    }

    /**
    */
    function getAddQuoteFormListView() {
        return $this->view->getAddQuoteFormListView();
    }

    /**
    */
    function getConfirmedQuoteDetails() {
        return $this->view->getConfirmedQuoteDetails();
    }

    /**
    */
    function getAddLineDrawingItemRecord(){
        return $this->view->getAddLineDrawingItemRecord();
    }

    /**
     *
     */
    function getConvertOppToProject() {
        return $this->model->getConvertOppToProject();
    }

    /**
    */
    function getAddQuoteForm() {
        return $this->view->getAddQuoteForm();
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

    /**
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
    function getDeleteAddQuote(){
        return $this->model->getDeleteAddQuote();
    }

    /**
    */
    function getDuplicateQuote(){
        return $this->model->getDuplicateQuote();
    }

    /**
    */
    function getAddQuoteFormSubmit() {
        return $this->model->getAddQuoteFormSubmit();
    }

    /**
    */
    function getDeleteLineItem(){
        return $this->model->getDeleteLineItem();
    }


    /**
    */
    function getPrintLinkForPdfNote(){
        return $this->view->getPrintLinkForPdfNote();
    }

    /**
    */
    function getPrintDrawingQuotePdf(){
        return $this->view->getPrintDrawingQuotePdf();
    }

    /**
    */
    function getPrintLinkForLogPdf(){
        return $this->view->getPrintLinkForLogPdf();
    }

    /**
    */
    function getPrintDrawingQuoteLogPdf(){
        return $this->view->getPrintDrawingQuoteLogPdf();
    }

    /**
    */
    function getViewQuoteLog1(){
        return $this->view->getViewQuoteLog1();
    }
}