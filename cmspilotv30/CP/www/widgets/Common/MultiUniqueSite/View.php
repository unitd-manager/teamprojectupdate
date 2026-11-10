<?
class CP_Www_Widgets_Common_MultiUniqueSite_View extends CP_Common_Lib_WidgetViewAbstract
{

    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $text = "
        ";

        return $text;
    }

}