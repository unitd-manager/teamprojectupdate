<?
class CP_Www_Modules_Edukloud_Attendance_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_staff'){
            $SQL   = "
            SELECT a.*
                  ,CONCAT_WS(' ', b.first_name, b.last_name) AS student_name
                  ,c.title as class_title
            FROM student_attendance a
            LEFT JOIN (student b) ON (a.student_id = b.student_id)
            LEFT JOIN (class c)   ON (a.class_id = c.class_id)
            ";
        } else if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_admin'){
            $SQL   = "
            SELECT sa.*
                  ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
            FROM staff_attendance sa
            LEFT JOIN (staff s) ON (sa.staff_id = s.staff_id)
            ";
        } else {
            $SQL   = "
            SELECT a.*
                  ,CONCAT_WS(' ', b.first_name, b.last_name) AS student_name
                  ,c.title as class_title
            FROM student_attendance a
            JOIN (student b) ON (a.student_id = b.student_id)
            LEFT JOIN (class c)   ON (a.class_id = c.class_id)
            ";
        }
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 't';

        $class_id = $fn->getReqParam('class_id');
        $status = $fn->getReqParam('status');
        $date = $fn->getReqParam('date');

        if ($_SESSION['cpLoginTypeWWW'] == 'edukloud_admin') {
            if ($tv['keyword'] != ''){
                $this->sqlSearchVar[] = "(
                                          s.first_name LIKE '%{$tv['keyword']}%' OR
                                          s.last_name LIKE  '%{$tv['keyword']}%'
                                        )";
            }

            $this->sqlSearchVar[] = "sa.record_date  = '{$date}'";
            $this->sortOrder = 'sa.record_date ASC';
        } else  {
            if ($class_id == '') {
                //$class_id = $fn->getFirstClass();
            }
    
            if ($class_id != '' ) {
                $this->sqlSearchVar[] = "a.class_id  = '{$class_id}'";
            }
    
            if ($status != '' ) {
                $this->sqlSearchVar[] = "a.status  = '{$status}'";
            }
    
            if ($tv['keyword'] != ''){
                $this->sqlSearchVar[] = "(
                                          b.first_name LIKE '%{$tv['keyword']}%' OR
                                          b.last_name LIKE  '%{$tv['keyword']}%'
                                        )";
            }
    
            $this->sortOrder = 'a.record_date ASC';
            if ($_SESSION['cpLoginTypeWWW'] == 'Student') {
                $this->sqlSearchVar[] = "a.student_id  = {$_SESSION['contact_id']}";
            } else if ($_SESSION['cpLoginTypeWWW'] == 'Parent') {
                $this->sqlSearchVar[] = "a.student_id  = {$_SESSION['student_id']}";
    
            } else if ($_SESSION['cpLoginTypeWWW'] == 'Staff') {
                $this->sqlSearchVar[] = "
                b.class_id IN (
                    SELECT class_id
                    FROM   staff_class
                    WHERE  staff_id = {$_SESSION['contact_id']}
                )
                ";
                $this->sortOrder = 'student_name';
                $this->sqlSearchVar[] = "a.record_date  = '{$date}'";
            }
        }
    }

    /**
     *
     */
    function getTakeAttendanceSubmit() {
        checkLoggedIn();
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $student_ids = $fn->getPostParam('student_ids');
        $other_student_ids = $fn->getPostParam('other_student_ids');
        $date        = $fn->getPostParam('date');
        $status      = $fn->getPostParam('status');
        $class_id    = $fn->getPostParam('class_id');
        $student_ids_arr = explode(',', $student_ids);
        $other_student_ids_arr = explode(',', $other_student_ids);

        foreach ($student_ids_arr as $student_id) {
            if ($student_id > 0){
                $fa = &$this->fieldsArray;
                $fa['student_id']    = $student_id;
                $fa['class_id']      = $class_id;
                $fa['status']        = $status;
                $fa['record_date']   = $date;
                $fa['creation_date'] = date('Y-m-d H:i:s');
                
                $SQL    = $dbUtilCommon->getInsertSQLStringFromArray($fa, 'student_attendance');
                $result = $db->sql_query($SQL);
            }
        }

        foreach ($other_student_ids_arr as $other_student_id) {
            if ($other_student_id > 0){
                $fa = &$this->fieldsArray;
                $fa['student_id']    = $other_student_id;
                $fa['class_id']      = $class_id;
                $fa['status']        = '';
                $fa['record_date']   = $date;
                $fa['creation_date'] = date('Y-m-d H:i:s');
                
                $SQL    = $dbUtilCommon->getInsertSQLStringFromArray($fa, 'student_attendance');
                $result = $db->sql_query($SQL);
            }
        }

        /*$SQL = $sqlMaster->getSQL('attendance', 1);
        $result = $db->sql_query($SQL);  
        $text = $this->getStudentList($result);*/
        //return $text;
    }
}
