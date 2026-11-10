<?
class CP_Common_Modules_Edukite_Task_Functions
{

    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukite_task');
        $modules->registerModule($modObj, array(
        ));
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukite_task', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukite_task', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

    //==================================================================//
    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_task', 'edukite_studentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'task_student'
           ,'displayTitleFieldName'  => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showAnchorInLinkPortal' => 0
        ));
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('edukite_task', 'edukite_classLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'task_class'
           ,'showAnchorInLinkPortal' => 0
        ));
    }
}
