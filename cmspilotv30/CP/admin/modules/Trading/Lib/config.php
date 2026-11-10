<?
$cpCfg = array();

/************** GENERAL *************/
$cpCfg['m.trading.yesNoArr'] = array(
     '1' => 'Yes'
    ,'0' => 'No'
    );

$cpCfg['m.trading.shippingMethodArr'] = array (
     'air'
    ,'sea'
    ,'road'
    ,'not applicable'
);

/************** VALUELIST *************/
$cpCfg['m.core.valuelist.recordTypeArr'] = array(
     'contactTitle' => 'Salutation'
    ,'partyType' => 'Party Type'
    ,'currency' => 'Currency'
    ,'invoiceType' => 'Invoice Type'
    ,'collection' => 'Collection'
    ,'productUnit' => 'UOM'
    ,'hardware' => 'Hardware'
    ,'deliveryTerms' => 'Delivery Terms'
    ,'paymentTerms' => 'Payment Terms'
);
asort($cpCfg['m.core.valuelist.recordTypeArr']);
$cpCfg['m.core.valuelist.hasCode'] = true;

/************** COMPANY *************/
$cpCfg['m.trading.contact.hasMultipleCompanyAddress'] = 0;

$cpCfg['m.trading.companyCurrency'] = 'GBP';

$tv['cfgKeys']['companyCurrency'] = $cpCfg['m.trading.companyCurrency'];

$cpCfg['m.trading.currencyDecimalPlaces'] = 2; //decimal places length
$cpCfg['m.trading.company.defaultCountry'] = '';
$cpCfg['m.trading.company.defaultSellCurrency'] = '';

/************** CONTACT *************/
$cpCfg['m.trading.contact.contactStatusArr'] = array (
     'active'
    ,'inactive'
);

/************** COMPANY *************/
$cpCfg['m.trading.company.categoryArr'] = array (
     'Customer'
    ,'Supplier'
);

$cpCfg['m.trading.company.statusArr'] = array (
     'active'
    ,'inactive'
    ,'on hold'
);

$cpCfg['m.trading.company.companyTypeArr'] = array (
     'Trading'
    ,'Manufacturer'
    ,'Retail'
);

$cpCfg['m.trading.company.partyArr'] = array (
     'Customer'
    ,'Supplier'
    ,'Sales Agent'
    ,'Supplier Agent'
);

/************** PRODUCT *************/
$cpCfg['m.trading.product.statusArr'] = array (
     'available'
    ,'not available'
);

$cpCfg['m.trading.product.enquiryProductStatusArr'] = array (
     'new'
    ,'pending customer'
    ,'RFQ generated'
    ,'RFQ selected'
    ,'quote generated'
    ,'due'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.product.RFQProductStatusArr'] = array (
     'new'
    ,'sent to supplier'
    ,'quote received'
    ,'quote confirmed'
    ,'due'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.product.quoteProductStatusArr'] = array (
     'new'
    ,'sent to customer'
    ,'customer confirmed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.product.salesOrderProductStatusArr'] = array (
     'new'
    ,'pending customer'
    ,'po generated'
    ,'order acknowledged'
    ,'partially shipped'
    ,'fully shipped'
    ,'due'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.product.purchaseOrderProductStatusArr'] = array (
     'new'
    ,'sent to supplier'
    ,'order acknowledged'
    ,'partially shipped'
    ,'fully shipped'
    ,'partially received'
    ,'fully received'
    ,'due'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.product.shipmentProductStatusArr'] = array (
     'new'
    ,'booked'
    ,'shipped'
    ,'delayed'
    ,'arrived'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.product.invoiceProductStatusArr'] = array (
     'new'
    ,'sent to customer'
    ,'partially received'
    ,'fully received'
    ,'due'
    ,'on hold'
    ,'cancelled'
);

/************** INVENTORY *************/
$cpCfg['m.trading.inventory.statusArr'] = array (
     'available'
    ,'on enquiry'
    ,'sold'

);
$cpCfg['m.trading.inventory.locationArr'] = array (
     'in factory'
    ,'in production'
    ,'ready to ship'
    ,'in shipment'
    ,'in warehouse'
    ,'SOR'
    ,'on appro/sample'
    ,'delivered'
);

/************** ENQUIRY *************/
$cpCfg['m.trading.enquiry.statusArr'] = array (
     'new'
    ,'open'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

/************** QUOTE *************/
$cpCfg['m.trading.quote.statusArr'] = array (
     'new'
    ,'open'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);
/************** RFQ *************/
$cpCfg['m.trading.rfq.statusArr'] = array (
     'new'
    ,'open'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

/************** SALES ORDER *************/
$cpCfg['m.trading.salesOrder.statusArr'] = array (
     'quote'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.salesOrder.orderTypeArr'] = array (
      'general'
     ,'SOR'
);

/************** PURCHASE ORDER *************/
$cpCfg['m.trading.purchaseOrder.statusArr'] = array (
     'new'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

/************** SHIPMENT *************/
$cpCfg['m.trading.shipment.statusArr'] = array (
     'new'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.shipment.containerTypeArr'] = array (
     '20 ft' => '20 ft - 1164 cbm'
    ,'40 ft' => '40 ft - 2328 cbm'
    ,'40 ft hc' => '40 ft hc - 2684 cbm'
    ,'lcl' => 'LCL'
);


/************** INVOICE *************/
$cpCfg['m.trading.invoice.statusArr'] = array (
     'due'
    ,'paid'
    ,'cancelled'

);

/************** PRICING TYPE *************/
$cpCfg['m.trading.pricingType.recordTypeArr'] = array (
     'has_tax' => 'Has VAT'
    ,'no_tax' => 'No VAT'
);

return $cpCfg;
