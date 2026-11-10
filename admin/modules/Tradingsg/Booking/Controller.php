<?
class CPL_Admin_Modules_Tradingsg_Booking_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSearchClientName() {
        return $this->model->getSearchClientName();
    }

    function getSearchEmployeeName() {
        return $this->model->getSearchEmployeeName();
    }

    function getNewCustomer() {
        return $this->view->getNewCustomer();
    }

    function getEditCustomer() {
        return $this->view->getEditCustomer();
    }

    function getAddCustomer() {
        return $this->model->getAddCustomer();
    }

    function getEditCustomerSubmit() {
        return $this->model->getEditCustomerSubmit();
    }

    function getClientFields() {
        return $this->view->getClientFields();
    }
}