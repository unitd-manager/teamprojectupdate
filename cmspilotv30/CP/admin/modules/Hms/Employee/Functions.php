<?
class CP_Admin_Modules_Hms_Employee_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('hms_employee');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'search', 'export')
           ,'actBtnsEdit'   => array('save', 'apply', 'cancel')
           ,'relatedTables' => array('media')
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('hms_employee', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('hms_employee', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('hms_employee', 'hms_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'a.company_name'
           ,'historyTableName'      => 'employee'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'company_id'
        ));
    }
}