<?
class CPL_Admin_Widgets_EnggCrm_ProjectDeliveryOrder_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    function getDeliveryOrderPortal() {
        return $this->view->getDeliveryOrderPortal();
    }

    function getCreateDeliveryOrder() {
        return $this->model->getCreateDeliveryOrder();
    }

    function getEditDeliveryOrder() {
        return $this->view->getEditDeliveryOrder();
    }

    function getEditForDOSubmit() {
        return $this->model->getEditForDOSubmit();
    }

    function getPrintDeliveryOrderPdf(){
        return $this->view->getPrintDeliveryOrderPdf();
    }
}