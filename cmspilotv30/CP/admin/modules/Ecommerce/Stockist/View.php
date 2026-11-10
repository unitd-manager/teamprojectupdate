<?
class CP_Admin_Modules_Ecommerce_Stockist_View extends CP_Common_Modules_Ecommerce_Stockist_View
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $rows = '';

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $email = $row['email'];

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['first_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['last_name'])}
            {$listObj->getListDataCell($row['email'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['stockist_type'])}
            {$listObj->getListDataCell($row['phone'])}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListPublishedImage($row['published'], $row['stockist_id'])}
            {$listObj->getListRowEnd($row['stockist_id'])}
            ";

            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('First Name', 's.first_name')}
        {$listObj->getListHeaderCell('Last Name', 's.last_name')}
        {$listObj->getListHeaderCell('Email', 's.email')}
        {$listObj->getListHeaderCell('Company Name', 's.company_name')}
        {$listObj->getListHeaderCell('Stockist Type', 's.stockist_type')}
        {$listObj->getListHeaderCell('Phone', 's.phone')}
        {$listObj->getListHeaderCell('Country', 'country_name')}
        {$listObj->getListHeaderCell('Published', 's.published', 'headerCenter')}
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

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();

        $fielset = "
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry)}
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
        $fn = Zend_Registry::get('fn');

        $sqlStkType = $fn->getValueListSQL('stockistType');
        $expVl      = array('sqlType' => 'OneField');

        $paypalEmail = '';
        if ($cpCfg['m.ecommerce.stockist.hasPaypalEmail']){
            $paypalEmail = $formObj->getTBRow('Paypal Email', 'paypal_email', $row['paypal_email'] );
        }

        $shippingCharge = '';
        if ($cpCfg['m.ecommerce.stockist.hasShippingCharge']){
            $shippingCharge = $formObj->getTBRow('Shipping Charge', 'shipping_charge', $row['shipping_charge'] );
        }        
        
        $formObj->mode  = $tv['action'];

        $fieldset1 = "
        {$formObj->getDDRowBySQL('Stockist Type', 'stockist_type', $sqlStkType, $row['stockist_type'], $expVl)}
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('First Name', 'first_name', $row['first_name'])}
        {$formObj->getTBRow('Last Name', 'last_name', $row['last_name'])}
        {$formObj->getTBRow('Email', 'email', $row['email'])}
        {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
        {$formObj->getTBRow('Fax', 'fax', $row['fax'])}
        {$formObj->getTBRow('Mobile', 'mobile', $row['mobile'])}
        {$paypalEmail}
        {$shippingCharge}
        ";
        
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['country_name']);
        
        $fieldset2 = "
        {$formObj->getTBRow('Address 1', 'address1', $row['address1'])}
        {$formObj->getTBRow('Address 2', 'address2', $row['address2'])}
        {$formObj->getTBRow('City/Town', 'address_city', $row['address_city'])}
        {$formObj->getTBRow('State', 'address_state', $row['address_state'])}
        {$formObj->getTBRow('Zip Code', 'address_po_code', $row['address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'address_country_code', $sqlCountry, $row['address_country_code'], $expCountry)}
        ";

        $fieldset3 = "
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Address Details', $fieldset2)}
        {$formObj->getFieldSetWrapped('Other Details', $fieldset3)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'stockist_id');

        $text = "
        {$media->getRightPanelMediaDisplay('Picture', 'ecommerce_stockist', 'picture', $row)}
        {$comment->getView(array(
             'roomName' => 'ecommerce_stockist'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $stockist_type = $fn->getReqParam('stockist_type');
        $country = $fn->getReqParam('country');

        $sqlStkType = $fn->getValueListSQL('stockistType');

        $SQLCountry = "
        SELECT gc.country_code
              ,gc.name
        FROM geo_country gc
        WHERE gc.country_code IN  (
            SELECT DISTINCT address_country_code FROM stockist
        )
        ORDER BY gc.name
        ";

        $text = "
        <td>
            <select name='country'>
                <option value=''>Country</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLCountry, $country)}
            </select>
        </td>
        <td>
            <select name='stockist_type'>
                <option value=''>Stockist Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlStkType, $stockist_type)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['cp.specialSearchArr'], $tv['special_search'])}
            </select>
        </td>
        ";

        
        return $text;
    }
}