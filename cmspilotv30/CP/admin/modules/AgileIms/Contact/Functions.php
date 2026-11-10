<?
class CP_Admin_Modules_AgileIms_Contact_Functions extends CP_Common_Modules_AgileIms_Contact_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_contact');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media')
           ,'actBtnsList'   => array('new')
           ,'hasMultiLang'  => 1
           ,'titleField'    => "first_name"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('agileIms_contact', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('agileIms_contact', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('agileIms_contact', 'relatedPicture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
    */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('agileIms_contact', 'common_interestLink');
        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'interest_contact'
            ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('agileIms_contact', 'agileIms_parentLink');        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'parent_contact'
           ,'showAnchorInLinkPortal' => 0
           ,'displayTitleFieldName' => 'a.first_name'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('agileIms_contact', 'agileIms_courseLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'course_contact'
           ,'displayTitleFieldName' => "c.title"
           ,'historyTableKeyField'  => 'course_contact_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'showAnchorInLinkPortal'=> 0
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 600
           ,'portalDialogHeight'    => 500
           ,'fieldlabel'            => array('Course'
                                            , 'Subsidy'
                                            , 'Discount'
                                            , 'Batch'
                                            , 'Enrollment Year'
                                            , 'Invoice Link'
                                       )
        ));  

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('agileIms_contact', 'agileIms_insuranceLink');
        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'student_insurance'
           ,'historyTableKeyField'  => 'student_insurance_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'showAnchorInLinkPortal'=> 0
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 600
           ,'portalDialogHeight'    => 500
           ,'fieldlabel'            => array('Course Name'
                                            ,'Insurance Company'
                                            ,'Certificate No'
                                            , 'Premium Amount'
                                            , 'Start Date'
                                            , 'End Date'
                                       )
        ));  
		
        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('agileIms_contact', 'webBasic_contentLink');        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'contact_content'
           ,'showAnchorInLinkPortal' => 0
        ));
    }
}