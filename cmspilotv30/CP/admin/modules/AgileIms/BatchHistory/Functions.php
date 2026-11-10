<?
class CP_Admin_Modules_AgileIms_BatchHistory_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_batchHistory');
        $modObj['tableName'] = 'course_contact';
        $modObj['keyField']  = 'course_contact_id';
        $modules->registerModule($modObj, array(
            'title'       => 'Batch History'
           ,'actBtnsList' => array()
           ,'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('agileIms_batchHistory', 'picture', 'image');
        $mediaObj = $mediaArr->getMediaObj('agileIms_batchHistory', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
   /**
    *
    */
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('agileIms_batch', 'agileIms_contactLink');

       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'course_contact'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'course_contact_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'hasModalChoose'            => false
            ,'fieldlabel'                => array('First Name', 'Last Name', 'Email', 'Phone')
            ,'showAnchorInLinkPortal'    => false
            ,'additionalFieldsArray' => array(
                'c.first_name'
                ,'c.last_name'
                ,'c.email'
                ,'c.phone'
            )
            ,'hasGridEdit'               => 0
       ));

   }    
}