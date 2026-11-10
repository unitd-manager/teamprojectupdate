<?
class CP_Www_Widgets_LawNews_EventRegister_Controller extends CP_Common_Lib_WidgetControllerAbstract
{

    var $module           = '';
    var $event_id         = '';
    var $formAction       = '/index.php?widget=lawNews_eventRegister&_spAction=add&showHTML=0';
    var $returnUrl        = '';
    var $attendeeHeading  = 'm.lawNews.eventRegister.form.attendee.heading';
    var $showCaptcha      = true;

    var $showCurrencySelection = false;
    var $currencyArray    =  array('usd' => 'USD');
    var $currency         = 'usd';
    var $currencyDisplay  = 'US$';

    var $mode             = 'edit';
    var $maxQuantity      = 10;
    var $showQtyDropDown  = false;
    var $selectMultipeEventItem = true;

    var $unitPriceFld     = 'price';
    var $orderBy          = 'ei.sort_order ASC, ei.title';

    /**
     * called on currency change by ajax
     */
    function getEventItem(){
        return $this->view->getEventItem();
    }

}