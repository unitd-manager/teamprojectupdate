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

$cpCfg['m.core.valuelist.hasCode'] = true;

$cpCfg['m.trading.quote.markUpTypeArr'] = array(
     '%' => '%'
    ,'Value' =>'Value'
    );

//paypal subscription button
$cpCfg['p.common.login.adminHasPaypalSubscription'] = false;

/************** COMPANY *************/
$cpCfg['m.trading.contact.hasMultipleCompanyAddress'] = 0;

$cpCfg['m.trading.companyCurrency'] = 'GBP';

$tv['cfgKeys']['companyCurrency'] = $cpCfg['m.trading.companyCurrency'];

$cpCfg['m.trading.currencyDecimalPlaces'] = 2; //decimal places length
$cpCfg['m.trading.company.defaultCountry'] = '';
$cpCfg['m.trading.company.defaultSellCurrency'] = '';
$cpCfg['m.tradingsg.company.hasMarkUpPercent'] = false;
$cpCfg['m.tradingsg.company.hasCstNo'] = false;
$cpCfg['m.tradingsg.company.hasGstNo'] = false;
$cpCfg['m.tradingsg.company.hasTinNo'] = false;
$cpCfg['m.tradingsg.company.hadDiscountLink'] = false;

/************** SUPPLIER *************/
$cpCfg['m.tradingsg.supplier.hasDiscountPercent'] = false;
$cpCfg['m.tradingsg.supplier.hasCstNo'] = false;
$cpCfg['m.tradingsg.supplier.hasTinNo'] = false;

/************** CONTACT *************/
$cpCfg['m.trading.contact.contactStatusArr'] = array (
     'active'
    ,'inactive'
);

/************** DISCOUNT LINK *************/
$cpCfg['m.tradingsg.discountLink.showDiscount'] = true;

/************** COMPANY *************/
$cpCfg['m.trading.company.categoryArr'] = array (
     'Customer'
    ,'Supplier'
);

$cpCfg['m.tradingsg.company.statusArr'] = array (
     'active'
    ,'inactive'
    ,'on hold'
);

$cpCfg['m.trading.company.companyTypeArr'] = array (
     'Trading'
    ,'Manufacturer'
    ,'Retail'
);

$cpCfg['m.tradingsg.company.partyArr'] = array (
     'Customer'
    ,'Supplier'
    ,'Sales Agent'
    ,'Supplier Agent'
);

/************** PRODUCT *************/
$cpCfg['m.tradingsg.product.hasGSt'] = true;
$cpCfg['m.tradingsg.product.gstType'] = array(
     '7' => 'Standard Rate'  // Standard Rate of GST in Singapore
    ,'0' => 'Zero Rated'     // Sales happened in SG but the purchase person is a foreigner and will apply for GST at the departure
    //,'0' => 'Exempt Supplies'// Products which SG govt has given exemption (No GST, eg: Gold Bar)
    //,'0' => 'Out of Scope'   // Purchase and sales in foreign country but sales recorded by SG company with the product not entering into SG
);

$cpCfg['m.tradingsg.product.displayTradingmassProductName'] = false;
$cpCfg['m.tradingsg.product.displayTradingmassProductNameValidate'] = false;

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
     'New'
    ,'Current'
    ,'Sent to Customer'
    ,'Customer Confirmed'
    ,'On Hold'
    ,'Cancelled'
);

