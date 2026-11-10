<?
class CP_Admin_Modules_Ecommerce_Order_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $currency = strtoupper($row['currency']);
            $ok_to_ship = '';
            if($cpCfg['m.ecommerce.order.showOkToShip']){
                $ok_to_ship = $listObj->getListDataCell($fn->getYesNo($row['ok_to_ship']), 'center');
            }
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['order_id'])}
            {$listObj->getListDataCell($row['shipping_first_name'] . " " . $row['shipping_last_name'])}
            {$listObj->getListDataCell($row['creation_date'])}
            {$listObj->getListDataCell($currency.'&nbsp;'.$row['order_amount'])}
            {$listObj->getListDataCell($row['order_status'])}
            {$ok_to_ship}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $ok_to_ship = '';
        if($cpCfg['m.ecommerce.order.showOkToShip']){
            $ok_to_ship = $listObj->getListHeaderCell('Ok to Ship', 'o.ok_to_ship', 'headerCenter');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Order Id', 'o.order_id')}
        {$listObj->getListHeaderCell('Contact Name', 'o.cust_name')}
        {$listObj->getListHeaderCell('Order Date', 'o.creation_date')}
        {$listObj->getListHeaderCell('Amount', '')}
        {$listObj->getListHeaderCell('Status', 'o.order_status')}
        {$ok_to_ship}
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
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getDateRow('Order Date', 'order_date')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $expStatus = array('sqlType' => 'OneField');
        $expNoEdit = array('isEditable' => 0);

        $creation_date = $dateUtil->formatDate($row['creation_date'], 'DD MM YYYY');

        $currency = strtoupper($row['currency']);

        $org = '';
        if ($cpCfg['m.ecommerce.order.showOrganization']) {
            $sqlOrg = "
            SELECT organization_id
                  ,name
            FROM organization
            ";
            $expOrg = array('detailValue' => $row['organization_name']);
            $org = $formObj->getDDRowBySQL('Organization', 'organization_id', $sqlOrg, $row['organization_id'], $expOrg);
        }

        $shipment_status = '';
        if($cpCfg['m.ecommerce.order.showShipmentStatus']){
            $shipment_status = $formObj->getDDRowByArr('Shipment Status', 'shipment_status', $cpCfg['m.ecommerce.order.shipmentStatusArr'], $row['shipment_status'], $expStatus);
        }

        $ok_to_ship = '';
        if($cpCfg['m.ecommerce.order.showOkToShip']){
            $ok_to_ship = $formObj->getYesNoRRow('Ok to Ship', 'ok_to_ship', $row['ok_to_ship']);
        }

        $DHL_AWB = '';
        if($cpCfg['m.ecommerce.order.showDHLAirwayBill']){
            $DHL_AWB = $formObj->getTBRow('DHL Airway Bill Number', 'dhl_airway_bill_number', $row['dhl_airway_bill_number'], $expNoEdit);
            if($row['dhl_dct_status_condition_code'] != ''){ //capability error
                $DHL_AWB .= $formObj->getTBRow('DHL Error Code', 'dhl_status_condition_code', $row['dhl_dct_status_condition_code'], $expNoEdit);
                $DHL_AWB .= $formObj->getTBRow('DHL Error', 'dhl_status_condition_data', $row['dhl_dct_status_condition_data'], $expNoEdit);
            } else if($row['dhl_sv_status_condition_code'] != ''){ //shipmentValidate error
                $DHL_AWB .= $formObj->getTBRow('DHL Error Code', 'dhl_status_condition_code', $row['dhl_sv_status_condition_code'], $expNoEdit);
                $DHL_AWB .= $formObj->getTBRow('DHL Error', 'dhl_status_condition_data', $row['dhl_sv_status_condition_data'], $expNoEdit);
            }
        }

        $discount = '';
        if ($cpCfg['m.ecommerce.order.hasDiscount']){
            $discount = $formObj->getTBRow('Discount', 'discount', $row['discount']);
        }
        
        $fielset1 = "
        {$formObj->getTBRow('Order Id', 'order_id', $row['order_id'], $expNoEdit)}
        {$formObj->getTBRow('Invoice No', 'order_code', $row['order_code'], $expNoEdit)}
        {$formObj->getDateRow('Order Date', 'creation_date', $creation_date)}
        {$formObj->getTBRow('Amount', 'amount', $currency.'&nbsp;'.$row['order_amount'], $expNoEdit)}
        {$formObj->getTBRow('Shipping Charge', 'shipping_charge', $row['shipping_charge'])}
        {$discount}
        {$formObj->getDDRowByArr('Status', 'order_status', $cpCfg['m.ecommerce.order.statusArr'], $row['order_status'], $expStatus)}
        {$formObj->getDDRowByArr('Payment Method', 'payment_method', $cpCfg['m.ecommerce.order.paymentMethodsArr'], $row['payment_method'])}
        {$formObj->getTARow('User Memo', 'memo', $row['memo'])}
        {$shipment_status}
        {$ok_to_ship}
        {$DHL_AWB}
        {$org}
        ";

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry1 = array('detailValue' => $row['cust_country_name']);
        $expCountry2 = array('detailValue' => $row['shipping_country_name']);

        $fielset2 = "
        {$formObj->getTBRow('First Name', 'cust_first_name', $row['cust_first_name'])}
        {$formObj->getTBRow('Last Name', 'cust_last_name', $row['cust_last_name'])}
        {$formObj->getTBRow('Email', 'cust_email', $row['cust_email'])}
        {$formObj->getTBRow('Phone', 'cust_phone', $row['cust_phone'])}
        {$formObj->getTBRow('Address 1', 'cust_address1', $row['cust_address1'])}
        {$formObj->getTBRow('Address 2', 'cust_address2', $row['cust_address2'])}
        {$formObj->getTBRow('Area', 'cust_address_area', $row['cust_address_area'])}
        {$formObj->getTBRow('City', 'cust_address_city', $row['cust_address_city'])}
        {$formObj->getTBRow('State', 'cust_address_state', $row['cust_address_state'])}
        {$formObj->getTBRow('Zip Code', 'cust_address_po_code', $row['cust_address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'cust_address_country_code', $sqlCountry, $row['cust_address_country_code'], $expCountry1)}
        ";

        $fielset3 = "
        {$formObj->getTBRow('First Name', 'shipping_first_name', $row['shipping_first_name'])}
        {$formObj->getTBRow('Last Name', 'shipping_last_name', $row['shipping_last_name'])}
        {$formObj->getTBRow('Phone', 'shipping_phone', $row['shipping_phone'])}
        {$formObj->getTBRow('Email', 'shipping_email', $row['shipping_email'])}
        {$formObj->getTBRow('Address 1', 'shipping_address1', $row['shipping_address1'])}
        {$formObj->getTBRow('Address 2', 'shipping_address2', $row['shipping_address2'])}
        {$formObj->getTBRow('Area', 'shipping_address_area', $row['shipping_address_area'])}
        {$formObj->getTBRow('City', 'shipping_address_city', $row['shipping_address_city'])}
        {$formObj->getTBRow('State', 'shipping_address_state', $row['shipping_address_state'])}
        {$formObj->getTBRow('Zip Code', 'shipping_address_po_code', $row['shipping_address_po_code'])}
        {$formObj->getDDRowBySQL('Country', 'shipping_address_country_code', $sqlCountry, $row['shipping_address_country_code'], $expCountry2)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Shipping Details', $fielset3)}
        {$formObj->getFieldSetWrapped('Customer Details', $fielset2)}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $creation_date1 = $fn->getReqParam('creation_date_1');
        $creation_date2 = $fn->getReqParam('creation_date_2');
        $order_status   = $fn->getReqParam('order_status');
        $shipment_status   = $fn->getReqParam('shipment_status');
        $shipping_address_country_code = $fn->getReqParam('shipping_address_country_code');

        $dirText = "";

        if ($cpCfg['cp.hasDirectoryMg'] == 1){
            $business_id = $fn->getReqParam('business_id');
            $business_contact_id = $fn->getReqParam('business_contact_id');

            $SQLBusiness = "
            SELECT b.business_id
                    ,b.business_name
            FROM business b
            ORDER BY b.business_name
            ";

            $SQLBusinessContact = "
            SELECT bc.business_contact_id
                    ,CONCAT_WS(' ', bc.first_name, bc.last_name) AS contact_name
            FROM business_contact bc
            ORDER BY contact_name
            ";

            $dirText = "
            <td class='fieldValue'>
                <select name='business_id'>
                    <option value=''>Business</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLBusiness, $business_id)}
                </select>
            </td>

            <td class='fieldValue'>
                <select name='business_contact_id'>
                    <option value=''>Contact</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $SQLBusinessContact, $business_contact_id)}
                </select>
            </td>
            ";
        }

        $orgText = "";
        if ($cpCfg['m.ecommerce.order.showOrganization']) {
	        $organization_id = $fn->getReqParam('organization_id');

	        $SQLOrg = "
	        SELECT o.organization_id
	              ,o.name
	        FROM organization o
	        ORDER BY o.name
	        ";

                $orgText = "
	        <td class='fieldValue'>
	            <select name='organization_id'>
	                <option value=''>Organization</option>
                        {$dbUtil->getDropDownFromSQLCols2($db, $SQLOrg, $organization_id)}
	            </select>
	        </td>
	        ";
        }

        $shipmentStatus = "";
        if ($cpCfg['m.ecommerce.order.showShipmentStatus']) {
            $shipmentStatus = "
            <td class='fieldValue'>
                <select name='shipment_status'>
                    <option value=''>Shipment Status</option>
                    {$cpUtil->getDropDown1($cpCfg['m.ecommerce.order.shipmentStatusArr'], $shipment_status)}
                </select>
            </td>
            ";
        }

        $text = "
        {$dirText}
        {$orgText}
        <td>
            {$formObj->getDateRangeRow('Creation Date:', 'creation_date', $creation_date1, $creation_date2)}
        </td>

        <td class='fieldValue'>
            <select name='shipping_address_country_code'>
                <option value=''>Country</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $fn->getGeoCountrySQL(), $shipping_address_country_code)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='order_status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.ecommerce.order.statusArr'], $order_status)}
            </select>
        </td>
        {$shipmentStatus}
        ";


        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');

        $links ='';
        if ($cpCfg['m.ecommerce.order.showAttachment'] == 1){
            $links .= $media->getRightPanelMediaDisplay('Attachments', 'ecommerce_order', 'attachment', $row);
        }

        if ($cpCfg['m.ecommerce.order.showLabelAttachment'] == 1){
            $links .= $media->getRightPanelMediaDisplay('Label', 'ecommerce_order', 'label', $row);
        }

        $text = "
        {$displayLinkData->getLinkPortalMain('ecommerce_order', 'ecommerce_orderItemLink', 'Order Items', $row)}
        {$links}
        ";

        return $text;
    }


    /**
     *
     */
}