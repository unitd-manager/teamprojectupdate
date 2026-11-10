<?
class CP_Admin_Modules_EnterpriseIms_Course_Functions extends CP_Common_Modules_EnterpriseIms_Course_Functions
{
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('enterpriseIms_course', 'enterpriseIms_batchLink');
       $inst->registerLinksArray($linkObj, array(
             'historyTableName'          => 'batch'
            ,'linkingType'               => 'grid'
            ,'historyTableKeyField'      => 'batch_id'
            ,'showLinkPanelInEdit'       => 1
            ,'hasPortalEdit'             => 0
            ,'hasPortalDelete'           => 0
            ,'hasPortalNew'              => 0
            ,'portalSearchFunction'      => 1
            ,'fieldlabel'                => array(
                'Title', 'Year', 'No of attendee', 'Status')
            ,'showAnchorInLinkPortal'    => false
            ,'hasGridEdit'               => 0
            ,'hideFieldsArray'           => array('level_id')
       ));

	   //------------------------------------------------------------------------------//
       $linkObj = $inst->getLinksArrayObj('enterpriseIms_course', 'enterpriseIms_courseSubsidyLink');
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
       $linkObj = $inst->getLinksArrayObj('enterpriseIms_course', 'enterpriseIms_levelLink');
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
     *
     */
    function getEnterpriseImsCourseEnterpriseImsBatchLinkPortalSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $current_year = date('Y');
        $sqlYear      = "SELECT DISTINCT cc.year_of_enrollment FROM course_contact cc";
        $sqlStatus    = $fn->getValueListSQL('batchStatus');

        $text = "
        <select name='batch_status' class='float_right m5'>
            <option value=''>Status</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus)}
        </select>

        <select name='enrollment_year' class='float_right m5'>
            <option value=''>Enrollment Year</option>
            {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $current_year)}
        </select>
        ";

        return $text;
    }
}