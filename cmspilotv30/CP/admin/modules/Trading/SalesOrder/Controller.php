<?
class CP_Admin_Modules_Trading_SalesOrder_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getChooseRFQFormForLine() {
        return $this->model->getChooseRFQFormForLine();
    }
    function getChooseRFQForLine() {
        return $this->model->getChooseRFQForLine();
    }

    //-----------------------//
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

    //-----------------------//
    function getRaiseInvoiceListInventory() {
        return $this->view->getRaiseInvoiceListInventory();
    }

    function getRaiseInvoiceListInventoryValidation() {
        return $this->model->getRaiseInvoiceListInventoryValidation();
    }

    function getRaiseInvoiceInventoryValidation() {
        return $this->model->getRaiseInvoiceInventoryValidation();
    }

    function getRaiseInvoiceInventory() {
        return $this->model->getRaiseInvoiceInventory();
    }

    //-----------------------//
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

    //-----------------------//
    function getEditInventoryForm() {
        return $this->view->getEditInventoryForm();
    }
    function getSaveInventory() {
        return $this->model->getSaveInventory();
    }

    //-----------------------//
    function getUpdateSellPriceFromQuote() {
        return $this->model->getUpdateSellPriceFromQuote();
    }

    //-----------------------//
    function getValidateEditProductItemLink() {
        return $this->model->getValidateEditProductItemLink();
    }

}