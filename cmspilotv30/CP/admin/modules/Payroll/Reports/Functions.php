<?
class CP_Admin_Modules_Payroll_Reports_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('payroll_reports');
        $modules->registerModule($modObj, array(
           'actBtnsList' => array()
        ));
    }
}