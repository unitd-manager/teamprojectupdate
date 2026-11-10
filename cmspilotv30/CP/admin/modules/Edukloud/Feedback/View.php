<?
class CP_Admin_Modules_Edukloud_Feedback_View extends CP_Common_Lib_ModuleViewAbstract
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
        {$formObj->getDDRowBySQL('Group', 'group', $sqlFeedbackGroup, '', $expVL)}
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
}