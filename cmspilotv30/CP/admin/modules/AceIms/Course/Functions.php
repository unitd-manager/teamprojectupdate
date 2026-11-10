<?
class CP_Admin_Modules_AceIms_Course_Functions extends CP_Common_Modules_AceIms_Course_Functions
{
   function setLinksArray($inst) {

       $linkObj = $inst->getLinksArrayObj('aceIms_course', 'aceIms_batchLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'batch'
            ,'linkingType'               => 'grid'
            ,'historyTableKeyField'      => 'batch_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'hasPortalNew'              => 0
            ,'portalSearchFunction'      => 1
            ,'fieldlabel'                => array('Title', 'Venue', 'Start Date', 'End Date', 'Start Time', 'End Time', 'No of attendee', 'Status')
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
            ,'hideFieldsArray'           => array('level_id')
       ));

	   //------------------------------------------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('aceIms_course', 'aceIms_courseSubsidyLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'course_subsidy_history'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'course_subsidy_history_id'
            ,'showLinkPanelInNew'        => 1
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalDelete'           => 1
            ,'fieldlabel'                => array('Title', 'Type')
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
            ,'additionalFieldsArray' => array(
                 'a.title'
                ,'a.category_type'
            )
       ));

       //------------------------------------------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('aceIms_course', 'aceIms_subjectLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'course_subject'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'course_subject_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
       ));

	   //------------------------------------------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('aceIms_course', 'aceIms_levelLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'course_level'
            ,'linkingType'               => 'modal'
            ,'historyTableKeyField'      => 'course_level_id'
            ,'showLinkPanelInEdit'       => 1
            ,'fieldlabel'                => array('Title', 'Code')
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
            ,'additionalFieldsArray' => array(
                 'a.title'
                ,'a.level_type'
            )
       ));
   }

    /**
    */
    function getAceImsCourseAceImsBatchLinkPortalSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $sqlStatus = $fn->getValueListSQL('batchStatus');
        
        $text = "
        <select name='batch_status' class='float_right m5'>
            <option value=''>Status</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus)}
        </select>
        ";

        return $text;
    }
}