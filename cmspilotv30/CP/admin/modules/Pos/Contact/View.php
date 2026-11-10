<?
class CP_Admin_Modules_Pos_Contact_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $textCompanyTable  = '';
        $textWebLogin      = '';
        $textTestWeb       = '';
        $textSalutation    = '';
        $rowsCompanyTable  = '';
        $rowsWebLogin      = '';
        $rowsPublishedTest = '';
        $rowsSalutation    = '';

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = $row['email'];

            if($cpCfg['m.common.contact.hasCompanyTable'] == 1){
                $rowsCompanyTable = "
                <td>
                    <div align='left'>
                        <a href='index.php?_topRm=project&module=company&_action=edit&company_id={$row['company_id']}'>{$row['c_company_name']}</a>
                    </div>
            	</td>
            	{$listObj->getListDataCell($row['c_phone'])}
            	";
            } else {
                $rowsCompanyTable = "
                {$listObj->getListDataCell($row['company_name']   )}
                {$listObj->getListDataCell($row['phone']          )}
                ";
            }

            if($cpCfg['m.common.contact.hasWebLogin'] == 1){
                $rowsWebLogin = "
                {$listObj->getListPublishedImage($row['published'], $row['contact_id'])}
                ";
            }

            if($cpCfg['m.common.contact.hasSalutation'] == 1){
                $rowsSalutation = "
                {$listObj->getListDataCell($row['salutation'] )}
                ";
            }

            $site = '';
            if($cpCfg['cp.hasMultiSites']){
                $site = "
                {$listObj->getListDataCell($row['site_title'] )}
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$rowsSalutation}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
            {$rowsCompanyTable}
            {$site}
            {$listObj->getListDataCell($row['phone_direct'])}
            {$rowsWebLogin}
            {$listObj->getListDataCell($fn->getYesNo($row['subscribe']), "center")}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $rowCounter++ ;
        }

        if($cpCfg['m.common.contact.hasCompanyTable'] == 1){
            $textCompanyTable = "
            {$listObj->getListHeaderCell('Company Name', 'b.company_name')}
            {$listObj->getListHeaderCell('Phone (Main)', 'b.phone')}
            ";
        } else{
            $textCompanyTable = "
            {$listObj->getListHeaderCell('Company Name', 'c.company_name')}
            {$listObj->getListHeaderCell('Phone (Main)', 'c.phone')}
            ";
        }

        if($cpCfg['m.common.contact.hasWebLogin'] == 1){
            $textWebLogin = "
            {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
            ";
        }

        if($cpCfg['m.common.contact.hasSalutation'] == 1){
            $textSalutation = "
            {$listObj->getListHeaderCell('Salutation', 'c.salutation')}
            ";
        }

        $site = '';
        if($cpCfg['cp.hasMultiSites']){
            $site = "
            {$listObj->getListHeaderCell('Site', 'site_title')}
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$textSalutation}
        {$listObj->getListHeaderCell('First Name', 'c.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'c.last_name')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$textCompanyTable}
        {$site}
        {$listObj->getListHeaderCell('Phone (Direct)', 'c.phone_direct')}
        {$textWebLogin}
        {$listObj->getListHeaderCell('Subscribed', 'c.subscribe', 'headerCenter')}
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

        $fielset = "
        {$formObj->getTBRow('First Name', 'first_name')}
        {$formObj->getTBRow('Last Name', 'last_name')}
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
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');

        $formObj->mode  = $tv['action'];
        $textSalutation = '';

        if($cpCfg['m.common.contact.hasSalutation'] == 1){
            $textSalutation = "
            {$formObj->getTBRow('Salutation', 'c.salutation', $row['salutation'])}
            ";
        }

        $passwordRow = '';
        $emailRow = '';
        if ($cpCfg['cp.hasPasswordSalt']) {
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
            $passwordRow = $formObj->getTBRow('Password', 'pass_word', $row['pass_word']);
            $emailRow = $formObj->getTBRow('Email', 'email', $row['email']);
        }

        $gender = $fn->getValuelistSql('gender');
        $exp = array('sqlType' => 'OneField');
        $expEdit = array('isEditable' => 0);

        $birthDateYear = $cpUtil->getYearListArray();
        $birthDateMonth = $cpUtil->getMonthArray();
        $expMonth = array('useKey' => 1);
        $birthDateDay = $cpUtil->getDayArray();

        $rowMember = $fn->getSettingsRowByKey('member');

        if ($rowMember['auto_generate_no'] == 1) {
            $memberNo = $formObj->getTBRow('Member No', 'member_no', $row['member_no'], $expEdit);
        } else {
            $memberNo = $formObj->getTBRow('Member No', 'member_no', $row['member_no']);
        }

        $fieldset1 = "
        {$memberNo}
        {$formObj->getTBRow('Reference no (ID Card)', 'reference_no', $row['reference_no'])}
        {$textSalutation}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$emailRow}
        {$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$formObj->getDDRowByArr('Birth Date (Year)', 'birth_date_year', $birthDateYear, $row['birth_date_year'])}
        {$formObj->getDDRowByArr('Birth Date (Month)', 'birth_date_month', $birthDateMonth, $row['birth_date_month'], $expMonth)}
        {$formObj->getDDRowByArr('Birth Date (Day)', 'birth_date_day', $birthDateDay, $row['birth_date_day'])}
        {$formObj->getDDRowBySQL('Sex', 'sex', $gender, $row['sex'], $exp)}
        {$formObj->getTARow('Remark', 'remark', $row['remark'])}
        {$formObj->getDateRow('Expiry Date From', 'expiry_date_from', $row['expiry_date_from'])}
        {$formObj->getDateRow('Expiry Date To', 'expiry_date_to', $row['expiry_date_to'])}
        {$formObj->getYesNoRRow('Auto Upgrade Member group', 'auto_upgrade_mem_grp', $row['auto_upgrade_mem_grp'])}
        {$formObj->getTBRow('Total Sales', 'total_sales', '', $expEdit)}
        {$formObj->getTBRow('Bonus', 'bonus', '', $expEdit)}
        {$formObj->getTBRow('Bonus Used', 'bonus_used', '', $expEdit)}
        {$formObj->getTBRow('Remain Bonus', 'remain_bonus', '', $expEdit)}
        ";

        $fieldset2 = "
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('Main Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Position', 'position', $row['position'])}
        {$formObj->getTBRow('Department', 'department', $row['department'])}
        ";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $fieldset3 = "
        {$formObj->getTBRow('Address 1', 'address1', $row['address1'])}
        {$formObj->getTBRow('Address 2', 'address2', $row['address2'])}
        {$formObj->getTBRow('City/Town', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('District', 'address_district', $row['address_district'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        ";

        $textWebLogin = '';
        $username = '';

        if($cpCfg['m.common.contact.showUsername'] == 1){
            $username = "
            {$formObj->getTBRow('Username', 'user_name', $row['user_name'])}
            ";
        }

        if($cpCfg['m.common.contact.hasWebLogin'] == 1){
            $textWebLogin = "
            {$username}
            {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
            {$passwordRow}
            ";
        }

        $subscribed = ($tv['newRecord'] == 1) ? 1 : $row['subscribe'];

        $langPref = '';
        if ($cpCfg['m.common.contact.showLangPrefernce']){
            $langPref = "{$formObj->getRRow('Language Preference', 'language', $row['language'], $cpCfg['cp.availableLanguages'])}";
        }

        $fieldset4 = "
        {$textWebLogin}
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $subscribed)}
        {$langPref}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Company Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Address Details', $fieldset3)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset4)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $rows = "";
        $links= "";

        if($cpCfg['m.common.contact.showInterest'] == 1){
            $rows .= $displayLinkData->getLinkPortalMain("pos_contact", "common_interestLink", "Member Group Linked", $row);
        }

        if($cpCfg['m.common.contact.showEvent'] == 1){
            $rows .= $displayLinkData->getLinkPortalMain("pos_contact", "event_eventLink", "Events Linked", $row);
        }

        if($cpCfg['m.common.contact.showContentLink'] == 1){
            $links .= $displayLinkData->getLinkPortalMain("pos_contact", "webBasic_contentLink", "Content Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'contact_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'pos_contact', 'picture', $row)}
        {$rows}
        {$links}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $interest_id    = $fn->getReqParam('interest_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $category       = $fn->getReqParam('category');
        $interestText   = "";

        if ($cpCfg['m.common.contact.showInterest'] == 1) {
            $sqlInterest = $fn->getDDSql('common_interest');

            $interestText = "
            <td>
                <select name='interest_id' >
                    <option value=''>Interest Group</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlInterest, $interest_id)}
                </select>
            </td>
            ";
        }

        //==================================================================//
        $spArray = array(
              "Subscribed"
             ,"Not-Subscribed"
             ,"Flagged"
             ,"Not-Flagged"
        );

        $text = "
        {$interestText}
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";


        return $text;
    }

    /**
     *
     */
    function getImportInstructions() {
        $cpPaths = Zend_Registry::get('cpPaths');

        $url = 'index.php?_spAction=streamFile&showHTML=0&modname=common_contact&filename=contact-import-template.xls';
        $text = "
        <p>Accepted file type: xls</p>
        <p>Template: <a href='{$url}'>Download</a></p>
        ";

        return $text;
    }

    /**
     *
     * @return string
     */
    function getAdditionalImportFields(){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $text = '';
        if($cpCfg['m.common.contact.showInterestInImport']){
            $sqlInterest = $fn->getDDSql('common_interest');
            $text .= $formObj->getDDRowBySQL('Interest', 'interest_id', $sqlInterest);
        }

        return $text;
    }
}