<?
class CP_Admin_Modules_Edukloud_Course_Model extends CP_Common_Modules_Edukloud_Course_Model
{
    function getSQL() {

        $SQL   = "
        SELECT c.*
              ,pg.title AS program_title
        FROM course c
        LEFT JOIN (program_group pg) ON (c.program_group_id = pg.program_group_id)
        ";

        return $SQL;
    }

    /**
    */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $course_type        = $fn->getReqParam('course_type');
        $course_id          = $fn->getReqParam('course_id');
        $special_search     = $fn->getReqParam('special_search');
        $program_group_id   = $fn->getReqParam('program_group_id');

        if ($course_id != "") {
            $searchVar->sqlSearchVar[] = "c.course_id = '{$course_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.course_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.course_id');

            if ($program_group_id != "") {
                $searchVar->sqlSearchVar[] = "c.program_group_id = '{$program_group_id}'";
            }

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "c.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(c.flag != 1 OR c.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "c.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "c.published = 0 OR c.published IS NULL OR c.published = ''";
            }

            if ($course_type != '' ) {
                $searchVar->sqlSearchVar[] = "c.course_type = '{$course_type}'";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.title   LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "c.course_id DESC";
        }
    }

    /**
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');

        $validate->resetErrorArray();

        if($cpCfg['m.edukloud.course.hasProgramGroup']){
            $validate->validateData('program_group_id', 'Please select the group');
        }
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('course_code', 'Please enter the course code');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $program_group_id   = $fn->getReqParam('program_group_id');
        
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        
        if($cpCfg['m.edukloud.course.hasProgramGroup']){
            $sqlInsert = "
            SELECT subsidy_discount_id
            FROM program_group_subsidy_discount
            WHERE program_group_id = {$program_group_id}
            ";
            $resultInsert  = $db->sql_query($sqlInsert);
            
            while ($row = $db->sql_fetchrow($resultInsert)) {
                $fa = array();
                
                $fa['course_id']            = $id;
                $fa['subsidy_discount_id']  = $row['subsidy_discount_id'];
                $fa['program_group_id']     = $program_group_id;
                $fa['creation_date']        = date("Y-m-d H:i:s");
            
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'course_subsidy_history');
                $result = $db->sql_query($SQL);
            } 
        }

        $fn->returnAfterNewSave($id);
    }

    /**
    */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    */
    function getSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
    */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'course_code');
        $fa = $fn->addToFieldsArray($fa, 'course_type');
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);

        $fa = $fn->addToFieldsArray($fa, 'total_hours');
        $fa = $fn->addToFieldsArray($fa, 'duration');
        $fa = $fn->addToFieldsArray($fa, 'month_or_hour');
        $fa = $fn->addToFieldsArray($fa, 'course_entry_requirements');
        $fa = $fn->addToFieldsArray($fa, 'price');
        $fa = $fn->addToFieldsArray($fa, 'program_group_id');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'valid_date_from');
        $fa = $fn->addToFieldsArray($fa, 'valid_date_to');
        $fa = $fn->addToFieldsArray($fa, 'qualification_type');
        $fa = $fn->addToFieldsArray($fa, 'developed_by');
        $fa = $fn->addToFieldsArray($fa, 'award_course');
        $fa = $fn->addToFieldsArray($fa, 'award_date');
        $fa = $fn->addToFieldsArray($fa, 'scheduled_holidays');
        $fa = $fn->addToFieldsArray($fa, 'examination_assessment');
        $fa = $fn->addToFieldsArray($fa, 'examination_results');

        return $fa;
    }

    /**
     * @param type $id = course_id
    */
    function getEdukloudCourseEdukloudBatchLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

        $status  = $fn->getReqParam('batch_status');
        $site_id = $fn->getSessionParam('cp_site_id');

        $extraFields = "";
        $current_year = date('Y');

        if ($status != "") {
            $whereSQL = " AND b.status = '{$status}'";
        } else {
            $whereSQL = "";
        }
        
        $current_year    = date('Y');
        $enrollment_year = $fn->getReqParam('enrollment_year', $current_year);
        
        $appendSql = "";
        if ($enrollment_year != '') {
            $appendSql .= " AND year_of_enrollment = '{$enrollment_year}'";
        }

        $appendSql = "";
        if ($site_id != '') {
            $appendSql .= " AND site_id = '{$site_id}'";
        }

        return "
        SELECT DISTINCT b.batch_id
              ,b.title
              ,l.title AS level_title
              ,l.level_id
              ,(
                SELECT count(*) FROM course_contact WHERE
                ((l.level_id = '' or l.level_id is null)
                    AND batch_id  = b.batch_id
                    AND level_id IS NULL
                    {$appendSql})
                
                or
                ((l.level_id > 0)
                    AND batch_id  = b.batch_id
                    AND course_id = {$id}
                    AND level_id  = l.level_id
                    {$appendSql})
               ) AS attendee_count
              ,b.status
        FROM batch b
        LEFT JOIN (course_contact cc) ON (b.batch_id = cc.batch_id)
        LEFT JOIN (level l) ON (cc.level_id = l.level_id)
        WHERE b.course_id = '{$id}'
          {$whereSQL}
        ORDER BY b.batch_id
        ";
    }

    /**
     * @param type $id = course_id
    */
    function getEdukloudCourseEdukloudCourseSubsidyLinkSQL($id) {

        $SQL = "
        SELECT csh.course_subsidy_history_id
              ,sd.title
              ,sd.category_type
        FROM course_subsidy_history csh 
        LEFT JOIN subsidy_discount sd ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
        WHERE csh.course_id = '{$id}'
        ORDER BY csh.course_subsidy_history_id
        ";

        return $SQL;
    }

    /**
     * @param type $id = course_id
    */
    function getEdukloudCourseEdukloudLevelLinkSQL($id) {

        $SQL = "
        SELECT cl.course_level_id
              ,l.title
              ,l.level_code
        FROM course_level cl 
        LEFT JOIN level l ON (l.level_id = cl.level_id)
        WHERE cl.course_id = '{$id}'
        ORDER BY cl.course_level_id
        ";

        return $SQL;
    }

    /**
    */
    function getAddSubsidyDiscountPortal() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $program_group_id   = $fn->getReqParam('program_group_id');
        $course_id          = $fn->getReqParam('course_id');
        
        $sqlDelete = "
        DELETE FROM course_subsidy_history
        WHERE course_id = {$course_id}
        ";
        $resultDelete  = $db->sql_query($sqlDelete);
        
        $sqlInsert = "
        SELECT subsidy_discount_id
        FROM program_group_subsidy_discount
        WHERE program_group_id = {$program_group_id}
        ";
        $resultInsert  = $db->sql_query($sqlInsert);
        
        while ($row = $db->sql_fetchrow($resultInsert)) {
            $fa = array();
            
            $fa['course_id']            = $course_id;
            $fa['subsidy_discount_id']  = $row['subsidy_discount_id'];
            $fa['program_group_id']     = $program_group_id;
            $fa['creation_date']        = date("Y-m-d H:i:s");

            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'course_subsidy_history');
            $result = $db->sql_query($SQL);
        }
    }

    /**
     * @param type $id = course_id
    */
    function getEdukloudCourseEdukloudSubjectLinkSQL($id) {

        return $SQL = "
        SELECT cs.course_subject_id
              ,s.title
        FROM course_subject cs 
        LEFT JOIN subject s ON (s.subject_id = cs.subject_id)
        WHERE cs.course_id = '{$id}'
        ORDER BY cs.course_subject_id
        ";
    }
    
    /**
     * 
    */
    function getCourseSQL() {
        return "
        SELECT course_id, title
        FROM course
        WHERE published = 1
        ";
    }
    
}