<?
class CP_Admin_Modules_Project_Contact_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('project_contact');
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
        $mediaObj = $mediaArr->getMediaObj('project_contact', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_contact', 'common_interestLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'       => 'interest_contact'
           ,'showAnchorInLinkPortal' => 0
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_contact', 'project_companyLink');

        $inst->registerLinksArray($linkObj, array(
            'displayTitleFieldName' => 'a.company_name'
           ,'historyTableName'      => 'contact'
           ,'linkMultiple'          => 0
           ,'keyFieldForHistory'    => 'company_id'
        ));
    }
}