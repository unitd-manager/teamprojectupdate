<?
class CP_Admin_Modules_Edukite_Parent_View extends CP_Common_Modules_Edukite_Parent_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

      //$this->getStudentParentLinking();
        /* Run the below function to update the parent status based on their student status */
      //$this->getUpdateParentStatus();

        $rows  = "";
        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['gender'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListPublishedImage($row['published'], $row['parent_id'])}
            {$listObj->getListDataCell($row['parent_id'], 'center')}
            {$listObj->getListRowEnd($row['parent_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 'p.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'p.last_name')}
        {$listObj->getListHeaderCell('Email', 'p.email')}
        {$listObj->getListHeaderCell('Gender', 'p.gender')}
        {$listObj->getListHeaderCell('Phone', 'p.phone')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'p.parent_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Email', 'email')}
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country']);

        $expVl   = array('sqlType' => 'OneField');

        $gendArr = array('Male', 'Female');

        $fielset1 = "
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getRRow('Gender', 'gender', $row['gender'], $gendArr, array('rowCls' => 'yesNo'))}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getTBRow('Home Phone', 'phone', $row['phone'])}
		";

        $fielset2 = "
        {$formObj->getTBRow('Address', 'address1', $row['address1'])}
        {$formObj->getTBRow('Street', 'address2', $row['address2'])}
        {$formObj->getTBRow('City', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_postal_code', $row['address_postal_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
		";

        $fielset3 = "
        {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('General Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Address Details', $fielset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fielset3)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'parent_id');

        $text ="
        {$media->getRightPanelMediaDisplay("Picture", "edukite_parent", "picture", $row)}
        {$displayLinkData->getLinkPortalMain("edukite_parent", "edukite_studentLink", "Student Linked", $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {

        $text = "
        ";

        return $text;
    }

    /**
     *
     */
    function getStudentParentLinking() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT student_id, family_code, first_name, last_name
        FROM student
        WHERE status = 'Active'
        ORDER BY student_id";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $SQLParent = "
            SELECT parent_id, first_name, last_name
            FROM parent
            WHERE status = 'Active'
              AND family_code ='{$row['family_code']}'";
            $resultParent = $db->sql_query($SQLParent);
            while ($rowParent = $db->sql_fetchrow($resultParent)) {
                $studentParent = $fn->getRecordByCondition('student_parent',"student_id = {$row['student_id']} AND parent_id = {$rowParent['parent_id']}");
                if($studentParent['student_parent_id'] == ''){
                    $fa = array();
                    $fa['student_id']  = $row['student_id'];
                    $fa['parent_id']   = $rowParent['parent_id'];
                    $fa['creation_date'] = date("Y-m-d H:i:s");

                    $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, "student_parent");
                    $resultInsert = $db->sql_query($SQLInsert);
                }
            }
        }

    }

    /**
     *
     */
    function getUpdateParentStatus() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT student_id, family_code, first_name, last_name
        FROM student
        ORDER BY student_id";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $SQLParent = "
            SELECT student_id, parent_id
            FROM student_parent
            WHERE student_id ='{$row['student_id']}'";
            $resultParent = $db->sql_query($SQLParent);
            while ($rowParent = $db->sql_fetchrow($resultParent)) {
                $SQLUpdate ="
                UPDATE `parent` SET status = 'Archive' WHERE parent_id = {$rowParent['parent_id']}
                ";
                $resultUpdate = $db->sql_query($SQLUpdate);
            }
        }
        $this->getCheckParentStatus();
    }

    /**
     *
     */
    function getCheckParentStatus() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT parent_id
        FROM parent
        ORDER BY parent_id";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $SQLStudent = "
            SELECT student_id, parent_id
            FROM student_parent
            WHERE parent_id ='{$row['parent_id']}'";
            $resultStudent = $db->sql_query($SQLStudent);
            while ($rowStudent = $db->sql_fetchrow($resultStudent)) {
                $student = $fn->getRecordByCondition('student',"student_id = {$rowStudent['student_id']}");
                if($student['status'] == 'Active'){
                    $SQLUpdate ="
                    UPDATE `parent` SET status = 'Active' WHERE parent_id = {$rowStudent['parent_id']}
                    ";
                    $resultUpdate = $db->sql_query($SQLUpdate);
                }
            }
        }
    }

    /**
     *
     */
    function getParentDuplicateRecord() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT parent_id, first_name, username, family_code,
        password, academic_year, COUNT( first_name ) AS NumOccurrences
        FROM parent
        GROUP BY first_name
        HAVING (COUNT( first_name ) >1) AND academic_year = '2011'
        ";
        $result = $db->sql_query($SQL);
        $count =0;
        while ($row = $db->sql_fetchrow($result)) {
             print "parent ----- " . $row['first_name'] ." / ". $row['family_code'] . "<br> " . $count;
             $SQLInsert = "DELETE FROM parent WHERE parent_id = {$row['parent_id']}";
            //$resultInsert = $db->sql_query($SQLInsert);
             print $SQLInsert . "<br><br>";
             $SQLInsert = "DELETE FROM student_parent WHERE parent_id = {$row['parent_id']}";
            //$resultInsert = $db->sql_query($SQLInsert);
             $count = $count + 1;

             print $SQLInsert . "<br><br>";
            //print $SQL . "<br>";
            //return;
        }
         print "STUDENT_PARENT CODE ENDS HERE";
        return;

    }
}