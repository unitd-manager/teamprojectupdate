<?
class CP_Admin_Modules_EnterpriseIms_SubjectLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
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
        
        $sqlBatch  = "
        SELECT b.batch_id
              ,b.title
        FROM batch b
        WHERE b.course_id = {$srcValue}
          AND b.status='Open'
        ";

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
            $rows .="
            <tr>
            <td style='width: 300px;'>
            <div class='type-check'>
                <input type='checkbox' name='subject_id[]' value='{$row['subject_id']}' class='{$id}' />
                <label for='{$id}'>{$row['title']}</label>
            </div>
            </td>
            <!--
            <td>
                <select name='batch_id[]'>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch)}
                </select>
            </td>
            -->
            </tr>
            ";
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
        
        $subjectTotal = '';
        $selectedSubjectIds = '';
        if($full_time == ''){
            $full_time   = $fn->getReqParam('full_time');
        }
        $count= '';
        
        //If getTotalOnly is = 1 then it is called from Edit Enrollment, to show the values from db and get the subject total else run the below code..
        if($getTotalOnly == ''){
            $subject_id   = $fn->getReqParam('subject_id');
            $checkedVal   = $fn->getReqParam('checkedVal');
            $no_of_months = $fn->getReqParam('no_of_months');
            
            if ($checkedVal == 1){
                $_SESSION['selectedSubjectIds'][] = $subject_id;
            }     
            else{
                $s = &$_SESSION['selectedSubjectIds'];

                if(($key = array_search($subject_id, $s)) !== false){
                    unset($s[$key]);
                }
            }
        }
        $selectedSubjectIds = join(',', $_SESSION['selectedSubjectIds']);
        //print_r ($selectedSubjectIds);
        $count = count($_SESSION['selectedSubjectIds']);
        
        if ($count == ''){
             $text = "
            <td>Subject Total</td>
            <td class='amount txtRight'>{$subjectTotal}</td>
            ";
            return $text;
        }
        
        $SQL  = "
        SELECT s.*
        FROM subject s
        WHERE s.subject_id IN ({$selectedSubjectIds})
        ";
        $result  = $db->sql_query($SQL);  
        $numRows = $db->sql_numrows($result);
        
        while ($row = $db->sql_fetchrow($result)) {
            if($full_time == 1){
                $subjectTotal += $row['fees'];
            }
            else{
                if($row['title'] == 'Science Lab'){
                    $row['fees'] = 400;
                }
                else{
                    if($no_of_months != 9 && $no_of_months != ''){
                        $row['fees'] = (1125/9)* $no_of_months;
                    }
                    else{
                        $row['fees'] = 1125;
                    }
                }
                //$subjectTotal += $row['fees'] - 255;
                $subjectTotal += $row['fees'] ;
            }
        }
            
        
        $text = "
        <td>Subject Total</td>
        <td class='amount txtRight'>{$subjectTotal}</td>
        ";
        if($getTotalOnly == 1){
            return $subjectTotal;
        }
        
        return $text;
    }
}