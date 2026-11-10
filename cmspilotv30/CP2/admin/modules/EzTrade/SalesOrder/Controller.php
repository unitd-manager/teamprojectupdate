<?
class CP_Admin_Modules_EzTrade_SalesOrder_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getRaiseInvoiceList() {
        return $this->view->getRaiseInvoiceList();
    }

    function getRaiseInvoiceListValidation() {
        return $this->model->getRaiseInvoiceListValidation();
    }

    function getRaiseInvoiceValidation() {
        return $this->model->getRaiseInvoiceListValidation();
    }

    function getRaiseInvoice() {
        return $this->model->getRaiseInvoice();
    } 
    
    function getRaisePOList() {
        return $this->view->getRaisePOList();
    }

    function getRaisePOListValidation() {
        return $this->model->getRaisePOListValidation();
    }

    function getRaisePOValidation() {
        return $this->model->getRaisePOListValidation();
    }

    function getRaisePO() {
        return $this->model->getRaisePO();
    } 
    
    function getRaiseShipmentList() {
        return $this->view->getRaiseShipmentList();
    }

    function getRaiseShipmentListValidation() {
        return $this->model->getRaiseShipmentListValidation();
    }

    function getRaiseShipmentValidation() {
        return $this->model->getRaiseShipmentListValidation();
    }

    function getRaiseShipment() {
        return $this->model->getRaiseShipment();
    } 


    
}