<?
class CP_Www_Widgets_Event_EventItem_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $eventId          = '';
    var $orderBy          = 'ei.sort_order ASC, ei.title';
    var $currency         = 'USD';
    var $currencyDisplay  = 'US$';
    var $mode             = 'edit';
    var $selectMultipeEventItem  = false;
    var $maxQuantity      = 10;
    var $showQtyDropDown  = false;
    var $unitPriceFld     = 'price';
    var $defaultEventItemId  = '';
}