<?
class CP_Www_Widgets_Common_Site_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $ulClass    = '';
    var $title      = '';
    var $showAsMenu = false;
    var $menuTitle  = 'w.common.site.menuTitle';

    function getSiteIdInSession(){
        $this->fns->setSiteIdInSession();
    }
}