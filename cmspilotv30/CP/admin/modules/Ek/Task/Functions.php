<?
class CP_Admin_Modules_Ek_Task_Functions
{

    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ek_task');
        $modules->registerModule($modObj, array(
        ));
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_task', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_task', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    //==================================================================//
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_task', 'ek_studentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'task_student'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('ek_task', 'ek_classLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'task_class'
           ,'showAnchorInLinkPortal' => 0
        ));
    }
}
