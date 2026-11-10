<?
class CPL_Admin_Modules_Core_Staff_View extends CP_Admin_Modules_Core_Staff_View
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows       = '';
        $staff_type = '';
        $country    = '';
        $userGrp    = '';

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $extraCols = '';

            $userGrp = '';
            $shortCode = '';

            if ($cpCfg['m.core.staff.showCountry'] == 1){
                $country = $listObj->getListDataCell($row['country_name']);
            }

            if ($cpCfg['cp.hasProjectMg'] == 1) {
                $extraCols .= $listObj->getListDataCell($row['team']);
                $extraCols .= $listObj->getListDataCell($row['staff_type']);
                if ($cpCfg['m.core.hasStaffGroup'] == 1) {
                    $extraCols .= $listObj->getListDataCell($row['staff_group_names']);
                }
                $extraCols .= $listObj->getListDataCell($row['status']);
            }

            if ($cpCfg['m.core.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
                $userGrp = $listObj->getListDataCell($row['user_group_title']);
            }
            if ($cpCfg['m.core.staff.showShortCode'] == 1){
                $shortCode = $listObj->getListDataCell($row['short_code']);
            }

            $rows .="
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['first_name'])}
            {$country}
            {$userGrp}
            {$listObj->getListDataCell($row['email'])}
            {$shortCode}
            {$extraCols}
            {$fn->getSiteFldForList($row)}
            {$listObj->getListDataCell($row[$cpCfg['cp.modAccessStaffIdLabel']], "center")}
            {$listObj->getListPublishedImage($row['published'], $row[$cpCfg['cp.modAccessStaffIdLabel']])}
            {$listObj->getListRowEnd($row[$cpCfg['cp.modAccessStaffIdLabel']])}
            ";

            $rowCounter++;
        }

        $extraCols = "";
        $shortCode = '';

       	if ($cpCfg['cp.hasProjectMg'] == 1) {
           $extraCols .= $listObj->getListHeaderCell('Team', 'a.team' );
           $extraCols .= $listObj->getListHeaderCell('Staff Type', 'a.staff_type');
           $extraCols .= $listObj->getListHeaderCell('Status', 'a.status');

           if ($cpCfg['m.core.hasStaffGroup'] == 1) {
               $extraCols .= $listObj->getListHeaderCell('Staff Group', 'staff_group_names');
           }
       	}

        if ($cpCfg['m.core.staff.showCountry'] == 1){
            $country = $listObj->getListHeaderCell('Country', 'co.country_name');
        }

        if ($cpCfg['m.core.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
            $userGrp = $listObj->getListHeaderCell($ln->gd('m.core.staff.lbl.group', 'Group'), 'b.title');
        }
        if ($cpCfg['m.core.staff.showShortCode'] == 1){
            $shortCode = $listObj->getListHeaderCell('Short Code', 'short_code');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.core.staff.lbl.firstName', 'Name'), 'a.first_name')}
        {$country}
        {$userGrp}
        {$listObj->getListHeaderCell($ln->gd('m.core.staff.lbl.email', 'Email'), 'a.email')}
        {$shortCode}
        {$extraCols}
        {$fn->getSiteLabelForList()}
        {$listObj->getListHeaderCell($ln->gd('m.core.staff.lbl.id', 'ID'), 'a.staff_id', 'headerCenter')}
        {$listObj->getListHeaderCell($ln->gd('m.core.staff.lbl.published', 'Published'), 'a.published', 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $fielset = "
        {$formObj->getTBRow($ln->gd('m.core.staff.lbl.firstName', 'Name'), 'first_name')}
        {$formObj->getTBRow($ln->gd('m.core.staff.lbl.email', 'Email'), 'email')}
        {$fnModCountry->getCountryDropDown('new')}
        {$fn->getSiteDropDown('new')}
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

            $exp = array('isEditable' => 0);
            if ($_SESSION['userGroupName'] == 'Super Administrator'){
                $staffRate   = $formObj->getTBRow('Staff Rate', 'staff_rate', $row['staff_rate']);
            } else {
                $staffRate   = $formObj->getTBRow('Staff Rate', 'staff_rate', $row['staff_rate'], $exp);
            }
        }

        if ($cpCfg['cp.hasFirstRoomValueInStaff'] == 1) {
            $sectionName = $formObj->getDDRowByArr("Login Section Default", "section_name", $am->getSectionNameArray(), $row['section_name']);
        }

        if ($cpCfg['m.core.staff.showStaffDescription'] == 1) {
            $description = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));
        }

        $fnMod = includeCPClass('ModuleFns', 'core_staff');

        $userGrp = '';

        if ($cpCfg['m.core.staff.showUserGroup'] == 1 || $cpCfg['cp.hasAccessModule']){
            $exp = array('hideFirstOption' => 1, 'detailValue' => $row['user_group_title']);

            $sqlUG = "
            SELECT user_group_id
                  ,title
            FROM {$cpCfg['cp.modAccessUserGroupTable']}
            ";

            $sqlUG = $fn->getSQL($sqlUG);
            $userGrp = $formObj->getDDRowBySQL($ln->gd('m.core.staff.lbl.userGroup', 'User Group'), 'user_group_id', $sqlUG, $row['user_group_id'], $exp);
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
            $lblPassword = $ln->gd('m.core.staff.lbl.password', 'Password');
            if ($row['pass_word'] != '') {
                $has_pwd = 1;
                $lblPassword = $ln->gd('m.core.staff.lbl.changePassword', 'Change Password');
            }
            $passwordRow = "
            {$formObj->getTBRow($lblPassword, 'pass_word')}
            <input type='hidden' name='has_pwd' value='{$has_pwd}' />
            ";

            $exp = array('isEditable' => 0);
            $emailRow = "
            {$formObj->getTBRow($ln->gd('m.core.staff.lbl.email', 'Email'), 'email', $row['email'], $exp)}
            <input type='hidden' name='email' value='{$row['email']}' />
            ";

        } else {
            $passwordRow = "{$formObj->getTBRow($ln->gd('m.core.staff.lbl.password', 'Password'), 'pass_word', $row['pass_word'])}";
            $emailRow = $formObj->getTBRow('Email', 'email', $row['email']);
        }
        
        $chngPwdNxtLogin = '';
        if($cpCfg['m.core.staff.hasChangePasswordNextLogin']){
            $chngPwdNxtLogin = $formObj->getYesNoRRow($ln->gd('m.core.staff.lbl.changePwdOnNext', 'Change password on next login'), 'change_password_next_login', $row['change_password_next_login']);
        }
        
        $commissionDetails = '';
        if($cpCfg['m.core.staff.hasCommissionDetails'] && $_SESSION['userGroupName'] == "Super Administrator"){

            $sqlCommType = $fn->getValueListSQL('commissionType');

            $commissionDetails = "
            {$formObj->getTBRow('Commission Rate', 'staff_commission_rate', $row['staff_commission_rate'])}
            {$formObj->getDDRowBySQL('Commission Type', 'commission_type', $sqlCommType, $row['commission_type'], $expVl)}
            ";
        }
        

        $fielset1 = "
        {$formObj->getTBRow($ln->gd('m.core.staff.lbl.firstName', 'Name'), 'first_name', $row['first_name'])}
        {$fnModCountry->getCountryDropDown($formObj->mode, $row)}
        {$emailRow}
        {$passwordRow}
        {$formObj->getDDRowByArr($ln->gd('m.core.staff.lbl.status', 'Status'), 'status', $fnMod->getStaffStatusArray(), $row['status'])}
        {$userGrp}
        {$shortCode}
        {$staffTeam}
        {$staffType}
        {$sectionName}
        {$staffRate}
        {$sensDetails}
        {$formObj->getTBRow($ln->gd('m.core.staff.lbl.position', 'Position'), 'position', $row['position'])}
        {$formObj->getYesNoRRow($ln->gd('m.core.staff.lbl.published', 'Published'), 'published', $row['published'])}
        {$fn->getSiteDropDown($formObj->mode, $row)}
        {$chngPwdNxtLogin}
        {$commissionDetails} 
        ";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_title']);

        $fielset2 = "
        {$formObj->getTBRow($ln->gd('m.core.staff.lbl.streetAddress', 'Street Address'), 'address_street', $row['address_street'])}
        {$formObj->getTBRow($ln->gd('m.core.staff.lbl.town', 'Town / Suburb'), 'address_town', $row['address_town'])}
        {$formObj->getTBRow($ln->gd('m.core.staff.lbl.state', 'State'), 'address_state', $row['address_state'])}
        {$zipCodeText}
        {$formObj->getDDRowBySQL($ln->gd('m.core.staff.lbl.country', 'Country'), 'address_country', $sqlCountry, $row['address_country'], $expCountry)}
        {$description}
        ";

        if ($description != ''){
            $description = "
            {$formObj->getFieldSetWrapped($ln->gd('m.core.staff.lbl.description', 'Description'), $description )}
            ";
        }

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.core.staff.lbl.staffDetails', 'Staff Details'), $fielset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.core.staff.lbl.address', 'Address'), $fielset2)}
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
        $ln = Zend_Registry::get('ln');
        
        $staffGroup = "";
        $signature  = "";
        $staffCommission  = "";

        if ($cpCfg['m.core.hasStaffGroup'] == 1) {
            $staffGroup = $displayLinkData->getLinkPortalMain("core_staff", "project_staffGroupLink", $ln->gd('m.core.staff.link.staffGroupLinked', 'Staff Group Linked'), $row);
        }

        if ($cpCfg['cp.hasProjectMg'] == 1) {
            $signature = $media->getRightPanelMediaDisplay("Signature", "core_staff", $ln->gd('m.core.staff.link.signature', 'signature'), $row);
        }

        if ($cpCfg['m.core.staff.hasStaffCommission']) {
            $staffCommission = $displayLinkData->getLinkPortalMain("core_staff", "manPower_staffCommissionLink", "Staff Commission", $row);
        }

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('m.core.staff.link.picture', 'Picture'), "core_staff", "picture", $row)}
        {$signature}
        {$staffGroup}
        {$staffCommission}
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
        $ln = Zend_Registry::get('ln');

        $stfGrp     = '';
        $statusTxt  = '';
        $countryTxt = '';
        $userGrp    = '';

        $user_group_id  = $fn->getReqParam('user_group_id');
        $staff_group_id = $fn->getReqParam('staff_group_id');
        $status         = $fn->getReqParam('status');

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
                    <option value=''>{$ln->gd('m.core.staff.lbl.group', 'Group')}</option>
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
                    <option value=''>{$ln->gd('m.core.staff.lbl.staffGroup', 'Staff Group')}</option>
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
                    <option value=''>{$ln->gd('m.core.staff.lbl.status', 'Status')}</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlCombo, $status)}
                </select>
            </td>
            ";
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');

        $text = "
        {$userGrp}
        {$stfGrp}
        {$statusTxt}
        {$fnModCountry->getCountryDropDown('search')}
        {$fn->getSiteDropDown('search')}
        ";

        return $text;
    }
}