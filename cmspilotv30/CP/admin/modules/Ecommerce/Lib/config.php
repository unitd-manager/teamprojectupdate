<?
$cpCfg = array();

//------------- PRODUCT -------------//
$cpCfg['m.ecommerce.product.showMetaData'] = 1;
$cpCfg['m.ecommerce.product.showSlidehshow'] = 0;
$cpCfg['m.ecommerce.product.showEmbedCode'] = 0;
$cpCfg['m.ecommerce.product.hasVoucherHistory'] = 0;
$cpCfg['m.ecommerce.product.showShortDesc'] = 1;
$cpCfg['m.ecommerce.product.btnPosArr'] = array (
	 'Published'
	,'Not-Published'
	,'Latest'
	,'Favourite'
	,'Flag'
);
$cpCfg['m.ecommerce.product.hasSortOrderFld'] = false;

$cpCfg['m.ecommerce.promoCode.amountTypeArr'] = array (
     'Amount'
    ,'Percentage'
);
$cpCfg['m.ecommerce.promoCode.hasPromoCodeCityLink'] = true;

$cpCfg['m.ecommerce.order.codePrefix'] = 'INV-';

$cpCfg['m.ecommerce.order.statusArr'] = array (
     'New'
    ,'Due'
    ,'Paid'
    ,'Cancelled'
    ,'Pending'
);

$cpCfg['m.ecommerce.order.shipmentStatusArr'] = array (
     'New'
    ,'Ready to Ship'
    ,'Shipped'
    ,'Returned'
    ,'Cancelled'
);

$cpCfg['m.ecommerce.order.paymentMethodsArr'] = array (
     'cash'
    ,'cheque'
    ,'paypal'
);

$cpCfg['m.ecommerce.serialNos.specialSearchArr'] = array(
     'Registered'
    ,'Not-Registered'
);


$cpCfg['m.ecommerce.product.hasProductItems'] = false;
$cpCfg['m.ecommerce.product.showDescription2'] = false;
$cpCfg['m.ecommerce.product.showWeight'] = false;

//------------- ORDER -------------//
$cpCfg['m.ecommerce.order.itemsMainModule'] = 'ecommerce_product';
$cpCfg['m.ecommerce.order.showOrganization'] = false;
$cpCfg['m.ecommerce.order.showOkToShip'] = false;
$cpCfg['m.ecommerce.order.showNotesPerOrderItem'] = false;
$cpCfg['m.ecommerce.order.showAttachment'] = false;
$cpCfg['m.ecommerce.order.showShipmentStatus'] = false;
$cpCfg['m.ecommerce.order.showLabelAttachment'] = false;
$cpCfg['m.ecommerce.order.showDHLAirwayBill'] = false;
$cpCfg['m.ecommerce.order.hasDiscount'] = false;

//------------- PROMO CODE -------------//
$cpCfg['m.ecommerce.promoCode.showCountry'] = false;
$cpCfg['m.ecommerce.promoCode.showCity'] = false;

//------------- STOCKIST -------------//
$cpCfg['m.ecommerce.stockist.hasPaypalEmail'] = false;
$cpCfg['m.ecommerce.stockist.hasShippingCharge'] = false;

//------------- REGISTER PRODUCT -------------//
$cpCfg['m.ecommerce.serialNos.showComments'] = true;

return $cpCfg;
