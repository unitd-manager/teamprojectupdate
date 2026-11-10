<?
class CP_Www_Modules_Edukloud_Staff_View extends CP_Common_Lib_ModuleViewAbstract
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
            $email = "<a href='mailto:{$row['email']}'>{$row['email']}</a>";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['teacher_name'], '', '', $row)}
            {$listObj->getListDataCell($email)}
            {$listObj->getListDataCell($row['teacher_id'], 'center')}
            {$listObj->getListRowEnd($row['teacher_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'teacher_name')}
        {$listObj->getListHeaderCell('Email', 't.email')}
        {$listObj->getListHeaderCell('ID', 't.teacher_id' , 'headerCenter')}
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
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('Username', 'username')}
        {$formObj->getTBRow('Password', 'pass_word')}
        {$formObj->getTBRow('Email', 'email')}
        {$formObj->getTBRow('Mobile', 'mobile')}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $media = Zend_Registry::get('media');

        $expVl   = array('sqlType' => 'OneField');
        $sqlGender = $fn->getValueListSQL('gender');
        
        $fieldset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('User Name', 'username', $row['username'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
		";

        $fieldset2 = "
        {$media->getRightPanelMediaDisplay('Picture', 'edukite_teacher', 'picture', $row)}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Picture', $fieldset2)}
        ";

        return $text;
    }


    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fn = Zend_Registry::get('fn');

        $subject_id = $fn->getReqParam('subject_id');

        $sqlCombo = "SELECT subject_id, title FROM subject ORDER BY title";

        $text = "
        <div>
            <select name='subject_id'>
                <option value=''>Select Subject</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $subject_id)}
            </select>
        </div>

        ";        
        
        return $text;
    }

}