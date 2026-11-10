<?
class CP_Admin_Modules_Payroll_Lib_Functions
{
    //==================================================================//
    function setActionsArray($actArray){
        $cpCfg = Zend_Registry::get('cpCfg');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');
        $cpUrl = Zend_Registry::get('cpUrl');

        //====================== Duplicate Jobinfo ================================//
        $actObj = $actArray->getActionObj('payrollDuplicateJobinfo');
        $actArray->registerAction($actObj, array(
            'title' => 'Duplicate Job Info'
           ,'url' => "javascript:cpm.payroll.jobInformation.duplicate();"
        ));
    }
}