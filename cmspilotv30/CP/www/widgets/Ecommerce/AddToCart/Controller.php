<?
class CP_Www_Widgets_Ecommerce_AddToCart_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $modName  = 'ecommerce_product';
    var $record   = array();
    var $childId  = '';
    var $url      = '/index.php?widget=ecommerce_addToCart&_spAction=add&showHTML=0';
    var $btnLabel = 'w.ecommerce.basket.btn.buyNow';
    var $btnLabelNoStock = 'w.ecommerce.basket.btn.outOfStock';
    var $btnStyle = 'button';
    var $btnStyleNoStock = 'button noStock';

    //========================================================//
    function getAdd() {
        return $this->model->getAdd();
    }

    /**
     *
     */
    function getSave(){
        return $this->model->getSave();
    }
}