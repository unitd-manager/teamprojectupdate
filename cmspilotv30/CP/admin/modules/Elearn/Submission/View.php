<?
class CP_Admin_Modules_ELearn_Submission_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['student_name'])}
            {$listObj->getListDataCell($row['school'])}
            {$listObj->getListDataCell($row['class'])}
            {$listObj->getListDataCell($row['book'])}
            {$listObj->getListDataCell($row['page'])}
            {$listObj->getListDataCell($row['book_page_title'])}
            {$listObj->getListDataCell($fn->getYesNo($row['answered']))}
            {$listObj->getListDataCell($row['answered_date'])}
            {$listObj->getListDataCell($fn->getYesNo($row['recording_done']))}
            {$listObj->getListDataCell($row['completion_date'])}
            {$listObj->getListDataCell($row['attempts'])}
            {$listObj->getListDataCell($row['score'])}
            {$listObj->getListDataCell($row['submission_id'], 'center')}
            {$listObj->getListRowEnd($row['submission_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Student', 'student_name')}
        {$listObj->getListHeaderCell('School', 'school')}
        {$listObj->getListHeaderCell('Class', 'class')}
        {$listObj->getListHeaderCell('Book / Color', 'book')}
        {$listObj->getListHeaderCell('Page No.', 'page')}
        {$listObj->getListHeaderCell('Page (English)', 'book_page_title')}
        {$listObj->getListHeaderCell('Answered?', 's.answered')}
        {$listObj->getListHeaderCell('Answered Date', 's.answered_date')}
        {$listObj->getListHeaderCell('Recording?', 's.recording_done')}
        {$listObj->getListHeaderCell('Recording Date', 's.completion_date')}
        {$listObj->getListHeaderCell('Attempts', 's.attempts')}
        {$listObj->getListHeaderCell('Score', 's.score')}
        {$listObj->getListHeaderCell('ID', 'a.submission_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $sqlStudent = "
        SELECT student_id
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
        FROM student s
        ORDER BY student_name
        ";

        $fieldset = "
        {$formObj->getDDRowBySQL('Student', 'student_id', $sqlStudent)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];
        
        $sqlBook = "
        SELECT book_id
              ,title
        FROM book b
        ORDER BY title
        ";
        $expBook = array('detailValue' => $row['book']);        

        $sqlBookPage = "
        SELECT book_page_id
              ,english
        FROM book_page b
        ORDER BY english
        ";
        $expBookPage = array('detailValue' => $row['book_page_title']);        

        $sqlStudent = "
        SELECT student_id
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
        FROM student s
        ORDER BY student_name
        ";
        $expStudent = array('detailValue' => $row['student_name']);        

        $sqlSchool = "
        SELECT school_id
              ,school_name
        FROM school
        ";
        $expSchool = array('detailValue' => $row['school']);

        $sqlClass = "
        SELECT klass_id
              ,title
        FROM klass
        ";
        $expClass = array('detailValue' => $row['class']);


        $fielset1 = "
        {$formObj->getDDRowBySQL('Student', 'student_id', $sqlStudent, $row['student_id'], $expStudent)}
        {$formObj->getDDRowBySQL('Book / Color', 'book_id', $sqlBook, $row['book_id'], $expBook)}
        {$formObj->getDDRowBySQL('School', 'school_id', $sqlSchool, $row['school_id'], $expSchool)}
        {$formObj->getDDRowBySQL('Class', 'klass_id', $sqlClass, $row['klass_id'], $expSchool)}
        {$formObj->getDDRowBySQL('Book Page', 'book_page_id', $sqlBookPage, $row['book_page_id'], $expBookPage)}
        {$formObj->getDateRow('Answered Date', 'answered_date', $row['answered_date'])}
        {$formObj->getYesNoRRow('Recording?', 'recording_done', $row['recording_done'])}
        {$formObj->getDateRow('Recording Date', 'completion_date', $row['completion_date'])}
		";
        $fielset2 = "
        {$formObj->getYesNoRRow('Answered Correctly?', 'answered', $row['answered'])}
        {$formObj->getTBRow('Score', 'score', $row['score'])}
        {$formObj->getTBRow('Attempts', 'attempts', $row['attempts'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Evalution', $fielset2)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'submission_id');

        $text ="
        {$comment->getView(array(
             'roomName' => 'elearn_submission'
            ,'recordId' => $record_id
        ))}
        {$media->getRightPanelMediaDisplay("Audio", "elearn_submission", "audio", $row)}
        ";
        return $text;
    }

    //==================================================================//
    //==================================================================//


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

        $school_id  = $fn->getReqParam('school_id');
        $klass_id   = $fn->getReqParam('klass_id');
        $student_id = $fn->getReqParam('student_id');
        $book_id    = $fn->getReqParam('book_id');

        $SQLSchool = "
        SELECT DISTINCT 
               s.school_id
              ,sc.school_name
        FROM submission s
        LEFT JOIN school sc ON (sc.school_id = s.school_id)
        WHERE sc.school_name != ''
        ORDER BY sc.school_name
        ";
        
        $appendSQLSchool = '';
        
        if ($school_id != '') {
            $appendSQLSchool = " WHERE s.school_id = '{$school_id}'";
        }

        $SQLClass = "
        SELECT DISTINCT 
               s.klass_id
              ,k.title
        FROM submission s
        LEFT JOIN klass k ON k.klass_id = s.klass_id
        {$appendSQLSchool}
        ORDER BY k.title
        ";
        
        $appendArr = array();
        
        if ($klass_id != '') {
            $appendArr[] = "s.klass_id = '{$klass_id}'";
        }

        if ($school_id != '') {
            $appendArr[] = "s.school_id = '{$school_id}'";
        }

        $appendSQLStudent = join(' AND ', $appendArr);

        if ($appendSQLStudent != '') {
            $appendSQLStudent = " WHERE {$appendSQLStudent}";
        }

        $SQLStudent = "
        SELECT DISTINCT 
               s.student_id
              ,CONCAT_WS(' ', st.first_name, st.last_name ) AS student_name 
        FROM submission s
        LEFT JOIN student st ON st.student_id = s.student_id
        {$appendSQLStudent}
        ORDER BY st.first_name
        ";

        $SQLBook = "
        SELECT b.book_id
              ,b.title
        FROM book b
        ORDER BY b.title
        ";

        $text = "
        <td class='fieldValue'>
            <select name='school_id'>
                <option value=''>School</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLSchool, $school_id)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='klass_id'>
                <option value=''>Class</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLClass, $klass_id)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='student_id'>
                <option value=''>Student</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLStudent, $student_id)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='book_id'>
                <option value=''>Book</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLBook, $book_id)}
            </select>
        </td>
        ";
        
        
        return $text;
    }
}