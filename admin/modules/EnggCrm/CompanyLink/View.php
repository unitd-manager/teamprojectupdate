<?
class CPL_Admin_Modules_EnggCrm_CompanyLink_View extends CP_Admin_Modules_EnggCrm_CompanyLink_View
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

        $category = $fn->getReqParam('category');
        $status   = $fn->getReqParam('status');
        
        if ($category) {
            $category = $category;
        } else {
            $category = 'Client';
        }

        $sqlCat = $fn->getValueListSQL('companyCategory');
        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='category'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCat, $category)}
            </select>
        </td>    
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
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
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlTitle = $fn->getValueListSQL('contactTitle');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $address = '';

        if ($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 0){
            $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
            $address = "
            {$formObj->getTBRow('Address 1', 'address_flat')}
            {$formObj->getTBRow('Address 2', 'address_street')}
            {$formObj->getTBRow('Postal Code', 'address_po_code')}
            {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry)}
            ";

            $address = $formObj->getFieldSetWrapped('Address', $address);
        }

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Comany Name', 'company_name')}
                {$formObj->getTARow('Address 1', 'address_flat')}
               
            </fieldset>
       
        </form>
        ";

        return $text;
    }

}
