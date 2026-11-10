<?
class CPL_Admin_Widgets_EnggCrm_ProjectDeliveryOrderNote_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    /**
    */

    function getAddMultipleJobLineItem1(){
        return $this->view->getAddMultipleJobLineItem1();

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
    function getAddMultipleLineItem(){
        return $this->view->getAddMultipleLineItem();
    }
     /**
    */
    function getUpdateDeliveryOrderSubmit(){
        return $this->model->getUpdateDeliveryOrderSubmit();
    }

    /**
    */

    function getAddMultipleJobLineItemSubmit1(){
        return $this->model->getAddMultipleJobLineItemSubmit1();

    }

    /**
    */
    function getGenerateOrderRecords(){
        return $this->model->getGenerateOrderRecords();
    }

    /**
    */
    function getCreationModificationQuote() {
        return $this->model->getCreationModificationQuote();
    }

    /**
    */

    function getDeliveryOrderNotePortal() {
        return $this->view->getDeliveryOrderNotePortal();

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
    function getSearchTitle() {
        return $this->model->getSearchTitle();
    }
    /**
    */

    function getEditForJob() {
        return $this->view->getEditForJob();

    }

    /**
    */

    function getEditForJobSubmit() {
        return $this->model->getEditForJobSubmit();

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

    function getAddJobFormSubmit() {
        return $this->model->getAddJobFormSubmit();

    }

    /**
    */

    function getDeleteJobLineItem(){
        return $this->model->getDeleteJobLineItem();

    }


    /**
    */
    function getPrintLinkForPdf(){
        return $this->view->getPrintLinkForPdf();
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

    function getViewJobLog(){
        return $this->view->getViewJobLog();

    }
}