$cpCfg['m.trading.product.quoteProductPriorityArr'] = array (
     'Low'
    ,'Medium'
    ,'High'
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

$cpCfg['m.tradingus.product.priceCategory'] = array (
     'Base Price'
    ,'Wholesale Price'
    ,'Retailer Price'
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
//to get the discount from cmpany
$cpCfg['m.tradingsg.quote.markUpDiscountfromCompany'] = false;
$cpCfg['m.tradingsg.quote.markUpBySellingPrice'] = false;
//to print the quote(export) in general style
$cpCfg['m.tradingsg.quote.updateDiscountByGroup'] = false;
$cpCfg['m.tradingsg.quote.printQuoteGeneral'] = true;
$cpCfg['m.tradingsg.quote.showExportExcellC2'] = true;
$cpCfg['m.tradingsg.quote.displayTradingmassClientNameValidate'] = false;
$cpCfg['m.tradingsg.quote.displayTradingmassClientName'] = false;
$cpCfg['m.tradingsg.quote.displayNoDiscountInQuote'] = false;
$cpCfg['m.tradingsg.quote.printQuoteGeneralTrading'] = false;
$cpCfg['m.tradingsg.quote.summaryDisplayForSplCase'] = false;
$cpCfg['m.tradingsg.quote.hasProductLinkForSplCase'] = 0;
$cpCfg['m.trading.quote.statusArr'] = array (
     'new'
    ,'open'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);
$cpCfg['siteLocationTitle'] = 0;

/************** REPORTS *************/
/* To include price from supplier field, in local site set this value as 1. Refer Engex config_last file */
$cpCfg['m.tradingsg.reports.hasPriceFromSupplierInProfit'] = 0;

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

/************** SUPPLIER QUOTE HISTORY STATUS *************/
$cpCfg['m.tradingsg.supplierQuoteHistory.statusArr'] = array (
     'new'
    ,'inprogress'
    ,'confirmed'
    ,'on hold'
    ,'cancelled'
);

/************** PURCHASE ORDER *************/
$cpCfg['m.tradingsg.purchaseOrder.showInvoiceButton'] = false;
/*
$cpCfg['m.trading.purchaseOrder.statusArr'] = array (
     'new'
    ,'inprogress'
    ,'confirmed'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);
*/
$cpCfg['m.trading.purchaseOrder.statusArr'] = array (
     'inprogress'
    ,'sent to supplier'
    ,'order acknowledged'
    ,'partially received'
    ,'closed'
    ,'on hold'
    ,'cancelled'
);

$cpCfg['m.trading.purchaseOrder.poProductStatusArr'] = array (
     'print'
    ,'inprogress'
    ,'partially delivered'
    ,'delivered'
    ,'paid'
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

/************** ORDER *************/
//to hide actions buttons in order
$cpCfg['m.tradingsg.order.hidePrintButtons'] = true;
$cpCfg['hasMasterAndOwnerTextInInvoicePdf'] = false;
$cpCfg['hasCompanyAddressInInvoicePdf'] = true;
$cpCfg['m.tradingsg.order.showReceiptPortalDisplay'] = true;
$cpCfg['m.tradingsg.order.showInvoicePortalDisplay'] = true;
$cpCfg['m.tradingsg.order.showInvoiceButton'] = true;
$cpCfg['m.tradingsg.order.showReceiptButton'] = true;
$cpCfg['m.tradingsg.order.showCaptainCopyButton'] = true;
$cpCfg['m.tradingsg.order.showOrderItemDisplay'] = true;
//syed : to add gstamount to order total ( list and edit of order : applied for via exim)
$cpCfg['m.tradingsg.order.addGstAmountToOrderTotal'] = false;


/************** PRICING TYPE *************/
$cpCfg['m.trading.pricingType.recordTypeArr'] = array (
     'has_tax' => 'Has VAT'
    ,'no_tax' => 'No VAT'
);

//================ CALL REGISTRY ================//
$cpCfg['m.tradingsg.callRegistry.hasCandidate'] = true;
$cpCfg['m.tradingsg.callRegistry.companyFromProjectModuleForCrm'] = false;
$cpCfg['m.tradingsg.callRegistry.hasNoOfCandidate'] = true;

//================ ENQUIRY ================//
$cpCfg['m.tradingsg.enquiry.hasEnquiryProductGroupLink'] 	= false;
return $cpCfg;

//================ ATTENDANCE ================//
$cpCfg['m.tradingsg.attendance.hasMultipleSessions']  = 0; // For session wise in and out time
$cpCfg['m.tradingsg.staffFieldLabel']           = "Staff";
$cpCfg['m.tradingsg.staffTeamLabel']            = "Team";
