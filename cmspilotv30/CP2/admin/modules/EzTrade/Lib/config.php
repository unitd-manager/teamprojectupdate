<?
$cpCfg = array();

/************** VALUELIST *************/
$cpCfg['m.core.valuelist.recordTypeArr'] = array(
     'contactTitle' => 'Salutation'
    ,'partyType' => 'Party Type'
    ,'currency' => 'Currency'
    ,'invoiceType' => 'Invoice Type'
    ,'collection' => 'Collection'
    ,'productUnit' => 'UOM'
    ,'hardware' => 'Hardware'
);

/************** COMPANY *************/
$cpCfg['m.trading.contact.hasMultipleCompanyAddress']   = 0;

$cpCfg['m.trading.companyCurrency'] = 'GBP';

$tv['cfgKeys']['companyCurrency'] = $cpCfg['m.trading.companyCurrency'];

$cpCfg['m.trading.currencyDecimalPlaces'] = 3; //decimal places length

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
     'EzTrade'
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
     'in production'
    ,'in shipment'
    ,'in warehouse'
    ,'sold'
    ,'cancelled'
    ,'SOR'
);

$cpCfg['m.trading.product.enquiryProductStatusArr'] = array (
     'new'
    ,'pending customer'
    ,'rfq generated'
    ,'rfq selected'
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

/************** ENQUIRY *************/
$cpCfg['m.trading.enquiry.statusArr'] = array (
     'new'
    ,'open'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.enquiry.shippingMethodArr'] = array (
     'air'
    ,'sea'
    ,'not applicable'
);

/************** RFQ *************/
$cpCfg['m.trading.rfq.statusArr'] = array (
     'new'
    ,'open'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.rfq.shippingMethodArr']    = array (
      'sea'
     ,'air'
     ,'not applicable'
);

/************** SALES ORDER *************/
$cpCfg['m.trading.salesOrder.statusArr'] = array (
     'new'
    ,'open'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.salesOrder.shippingMethodArr']    = array (
      'sea'
     ,'air'
     ,'not applicable'
);

/************** PURCHASE ORDER *************/
$cpCfg['m.trading.purchaseOrder.statusArr'] = array (
     'new'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.purchaseOrder.shippingMethodArr']    = array (
      'sea'
     ,'air'
     ,'not applicable'
);

/************** SHIPMENT *************/
$cpCfg['m.trading.shipment.shippingMethodArr']    = array (
      'sea'
     ,'air'
     ,'not applicable'
);

$cpCfg['m.trading.shipment.statusArr'] = array (
     'new'
    ,'open'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

/************** QUOTE *************/
$cpCfg['m.trading.quote.shippingMethodArr']    = array (
      'sea'
     ,'air'
     ,'not applicable'
);


return $cpCfg;