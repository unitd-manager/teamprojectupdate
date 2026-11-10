<?
class CP_Www_Widgets_Ecommerce_Orders_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $modName              = 'ecommerce_product';
    var $heading              = 'w.ecommerce.orders.heading';
    var $titleLbl             = 'w.ecommerce.orders.lbl.title';
    var $totalLbl             = 'w.ecommerce.orders.lbl.total';
    var $orderBy              = 'o.order_id';
    var $currency             = '';
    var $currencyDisplay      = '';
    var $decimals             = '';
    var $keyFldName           = '';
    var $mode                 = 'edit';
    var $unitPriceFld         = 'price';
    var $showNotesPerItem     = false;

    /**
     *
     */
    function getUpdateAmountPaid(){
        return $this->model->getUpdateAmountPaid();
    }

    /**
     *
     */
    function getPrintOrder(){
        return $this->view->getPrintOrder();
    }
}