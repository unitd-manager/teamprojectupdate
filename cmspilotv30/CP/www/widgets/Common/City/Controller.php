<?
class CP_Www_Widgets_Common_City_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $ulClass    = '';
    var $title      = '';
    var $showAsMenu = false;
    var $menuTitle  = 'chooseCity';

    function getCityIdInSession(){
        $this->fns->setCityIdInSession();
    }
}