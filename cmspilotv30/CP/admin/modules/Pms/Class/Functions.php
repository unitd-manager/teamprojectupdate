<?
class CP_Admin_Modules_Pms_Class_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_class');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_class', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }

   /**
    */
   function setLinksArray($inst) {

       $linkObj = $inst->getLinksArrayObj('pms_class', 'pms_contactLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'student_class'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'student_class_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));
   }
}