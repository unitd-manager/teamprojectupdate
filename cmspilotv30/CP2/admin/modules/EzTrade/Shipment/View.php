<?
class CP_Admin_Modules_EzTrade_Shipment_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
        	{$listObj->getGoToDetailText($count, $row['shipment_code'])}
        	{$listObj->getListDataCell($row['booking_no'])}
        	{$listObj->getListDataCell($row['company_name'])}
        	{$listObj->getListDateCell($row['scheduled_ship_date'])}
        	{$listObj->getListDateCell($row['estimated_arrival_date'])}
        	{$listObj->getListDataCell($row['ship_to_location'])}
        	{$listObj->getListDataCell($row['port_of_loading'])}
        	{$listObj->getListDataCell($row['port_of_discharge'])}
        	{$listObj->getListDataCell($row['status'])}
        	{$listObj->getListRowEnd($row['shipment_id'])}
            ";
            $count++;
        }

        $text = "
        {$listObj->getListHeader()}
      	{$listObj->getListHeaderCell('Shipment Number', 'sm.shipment_code')}
      	{$listObj->getListHeaderCell('Bill of Lading #', 'sm.booking_no')}
      	{$listObj->getListHeaderCell('Client Name', 'c.company_name')}
      	{$listObj->getListHeaderCell('Ship Date', 'sm.scheduled_ship_date')}
      	{$listObj->getListHeaderCell('Estimated Arrival Date', 'sm.estimated_arrival_date')}
      	{$listObj->getListHeaderCell('Ship to Location', 'ship_to_location')}
      	{$listObj->getListHeaderCell('Port of Origin', 'port_of_loading')}
      	{$listObj->getListHeaderCell('Port of Arrival', 'port_of_discharge')}
      	{$listObj->getListHeaderCell('Status', 'sm.status')}
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
        $fn = Zend_Registry::get('fn');

        $sqlCompanyName = $fn->getDDSql('ezTrade_company', array('condn' => "category = 'Customer'"));

        $fieldset = "
        {$formObj->getDDRowBySQL('Client Name', 'company_id', $sqlCompanyName)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $expNoEdit = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');
        $expSO = array('displayText' => $row['so_code']);
        $soCodeText = $fn->getRecordDetailLink('ezTrade_salesOrder', 'record_id', $row['sales_order_id'], $expSO);

        $expShipToLoc = array('detailValue' => $row['ship_to_location']);
        $modDeliveryAddress = getCPModuleObj('ezTrade_deliveryAddressLink');
        $sqlShipToLocation = $modDeliveryAddress->model->getShipToLocationSQL($row['company_id']);

        $modDeliveryTerms = getCPModuleObj('ezTrade_deliveryTermsLink');
        $sqlDeliveryTermsClient = $modDeliveryTerms->model->getDeliveryTermsSupplierSQL($row['company_id']);

        $expComp = array('displayText' => $row['company_name']);
        $companyText = $fn->getRecordDetailLink('ezTrade_company', 'record_id', $row['company_id'], $expComp);

        $fieldset1 = "
        {$formObj->getTBRow('Shipment Number', 'shipment_code', $row['shipment_code'], $expNoEdit)}
        {$formObj->getTBRow('Shipping Mark Number', '', $soCodeText, $expNoEdit)}
        {$formObj->getTBRow('Client Name', 'company_id', $companyText, $expNoEdit)}
        {$formObj->getTBRow('Bill of Lading Number', 'booking_no', $row['booking_no'])}
        {$formObj->getTBRow('Forwarder', 'forwarder', $row['forwarder'])}
        {$formObj->getTBRow('Port of Origin', 'port_of_loading', $row['port_of_loading'])}
        {$formObj->getTBRow('Port of Arrival', 'port_of_discharge', $row['port_of_discharge'])}
        {$formObj->getDDRowBySQL('Ship to Location', 'delivery_address_id', $sqlShipToLocation, $row['delivery_address_id'], $expShipToLoc)}
        {$formObj->getDDRowBySQL('Delivery Terms', 'delivery_terms', $sqlDeliveryTermsClient, $row['delivery_terms'], $expVl)}
        {$formObj->getTBRow('Consignee (Agent) Name', 'consignee_name', $row['consignee_name'])}
        {$formObj->getTBRow('Consignee Address', 'consignee_address', $row['consignee_address'])}
        {$formObj->getTBRow('Consignee Phone', 'consignee_phone', $row['consignee_phone'])}
        {$formObj->getDateRow('Actual Ship Date', 'scheduled_ship_date', $row['scheduled_ship_date'])}
        {$formObj->getTBRow('Container Number', 'container_no', $row['container_no'])}
        {$formObj->getTBRow('Container Type', 'container_type', $row['container_type'])}
        {$formObj->getDateRow('Estimated Arrival Date (ETA)', 'estimated_arrival_date', $row['estimated_arrival_date'])}
        {$formObj->getDateRow('Estimated Delivery Date', 'estimated_delivery_date', $row['estimated_delivery_date'])}
        {$formObj->getDDRowByArr('Shipment Status', 'status', $cpCfg['m.trading.shipment.statusArr'], $row['status'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Shipment Header', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $comment = getCPPluginObj('common_comment');

        $links = "";

        $record_id = $fn->getIssetParam($row, 'shipment_id');

        $links .= "
        {$displayLinkData->getLinkPortalMain('ezTrade_shipment', 'ezTrade_productLink', 'Shipment Line', $row)}
        ";

        $text = "
        {$links}
        {$comment->getView(array(
             'roomName' => 'ezTrade_shipment'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $status       = $fn->getReqParam('status');

        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.shipment.statusArr'], $status)}
            </select>
        </td>
        ";

        return $text;
    }
}