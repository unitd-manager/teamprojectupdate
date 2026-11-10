<?
class CPL_Admin_Modules_EnggCrm_Project_Controller extends CP_Admin_Modules_EnggCrm_Project_Controller
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
    function getConvertToProject() {
        return $this->model->getConvertToProject();
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
    function getPrintTimesheet1Pdf(){
        return $this->view->getPrintTimesheet1Pdf();
    }

    /**
    */
    function getPrintTimesheet2Pdf(){
        return $this->view->getPrintTimesheet2Pdf();
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
    function getGenerateOrderManpowerRecords(){
        return $this->model->getGenerateOrderManpowerRecords();
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

    /**
    */
    function getUpdateEmployeeCategoryType() {
        return $this->model->getUpdateEmployeeCategoryType();
    }

    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getInvoicePortalDisplay() {
        $modObj = getCPModuleObj('enggCrm_order');
        return $modObj->view->getInvoicePortalDisplay();
    }

    function getReceiptPortalDisplay() {
        $modObj = getCPModuleObj('enggCrm_order');
        return $modObj->view->getReceiptPortalDisplay();
    }

    function getInvoiceReceiptPortalDetails() {
        return $this->view->getInvoiceReceiptPortalDetails();
    }

    function getAddTransferProjectRowRecord() {
        return $this->model->getAddTransferProjectRowRecord();
    }

    function getCreationModificationDetailsPopup() {
        return $this->model->getCreationModificationDetailsPopup();
    }
}