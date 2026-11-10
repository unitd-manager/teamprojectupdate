<?
class CP_Admin_Modules_ManPower_CompanyLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['status'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['company_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Company Name', 'a.company_name')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Category', 'a.category')}
        {$listLinkObj->getListHeaderCellLink($linkRecType,'Status', 'a.status')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
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
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $status   = $fn->getReqParam('status');

        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
              "Flagged"
             ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
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
    function getAddNewCompanyForCallRegistryForm() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        
        $sqlCategory = $fn->getValueListSQL('companyCategory');
        $expVl       = array('sqlType' => 'OneField');
        $expEdit     = array('isEditable' => 0);

        $formAction = "index.php?_topRm=opportunity&module=manPower_companyLink&_spAction=addNewCompanyForCallRegistryFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <div class='company_record'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
            {$formObj->getTBRow('Company Name', 'company_name')}
            {$formObj->getDDRowBySQL('Choose Category', 'category', $sqlCategory, '', $expVl)}
            {$formObj->getTBRow('Main Phone', 'phone')}
        </form>
        ";

        return $text;
    }
}
