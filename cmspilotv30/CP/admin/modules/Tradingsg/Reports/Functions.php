<?
class CP_Admin_Modules_Tradingsg_Reports_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('tradingsg_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}