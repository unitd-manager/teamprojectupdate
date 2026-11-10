<?
class CP_Www_Widgets_Ecommerce_Basket_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $modName           = 'ecommerce_product';
    var $heading           = 'w.ecommerce.basket.heading';
    var $infoText          = 'w.ecommerce.basket.info';
    var $continueShopping  = 'w.ecommerce.basket.btn.continueShopping';
    var $emptyCart         = 'w.ecommerce.basket.btn.emptyCart';
    var $checkout          = 'w.ecommerce.basket.btn.checkout';
    var $emptyCartInfo     = 'w.ecommerce.basket.emptyCart.info';
    var $currency          = '';
    var $currencyDisplay   = '';
    var $decimals          = '';
    var $mode              = 'edit';
    var $orderId           = '';
    var $hasShippingCharge = '';
    var $hasPromoCode      = '';
    var $hasDiscount       = '';
    var $showPicture       = '';
    var $totalTableCols    = 5;
    var $showNotesPerItem  = '';
    var $hasItemsInChildTable = ''; // product items
    var $showCtryDdInBktForShipCalc = ''; // product items

    /**
     *
     */
    function getEmptyBasket(){
        return $this->model->getEmptyBasket();
    }

    /**
     *
     */
    function getChangeShippingCountry(){
        return $this->model->getChangeShippingCountry();
    }


    /**
     *
     */
    function getChangeShippingCharge(){
        return $this->model->getChangeShippingCharge();
    }

    /**
     *
     */
    function getEnterPromoCode(){
        return $this->view->getEnterPromoCode();
    }

    /**
     *
     */
    function getAddPromoCode(){
        return $this->model->getAddPromoCode();
    }

    /**
     *
     */
    function getRemovePromoCode(){
        return $this->model->getRemovePromoCode();
    }

    /**
     *
     */
    function isBasketEmpty(){
        return $this->model->isBasketEmpty();
    }

}