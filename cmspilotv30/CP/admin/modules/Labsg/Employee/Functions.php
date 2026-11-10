<?
class CP_Admin_Modules_Labsg_Employee_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('labsg_employee');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new', 'search', 'export')
           ,'relatedTables' => array('media')
           ,'titleField'    => "CONCAT_WS(' ', first_name, last_name)"
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('labsg_employee', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('labsg_employee', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('labsg_employee', 'labsg_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'a.company_name'
           ,'historyTableName'      => 'employee'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'company_id'
        ));
    }
}