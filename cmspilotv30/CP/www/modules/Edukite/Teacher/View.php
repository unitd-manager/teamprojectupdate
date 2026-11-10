<?
class CP_Www_Modules_Edukite_Teacher_View extends CP_Common_Modules_Edukite_Teacher_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $tv = Zend_Registry::get('tv');
        $searchHTML = Zend_Registry::get('searchHTML');
        $pager = Zend_Registry::get('pager');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows  = "";
        $email = "";
        $rowCounter = 0;

        $urlArray = array();
        $urlArray['siteType'] = 'kite';
        $secRec = getCPModelObj('webBasic_section')->getRecordByType('Home');
        $urlArray['section_title'] = $secRec['title'];
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $urlArray['sitePfxId'] = $row['teacher_id'];
            $kiteUrl = $cpUrl->make_seo_url($urlArray);
            $kiteUrl = $kiteUrl.'?teacherKite=1';


            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['teacher_name'], '', '', $row)}
            {$listObj->getListDataCell($row['email'])}
            <td align='right'>
                <a href='{$kiteUrl}' class='kiteIcon'>
                <img src='/cmspilotv30/CP/www/themes/Manager/images/kite-icon.png'></a>
            </td>
            {$listObj->getListRowEnd($row['teacher_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        <div class='teacherList'>
            {$listObj->getListHeader()}
            {$listObj->getListHeaderCell('Name', 'teacher_name')}
            {$listObj->getListHeaderCell('Email', 't.email')}
            {$listObj->getListHeaderCell('Kite', 't.email')}
            {$listObj->getListHeaderEnd()}
            {$rows}
            {$listObj->getListFooter()}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $roleArr = array(
            "Teacher"
           ,"Kite Master"
        );

        $fieldset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
        {$formObj->getTBRow('Email', 'email')}
        {$formObj->getTBRow('Password', 'pass_word')}
        {$formObj->getTBRow('Mobile', 'mobile')}
        {$formObj->getDDRowByArr('Role', 'role', $roleArr)}
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $expVl   = array('sqlType' => 'OneField');
        $sqlGender = $fn->getValueListSQL('gender');
        $roleArr = array(
            "Teacher"
           ,"Kite Master"
        );


        $hostName   = $_SERVER['HTTP_HOST'];
		$position = '' ;
        if(strpos($hostName, 'scbc') !== false){
	       $position = "{$formObj->getTBRow('Position', 'address1', $row['address1'])}";
		}

        $status = '';

        $teacherRec    = $fn->getRecordRowByID('teacher', 'teacher_id', $_SESSION['cpContactId']);
        if($teacherRec['role'] == 'Kite Master'){
            $status ="
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.edukite.statusArr'], $row['status'])}
            ";
        }

        $fieldset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$position}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getDDRowByArr('Role', 'role', $roleArr, $row['role'])}
        {$status}
		";

        $fieldset2 = "
        {$media->getRightPanelMediaDisplay('Picture', 'edukite_teacher', 'picture', $row)}
		";

        $text = "
        {$formObj->getFieldSetWrapped('', $fieldset2)}
        {$formObj->getFieldSetWrapped('Staff Details', $fieldset1)}
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
        <!--<div>
            <select name='subject_id'>
                <option value=''>Select Subject</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $subject_id)}
            </select>
        </div>-->

        ";

        return $text;
    }
}