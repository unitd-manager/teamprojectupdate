<?
class CPL_Admin_Widgets_EnggCrm_StatementofAccountsReport_Functions extends CP_Admin_Widgets_EnggCrm_StatementofAccountsReport_Functions
{
    function setWidgetArray($widgets){
        $cpCfg 	   = Zend_Registry::get('cpCfg');
        $widgetObj = $widgets->getWidgetObj('enggCrm_statementofAccountsReport');

        $widgets->registerWidget($widgetObj, array(
        	'title' => 'Statement of Accounts Report'
        ));
    }
}
