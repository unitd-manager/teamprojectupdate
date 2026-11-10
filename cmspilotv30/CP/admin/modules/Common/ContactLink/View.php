<?
class CP_Admin_Modules_Common_ContactLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;
        $hasCompany = false;
        $company = '';

        foreach ($dataArray as $row){
            if($cpCfg['m.common.contact.hasCompanyTable'] == 1){
                $company = "
                {$listObj->getListDataCell($row['c_company_name'])}
            	";
            } else {
                if (array_key_exists('company_name', $row)){
                    $company = $listObj->getListDataCell($row['company_name']);
                    $hasCompany = true;
                }
            }
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['first_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$company}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
            ";
            $rowCounter++ ;
        }

        if($cpCfg['m.common.contact.hasCompanyTable'] == 1){
            $company = $listLinkObj->getListHeaderCellLink($linkRecType, 'Company Name', 'c_company_name');
        } else {
            if ($hasCompany){
                $company = $listLinkObj->getListHeaderCellLink($linkRecType, 'Company Name', 'c.company_name');
            }
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'first_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'c.email')}
        {$company}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

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
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

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
}
