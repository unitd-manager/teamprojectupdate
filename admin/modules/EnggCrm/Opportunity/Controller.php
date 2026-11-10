<?
class CPL_Admin_Modules_EnggCrm_Opportunity_Controller extends CP_Admin_Modules_EnggCrm_Opportunity_Controller
{
    /**
     *
     */
    function getConfirmedQuoteIDJSON() {
        return $this->model->getConfirmedQuoteIDJSON();
    }

    /**
     *
     */
    function getAddMultipleMaterialsSubmit() {
        return $this->model->getAddMultipleMaterialsSubmit();
    }

    /**
    */
    function getAddQuoteForm() {
        return $this->view->getAddQuoteForm();
    }

     /**
    */
    function getprintLinkForPdfOld() {
        return $this->view->getprintLinkForPdfOld();
    }

    /**
    */
    function getAddMultipleMaterials() {
        return $this->view->getAddMultipleMaterials();
    }

    /**
    */
    function getProjectMaintenanacePortal() {
        return $this->view->getProjectMaintenanacePortal();
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

    function getPrintLinkForPdfNote(){
        return $this->view->getPrintLinkForPdfNote();
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

    /**
    */
    function getNewCompanyJSON(){
        return $this->model->getNewCompanyJSON();
    }

    /**
    */
    function getAddLineDrawingItemRecord(){
        return $this->view->getAddLineDrawingItemRecord();
    }

    /**
    */
    function getPrintDrawingQuotePdf(){
        return $this->view->getPrintDrawingQuotePdf();
    }

    /**
    */
    function getViewQuoteLog(){
        return $this->view->getViewQuoteLog();
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
}