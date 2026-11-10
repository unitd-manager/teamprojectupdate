<?
class CP_Admin_Modules_AceIms_Subject_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['fees'], 'right')}
            {$listObj->getListDataCell($row['subject_id'], 'center')}
            {$listObj->getListRowEnd($row['subject_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Subject Code', 'code')}
        {$listObj->getListHeaderCell('Subject Title', 'title')}
        {$listObj->getListHeaderCell('Fees (SGD)', 'fees', 'headerRight')}
        {$listObj->getListHeaderCell('ID', 'subject_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Subject Code', 'code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $fieldset1 = "
        {$formObj->getTBRow('Subject Code', 'code', $row['code'])}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTBRow('Fees', 'fees', $row['fees'])}
        {$formObj->getTARow('Outcome', 'outcome', $row['outcome'])}
        {$formObj->getTARow('Synopsys', 'synopsys', $row['synopsys'])}
    	";

        $text = "
        {$formObj->getFieldSetWrapped('Subject Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $text = "";

        return $text;
    }

    /**
     *
     */
    function getSubjectList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $course_id  = $fn->getReqParam('course_id');
        $contact_id = $fn->getReqParam('contact_id');

        $courseRec   = $fn->getRecordRowById('course', 'course_id', $course_id);
        $course_name = $courseRec['title'];

        $sqlSubject  = "
        SELECT s.subject_id
              ,s.title
        FROM subject s
        LEFT JOIN course_subject cs ON (s.subject_id = cs.subject_id)
        WHERE cs.course_id = {$course_id}
        ";
        $result   = $db->sql_query($sqlSubject);
        $numRows  = $db->sql_numrows($result);

        $rows  = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $pfx = $contact_id . '_' ;
            $id  = 'subject_ids_' . $count;
            $id  = 'subject_id';

            $checked = '';
            $batch_id = '';
            $batchRow = '';
            $selected = '';

            $subKey = $pfx . $row['subject_id'];

            if (in_array($subKey, $_SESSION['selectedSubjectIds'])) {
                $checked = "checked='checked'";
            }

            /*The below sql is to display the batch dropdown based on subject*/
            $sqlBatch  = "
            SELECT b.batch_id
                  ,b.title
            FROM batch b
            WHERE b.course_id = {$course_id}
              AND b.status='Open'
              AND b.subject_id = {$row['subject_id']}
            ";
            $resultBatch   = $db->sql_query($sqlBatch);
            while ($rowBatch = $db->sql_fetchrow($resultBatch)) {
                /*The below codes are to display the selected batches from the session*/
                $subKey1 = $pfx . $rowBatch['batch_id'] .'_'. $row['subject_id'];
                if (in_array($subKey1, $_SESSION['selectedBatchIds'])) {
                    $selected = "selected='selected'";
                } else {
                    $selected = '';
                }

                $batchRow .= "
                <option value='{$rowBatch['batch_id']}_{$row['subject_id']}' {$selected}>{$rowBatch['title']}</option>
                ";
            }

            $rows .="
            <tr contact_id='{$contact_id}'>
                <td>
                    <div class='type-check'>
                        <input type='checkbox' {$checked} name='{$subKey}subject_id[]' value='{$row['subject_id']}' class='{$id}' />
                        <label for='{$id}'>&nbsp;&nbsp;&nbsp;{$row['title']}</label>
                    </div>
                </td>
                <td>
                    <select name='batch_id[]' class='batch_id'>
                        <option value='0_{$row['subject_id']}'>Select Batch</option>
                        {$batchRow}
                    </select>
                </td>
            </tr>
            ";
            $count++;
        }

        $text = "
        <table class='subjectForStudents'>
            <tr><td>Subjects for the course chosen: <strong>{$course_name}</strong></td></tr>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";
        return $text;
    }

    /**
     *
     */
    function getEditSubjectList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $order_id   = $fn->getReqParam('order_id');
        $contact_id = $fn->getReqParam('contact_id');

        $ccRec = $fn->getRecordByCondition('course_contact', "order_id = '{$order_id}' AND contact_id = '{$contact_id}'");
        $courseRec = $fn->getRecordRowById('course', 'course_id', $ccRec['course_id']);
        $receiptRec = $fn->getRecordByCondition('receipt', "order_id = {$order_id} AND receipt_status = 'Paid'");

        $course_name = $courseRec['title'];

        $sqlSubject  = "
        SELECT s.subject_id
              ,s.title
        FROM subject s
        LEFT JOIN course_subject cs ON (s.subject_id = cs.subject_id)
        WHERE cs.course_id = {$ccRec['course_id']}
        ";
        $result   = $db->sql_query($sqlSubject);
        $numRows  = $db->sql_numrows($result);

        $rows  = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $pfx = $contact_id . '_' ;
            $id  = 'subject_ids_' . $count;
            $id  = 'subject_id';

            $checked = '';
            $batchRow = '';
            $selected = '';
            $disabled = '';

            $subKey = $pfx . $row['subject_id'];

            if (in_array($subKey, $_SESSION['selectedSubjectIds'])) {
                $checked = "checked='checked'";
            }

            if($receiptRec){
                $disabled = "disabled='1'";
            }

            $sqlBatch  = "
            SELECT b.batch_id
                  ,b.title
            FROM batch b
            WHERE b.course_id = {$ccRec['course_id']}
              AND b.status='Open'
              AND b.subject_id = {$row['subject_id']}
            ";
            $resultBatch   = $db->sql_query($sqlBatch);
            while ($rowBatch = $db->sql_fetchrow($resultBatch)) {
                $subKey1 = $pfx . $rowBatch['batch_id'] .'_'. $row['subject_id'];
                if (in_array($subKey1, $_SESSION['selectedBatchIds'])) {
                    $selected = "selected='selected'";
                } else {
                    $selected = '';
                }

                $batchRow .= "
                <option value='{$rowBatch['batch_id']}_{$row['subject_id']}' {$selected}>{$rowBatch['title']}</option>
                ";
            }

            $rows .="
            <tr contact_id='{$contact_id}'>
                <td>
                    <div class='type-check'>
                        <input type='checkbox' {$checked} {$disabled} name='{$subKey}subject_id[]' value='{$row['subject_id']}' class='{$id}' />
                        <label for='{$id}'>&nbsp;&nbsp;&nbsp;{$row['title']}</label>
                    </div>
                </td>
                <td>
                    <select name='batch_id[]' class='batch_id'>
                        <option value='0_{$row['subject_id']}'>Select Batch</option>
                        {$batchRow}
                    </select>
                </td>
            </tr>
            ";
            $count++;
        }

        $text = "
        <table class='subjectForStudents'>
            <tr><td>Subjects for the course chosen: <strong>{$course_name}</strong></td></tr>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";
        return $text;
    }
}