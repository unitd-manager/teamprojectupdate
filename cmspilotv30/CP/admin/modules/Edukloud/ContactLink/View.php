<?
class CP_Admin_Modules_Edukloud_ContactLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        
        if ($tv['srcRoom'] == 'edukloud_class' && $tv['lnkRoom'] == 'edukloud_contactLink') {
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

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['last_name'])}
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
        {$listLinkObj->getListHeaderCellLink($linkRecType,'First Name', 'first_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Last Name', 'last_name')}
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
            {$listObj->getListDataCell($row['class_title'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'first_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'ID Card No.', 'c.id_card_no')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Class', 'c.class_title')}
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
        
        if ($tv['srcRoom'] == 'edukloud_resources' && $tv['lnkRoom'] == 'edukloud_contactLink') {
            return $this->getNewForResources();
        }

        $sqlTitle = $fn->getValueListSQL('contactTitle');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Title', 'salutation', $sqlTitle, '', $exp)}
                {$formObj->getTBRow('First Name', 'first_name')}
                {$formObj->getTBRow('Last Name', 'last_name')}
                {$formObj->getTBRow('Email', 'email')}
                {$formObj->getTBRow('Position', 'position')}
                {$formObj->getTBRow('Department', 'department')}
                {$formObj->getTBRow('Phone (Direct)', 'phone_direct')}
                {$formObj->getTBRow('Fax (Direct)', 'fax')}
                {$formObj->getTBRow('Mobile', 'mobile')}
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

        $sqlContact = $fn->getDDSql('edukloud_contact');

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

        if ($tv['srcRoom'] == 'edukloud_resources' && $tv['lnkRoom'] == 'edukloud_contactLink') {
            return $this->getEditForResources();
        }

        $sqlTitle = $fn->getValueListSQL('contactTitle');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('contact', 'contact_id', $id);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Title', 'salutation', $sqlTitle, $row['salutation'], $exp)}
                {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
                {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
                {$formObj->getTBRow('Email', 'email', $row['email'])}
                {$formObj->getTBRow('Position', 'position', $row['position'])}
                {$formObj->getTBRow('Department', 'department', $row['department'])}
                {$formObj->getTBRow('Phone (Direct)', 'phone_direct', $row['phone_direct'])}
                {$formObj->getTBRow('Fax (Direct)', 'fax', $row['fax'])}
                {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
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
        
        $sqlContact = $fn->getDDSql('edukloud_contact');
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
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $interest_id    = $fn->getReqParam('interest_id');
        $class_id       = $fn->getReqParam('class_id');
        $subscribe      = $fn->getReqParam('subscribe');
        $special_search = $fn->getReqParam('special_search');
        $category       = $fn->getReqParam('category');
        $language       = $fn->getReqParam('language');
        $interestText   = "";
        $languageText   = '';

        $sqlInterest = $fn->getDDSql('common_interest');
        $sqlClass    = $fn->getDDSql('edukloud_class');

        $interestText = "
        <td>
            <select name='interest_id' >
                <option value=''>Interest Group</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlInterest, $interest_id)}
            </select>
        </td>
        ";

        if ($cpCfg['m.common.contact.showLangPrefernce'] == 1) {
            $languageText = "
            <td>
                <select name='language' >
                    <option value=''>Language Preference</option>
                    {$cpUtil->getDropDown1($cpCfg['cp.availableLanguages'], $language, true)}
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
        {$languageText}
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        <td>
            <select name='class_id' >
                <option value=''>Class</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlClass, $class_id)}
            </select>
        </td>
        ";


        return $text;
    }
}
