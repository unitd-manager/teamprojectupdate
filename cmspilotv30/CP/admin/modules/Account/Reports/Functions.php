<?
class CP_Admin_Modules_Account_Reports_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('account_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array('export')
        ));
    }
}