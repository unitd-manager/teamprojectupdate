<?
class CP_Common_Widgets_Ecommerce_DHL_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    var $region = 'AP';
    
    var $siteID   = '';
    var $password = '';
    
    var $shipperID = '';
    
    var $languageCode = 'en';
    var $piecesEnabled = 'Y';
    
    var $shipperAccountNumber = '';
    var $shippingPaymentType  = 'S'; //S (Shipper) / R (Receiver) / T (Third Party)
    var $dutyPaymentType = 'S'; //S (Shipper) / R (Receiver) / T (Third Party)
    var $dutyAccountNumber = '';
    var $referenceIDPrefix = '';
    
    var $commodityCode = '1';
    var $commodityName = '';
    
    //ShipmentDetails
    var $numberOfPieces = 1; //No. of pieces in shipment or pickup request
    var $currencyCode = 'USD';
    var $packageType  = 'YP'; //EE (DHL Express Envelope) 
                            //OD (Other DHL Packaging) 
                            //CP (Customer-provided packaging) 
                            //JB (Jumbo box) 
                            //JJ (Junior jumbo Box) 
                            //DF (DHL Flyer) 
                            //YP (Your packaging) 
    var $dimensionUnit = 'C'; //C (Centimeter) / I (Inches) 
    var $weightUnit = 'K'; //K (Kilogram)
    
    var $globalProductCode = 'D';  //DHL service code i.e. EXPRESS WORLDWIDE
    var $localProductCode  = 'D';  //DHL local service code i.e. EXPRESS WORLDWIDE
    var $doorTo  = 'DD';  //DD (Door to Door), DA (Door to Airport) , AA and DC (Door to Door non-compliant) 
    var $shipmentContents = 'For testing purpose only. Please do not ship'; //the Shipment contents description
    
    var $testURL  = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet';
    var $liveURL  = 'https://xmlpi-ea.dhl.com/XMLShippingServlet';
    
    var $orderArr = array();
    
    //Capability
    var $readyTime = "PT12H00M"; //Eg. PT10H21M 
    var $isDutiable = "N"; //Y = Dutiable/Non-Doc / N = Non-Dutiable/Doc
    var $networkTypeCode = "AL";//AL – Both Time and Day Definite 
                                //DD - Day Definite 
                                //TD - Time Definite  
    //Dutiable
    var $declaredCurrency = 'USD';
    var $declaredValue = '0.00';
    var $shipperEIN = ''; //Shipper’s employee identification number (SSN, EIN) 
    var $termsOfTrade = 'DTP';  //DDU	Duties and Taxes Unpaid (Default)
                            //DTP	Duties and Taxes Paid
                            //FOB	Free On Board
                            //FCA	Free Carrier
                            //CFR	Cost & Freight
                            //CIF	Cost, Insurance and Freight
                            //DVU	Split Duty and VAT

}