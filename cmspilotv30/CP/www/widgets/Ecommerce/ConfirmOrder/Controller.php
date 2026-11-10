<?
class CP_Www_Widgets_Ecommerce_ConfirmOrder_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $modName          = 'ecommerce_product';
    var $heading          = 'w.ecommerce.confirmOrder.heading';
    var $infoText         = 'w.ecommerce.confirmOrder.info';
    var $continueShopping = 'w.ecommerce.basket.btn.continueShopping';
    var $confirmOrder     = 'w.ecommerce.basket.btn.confirmOrder';
    var $editDetails      = 'w.ecommerce.basket.btn.editDetails';
    var $showBasket       = '';
    var $showPaymentMethods = '';
    /**
     *
     */
    function getEmptyBasket(){
        return $this->model->getEmptyBasket();
    }
}