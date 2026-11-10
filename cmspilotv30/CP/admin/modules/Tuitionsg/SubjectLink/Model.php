<?
class CP_Admin_Modules_Tuitionsg_SubjectLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getSubjectValueForCheckBox() {
        $db = Zend_Registry::get('db');
        $fn     = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $module   = $fn->getReqParam('room');
        $srcFld   = $fn->getReqParam('srcFld', '', true);
        $srcValue = $fn->getReqParam('srcValue', '', true);
        $dbUtil = Zend_Registry::get('dbUtil');
        $rows = '';

        // when you select a new course the session of subject id has to be null
        if (isset($_SESSION['selectedSubjectIds'])){
            unset($_SESSION['selectedSubjectIds']);
        }
        $_SESSION['selectedSubjectIds'] = array();

        $courseRec = $fn->getRecordRowById('course', 'course_id', $srcValue);

        if ($courseRec['course_type'] == 'Short Term' || $srcValue == '') {
            return;
        }

        $sqlSubject  = "
        SELECT s.subject_id
              ,s.title
        FROM subject s
        LEFT JOIN course_subject cs ON (s.subject_id = cs.subject_id)
        WHERE cs.course_id = {$srcValue}
        ";
        $result   = $db->sql_query($sqlSubject);
        $numRows  = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $id = 'subject_ids_' . $count;
            $id = 'subject_id';

            $sqlBatch  = "
            SELECT b.batch_id
                  ,b.title
            FROM batch b
            WHERE b.course_id = {$srcValue}
              AND b.status='Open'
              AND b.subject_id = {$row['subject_id']}
            ";

            $rows .="
            <tr>
            <td style='width: 300px;'>
            <div class='type-check'>
                <input type='checkbox' name='subject_id[]' value='{$row['subject_id']}' class='{$id}' />
                <label for='{$id}'>{$row['title']}</label>
            </div>
            </td>
            <td>
                <select name='batch_id[]'>
                    <option value=''>Select Batch</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch)}
                </select>
            </td>
            </tr>
            ";
            //$_SESSION['selectedSubjectIds'][] = $row['subject_id'];
            $count++;
        }

        $text="
        <div class='form-row-wrapper'>
            <div class='leftCol'>
                <label for='fld_subject_ids[]'>Subject</label>
            </div>

            <div class='rightCol'>
                <table>
                {$rows}
                </table>
            </div>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getAddSubjectAmountToTotal($getTotalOnly = "", $full_time = "", $no_of_months = ""){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $selectedSubjectIds = '';
        $subjectTotal = '';

        $fees_by_module = $fn->getReqParam('fees_by_module');
        $checkedVal     = $fn->getReqParam('checkedVal');
        $subject_id     = $fn->getReqParam('subject_id');
        $course_id      = $fn->getReqParam('course_id');

        //Subject Ids saved in session variable//
        if ($checkedVal == 1) {
            $_SESSION['selectedSubjectIds'][] = $subject_id;
        } else {
            $s = &$_SESSION['selectedSubjectIds'];
            if(($key = array_search($subject_id, $s)) !== false){
                unset($s[$key]);
            }
        }

        $text = $this->getCalculateTotalCheckedSubjectAmount($fees_by_module, $course_id);

        return $text;
    }
    /**
     *
     */
    function getAddAllSubjectAmountToTotal($getTotalOnly = "", $full_time = "", $no_of_months = ""){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $course_id      = $fn->getReqParam('course_id');
        $fees_by_module = $fn->getReqParam('fees_by_module');

        $subjectTotal = 0;
        $selectedSubjectIds = '';

        $selectedSubjectIds = join(',', $_SESSION['selectedSubjectIds']);

        if ($course_id == '') {
            $label = "Total";
            $subjectTotal = 0;
        } else if ($fees_by_module == 1) {
            $SQL = "
            SELECT s.*
            FROM subject s
            WHERE s.subject_id IN ({$selectedSubjectIds})
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            while ($row = $db->sql_fetchrow($result)) {
                $subjectTotal += $row['fees'];
            }
            $label = "Subject Total";
        } else {
            $sqlSub = "
            SELECT s.*
            FROM subject s
            LEFT JOIN (course_subject cs) ON (s.subject_id = cs.subject_id)
            WHERE cs.course_id = {$course_id}
            ";
            $resultSub  = $db->sql_query($sqlSub);
            $numRowsSub = $db->sql_numrows($resultSub);

            $subject_count_selected = count($_SESSION['selectedSubjectIds']);
            $courseRec = $fn->getRecordRowById('course', 'course_id', $course_id);
            $total_course_amt = ($courseRec['price']/$numRowsSub) * $subject_count_selected;
            $subjectTotal = round($total_course_amt, 2);
            $label = $courseRec['title'];
        }

        $text = "
        <td>{$label}</td>
        <td class='amount txtRight'>{$subjectTotal}</td>
        ";

        return $text;
    }

    /**
     *
     */
    function getCourseRelatedSubjectsSQL($course_id) {
        return $sql = "
        SELECT s.subject_id
              ,s.title
        FROM subject s
        LEFT JOIN (course_subject cs) ON (s.subject_id = cs.subject_id)
        WHERE cs.course_id = '{$course_id}'
        ORDER BY s.title
        ";
    }

    /**
     *
     */
    function getSubjectsByCourseJSON(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $rows = "";

        $course_id = $fn->getReqParam('course_id');

        $json  = array();

        if ($course_id == ""){
            return json_encode($json);
        }

        $SQL = "
        SELECT s.subject_id
              ,s.title
        FROM subject s
        LEFT JOIN (course_subject cs) ON (s.subject_id = cs.subject_id)
        WHERE cs.course_id = '{$course_id}'
        ORDER BY s.title
        ";
        $result   = $db->sql_query($SQL);
        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['subject_id'], "caption" => $row['title']);
        }

        return json_encode($json);
    }

    /**
     *
     */
    function getCalculateTotalCheckedSubjectAmount($fees_by_module, $course_id){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $subjectTotal = 0;
        $selectedSubjectIds = '';

        $selectedSubjectIds = join(',', $_SESSION['selectedSubjectIds']);
        $subject_count_selected = count($_SESSION['selectedSubjectIds']);

        if ($subject_count_selected == 0) {
            $label = "Subject Total";
            $subjectTotal = 0;
        } else if ($fees_by_module == 1) {
            $SQL = "
            SELECT s.*
            FROM subject s
            WHERE s.subject_id IN ({$selectedSubjectIds})
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            while ($row = $db->sql_fetchrow($result)) {
                $subjectTotal += $row['fees'];
            }
            $label = "Subject Total";
        } else {
            $sqlSub = "
            SELECT s.*
            FROM subject s
            LEFT JOIN (course_subject cs) ON (s.subject_id = cs.subject_id)
            WHERE cs.course_id = {$course_id}
            ";
            $resultSub  = $db->sql_query($sqlSub);
            $numRowsSub = $db->sql_numrows($resultSub);

            $subject_count_selected = count($_SESSION['selectedSubjectIds']);
            $courseRec = $fn->getRecordRowById('course', 'course_id', $course_id);
            $total_course_amt = ($courseRec['price']/$numRowsSub) * $subject_count_selected;
            $subjectTotal = round($total_course_amt, 2);
            $label = $courseRec['title'];
        }

        $text = "
        <td>{$label}</td>
        <td class='amount txtRight'>{$subjectTotal}</td>
        ";

        return $text;
    }

}