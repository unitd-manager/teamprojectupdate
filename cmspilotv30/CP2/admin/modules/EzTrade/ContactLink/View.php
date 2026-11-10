<?
class CP_Admin_Modules_EzTrade_ContactLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows       = '';
        $rowCounter = 0;
        
        
        if ($linkRecType == 'notLinked'){
            foreach ($dataArray as $row){
                $rows .= "
                {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
                {$listObj->getListDataCell($row['contact_name'])}
                {$listObj->getListDataCell($row['email'])}
                {$listObj->getListDataCell($row['c_company_name'])}
                {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
                ";
                $rowCounter++ ;
            }
    
            $text = "
            {$listLinkObj->getListHeaderLink()}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'contact_name')}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'a.email')}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Company Name', 'b.company_name')}
            {$listLinkObj->getListHeaderEndLink($linkRecType)}
            {$rows}
            {$listLinkObj->getListFooterLink()}
            ";
        } else {
            foreach ($dataArray as $row){
                $rows .= "
                {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
                {$listObj->getListDataCell($row['contact_name'])}
                {$listObj->getListDataCell($row['email'])}
                {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
                ";
                $rowCounter++ ;
            }
    
            $text = "
            {$listLinkObj->getListHeaderLink()}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'contact_name')}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'a.email')}
            {$listLinkObj->getListHeaderEndLink($linkRecType)}
            {$rows}
            {$listLinkObj->getListFooterLink()}
            ";
        }
        
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

        $fnsModContact = includeCPClass('ModuleFns', 'ezTrade_contact');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Title', 'salutation', $sqlTitle, '', $exp)}
                {$formObj->getTBRow('First Name', 'first_name')}
                {$formObj->getTBRow('Last Name', 'last_name')}
                {$formObj->getTBRow('Email', 'email')}
                {$formObj->getTBRow('Position', 'position')}
                {$formObj->getTBRow('Department', 'department')}
                {$formObj->getPhoneNoRow2('Phone (Direct)', 'phone_country_code', 'phone_area_code', 'phone')}
                {$formObj->getPhoneNoRow2('Fax', 'fax_country_code', 'fax_area_code', 'fax')}
                {$formObj->getPhoneNoRow2('Mobile', 'mobile_country_code', 'mobile_area_code', 'mobile')}
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

        $fnsModContact = includeCPClass('ModuleFns', 'ezTrade_contact');

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
            {$formObj->getPhoneNoRow2('Phone (Direct)', 'phone_country_code', 'phone_area_code', 'phone',
                                      $row['phone_country_code'], $row['phone_area_code'], $row['phone'])}
            {$formObj->getPhoneNoRow2('Fax', 'fax_country_code', 'fax_area_code', 'fax',
                                      $row['fax_country_code'], $row['fax_area_code'], $row['fax'])}
            {$formObj->getPhoneNoRow2('Mobile', 'mobile_country_code', 'mobile_area_code', 'mobile',
                                      $row['mobile_country_code'], $row['mobile_area_code'], $row['mobile'])}
            </fieldset>
            <input type='hidden' name='contact_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
