<?
class CPL_Admin_Widgets_EnggCrm_ProjectWorkOrder_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    /**
    */
    function getEditForWorkOrder(){
        return $this->view->getEditForWorkOrder();
    }

    /**
    */
    function getPrintLinkWOForPdf(){
        return $this->view->getPrintLinkWOForPdf();
    }

    /**
    */
    function getEditWOLineItem() {
        return $this->view->getEditWOLineItem();
    }

    /**
    */
    function getEditForWorkOrderSubmit(){
        return $this->model->getEditForWorkOrderSubmit();
    }

    /**
    */
    function getEditWOLineItemSubmit() {
        return $this->model->getEditWOLineItemSubmit();
    }

    function getAddWOFormSubmit() {
        return $this->model->getAddWOFormSubmit();
    }

    /**
    */
    function getDeleteWOLineItem(){
        return $this->model->getDeleteWOLineItem();
    }

    /**
    */
    function getAddMultipleWOItem(){
        return $this->view->getAddMultipleWOItem();
    }

    /**
    */
    function getAddWOLineItemRecord(){
        return $this->view->getAddWOLineItemRecord();
    }

    /**
    */
    function getAddMultipleWOItemSubmit(){
        return $this->model->getAddMultipleWOItemSubmit();
    }

    function getWorkOrderListView() {
        return $this->view->getWorkOrderListView();
    }
}