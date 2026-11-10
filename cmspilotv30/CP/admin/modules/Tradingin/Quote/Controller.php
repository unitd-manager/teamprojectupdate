<?
class CP_Admin_Modules_Tradingin_Quote_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    function getRaiseSOList() {
        return $this->view->getRaiseSOList();
    }

    function getRaiseSOListValidation() {
        return $this->model->getRaiseSOListValidation();
    }

    function getRaiseSOValidation() {
        return $this->model->getRaiseSOListValidation();
    }

    function getRaiseSO() {
        return $this->model->getRaiseSO();
    }

    function getDuplicateQuote() {
        return $this->model->getDuplicateQuote();
    }

    function getUpdateProductLineItems() {
        return $this->model->getUpdateProductLineItems();
    }

    function getUpdateSellingLineItems() {
        return $this->model->getUpdateSellingLineItems();
    }

    function getUpdateQty() {
        return $this->model->getUpdateQty();
    }

    function getPrintQuoteExcel() {
        return $this->view->getPrintQuoteExcel();
    }

    function getPrintQuoteExcelBasic() {
        return $this->view->getPrintQuoteExcelBasic();
    }

    function getPrintQuoteGeneralTrading() {
        return $this->view->getPrintQuoteGeneralTrading();
    }

    function getPrintQuoteExcelGeneral() {
        return $this->view->getPrintQuoteExcelGeneral();
    }

    function getRaisePurchaseOrder() {
        return $this->model->getRaisePurchaseOrder();
    }

    function getRaiseInvoice() {
        return $this->model->getRaiseInvoice();
    }

    function getPrintPOExcel() {
        return $this->view->getPrintPOExcel();
    }

    function getPrintPurchaseOrder() {
        return $this->view->getPrintPurchaseOrder();
    }

    function getPrintPurchaseOrderWithPrice() {
        return $this->view->getPrintPurchaseOrderWithPrice();
    }

    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getSupplierJsonByProductId() {
        return $this->model->getSupplierJsonByProductId();
    }

    function getUpdateMarkupByGroupForm() {
        return $this->view->getUpdateMarkupByGroupForm();
    }

    function getUpdateDiscountByGroupForm() {
        return $this->view->getUpdateDiscountByGroupForm();
    }

    function getUpdateDiscountByGroupFormSubmit() {
        return $this->model->getUpdateDiscountByGroupFormSubmit();
    }

    function getUpdateDiscountByGroupFormValidate() {
        return $this->model->getUpdateDiscountByGroupFormValidate();
    }

    function getUpdateMarkupByGroupFormSubmit() {
        return $this->model->getUpdateMarkupByGroupFormSubmit();
    }

    function getUpdateMarkupByCategoryForm() {
        return $this->view->getUpdateMarkupByCategoryForm();
    }

    function getUpdateMarkupByCategoryFormSubmit() {
        return $this->model->getUpdateMarkupByCategoryFormSubmit();
    }

    function getUpdateProfit() {
        return $this->model->getUpdateProfit();
    }

    function getUpdateDiscountForm() {
        return $this->view->getUpdateDiscountForm();
    }

    function getUpdateDiscountFormValidate() {
        return $this->model->getUpdateDiscountFormValidate();
    }

    function getUpdateDiscountFormSubmit() {
        return $this->model->getUpdateDiscountFormSubmit();
    }

    function getPreviousOrderForClient() {
        return $this->view->getPreviousOrderForClient();
    }

    function getRaiseGeneralQuotation() {
        return $this->model->getRaiseGeneralQuotation();
    }

    function getDeleteProductsLinked() {
        return $this->model->getDeleteProductsLinked();
    }

    function getGenerateBulkProduct() {
        return $this->model->getGenerateBulkProduct();
    }

    function getAddProduct() {
        return $this->model->getAddProduct();
    }

    function getGenerateProductFormSubmit() {
        return $this->model->getGenerateProductFormSubmit();
    }

    function getUpdateClientId() {
        return $this->model->getUpdateClientId();
    }

    function getUpdateProductLineItemsForShipChannelising() {
        return $this->model->getUpdateProductLineItemsForShipChannelising();
    }

    function getUpdateProductLineItemsForGeneralTrading() {
        return $this->model->getUpdateProductLineItemsForGeneralTrading();
    }

    function getCheckboxForDeleteProductsLinked() {
        return $this->model->getCheckboxForDeleteProductsLinked();
    }

    function getDeleteCheckedProductsLinked() {
        return $this->model->getDeleteCheckedProductsLinked();
    }

    function getUpdateGeneralQuotation() {
        return $this->model->getUpdateGeneralQuotation();
    }

    function getProfitTypeValue() {
        return $this->model->getProfitTypeValue();
    }

    function getPrintExportAsPdf() {
        return $this->model->getPrintExportAsPdf();
    }

    function getAddNotePo() {
        return $this->view->getAddNotePo();
    }

    function getAddNoteFormSubmit() {
        return $this->model->getAddNoteFormSubmit();
    }
}