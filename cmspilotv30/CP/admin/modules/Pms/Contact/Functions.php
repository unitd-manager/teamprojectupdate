<?
class CP_Admin_Modules_Pms_Contact_Functions extends CP_Common_Modules_Pms_Contact_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_contact');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media')
           ,'actBtnsList'   => array('export')
           ,'hasMultiLang'  => 1
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
    */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_contact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_contact', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_contact', 'relatedPicture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
    */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_contact', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'interest_contact'
            ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_contact', 'pms_parentLink');
        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'parent_contact'
           ,'showAnchorInLinkPortal' => 0
           ,'displayTitleFieldName' => 'a.first_name'
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_contact', 'pms_courseLink');

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
        $linkObj = $inst->getLinksArrayObj('pms_contact', 'pms_insuranceLink');

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
		
        /* DO NOT DELETE - SYED
        $courseArr = $fn->getDdDataAsArray('pms_course');
        $batchArr = $fn->getDdDataAsArray('pms_batch');
		$inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'course_contact'
            ,'historyTableKeyField'  => 'course_contact_id'
            ,'linkingType'       => 'grid'
            ,'keyField'          => 'course_contact_id'
            ,'showAnchorInLinkPortal' => 0
            ,'hasPortalEdit'       => 0
            ,'hasPortalDelete'     => 1
            ,'fieldlabel'          => array('Course', 'Batch', 'Invoice')
            ,'gridFieldTypeArray'  => array(
                  array('type' => 'dropdown', 'ddArr' => $courseArr)
                 ,array('type' => 'dropdown', 'ddArr' => $batchArr)
            )
            ,'fieldClassArray' => array('w100', 'w100')
        ));
		*/


        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pms_contact', 'webBasic_contentLink');
        
        $inst->registerLinksArray($linkObj, array(
            'historyTableName' => 'contact_content'
           ,'showAnchorInLinkPortal' => 0
        ));
    }
}