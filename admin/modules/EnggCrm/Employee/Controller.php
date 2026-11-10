<?
class CPL_Admin_Modules_EnggCrm_Employee_Controller extends CP_Admin_Modules_EnggCrm_Employee_Controller
{
    function getContactByCompanyJSON(){
        return $this->model->getContactByCompanyJSON();
    }

    function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

    function getValueByValuelistJSON() {
        return $this->model->getValueByValuelistJSON();
    }

    function getPrintEmployeeAttachmentPdf() {
        return $this->view->getPrintEmployeeAttachmentPdf();
    }

    function getEmployeeCategoryPortal() {
        return $this->view->getEmployeeCategoryPortal();
    }

    function getAddEmployeeCategory() {
        return $this->view->getAddEmployeeCategory();
    }

    function getEmployeeCategorySubmit() {
        return $this->model->getEmployeeCategorySubmit();
    }

    function getDeleteEmployeeCategory() {
        return $this->model->getDeleteEmployeeCategory();
    }
}