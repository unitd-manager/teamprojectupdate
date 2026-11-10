<?
class CPL_Admin_Modules_EnggCrm_ContactLink_View extends CP_Admin_Modules_EnggCrm_ContactLink_View
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
                $rowCounter++;
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
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $company_id  = $fn->getReqParam('company_id');

        $sqlTitle = $fn->getValueListSQL('contactTitle');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $sqlCompany ="
        SELECT company_id, company_name FROM company
        WHERE category = 'Client'
        ORDER BY company_name
        ";
            
        $companyName = "";
        if ($tv['srcRoomId'] != ''){
            $companyFld = "<input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />";
        } else {
            $expComp  = array(
                 'autoSgstModule' => 'enggCrm_company'
                ,'autoSgstSrchFld' => 'company_name'
                ,'autoSgstActualFld' => 'company_id'
            );
            $companyFld = "
            {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, $company_id, $expComp)}
            ";

            $companyName = "";
            if($company_id != "") {
                $SQL2 = "
                SELECT company_name
                FROM company
                WHERE company_id = '{$company_id}'
                ";
                $result2 = $db->sql_query($SQL2);
                $row2    = $db->sql_fetchrow($result2);

                $companyName = "
                <div class='companyNameInBgContactLink'>
                    {$row2['company_name']}
                </div>
                ";
            }
        }

        if($company_id > 0) {
            $text = "
            <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
                <fieldset>
                    {$formObj->getTBRow('Contact Name', 'first_name')}
                    {$formObj->getTBRow('Email', 'email')}
                   
                    {$formObj->getTBRow('Mobile', 'mobile')}
                    <input name='company_id' value='{$company_id}' type='hidden'>
                </fieldset>
            </form>
            ";
        } else {
            $text = "<h3 class='h3 txtCenter noCompanyContactLink'>Please choose company</h3>";
        }

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
                {$formObj->getTBRow('Name', 'first_name', $row['first_name'])}
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
