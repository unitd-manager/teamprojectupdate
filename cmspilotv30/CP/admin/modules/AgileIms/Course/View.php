<?
class CP_Admin_Modules_AgileIms_Course_View extends CP_Common_Modules_AgileIms_Course_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $count      = 0;
        $rows       = '';
        $rowGroup   = '';
        $textGroup  = '';

        foreach ($dataArray as $row){

            // This is used to dispaly Program Group
            if($cpCfg['m.agileIms.course.hasProgramGroup']){
                $rowGroup = $listObj->getListDataCell($row['program_title']);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['title'])}
            {$listObj->getListDataCell($row['course_code'])}
            {$listObj->getListDataCell($row['course_type'])}
            {$rowGroup}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListPublishedImage($row['published'], $row['course_id'])}
            {$listObj->getListSortOrderField($row, 'course_id')}
            {$listObj->getListRowEnd($row['course_id'])}
            ";

            $count++ ;
        }

        // This is used to dispaly Program Group
        if($cpCfg['m.agileIms.course.hasProgramGroup']){
            $textGroup = $listObj->getListHeaderCell($modulesArr['agileIms_programGroup']['title'], 'program_title');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'c.title')}
        {$listObj->getListHeaderCell('Code', 'c.course_code')}
        {$listObj->getListHeaderCell('Course Type', 'c.course_type')}
        {$textGroup}
        {$listObj->getListHeaderCell('Fees (SGD)', 'price')}
        {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
        {$listObj->getListSortOrderImage('c')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
    */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $textGroup = '';
        
        // This is used to dispaly Program Group
        if($cpCfg['m.agileIms.course.hasProgramGroup']){
            $modGroup = getCPModuleObj('agileIms_programGroup');
            $sqlGroup = $modGroup->model->getProgramGroupSQL();

            $textGroup = $formObj->getDDRowBySQL($modulesArr['agileIms_programGroup']['title'], 'program_group_id', $sqlGroup);
        }

        $fielset = "
        {$textGroup}
        {$formObj->getTBRow('Title', 'title')}
        {$formObj->getTBRow('Course Code', 'course_code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
    */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $textGroup = '';
        $exp = array('isEditable' => 0);

        $sqlCourseType    = $fn->getValueListSQL('courseType');
        $sqlMonthOrHour   = $fn->getValueListSQL('monthsOrHours');
        $sqlQualification = $fn->getValueListSQL('qualificationType');
        $expVL = array('sqlType' => 'OneField');

        // This is used to dispaly Program Group
        if($cpCfg['m.agileIms.course.hasProgramGroup']){
            $expGroup = array('detailValue' => $row['program_title']);
            $modGroup = getCPModuleObj('agileIms_programGroup');
            $sqlGroup = $modGroup->model->getProgramGroupSQL();
            $textGroup = $formObj->getDDRowBySQL($modulesArr['agileIms_programGroup']['title'], 'program_group_id', $sqlGroup, $row['program_group_id'], $expGroup);
        }

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTBRow('Course Code', 'course_code', $row['course_code'])}
        {$formObj->getDDRowBySQL('Course Type', 'course_type', $sqlCourseType, $row['course_type'] , $expVL)}
        {$formObj->getTBRow('Duration', 'duration', $row['duration'])}
        {$formObj->getDDRowBySQL('Months or Days or Hours Filter', 'month_or_hour', $sqlMonthOrHour, $row['month_or_hour'] , $expVL)}
        {$formObj->getTBRow('Fees (SGD)', 'price', $row['price'])}
        {$textGroup}
        {$formObj->getDateRow('Valid From', 'valid_date_from', $row['valid_date_from'])}
        {$formObj->getDateRow('Valid To', 'valid_date_to', $row['valid_date_to'])}
        {$formObj->getDDRowBySQL('Type of Qualification', 'qualification_type', $sqlQualification, $row['qualification_type'] , $expVL)}
        {$formObj->getTBRow('Organisation developed the course', 'developed_by', $row['developed_by'])}
        {$formObj->getTBRow('Organisation which awards the course', 'award_course', $row['award_course'])}
        {$formObj->getTARow('Course Entry Requirements', 'course_entry_requirements', $row['course_entry_requirements'])}
        {$formObj->getDateRow('Award Conferment date', 'award_date', $row['award_date'])}
        {$formObj->getTARow('Scheduled Holidays', 'scheduled_holidays', $row['scheduled_holidays'])}
        {$formObj->getTARow('Examination and Assessment Period', 'examination_assessment', $row['examination_assessment'])}
        {$formObj->getTARow('Examination Results details', 'examination_results', $row['examination_results'])}
        ";

        $fieldset2 = "
        {$formObj->getTARow('Regular Class', 'regular_class', $row['regular_class'])}
        {$formObj->getTARow('Part Time Evening Classes', 'part_time_class', $row['part_time_class'])}
        {$formObj->getTARow('Sunday Classes', 'sunday_class', $row['sunday_class'])}
        {$formObj->getTBRow('Attendance', 'min_attendance_requirement', $row['min_attendance_requirement'])}
        {$formObj->getTARow('Passing Mark', 'passing_mark', $row['passing_mark'])}
        {$formObj->getTBRow('Class Size', 'class_size', $row['class_size'])}
        {$formObj->getTARow('Legal requirements', 'legal_requirement', $row['legal_requirement'])}
        {$formObj->getTARow('Aim of Course', 'aim_of_course', $row['aim_of_course'])}
        ";

        $fieldset5 = $formObj->getHTMLEditor('Replacement/Reschedule', 'replacement_notes', $ln->gfv($row, 'replacement_notes', '0'));
        $fieldset6 = $formObj->getHTMLEditor('General Rules', 'general_rules', $ln->gfv($row, 'general_rules', '0'));
        $fieldset3 = $formObj->getHTMLEditor('Course Content', 'description', $ln->gfv($row, 'description', '0'));

        $fieldset4 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Course Date & Time', $fieldset2)}
        {$formObj->getFieldSetWrapped('Replacement/Reschedule', $fieldset5)}
        {$formObj->getFieldSetWrapped('General Rules', $fieldset6)}
        {$formObj->getFieldSetWrapped('Course Content / Description', $fieldset3)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset4)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
    */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $text = "
        {$displayLinkData->getLinkPortalMain('agileIms_course', 'agileIms_batchLink', $modulesArr['agileIms_batch']['title'], $row)}
        {$displayLinkData->getLinkPortalMain('agileIms_course', 'agileIms_subjectLink', 'Subject Linked', $row)}
        {$displayLinkData->getLinkPortalMain('agileIms_course', 'agileIms_courseSubsidyLink', 'Subsidy Discount Link', $row)}
        ";

        return $text;
    }

    /**
    */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $course_type        = $fn->getReqParam('course_type');
        $special_search     = $fn->getReqParam('special_search');
        $program_group_id   = $fn->getReqParam('program_group_id');

        $sqlCourseType = $fn->getValueListSQL('courseType');

        $modGroup = getCPModuleObj('agileIms_programGroup');
        $sqlGroup = $modGroup->model->getProgramGroupSQL();

        //==================================================================//
        $spArray = array(
              "Flagged"
             ,"Not-Flagged"
             ,"Published"
             ,"Not-Published"
        );
        
        $courseType = '';
        if($cpCfg['m.agileIms.course.otherDetailsPvt']){
            $courseType = "
            <td>
                <select name='course_type' >
                    <option value=''>Course Type</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlCourseType, $course_type)}
                </select>
            </td>
            ";
        }
        
        $text = "
        {$courseType}
        
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";

        return $text;
    }
}