<?
class CPL_Admin_Modules_EnggCrm_Company_View extends CP_Admin_Modules_EnggCrm_Company_View
{
    /**
     *
     */
    function getList($dataArray){
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');
        
        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $website   = $row['website'];

            $contatRec = $fn->getRecordByCondition('contact', "company_id = {$row['company_id']}", 'first_name ASC');
            $email     = is_array($contatRec) ? $contatRec['email'] : '';

            if($row['phone'] != ''){
                $phone = $row['phone'];
            } else {
                $phone = is_array($contatRec) ? $contatRec['mobile'] : '';
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['company_name'])}
            <td><div align='left'><a href='mailto:{$email}'>{$email}</a></div></td>
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($phone)}
            {$listObj->getListRowEnd($row['company_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Name', 'a.company_name')}
        {$listObj->getListHeaderCell('Email', 'a.email')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderCell('Phone', 'a.phone' )}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $chineseName = '';
        $chineseAdd  = '';
        $address     = '';
        $deliveryAddress = '';

        if ($cpCfg['m.enggCrm.company.showChineseFields'] == 1){
            $chineseName = $formObj->getTBRow('Company Name (Chinese)', 'chi_company_name', $row['chi_company_name']);
            $chineseAdd  = $formObj->getTARow('Company Address (Chinese)', 'chi_company_address', $row['chi_company_address']);
        }

        if ($cpCfg['m.enggCrm.hasMultipleCompanyAddress'] == 0){
            $address = "
            {$formObj->getTBRow('Address 1', 'billing_address_flat', $row['billing_address_flat'])}
            {$formObj->getTBRow('Address 2', 'billing_address_street', $row['billing_address_street'])}
            {$formObj->getTBRow('Country', 'billing_address_country', $row['billing_address_country'])}
            {$formObj->getTBRow('Postal Code', 'billing_address_po_code', $row['billing_address_po_code'])}
            ";
            $address = $formObj->getFieldSetWrapped('Billing Address', $address);

            $deliveryAddress = "
            {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
            {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
            {$formObj->getTBRow('Country', 'address_country', $row['address_country'])}
            {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
            ";
            $deliveryAddress = $formObj->getFieldSetWrapped('Delivery Address', $deliveryAddress);
        }

        $sqlStatus   = $fn->getValueListSQL('companyStatus');
        $sqlSupplier = $fn->getValueListSQL('supplierType');
        $sqlIndustry = $fn->getValueListSQL('companyIndustry');
        $sqlSize     = $fn->getValueListSQL('companySize');
        $sqlSource   = $fn->getValueListSQL('companySource');
        $sqlCountry  = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expVl = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Name *', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Website', 'website', $row['website'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Address 1', 'address_flat', $row['address_flat'])}
        {$formObj->getTBRow('Address 2', 'address_street', $row['address_street'])}
        {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, $row['address_country'])}
        {$formObj->getTBRow('Postal Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getTBRow('Latitude', 'latitude', $row['latitude'])}
        {$formObj->getTBRow('Longitude', 'longitude', $row['longitude'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Client Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
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

        $sqlStatus = $fn->getValueListSQL('companyStatus');

        $spArray = array(
            "Flagged"
           ,"Flagged"
           ,"Not-Flagged"
        );

        $text = "
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";
        
        return $text;
    }
}