<?
class CP_Admin_Modules_EnggCrm_Project_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getDuplicateProject() {
        return $this->model->getDuplicateProject();
    }

    /**
     *
     */
    function getAddHoursFromEmployee() {
        return $this->view->getAddHoursFromEmployee();
    }

    
    /**
     *
     */
    function getProjectHistoryDetails() {
        return $this->view->getProjectHistoryDetails();
    }

    /**
     *
     */
    function getConvertOppToProject() {
        return $this->model->getConvertOppToProject();
    }

    /**
     *
     */
    function getConvertToProject() {
        return $this->model->getConvertToProject();
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
    function getEditForPo() {
        return $this->view->getEditForPo();
    }
    /**
    */
    function getEditForPoSubmit() {
        return $this->model->getEditForPoSubmit();
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

    function getprintmaterialLinkForPdf(){
        return $this->view->getprintmaterialLinkForPdf();
    }

    /**
    */

    function getPrintpurchaseorder(){
        return $this->view->getPrintpurchaseorder();
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
    function getAddHoursFromEmployeeSubmit() {
        return $this->model->getAddHoursFromEmployeeSubmit();
    }

    /**
    */
    function getEmploymentTimeSheetView() {
        return $this->view->getEmploymentTimeSheetView();
    }


    /**
    */

    function getDeleteEmployeePortal(){
        return $this->model->getDeleteEmployeePortal();
    }

    /**
    */
    function getEditEmploymentViewItem(){
        return $this->view->getEditEmploymentViewItem();
    }

    /**
    */
    function getEditEmployeeItemSubmit(){
        return $this->model->getEditEmployeeItemSubmit();
    }

    /**
    */
    function getDeleteEmployeeItem(){
        return $this->model->getDeleteEmployeeItem();
    }

    /**
    */
    function getPrintEmployeeTimesheetForPdf(){
        return $this->view->getPrintEmployeeTimesheetForPdf();
    }

    /**
    */
    function getPrintOverAllEmployeeTimesheetForPdf(){
        return $this->view->getPrintOverAllEmployeeTimesheetForPdf();
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
    function getAddMultipleMaterials(){
        return $this->view->getAddMultipleMaterials();
    }

    /**
    */
    function getAddMaterialRecord(){
        return $this->view->getAddMaterialRecord();
    }

    /**
    */
    function getAddMultipleMaterialsSubmit(){
        return $this->model->getAddMultipleMaterialsSubmit();
    }

    /**
    */
    function getGenerateOrderRecords(){
        return $this->model->getGenerateOrderRecords();
    }

    /**
    */
    function getGenerateOrderManpowerRecords(){
        return $this->model->getGenerateOrderManpowerRecords();
    }


    /**
    */
    function getCancelMaterial(){
        return $this->model->getCancelMaterial();
    }

    /**
    */
    function getAddMultiplePurchaseOrder(){
        return $this->view->getAddMultiplePurchaseOrder();
    }

    /**
    */
    function getAddSinglePurchaseOrderRecord(){
        return $this->view->getAddSinglePurchaseOrderRecord();
    }

    /**
    */
    function getAddMultiplePurchaseOrderSubmit(){
        return $this->model->getAddMultiplePurchaseOrderSubmit();
    }

    /**
    */
    function getCancelPoItem(){
        return $this->model->getCancelPoItem();
    }

    /**
    */
    function getAddHoursProjectEmployee(){
        return $this->view->getAddHoursProjectEmployee();
    }

    /**
    */
    function getAddMultipleTimesheetRecordsSubmit(){
        return $this->model->getAddMultipleTimesheetRecordsSubmit();
    }

    /**
    */
    function getAddDaysRowHeadTimesheet(){
        return $this->view->getAddDaysRowHeadTimesheet();
    }

    /**
    */
    function getEditHoursProjectEmployee(){
        return $this->view->getEditHoursProjectEmployee();
    }

    /**
    */
    function getPrintTimeSheetPdf(){
        return $this->view->getPrintTimeSheetPdf();
    }

    /**
    */
    function getEditForMaterialUsed() {
        return $this->view->getEditForMaterialUsed();
    }
    /**
    */
    function getEditForMaterialUsedSubmit() {
        return $this->model->getEditForMaterialUsedSubmit();
    }
    /**
    */
    function getprintQuoteDisplayPdf() {
        return $this->view->getprintQuoteDisplayPdf();
    }
    /**
    */
    function getAddQuoteColumn() {
        return $this->model->getAddQuoteColumn();
    }
    /**
    */
    function getDeleteQuoteColumn() {
        return $this->model->getDeleteQuoteColumn();
    }

    /**
    */
    function getAddRemoveEmployeeToProject() {
        return $this->model->getAddRemoveEmployeeToProject();
    }

    /**
    */
    function getQsafeFunction() {
        return $this->view->getQsafeFunction();
    }
}