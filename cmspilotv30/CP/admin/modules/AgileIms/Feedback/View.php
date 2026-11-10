<?
class CP_Admin_Modules_AgileIms_Feedback_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $email = "";
        $rowCounter = 0;
        
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['feedback_group'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['feedback_id'], 'center')}
            {$listObj->getListRowEnd($row['feedback_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Group', 'f.feedback_group')}
        {$listObj->getListHeaderCell('Title', 'f.title')}
        {$listObj->getListHeaderCell('ID', 'f.feedback_id' , 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');

        $sqlFeedbackGroup = $fn->getValueListSQL('feedbackGroup');
        $expVL = array('sqlType' => 'OneField');

        $fieldset = "
        {$formObj->getDDRowBySQL('Group', 'feedback_group', $sqlFeedbackGroup, '', $expVL)}
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
        
        $sqlFeedbackGroup = $fn->getValueListSQL('feedbackGroup');
        $expVL = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getDDRowBySQL('Group', 'feedback_group', $sqlFeedbackGroup, $row['feedback_group'] , $expVL)}
        {$formObj->getTARow('Title', 'title', $row['title'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Feedback Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){

        $text ="
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $group = $fn->getReqParam('group');
        $sqlFeedbackGroup = $fn->getValueListSQL('feedbackGroup');

        $text = "
        <td>
            <select name='group'>
                <option value=''>Feedback Group</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlFeedbackGroup, $group)}
            </select>
        </td>
        ";
        
        return $text;
    }

    /**
     *
     */
     function getStudentFeedback() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $batch_id = $fn->getReqParam('id');
        $feedback_group = $fn->getReqParam('feedback_group');
        
        $rows = '';
        $headerRow = '';
        $questionList = '';
        
        $formAction = "index.php?module=agileIms_feedback&_spAction=studentFeedbackSubmit&showHTML=0";

        $sqlFeedbackGroup = $fn->getValueListSQL('feedbackGroup');
        $expVL = array('sqlType' => 'OneField');
        
        $text = "
        <div id='studentFeedbackOuter'>
            <form id='portalForm' class='yform columnar agileIms_batch__agileIms_studentFeedbackLink' method='post' action='{$formAction}'>
                <div class='floatbox'>
                    {$formObj->getDDRowBySQL('Feedback Group', 'feedback_group', $sqlFeedbackGroup, '', $expVL)}
        	        {$formObj->getTBRow('Feedback Title', 'title')}
                    <input type='hidden' name='batch_id' value='{$batch_id}' />
                </div>
	            <div id='questionsList'></div>
            </form>
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
     function getEditStudentFeedback() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $batch_feedback_id = $fn->getReqParam('id');
        $batchFeedbackRec  = $fn->getRecordRowById('batch_feedback', 'batch_feedback_id ', $batch_feedback_id);
        
        $title = $batchFeedbackRec['title'];
        
        $rows = '';
        $headerRow = '';
        
        $formAction = "index.php?module=agileIms_feedback&_spAction=editStudentFeedbackSubmit&showHTML=0";

        $SQLfb = "
        SELECT DISTINCT f.feedback_id
              ,f.title
              ,bf.batch_id
        FROM feedback f
        LEFT JOIN (batch_feedback bf) ON (f.feedback_group = bf.feedback_group)
        WHERE bf.feedback_group = '{$batchFeedbackRec['feedback_group']}'
        ";
        $resultFb = $db->sql_query($SQLfb);
        $numRows = $db->sql_numrows($resultFb);
        
        while ($row = $db->sql_fetchrow($resultFb)) {            
            $headerRow .="
            <th class='questionHeader'>{$row['title']}</th>        
            ";
            $feedback_id = $row['feedback_id'];
            $batch_id = $row['batch_id'];
        }

        $rows .= $this->getEditStudentFeedbackRecords($numRows, $batch_id);
        
        $expEdit = array('isEditable' => 0);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='questionsList' class='thinlist'>
  		        {$formObj->getTBRow('Feedback Group', 'feedback_group', $batchFeedbackRec['feedback_group'], $expEdit)}
  		        {$formObj->getTBRow('Feedback Title', 'title', $title)}
                <thead>
                    <tr>
                        <th class='questionHeader'>Questions</th>
                        {$headerRow}
                    </tr>
                </thead>
                {$rows}
            </table>    
            <input type='hidden' name='batch_id' value='{$batch_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getStudentFeedbackRecords($numRows, $batch_id, $feedback_group){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $SQL = "
        SELECT cc.course_contact_id
            ,cc.contact_id
            ,c.first_name AS trainee_name
        FROM course_contact cc
        LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
        WHERE cc.batch_id = {$batch_id}
        ";
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            //$trainee_name = $row['first_name'] . ' ' . $row['last_name'];          
            $rows .="
            <tr>
                <td>{$row['trainee_name']}</td>        
                {$this->getStudentRemarksFunctionTD($numRows, $row['contact_id'], $feedback_group)}
            </tr>            
            ";
        }
                
        $text = "
        {$rows}         
        ";

        return $text;
    }
    
    /**
     *
     */
    function getEditStudentFeedbackRecords($numRows, $batch_id){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        
        $SQL = "
        SELECT cc.course_contact_id
            ,cc.contact_id
            ,c.first_name
        FROM course_contact cc
        LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
        WHERE batch_id = {$batch_id}
        ";
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {            
            $rows .="
            <tr>
                <td>{$row['first_name']}</td>        
                {$this->getEditStudentRemarksFunctionTD($numRows, $row['contact_id'])}
            </tr>            
            ";
        }

        $text = "
        {$rows}         
        ";

        return $text;
    }
    
    /**
     *
     */     
    function getFeedbackQuestions(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $feedback_group = $fn->getReqParam('feedback_group');
        $batch_id = $fn->getReqParam('batch_id');
        
        $rows = '';
        $headerRow = '';
        
        $SQLfb = "
        SELECT feedback_id, title
        FROM feedback
        WHERE feedback_group = '{$feedback_group}'
        ORDER BY feedback_id ASC
        ";        
        $resultFb = $db->sql_query($SQLfb);
        $numRows = $db->sql_numrows($resultFb);
        
        if ($numRows == 0) {
            return;
        }
        
        while ($row = $db->sql_fetchrow($resultFb)) {            
            $headerRow .= "<th class='questionHeader'>{$row['title']}</th>";
            $feedback_id = $row['feedback_id'];
        }
        
        $rows .= $this->getStudentFeedbackRecords($numRows, $batch_id, $feedback_group);
        
        $text = "
        <table class='thinlist'>
            <thead>
                <tr>
                    <th class='questionHeader'>Questions</th>
                    {$headerRow}
                </tr>
            </thead>
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
    function getStudentRemarksFunctionTD($numRowsStudent, $contact_id, $feedback_group){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $rowCounter = 1;
        $feedbackArray ='';
        
        $arr = array(1 => 1, 2 => 2, 3 => 3, 4 => 4);

        $batch_id = $fn->getReqParam('id');

        $feedbackArray = array();

        $SQLfb = "
        SELECT feedback_id, title
        FROM feedback
        WHERE feedback_group = '{$feedback_group}'
        ORDER BY feedback_id ASC
        ";        
        $resultFb = $db->sql_query($SQLfb);
        $numRows = $db->sql_numrows($resultFb);
        
        while ($row = $db->sql_fetchrow($resultFb)) {
            
            $feedbackArray[]= $row['feedback_id'];
        }

        $row = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
        $pfx = $contact_id . '_' ;
        for($i=1; $i<=$numRowsStudent; $i++){
            $k = $i-1;
            $pfx = 'marks_' . $contact_id . '_' . $feedbackArray[$k];
            $rows .= "<td>{$formObj->getRadioArrRow(' ', "{$pfx}", '', $arr, '')}</td>";
            
            $rowCounter++;          
        }

        $text = "
        {$rows} 
        ";

        return $text;
    }
    
    /**
     *
     */
    function getEditStudentRemarksFunctionTD($numRowsStudent, $contact_id){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $rows = '';
        $rowCounter = 1;
        $feedbackArray ='';
        
        $arr = array(1 => 1, 2 => 2, 3 => 3, 4 => 4);

        $batch_feedback_id = $fn->getReqParam('id');
        $batchFeedbackRec  = $fn->getRecordRowById('batch_feedback', 'batch_feedback_id ', $batch_feedback_id);
        
        $title = $batchFeedbackRec['title'];

        $SQLfb = "
        SELECT DISTINCT f.feedback_id
              ,bf.title
              ,bf.batch_id
        FROM feedback f
        LEFT JOIN (batch_feedback bf) ON (f.feedback_group = bf.feedback_group)
        WHERE bf.feedback_group = '{$batchFeedbackRec['feedback_group']}'
        ";        
        $resultFb = $db->sql_query($SQLfb);
        $numRows = $db->sql_numrows($resultFb);

        $feedbackArray = array();
        
        while ($row = $db->sql_fetchrow($resultFb)) {
            $feedbackArray[]= $row['feedback_id']; 
            $batch_id = $row['batch_id'];           
        }
        
        $pfx = $contact_id . '_' ;
        for($i=1; $i<=$numRowsStudent; $i++){
            $k = $i-1;

            $batchFeedback = $fn->getRecordByCondition('batch_feedback', "title = '{$title}' AND batch_id = '{$batch_id}'");

            $studentFeedbackRow = $fn->getRecordByCondition('student_feedback', 
                "contact_id = '{$contact_id}' AND 
                 batch_id = '{$batch_id}' AND 
                 feedback_id = '{$feedbackArray[$k]}' AND 
                 batch_feedback_id = '{$batch_feedback_id}'"
            );
            
            $pfx = 'marks_' . $contact_id . '_' . $feedbackArray[$k] . '_' . $studentFeedbackRow['student_feedback_id'];

            $rows .= "<td>{$formObj->getRadioArrRow(' ', "{$pfx}", $studentFeedbackRow['marks'], $arr, '')}</td>";
            
            $rowCounter++;          
        }

        $text = "
        {$rows} 
        ";

        return $text;
    }
}