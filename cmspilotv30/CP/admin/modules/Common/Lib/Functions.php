<?
class CP_Admin_Modules_Common_Lib_Functions
{
    function setActionsArray($actArray){
        $tv = Zend_Registry::get('tv');

        //====================== Send ================================//
        $actObj = $actArray->getActionObj('send');
        $actArray->registerAction($actObj, array(
            'url' => "javascript:Broadcast.sendBroadcast('{$tv['topRm']}')"
        ));

        //====================== Send Test ================================//
        $actObj = $actArray->getActionObj('sendTest');
        $actArray->registerAction($actObj, array(
            'title' => 'Test'
           ,'url'   => "javascript:Broadcast.sendTestBroadcast('{$tv['topRm']}')"
        ));
    }
}
