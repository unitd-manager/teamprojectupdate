<?
class CP_Www_Widgets_Ecommerce_ProductList_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $modName              = 'ecommerce_product';
    var $heading              = 'w.ecommerce.productList.heading';
    var $infoText             = 'w.ecommerce.productList.info'; // a short text above the table
    var $showHeading          = true;
    var $titleLbl             = 'w.ecommerce.basket.lbl.title';
    var $qtyLbl               = 'w.ecommerce.basket.lbl.quantity';
    var $priceLbl             = 'w.ecommerce.basket.lbl.unitPrices';
    var $totalLbl             = 'w.ecommerce.basket.lbl.total';
    var $grossTotalLbl        = 'w.ecommerce.basket.lbl.grossTotal';
    var $orderBy              = 'p.sort_order ASC, p.title';
    var $currency             = '';
    var $currencyDisplay      = '';
    var $decimals             = '';
    var $keyFldName           = '';
    var $mode                 = 'edit';
    var $selection            = 'checkbox'; //checkbox, radio 
    var $maxQuantity          = 100;
    var $showQtyDropDown      = true;
    var $unitPriceFld         = 'price';
    var $showNotesPerItem     = false;
    var $notesPreText         = 'w.ecommerce.productList.fld.notes.preText';
    var $notesLbl             = 'w.ecommerce.productList.fld.notes.lbl';
}