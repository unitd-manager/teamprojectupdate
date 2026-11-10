<?
class CP_Admin_Modules_Account_Contact_View extends CP_Common_Lib_ModuleViewAbstract
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

            if($cpCfg['m.account.contact.hasCompanyTable'] == 1){
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

            $site = '';
            if($cpCfg['cp.hasMultiSites']){
                $site = "
                {$listObj->getListDataCell($row['site_title'] )}
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['salutation'] )}
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

        if($cpCfg['m.account.contact.hasCompanyTable'] == 1){
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

        $site = '';
        if($cpCfg['cp.hasMultiSites']){
            $site = "
            {$listObj->getListHeaderCell('Site', 'site_title')}
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Salutation', 'c.salutation')}
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

        $formObj->mode  = $tv['action'];

        $fieldset1 = "
        {$formObj->getTBRow('Salutation', 'c.salutation', $row['salutation'])}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
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
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        ";

        $subscribed = ($tv['newRecord'] == 1) ? 1 : $row['subscribe'];

        $fieldset4 = "
        {$formObj->getYesNoRRow('Newsletter Subscribed', 'subscribe', $subscribed)}
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

        if($cpCfg['m.account.contact.showInterest'] == 1){
            $rows .= $displayLinkData->getLinkPortalMain("account_contact", "common_interestLink", "Interests Linked", $row);
        }

        $record_id = $fn->getIssetParam($row, 'contact_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'account_contact', 'picture', $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'account_contact'
            ,'recordId' => $record_id
        ))}
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
        $language       = $fn->getReqParam('language');
        $interestText   = "";
        $languageText   = '';

        if ($cpCfg['m.account.contact.showInterest'] == 1) {
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
        $spArray = $cpCfg['m.common.contact.specialSearchArr'];
        
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

        $url = 'index.php?_spAction=streamFile&showHTML=0&modname=account_contact&filename=contact-import-template.xls';
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
        if($cpCfg['m.account.contact.showInterestInImport']){
            $sqlInterest = $fn->getDDSql('common_interest');
            $text .= $formObj->getDDRowBySQL('Interest', 'interest_id', $sqlInterest);
        }

        return $text;
    }
}