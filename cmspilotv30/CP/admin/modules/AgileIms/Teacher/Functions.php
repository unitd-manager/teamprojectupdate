<?
class CP_Admin_Modules_AgileIms_Teacher_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_teacher');
        $modules->registerModule($modObj, array(
            'title'       => 'Teacher'
           ,'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('agileIms_teacher', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('agileIms_teacher', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

   /**
    *
    */
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('agileIms_teacher', 'agileIms_courseLink');
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
       $linkObj = $inst->getLinksArrayObj('agileIms_teacher', 'agileIms_batchLink');
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