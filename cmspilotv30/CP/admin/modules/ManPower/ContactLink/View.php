<?
class CP_Admin_Modules_ManPower_ContactLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

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

        $sqlTitle  = $fn->getValueListSQL('salutation');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        if ($tv['srcRoomId'] != ''){
            $companyFld = "<input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />";
        } else {
            $expComp  = array(
                 'autoSgstModule' => 'project_company'
                ,'autoSgstSrchFld' => 'company_name'
                ,'autoSgstActualFld' => 'company_id'
            );
            $companyFld = "
            {$formObj->getTBRow('Company Name', 'company_name', '', $expComp)}
            ";
        }

        $member_type = '';
        if ($tv['srcRoom'] == 'manPower_company'){
            $member_type = 'Client';
        } else if ($tv['srcRoom'] == 'manPower_agent'){
            $member_type = 'Agent';
        }

        $contact_priority = array('1','2','3','4','5');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$companyFld}
                {$formObj->getDDRowByArr('Primary Contact', 'contact_priority',$contact_priority)}
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
            <input type='hidden' name='member_type' value='{$member_type}' />

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

        $sqlTitle  = $fn->getValueListSQL('salutation');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('contact', 'contact_id', $id);

        $contact_priority = array('1','2','3','4','5');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowByArr('Primary Contact', 'contact_priority',$contact_priority, $row['contact_priority'])}
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
