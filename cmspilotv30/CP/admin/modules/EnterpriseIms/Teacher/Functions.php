<?
class CP_Admin_Modules_EnterpriseIms_Teacher_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_teacher');
        $modules->registerModule($modObj, array(
            'title' => 'Teacher'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_teacher', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_teacher', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }

   /**
    *
    */
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('enterpriseIms_teacher', 'enterpriseIms_courseLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'teacher_course'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'teacher_course_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));

        //------------------------------------------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('enterpriseIms_teacher', 'enterpriseIms_batchLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'batch'
            ,'linkingType'               => 'grid'
            ,'historyTableKeyField'      => 'batch_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalNew'              => 0
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'fieldlabel'                => array('Title', 'Venue', 'Start Date', 'End Date', 'Start Time', 'End Time', 'No of attendee')
            ,'showAnchorInLinkPortal'    => false
            ,'additionalFieldsArray' => array(
                 'b.title'
                ,'b.venue'
                ,'b.start_date'
                ,'b.end_date'
                ,'b.start_time'
                ,'b.end_time'
                ,'(SELECT count(*) 
                  FROM course_contact
                  WHERE batch_id = b.batch_id
                ) AS attendee_count
                '
            )
            ,'hasGridEdit'               => 0
       ));
   }    
}