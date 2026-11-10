<?
class CP_Www_Widgets_Common_MultiUniqueSite_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSiteRecord() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $row = $fn->getRecordRowByID('site', 'site_id', $cpCfg['cp.site_id']);

        return $row;
    }
}
