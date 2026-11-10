<?
class CP_Admin_Modules_Trading_Shipment_View extends CP_Common_Lib_ModuleViewAbstract
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
        	{$listObj->getListDataCell($row['port_of_loading'])}
        	{$listObj->getListDataCell($row['port_of_discharge'])}
        	{$listObj->getListDataCell($row['status'])}
        	{$listObj->getListRowEnd($row['shipment_id'])}
            ";
            $count++;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
      	{$listObj->getListHeaderCell('Shipment Number', 'sm.shipment_code')}
      	{$listObj->getListHeaderCell('Bill of Lading #', 'sm.booking_no')}
      	{$listObj->getListHeaderCell('Client Name', 'c.company_name')}
      	{$listObj->getListHeaderCell('Ship Date', 'sm.scheduled_ship_date')}
      	{$listObj->getListHeaderCell('Estimated Arrival Date', 'sm.estimated_arrival_date')}
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

        $sqlCompanyName = $fn->getDDSql('trading_company', array('condn' => "category = 'Customer'"));

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

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');
        
        $expNoEdit = array('isEditable' => 0);

        $expVl = array('sqlType' => 'OneField');
        $expSO = array('displayText' => $row['so_code']);
        $soCodeText = $fn->getRecordDetailLink('trading_salesOrder', 'record_id', $row['sales_order_id'], $expSO);

        $expShipToLoc = array('detailValue' => $row['ship_to_location']);
        $modDeliveryAddress = getCPModuleObj('trading_deliveryAddressLink');
        $sqlShipToLocation = $modDeliveryAddress->model->getShipToLocationSQL($row['company_id']);

        $expDeliveryTerms = $fnsModGrp->getTermsParamArr('trading_deliveryTermsLink',
                                                        $row['company_id'],
                                                        'fld_delivery_terms'
                                                        );

        $expComp = array('displayText' => $row['company_name']);
        $companyText = $fn->getRecordDetailLink('trading_company', 'record_id', $row['company_id'], $expComp);

        
        $expUseKey = array('useKey' => 1);
        
        $fieldset1 = "
        {$formObj->getTBRow('Shipment Number', 'shipment_code', $row['shipment_code'], $expNoEdit)}
        {$formObj->getTBRow('Shipping Mark Number', 'shipping_mark', $row['shipping_mark'])}
        {$formObj->getTBRow('Client Name', 'company_id', $companyText, $expNoEdit)}
        {$formObj->getTBRow('Bill of Lading Number', 'booking_no', $row['booking_no'])}
        {$formObj->getTBRow('Forwarder', 'forwarder', $row['forwarder'])}
        {$formObj->getTBRow('Port of Origin', 'port_of_loading', $row['port_of_loading'])}
        {$formObj->getTBRow('Port of Arrival', 'port_of_discharge', $row['port_of_discharge'])}
        {$formObj->getDDRowBySQL('Ship to Location', 'delivery_address_id', $sqlShipToLocation, $row['delivery_address_id'], $expShipToLoc)}
        {$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'], $expDeliveryTerms)}
        {$formObj->getTBRow('Consignee (Agent) Name', 'consignee_name', $row['consignee_name'])}
        {$formObj->getTBRow('Consignee Address', 'consignee_address', $row['consignee_address'])}
        {$formObj->getTBRow('Consignee Phone', 'consignee_phone', $row['consignee_phone'])}
        {$formObj->getDateRow('Actual Ship Date', 'scheduled_ship_date', $row['scheduled_ship_date'])}
        {$formObj->getTBRow('Container Number', 'container_no', $row['container_no'])}
        {$formObj->getDDRowByArr('Container Type', 'container_type', 
                                 $cpCfg['m.trading.shipment.containerTypeArr'], 
                                 $row['container_type'], $expUseKey)}
        {$formObj->getDateRow('Estimated Arrival Date (ETA)', 'estimated_arrival_date', $row['estimated_arrival_date'])}
        {$formObj->getDateRow('Estimated Delivery Date', 'estimated_delivery_date', $row['estimated_delivery_date'])}
        {$formObj->getDDRowByArr('Shipment Status', 'status', $cpCfg['m.trading.shipment.statusArr'], $row['status'])}
        {$formObj->getHiddenFldObj('status_prev', $row['status'])}
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
        {$displayLinkData->getLinkPortalMain('trading_shipment', 'trading_inventoryLink', 'Shipment Line', $row)}
        ";

        $showInventory = "
        <div class='floatbox'>
            <div class='float_right'>
            <a id='showInventory' href='#' shipment_id='{$row['shipment_id']}'>Show Inventory</a>
            </div>
        </div>
        ";
            
        $text = "
        {$showInventory}
        {$links}
        {$comment->getView(array(
             'roomName' => 'trading_shipment'
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
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $status = $fn->getReqParam('status');
        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );
        
        $text = "
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.shipment.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select class='w125' name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    function getEditInventoryForm() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $listObj = Zend_Registry::get('listObj');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $shipment_id = $fn->getReqParam('shipment_id');

        $SQL = "
        SELECT DISTINCT
               i.*
              ,p.product_code
              ,p.title product_name
              ,p.collection_name
              ,s.shipment_code
        FROM inventory i
        JOIN product p ON p.product_id = i.product_id
        JOIN shipment s ON s.shipment_id = i.shipment_id
        WHERE s.shipment_id = {$shipment_id}
        ORDER BY i.product_id
                ,i.serial_no
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows();
        $count = 0;

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $statusText = "
            <select name='status[{$row['inventory_id']}]' class='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.statusArr'], $row['status'])}
            </select>
            ";
            $locationText = "
            <select name='location[{$row['inventory_id']}]' class='location'>
                <option value=''>Location</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.locationArr'], $row['location'])}
            </select>
            ";

            $exp = array('hasFlagInList' => false
                        ,'keyFieldValue' => $row['inventory_id']
                        ,'hasEditInList' => false
                        ,'hasRowNumber' => false
            );
            $rows .= "
            {$listObj->getListRowHeader($row, $count, '', $exp)}
            {$listObj->getGoToDetailText($count, $row['product_code'])}
            {$listObj->getListDataCell($row['serial_no'])}
            {$listObj->getListDataCell($row['collection_name'])}
            {$listObj->getListDataCell($row['product_name'])}
            {$listObj->getListDataCell($row['shipment_code'])}
            {$listObj->getListDataCell($statusText)}
            {$listObj->getListDataCell($locationText)}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListRowEnd($row['inventory_id'])}
            ";

            $count++;
        }

        $raiseBtn = "
        <form class='yform'>
            <div class='type-button float_right'>
            <input type='reset' value='Cancel' id='btnUpdateInventoryCancel' />
            <input type='button' value='Update' id='btnUpdateInventory' />
            </div>
        </form>
        ";

        $fnMod = getCPModelObj('trading_company');
        $sqlSupplier = $fnMod->getSupplierSQL();

        $exp = array('hasEditInList' => false
                    ,'hasRowNumber' => false
                    ,'hasFlagInList' => false
               );

        $rowSummary = "
        <tr class='even'>
        <td colspan='5'></td>

        <td>
        <select id='status_common'>
            <option value=''>Update Status</option>
            {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.statusArr'])}
        </select>
        </td>
        <td>
        <select id='location_common'>
            <option value=''>Update Location</option>
            {$cpUtil->getDropDown1($cpCfg['m.trading.inventory.locationArr'])}
        </select>
        </td>

        <td colspan='2'></td>
        </tr>
        ";
        $text = "
        <div id='updateInventory'>
            {$raiseBtn}
            {$listObj->getListHeader($exp)}
            {$listObj->getListHeaderCell('Product Code')}
            {$listObj->getListHeaderCell('Serial')}
            {$listObj->getListHeaderCell('Collection')}
            {$listObj->getListHeaderCell('Product Name')}
            {$listObj->getListHeaderCell('Shipment #')}
            {$listObj->getListHeaderCell('Status')}
            {$listObj->getListHeaderCell('Location')}
            {$listObj->getListHeaderCell('Creation Date')}
            {$listObj->getListHeaderEnd()}
            {$rowSummary}
            {$rows}
            {$listObj->getListFooter()}
            {$formObj->getHiddenFldObj('shipment_id', $shipment_id)}
        </div>
        ";

        return $text;
    }
    
}