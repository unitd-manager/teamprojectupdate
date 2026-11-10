<?
class CP_Admin_Modules_Korban_Contact_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $textWebLogin      = '';
        $textTestWeb       = '';
        $textSalutation    = '';
        $rowsWebLogin      = '';
        $rowsPublishedTest = '';
        $rowsSalutation    = '';

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = $row['email'];

            if($cpCfg['m.korban.contact.hasWebLogin'] == 1){
                $rowsWebLogin = "
                {$listObj->getListPublishedImage($row['published'], $row['contact_id'])}
                ";
            }

            if($cpCfg['m.korban.contact.hasSalutation'] == 1){
                $rowsSalutation = "
                {$listObj->getListDataCell($row['salutation'] )}
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$rowsSalutation}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            {$listObj->getListDataCell($row['organization_name'])}
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
            {$listObj->getListDataCell($row['phone_direct']      )}
            {$rowsWebLogin}
            {$listObj->getListRowEnd($row['contact_id'])}
            ";

            $rowCounter++ ;
        }

        if($cpCfg['m.korban.contact.hasWebLogin'] == 1){
            $textWebLogin = "
            {$listObj->getListHeaderCell('Published', 'c.published', 'headerCenter')}
            ";
        }

        if($cpCfg['m.korban.contact.hasSalutation'] == 1){
            $textSalutation = "
            {$listObj->getListHeaderCell('Salutation', 'c.salutation')}
            ";
        }

        $text = "
        {$listObj->getListHeader()}
        {$textSalutation}
        {$listObj->getListHeaderCell('First Name', 'c.first_name')}
        {$listObj->getListHeaderCell('Last Name', 'c.last_name')}
        {$listObj->getListHeaderCell('Organization', 'organization_name')}
        {$listObj->getListHeaderCell('Email', 'c.email')}
        {$listObj->getListHeaderCell('Phone', 'c.phone_direct')}
        {$textWebLogin}
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
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $textSalutation = '';

        if($cpCfg['m.korban.contact.hasSalutation'] == 1){
            $textSalutation = "
            {$formObj->getTBRow('Salutation', 'c.salutation', $row['salutation'])}
            ";
        }

        $sqlOrg = $fn->getDDSql('korban_organization');
        $expOrg = array('detailValue' => $row['organization_name']);

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);

        $fieldset1 = "
        {$textSalutation}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Phone', 'phone_direct', $row['phone_direct'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        ";

        $fieldset2 = "
        {$formObj->getDDRowBySQL('Organization', 'organization_id', $sqlOrg, $row['organization_id'], $expOrg)}
        {$formObj->getTBRow('Position', 'position', $row['position'])}
        ";

        $fieldset3 = "
        {$formObj->getTBRow('Address 1', 'address1', $row['address1'])}
        {$formObj->getTBRow('Address 2', 'address2', $row['address2'])}
        {$formObj->getTBRow('City/Town', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        ";

        $textWebLogin = '';
        $username = '';

        if($cpCfg['m.korban.contact.showUsername'] == 1){
            $username = "
            {$formObj->getTBRow('Username', 'user_name', $row['user_name'])}
            ";
        }

        if($cpCfg['m.korban.contact.hasWebLogin'] == 1){
            $textWebLogin = "
            {$username}
            {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
            {$formObj->getTBRow('Password', 'pass_word', $row['pass_word'])}
            ";
        }

        $fieldset4 = "
        {$textWebLogin}
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

        $text = "
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
        $organization_id = $fn->getReqParam('organization_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $category       = $fn->getReqParam('category');
        $interestText   = "";

        if ($cpCfg['m.korban.contact.showInterest'] == 1) {
            $sqlCombo = "
            SELECT interest_id
                  ,title
            FROM interest
            ORDER BY title
            ";

            $interestText = "
            <td>
                <select name='interest_id' >
                    <option value=''>Interest Group</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $interest_id)}
                </select>
            </td>
            ";
        }

        $sqlOrg = $fn->getDDSql('korban_organization');

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
            <select name='organization_id'>
                <option value=''>Organization</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlOrg, $organization_id)}
            </select>
        </td>

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

}