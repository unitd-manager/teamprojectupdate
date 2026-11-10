<?
class CP_Admin_Modules_Trading_RfqItemsLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getEditPortal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fnsModCompany = includeCPClass('ModuleFns', 'trading_company');
        $sqlSupplier = $fnsModCompany->getSupplierSQL();

        $id = $fn->getReqParam('id');

        $SQL = "
        SELECT qri.*
              ,qr.company_id_supplier
              ,qr.valid_until
              ,qr.delivery_terms_supplier
              ,qr.shipping_method
              ,c.company_name
        FROM quote_request_items qri
        JOIN quote_request qr ON (qr.quote_request_id = qri.quote_request_id)
        JOIN company c ON (c.company_id = qr.company_id_supplier)
        WHERE qri.quote_request_items_id = {$id}
        ";
        $row = $fn->getRecordBySQL($SQL);

        $expCust = array('detailValue' => $row['company_name']);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action=''>
            <fieldset>
                {$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, $row['company_id_supplier'], $expCust)}
                {$formObj->getTBRow('Minimum Order Quantity', 'min_order_quantity', $row['min_order_quantity'])}
                {$formObj->getTBRow('Lead Time', 'lead_time', $row['lead_time'])}
                {$formObj->getTBRow('Order Multiplier', 'order_multiplier', $row['order_multiplier'])}
                {$formObj->getTBRow('Buy Price', 'buy_unit_price', $row['buy_unit_price'])}
                {$formObj->getTBRow('Lead Time', 'lead_time', $row['lead_time'])}
                {$formObj->getTBRow('Min Order Quantity', 'min_order_quantity', $row['min_order_quantity'])}
                {$formObj->getTBRow('Order Multiplier', 'order_multiplier', $row['order_multiplier'])}
                {$formObj->getTBRow('Valid Until', 'valid_until2', $row['valid_until'])}
                {$formObj->getTBRow('Note From Supplier', 'notes_from_supplier', $row['notes_from_supplier'])}
                {$formObj->getTBRow('Country of Origin', 'country_of_origin', $row['country_of_origin'])}
                {$formObj->getTBRow('Delivery Terms', 'delivery_terms_supplier', $row['delivery_terms_supplier'])}
                {$formObj->getTBRow('Shipping Method', 'shipping_method', $row['shipping_method'])}
                {$formObj->getTBRow('Packing Details', 'packing_details', $row['packing_details'])}
                {$formObj->getTBRow('Carton Dimensions', 'carton_dimensions', $row['carton_dimensions'])}
                {$formObj->getTBRow('Gross Weight (kg)', 'gross_weight', $row['gross_weight'])}
                {$formObj->getTBRow('Net Weight (kg)', 'net_weight', $row['net_weight'])}
                {$formObj->getTBRow('Total Volume', 'total_volume', $row['total_volume'])}
            </fieldset>
        </form>
        ";

        return $text;
    }
}
