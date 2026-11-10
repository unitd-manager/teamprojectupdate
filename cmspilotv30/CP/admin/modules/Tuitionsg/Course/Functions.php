<?
class CP_Admin_Modules_Tuitionsg_Course_Functions extends CP_Common_Modules_Tuitionsg_Course_Functions
{
   function setLinksArray($inst) {
       $linkObj = $inst->getLinksArrayObj('tuitionsg_course', 'tuitionsg_batchLink');
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
       $linkObj = $inst->getLinksArrayObj('tuitionsg_course', 'tuitionsg_subjectLink');
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
   }

    /**
     *
     */
    function getTuitionsgCourseTuitionsgBatchLinkPortalSearch() {
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