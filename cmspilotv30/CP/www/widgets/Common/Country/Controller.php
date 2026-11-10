<?
class CP_Www_Widgets_Common_Country_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $ulClass    = '';
    var $title      = '';
    var $showAsMenu = false;
    var $menuTitle  = 'chooseCountry';

    function getCountryIdInSession(){
        $this->fns->setCountryIdInSession();
    }
}