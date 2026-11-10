<?
class CP_Admin_Modules_AceIms_ContactLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        if ($tv['srcRoom'] == 'aceIms_class' && $tv['lnkRoom'] == 'aceIms_contactLink') {
            return $this->getListForClass($dataArray, $linkRecType);
        }

        $rows       = '';
        $rowCounter = 0;
        $company = '';

        foreach ($dataArray as $row){
            if($cpCfg['m.common.contact.hasCompanyTable'] == 1){
                $company = "
                {$listObj->getListDataCell($row['c_company_name'])}
            	";
            } else {
                if (isset($row['company_name'])){
                    $company = $listObj->getListDataCell($row['company_name']);
                }
            }

            $student_name = $row['first_name'] . ' ' . $row['last_name'];

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($student_name)}
            {$listObj->getListDataCell($row['email'])}
            {$company}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
            ";
            $rowCounter++ ;
        }

        if($cpCfg['m.common.contact.hasCompanyTable'] == 1){
            $company = $listLinkObj->getListHeaderCellLink($linkRecType, 'Company Name', 'c_company_name');
        } else {
            if (isset($row['company_name'])){
                $company = $listLinkObj->getListHeaderCellLink($linkRecType, 'Company Name', 'c.company_name');
            }
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Student Name', 'first_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'c.email')}
        {$company}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

    /**
    */
    function getListForClass($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $contact_name = $row['first_name'] . ' ' . $row['last_name'];

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($contact_name)}
            {$listObj->getListDataCell($row['id_card_no'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'first_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'ID Card No.', 'c.id_card_no')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

    /**
    */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        if ($tv['srcRoom'] == 'aceIms_resources' && $tv['lnkRoom'] == 'aceIms_contactLink') {
            return $this->getNewForResources();
        }

        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');

        $expVL = array('sqlType' => 'OneField');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Name *', 'first_name')}
                {$formObj->getDDRowBySQL('Gender *', 'gender', $sqlGender, '', $expVL)}
                {$formObj->getDateRow('Date of Birth *', 'date_of_birth', '', array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
                {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, '', $expVL)}
                {$formObj->getTBRow('NRIC/FIN/Work Permit No. *', 'id_card_no')}
                {$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, '', $expVL)}
                {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, '', $expVL)}
                {$formObj->getTBRow('Email *', 'email')}
                {$formObj->getTBRow('Password', 'pass_word')}
                {$formObj->getTBRow('Phone', 'phone')}
                {$formObj->getTBRow('HP/Mobile No. *', 'mobile')}
                {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen')}
                {$formObj->getYesNoRRow('Student Pass Holder', 'student_pass_holder')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
    */
    function getNewForResources(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addForResources&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $sqlStatus = $fn->getValueListSQL('contactResourcesStatus');
        $exp = array('sqlType' => 'OneField');

        $sqlContact = $fn->getDDSql('aceIms_contact');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Student Name', 'contact_id', $sqlContact)}
                {$formObj->getDateRow('From Date', 'from_date')}
                {$formObj->getDateRow('To Date', 'to_date')}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $exp)}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='srcRoom' value='{$tv['srcRoom']}' />
        </form>
        ";

        return $text;
    }

    /**
    */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        if ($tv['srcRoom'] == 'aceIms_resources' && $tv['lnkRoom'] == 'aceIms_contactLink') {
            return $this->getEditForResources();
        }

        $sqlGender        = $fn->getValueListSQL('gender');
        $sqlMaritalStatus = $fn->getValueListSQL('maritalStatus');
        $sqlNationality   = $fn->getValueListSQL('nationality');
        $sqlRace          = $fn->getValueListSQL('race');

        $expVL = array('sqlType' => 'OneField');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('contact', 'contact_id', $id);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Name *', 'first_name', $row['first_name'])}
                {$formObj->getDDRowBySQL('Gender *', 'gender', $sqlGender, $row['gender'], $expVL)}
                {$formObj->getDateRow('Date of Birth *', 'date_of_birth', $row['date_of_birth'], array('yearStart' => 1920, 'yearEnd' => date('Y') + 10))}
                {$formObj->getDDRowBySQL('Marital Status', 'marital_status', $sqlMaritalStatus, $row['marital_status'], $expVL)}
                {$formObj->getTBRow('NRIC/FIN/Work Permit No. *', 'id_card_no', $row['id_card_no'])}
                {$formObj->getDDRowBySQL('Nationality *', 'nationality', $sqlNationality, $row['nationality'], $expVL)}
                {$formObj->getDDRowBySQL('Race', 'race', $sqlRace, $row['race'], $expVL)}
                {$formObj->getTBRow('Email *', 'email', $row['email'])}
                {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
                {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
                {$formObj->getTBRow('HP/Mobile No. *', 'mobile', $row['mobile'])}
                {$formObj->getYesNoRRow('Singapore Citizen / PR', 'is_citizen', $row['is_citizen'])}
                {$formObj->getYesNoRRow('Student Pass Holder', 'student_pass_holder', $row['student_pass_holder'])}
            </fieldset>
            <input type='hidden' name='contact_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

    /**
    */
    function getEditForResources(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=saveForResources&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('resources_contact', 'resources_contact_id', $id);

        $sqlContact = $fn->getDDSql('aceIms_contact');
        //$expCont  = array('detailValue' => $row['contact_name']);

        $sqlStatus = $fn->getValueListSQL('contactResourcesStatus');
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Student Name', 'contact_id', $sqlContact, $row['contact_id'])}
                {$formObj->getDateRow('From Date', 'from_date', $row['from_date'])}
                {$formObj->getDateRow('To Date', 'to_date', $row['to_date'])}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
            </fieldset>
            <input type='hidden' name='resources_contact_id' value='{$id}' />
            <input type='hidden' name='srcRoom' value='{$tv['srcRoom']}' />
        </form>
        ";

        return $text;
    }

    /**
    */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $batch_id = $fn->getReqParam('batch_id');

        $sqlBatch = "SELECT batch_id, title FROM batch";

        $text = "
        <td>
            <select name='batch_id' >
                <option value=''>Batch</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlBatch, $batch_id)}
            </select>
        </td>
        ";

        return $text;
    }
}
