<?
class CP_Admin_Modules_ELearn_Student_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['school'])}
            {$listObj->getListDataCell($row['class'])}
            {$listObj->getListPublishedImage($row['published'], $row['student_id'])}
            {$listObj->getListDataCell($row['student_id'], 'center')}
            {$listObj->getListRowEnd($row['student_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'a.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'a.last_name')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('School', 'school')}
        {$listObj->getListHeaderCell('Class', 'k.title')}
        {$listObj->getListHeaderCell('Published', 'a.published')}
        {$listObj->getListHeaderCell('ID', 'a.student_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $sqlKlass = "
        SELECT klass_id
              ,title
        FROM klass k
        ORDER BY title
        ";
        
        $sqlSchool = "
        SELECT school_id
              ,school_name
        FROM school
        ";

        $fieldset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getDDRowBySQL('School', 'school_id', $sqlSchool)}
        {$formObj->getDDRowBySQL('Class', 'klass_id', $sqlKlass)}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $formObj->mode = $tv['action'];
                
        $expVl   = array('sqlType' => 'OneField');
        $sqlReader = "
        SELECT b.book_id
              ,b.title
        FROM book b
        ";

        $sqlSchool = "
        SELECT school_id
              ,school_name
        FROM school
        ";
        $expSchool = array('detailValue' => $row['school']);

        $sqlKlass = "
        SELECT sk.klass_id
              ,k.title as class
        FROM  school_klass sk
        JOIN klass k ON (k.klass_id = sk.klass_id)
        WHERE sk.school_id = '{$row['school_id']}'
        ";
        $expKlass = array('detailValue' => $row['class']);

        $gendArr = array('Male', 'Female');

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Parent Email', 'parent_email', $row['parent_email'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getRRow('Gender', 'gender', $row['gender'], $gendArr, array('rowCls' => 'yesNo'))}
        {$formObj->getDateRow('D.o.b', 'dob', $row['dob'])}
		";
		
        $fielset2 = "
        {$formObj->getDDRowBySQL('School', 'school_id', $sqlSchool, $row['school_id'], $expSchool)}
        {$formObj->getDDRowBySQL('Class', 'klass_id', $sqlKlass, $row['klass_id'], $expKlass)}
        {$formObj->getDateRow('Joined Date', 'joined_date', $row['joined_date'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('General Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Course Details', $fielset2)}
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

        $record_id = $fn->getIssetParam($row, 'student_id');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'elearn_student', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('elearn_student', 'elearn_bookLink', 'Books Linked', $row)}
        {$comment->getView(array(
             'roomName' => 'elearn_contact'
            ,'recordId' => $record_id
        ))}
        ";
        return $text;
    }

    //==================================================================//
    //==================================================================//
    function getKlassName(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $school_id   = $fn->getReqParam('school_id');
        $json  = array();

        if($school_id == ""){
            return json_encode($json);
        }


        $SQL    = "
        SELECT sk.klass_id
              ,k.title as class
        FROM  school_klass sk
        JOIN klass k ON (k.klass_id = sk.klass_id)
        WHERE sk.school_id = {$school_id}
        ";


        $result   = $db->sql_query($SQL);

        $json[] = array("value" => "", "caption" => "Please Select");
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['klass_id'], "caption" => $row['class']);
        }

        return json_encode($json);
    }

    /**
     *
     * @return <type>
     */


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

        $school     = $fn->getReqParam('school_id');
        $class     = $fn->getReqParam('klass_id');

        $SQLSchool = "
        SELECT DISTINCT
               st.school_id
              ,sc.school_name
        FROM student st
        LEFT JOIN school sc ON st.school_id = sc.school_id
        WHERE sc.school_name != ''
        ORDER BY sc.school_name
        ";

        $SQLClass = "
        SELECT DISTINCT
               st. klass_id
              ,k.title
        FROM student st
        LEFT JOIN klass k ON k.klass_id = st.klass_id
        ORDER BY k.title
        ";

        $schoolType = $dbUtil->getDropDownFromSQLCols2($db, $SQLSchool, $school);
        $classType = $dbUtil->getDropDownFromSQLCols2($db, $SQLClass, $class);

        $text = "
        <td class='fieldValue'>
            <select name='school_id'>
                <option value=''>School</option>
                {$schoolType}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='klass_id'>
                <option value=''>Class</option>
                {$classType}
            </select>
        </td>
        ";

        
        return $text;
    }
}