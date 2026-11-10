<?
class CP_Www_Modules_Edukloud_Task_Functions
{
    //==================================================================//
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_task');
        $modules->registerModule($modObj, array(
        ));
    }

   /**
    *
    */
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('edukloud_task', 'edukloud_classLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'task_class'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'task_class_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
            ,'hasModalChoose'            => 1   
       ));
    //==================================================================//
       $linkObj = $inst->getLinksArrayObj('edukloud_task', 'edukloud_studentLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'task_student'
            ,'displayTitleFieldName'     => "CONCAT_WS(' ', a.first_name, a.last_name)" 
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'task_student_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
            ,'hasModalChoose'            => 1   
            ,'fieldlabel' => array(
                 'Name'
                ,'Class'
                ,'Star Rating'
            )
       ));
    //==================================================================//
       $linkObj = $inst->getLinksArrayObj('edukloud_task', 'edukloud_staffLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'task_staff'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'task_staff_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
            ,'hasModalChoose'            => 1   
       ));
   }    

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_task', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_task', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}
