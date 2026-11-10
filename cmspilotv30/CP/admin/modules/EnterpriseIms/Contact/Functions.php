<?
class CP_Admin_Modules_EnterpriseIms_Contact_Functions extends CP_Common_Modules_EnterpriseIms_Contact_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_contact');
        $modules->registerModule($modObj, array(
            'relatedTables' => array('media')
           ,'actBtnsList'   => array('new', 'export', 'import')
           ,'hasMultiLang'  => 1
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_contact', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_contact', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
    */
    function setLinksArray($inst) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enterpriseIms_contact', 'enterpriseIms_parentLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'parent_contact'
           ,'displayTitleFieldName' => "CONCAT_WS(' ', a.first_name, a.last_name)"
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalNew'          => 0
           ,'hasPortalEdit'         => 1
           ,'hasPortalDelete'       => 1
           ,'portalDialogWidth'     => 700
           ,'portalDialogHeight'    => 500
           ,'fieldlabel'            => array( 'Name'
                                            , 'NRIC no'
                                            , 'Telephone'
                                            , 'Mobile'
                                            , 'Email'
                                       )
            ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('enterpriseIms_contact', 'enterpriseIms_courseLink');


        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'course_contact'
           ,'displayTitleFieldName' => "c.title"
           ,'historyTableKeyField'  => 'course_contact_id'
           ,'showLinkPanelInNew'    => 0
           ,'showLinkPanelInEdit'   => 1
           ,'linkingType'           => 'portal'
           ,'hasPortalEdit'         => 0
           ,'hasPortalDelete'       => 0
           ,'portalDialogWidth'     => 600
           ,'portalDialogHeight'    => 500
           ,'fieldlabel'            => array($cpCfg['m.enterpriseIms.course.sectionTitle']
                                            , 'Subsidy'
                                            , 'Discount'
                                            , $cpCfg['m.enterpriseIms.batch.sectionTitle']
                                            , 'Enrollment Year'
                                            , 'Finance'
                                       )
        ));
    }
}