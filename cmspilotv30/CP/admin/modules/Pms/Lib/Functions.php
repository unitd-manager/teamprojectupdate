<?
class CP_Admin_Modules_Pms_Lib_Functions
{    
    function setActionsArray($actArray){
        $tv = Zend_Registry::get('tv');

        //====================== Send ================================//
        $actObj = $actArray->getActionObj('sendSms');
        $actArray->registerAction($actObj, array(
            'title' => 'Send SMS'
           ,'url' => "javascript:SmsBroadcast.sendBroadcast('{$tv['topRm']}')"
        ));

        //====================== Send Test ================================//
        $actObj = $actArray->getActionObj('sendTestSms');
        $actArray->registerAction($actObj, array(
            'title' => 'Test SMS'
           ,'url'   => "javascript:SmsBroadcast.sendTestBroadcast('{$tv['topRm']}')"
        ));
    }
}