<?
class CP_Admin_Modules_Pos_Staff_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $rowCounter = 0;
        $rows       = '';
        $staff_type = '';
        $country    = '';
        $userGrp    = '';

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .="
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['staff_no'])}
            {$listObj->getGoToDetailText($rowCounter, $row['staff_code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['staff_name'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['shop_title'])}
            {$listObj->getListDataCell($row[$cpCfg['cp.modAccessStaffIdLabel']], "center")}
            {$listObj->getListPublishedImage($row['published'], $row[$cpCfg['cp.modAccessStaffIdLabel']])}
            {$listObj->getListRowEnd($row[$cpCfg['cp.modAccessStaffIdLabel']])}
            ";

            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Staff No.', 'a.staff_no')}
        {$listObj->getListHeaderCell('Staff Code', 'a.staff_code')}
        {$listObj->getListHeaderCell('Staff Name', 'a.staff_name')}
        {$listObj->getListHeaderCell('Title', 'a.title')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Shop Tile', 's.shop_title')}
        {$listObj->getListHeaderCell('ID', 'a.staff_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'a.published', 'headerCenter')}
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

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $fielset = "
        {$formObj->getTBRow('Staff Name', 'staff_name')}
        {$formObj->getTBRow('Staff No.', 'staff_no')}
        {$formObj->getTBRow('Staff Code', 'staff_code')}
        {$formObj->getTBRow('Email', 'email')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $am = Zend_Registry::get('am');

        $formObj->mode = $tv['action'];

        $staffTeam        = "";
        $staffType        = "";
        $sectionName      = "";
        $staffRate        = "";
        $sensDetails      = "";
        $description      = "";
        $userGroup        = "";
        $shortCode        = "";

        $expVl = array('sqlType' => 'OneField');

        if ($cpCfg['cp.hasProjectMg'] == 1) {
            $sqlTeam = $fn->getValueListSQL('staffTeam');
            $sqlType = $fn->getValueListSQL('staffType');

            $staffType   = $formObj->getDDRowBySQL('Staff Type', 'staff_type', $sqlType, $row['staff_type'], $expVl);
            $staffTeam   = $formObj->getDDRowBySQL('Staff Team', 'team', $sqlTeam, $row['team'], $expVl);
            if ($cpCfg['m.core.staff.showFldSensitiveDetails'] == 1){
                $sensDetails = $formObj->getYesNoRRow('Show Sensitive Details', 'show_sensitive_details', $row['show_sensitive_details']);
            }
            $staffRate   = $formObj->getTBRow('Staff Rate', 'staff_rate', $row['staff_rate']);
        }

        if ($cpCfg['cp.hasFirstRoomValueInStaff'] == 1) {
            $sectionName = $formObj->getDDRowByArr("Login Section Default", "section_name", $am->getSectionNameArray(), $row['section_name']);
        }

        if ($cpCfg['m.core.staff.showStaffDescription'] == 1) {
            $description = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));
        }

        $fnMod = includeCPClass('ModuleFns', 'pos_staff');

        $userGrp = '';

        if ($cpCfg['m.core.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
            $exp = array('hideFirstOption' => 1, 'detailValue' => $row['user_group_title']);

            $sqlUG = "
            SELECT user_group_id
                  ,title
            FROM {$cpCfg['cp.modAccessUserGroupTable']}
            ORDER BY title
            ";

            $userGrp = $formObj->getDDRowBySQL('User Group', 'user_group_id', $sqlUG, $row['user_group_id'], $exp);
        }

        if ($cpCfg['m.core.staff.showShortCode'] == 1){
            $shortCode = $formObj->getTBRow('Short Code', 'short_code', $row['short_code']);
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $zipCodeText = '';
        if ($cpCfg['m.core.staff.hasZipCode']) {
            $zipCodeText = $formObj->getTBRow('Zip', 'zip_code', $row['zip_code']);
        }

        $passwordRow = '';
        $emailRow = '';
        if ($cpCfg['m.core.staff.hasPasswordSalt']) {
            $has_pwd = '';
            $lblPassword = 'Password';
            if ($row['pass_word'] != '') {
                $has_pwd = 1;
                $lblPassword = 'Change Password';
            }
            $passwordRow = "
            {$formObj->getTBRow($lblPassword, 'pass_word')}
            <input type='hidden' name='has_pwd' value='{$has_pwd}' />
            ";

            $exp = array('isEditable' => 0);
            $emailRow = "
            {$formObj->getTBRow('Email', 'email', $row['email'], $exp)}
            <input type='hidden' name='email' value='{$row['email']}' />
            ";

        } else {
            $passwordRow = "{$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}";
            $emailRow = $formObj->getTBRow('Email', 'email', $row['email']);
        }

        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();

        $sqlWmo = getCPModuleObj('pos_mwo')->model->getMwoCodeSQL();

        $fielset1 = "
        {$formObj->getTBRow('Staff No.', 'staff_no', $row['staff_no'])}
        {$formObj->getTBRow('Staff Code', 'staff_code', $row['staff_code'])}
        {$formObj->getTBRow('Staff Name', 'staff_name', $row['staff_name'])}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$emailRow}
        {$passwordRow}
        {$formObj->getTBRow('Smart Card No', 'smart_card_no', $row['smart_card_no'])}
        {$formObj->getTBRow('Salary', 'salary', $row['salary'])}
        {$formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], array('sqlType' => 'OneField'))}
        {$formObj->getDDRowBySQL('WMO Code', 'wmo_code', $sqlWmo, $row['wmo_code'], array('sqlType' => 'OneField'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$fn->getSiteDropDown($formObj->mode, $row)}
        ";


        if ($description != ''){
            $description = "
            {$formObj->getFieldSetWrapped('Description', $description )}
            ";
        }

        $text = "
        {$formObj->getFieldSetWrapped('Staff Details', $fielset1)}
        {$description}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'pos_staff', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('pos_staff', 'pos_shopUsergroupLink', 'Shop Usergroup Linked', $row)}
        ";
        return $text;
    }

    /**
     *
     */
    function getStaffXML() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "";
        $project_id       = $fn->getReqParam('project_id');
        $opportunity_id   = $fn->getReqParam('opportunity_id');

        $text = "";

        $text .= $fn->getAjaxXMLHeader();
        $text .= "<data>";

        if ($opportunity_id != "") {
            $SQL    = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name
            FROM staff a, opportunity_staff b
            WHERE a.staff_id = b.staff_id
              AND b.opportunity_id = {$opportunity_id}
            ORDER BY staff_name
            ";
        } else {
            $SQL    = "
            SELECT a.staff_id
                  ,CONCAT_WS(' ', a.first_name, a.last_name ) AS staff_name
            FROM staff a, project_staff b
            WHERE a.staff_id = b.staff_id
              AND b.project_id = {$project_id}
            ORDER BY staff_name
            ";
        }
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $text .= "<row>";
            $text .= "<record_id>"      . $row[$cpCfg['cp.modAccessStaffIdLabel']]     . "</record_id>";
            $text .= "<title><![CDATA[" . $row['staff_name']   . "]]></title>";
            $text .= "</row>";
        }
        $text .= "</data>";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $stfGrp     = '';
        $statusTxt  = '';
        $countryTxt = '';
        $userGrp    = '';

        $user_group_id  = $fn->getReqParam('user_group_id');
        $staff_group_id = $fn->getReqParam('staff_group_id');
        $status         = $fn->getReqParam('status');
        $shop_id        = $fn->getReqParam('shop_id');

        if ($cpCfg['m.core.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
            $sqlUG = "
            SELECT user_group_id
                  ,title
            FROM {$cpCfg['cp.modAccessUserGroupTable']}
            ORDER BY title
            ";

            $userGrp = "
            <td>
                <select name='user_group_id' >
                    <option value=''>Group</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlUG, $user_group_id)}
                </select>
            </td>
            ";
        }

        if ($cpCfg['m.core.hasStaffGroup'] == 1) {
            $sqlCombo = "
            SELECT staff_group_id
                  ,title
            FROM staff_group
            ORDER BY title
            ";

            $stfGrp = "
            <td>
                <select name='staff_group_id'>
                    <option value=''>Staff Group</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $staff_group_id)}
                </select>
            </td>
            ";
        }

        if ($cpCfg['cp.hasProjectMg'] == 1) {
            $sqlCombo = "
            SELECT value
            FROM valuelist
            WHERE key_text = 'staffStatus'
            ORDER BY sort_order
            ";

            $statusTxt = "
            <td>
                <select name='status'>
                    <option value=''>Status</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlCombo, $status)}
                </select>
            </td>
            ";
        }

        $sqlShop = $SQL = "SELECT s.shop_id, s.title FROM shop s";
        $shopTitle = "
        <td>
            <select name='shop_id'>
                <option value=''>Shop</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlShop, $shop_id)}
            </select>
        </td>
        ";

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $text = "
        {$userGrp}
        {$shopTitle}
        {$stfGrp}
        {$statusTxt}
        {$fnModCountry->getCountryDropDown('search')}
        {$fn->getSiteDropDown('search')}
        ";

        return $text;
    }
}