<?
class CP_Admin_Modules_ELearn_Teacher_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListDataCell($row['school'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['mobile'])}
            {$listObj->getListPublishedImage($row['published'], $row['teacher_id'])}
            {$listObj->getListDataCell($row['teacher_id'], 'center')}
            {$listObj->getListRowEnd($row['teacher_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'a.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'a.last_name')}
        {$listObj->getListHeaderCell('School', 'school')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Mobile', 'a.mobile')}
        {$listObj->getListHeaderCell('Published', 'a.published')}
        {$listObj->getListHeaderCell('ID', 'a.teacher_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('Email', 'email')}
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
        
        $sqlKlass = "
        SELECT klass_id
              ,title
        FROM klass k
        ORDER BY title
        ";
        $expKlass = array('detailValue' => $row['class']);
        
        $expVl   = array('sqlType' => 'OneField');

        $sqlSchool = "
        SELECT school_id
              ,school_name
        FROM school
        ";
        $expSchool = array('detailValue' => $row['school']);

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
		";
			
        $fielset2 = "
        {$formObj->getDDRowBySQL('School', 'school_id', $sqlSchool, $row['school_id'], $expSchool)}
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
        $record_id = $fn->getIssetParam($row, 'teacher_id');

        $text ="
        {$media->getRightPanelMediaDisplay("Picture", "elearn_teacher", "picture", $row)}
        {$displayLinkData->getLinkPortalMain("elearn_teacher", "elearn_studentLink", "Students Linked", $row)}
        {$displayLinkData->getLinkPortalMain("elearn_teacher", "elearn_klassLink", "Class Linked", $row)}
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

        $school     = $fn->getReqParam('school_id');

        $SQLSchool = "
        SELECT DISTINCT
               t.school_id
              ,sc.school_name
        FROM teacher t
        LEFT JOIN school sc ON t.school_id = sc.school_id
        WHERE sc.school_name != ''
        ORDER BY sc.school_name
        ";

        $schoolType = $dbUtil->getDropDownFromSQLCols2($db, $SQLSchool, $school);

        $text = "
        <td class='fieldValue'>
            <select name='school_id'>
                <option value=''>School</option>
                {$schoolType}
            </select>
        </td>
        ";

        
        return $text;
    }
}