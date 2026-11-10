<?
class CP_Common_Widgets_Ecommerce_DHL_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getShipmentValidate(){
        $c = &$this->controller;

        $headXML = "<?xml version='1.0' encoding='UTF-8'?>
                    <ShipmentValidateRequestAP xmlns:req='http://www.dhl.com' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.dhl.com
                    ship-val-req_AP.xsd' />"; 

        $xml = new SimpleXMLElement($headXML);
        $xml = $this->getHeaderXMLTags($xml);
        $xml = $this->getSVRBillingXMLTags($xml);
        $xml = $this->getSVRConsigneeXMLTags($xml);
        //$xml = $this->getSVRCommodityXMLTags($xml);
        if($c->isDutiable == 'Y'){
            $xml = $this->getSVRDutiableXMLTags($xml);
        }        
        $xml = $this->getSVRReferenceXMLTags($xml);
        $xml = $this->getSVRShipmentDetailsXMLTags($xml);
        $xml = $this->getSVRShipperXMLTags($xml);
   
        $requestXML  = str_replace('ShipmentValidateRequestAP', "req:ShipmentValidateRequest{$c->region}", $xml->asXML());
        $responseXML = $this->postXMLUsingCURL($requestXML);

        $resultArr = array(
             'requestXML'  => $requestXML
            ,'responseXML' => $responseXML->asXML()
        );

        return $resultArr;
    }
    
    
    function getCapability(){
        $c = &$this->controller;

        $headXML = "<?xml version='1.0' encoding='UTF-8'?>
                    <DCTRequest xmlns:p='http://www.dhl.com' xmlns:p1='http://www.dhl.com/datatypes' xmlns:p2='http://www.dhl.com/DCTRequestdatatypes' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.dhl.com
                    DCT-req.xsd ' />"; 
        $xml = new SimpleXMLElement($headXML);
        $GetCapability = $xml->addChild('GetCapability');
        $GetCapability = $this->getHeaderXMLTags($GetCapability);
        $GetCapability = $this->getDCTFromXMLTags($GetCapability);
        $GetCapability = $this->getDCTBkgDetailsXMLTags($GetCapability);
        $GetCapability = $this->getDCTToXMLTags($GetCapability);
        if($c->isDutiable == 'Y'){
            $GetCapability = $this->getDCTDutiableXMLTags($GetCapability);
        }
    
        $requestXML  = str_replace('DCTRequest', "p:DCTRequest", $xml->asXML());
        $responseXML = $this->postXMLUsingCURL($requestXML);

        $resultArr = array(
             'requestXML'  => $requestXML
            ,'responseXML' => $responseXML->asXML()
        );

        return $resultArr;
    }
    
    /**
     * 
     */
    function getHeaderXMLTags(SimpleXMLElement $xml ){
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;
        
//        $timezone = new DateTimeZone(date_default_timezone_get());
//        $offset = $timezone->getOffset(new DateTime("now"));
//        $tzOffset = ($offset < 0 ? '-' : '+').round($offset/3600).':00';

        $messageTime = date('Y-m-d')."T".date('h:m:s');
        
        $Request = $xml->addChild('Request');
        $ServiceHeader = $Request->addChild('ServiceHeader');
        $ServiceHeader->addChild('MessageTime', $messageTime);
        $ServiceHeader->addChild('MessageReference', $cpUtil->getRandomInteger(28, 32));
        $ServiceHeader->addChild('SiteID', $c->siteID);
        $ServiceHeader->addChild('Password', $c->password);

        return $xml;
        
    }
    
    /**
     *
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getSVRBillingXMLTags(SimpleXMLElement $xml){ 
        $c = &$this->controller;
        $xml->addChild('LanguageCode' , $c->languageCode);
        $xml->addChild('PiecesEnabled', $c->piecesEnabled);
        
        $Billing = $xml->addChild('Billing');
        $Billing->addChild('ShipperAccountNumber', $c->shipperAccountNumber);
        $Billing->addChild('ShippingPaymentType', $c->shippingPaymentType);
        if($c->isDutiable != 'Y'){
            $Billing->addChild('DutyPaymentType', $c->dutyPaymentType);
            if($c->dutyPaymentType == 'T'){
                $DutyAccountNumber = ($c->dutyAccountNumber != '') ? $c->dutyAccountNumber : $c->shipperAccountNumber;
                $Billing->addChild('DutyAccountNumber', $DutyAccountNumber);
            }
        }
        
        return $xml;        
    }
    /**
     *
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getSVRConsigneeXMLTags(SimpleXMLElement $xml){
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $row = $c->orderArr; 
        $Consignee = $xml->addChild('Consignee'); 
        $personName = $row['shipping_first_name']. ' ' . $row['shipping_last_name'];
        $Consignee->addChild('CompanyName', $fn->getIssetParam($row, 'shipping_company_name' , $personName));
        $Consignee->addChild('AddressLine', $row['shipping_address1']);
        $Consignee->addChild('AddressLine', $row['shipping_address2']);
        $Consignee->addChild('AddressLine', $row['shipping_address_area']);
        $Consignee->addChild('City', $row['shipping_address_city']);
        
        if($fn->getIssetParam($row, 'shipping_address_state')){
            if($c->region == 'EA'){
                $Consignee->addChild('Division', $row['shipping_address_state']);
            }else {
                $Consignee->addChild('DivisionCode', $row['shipping_address_state']);
            }
        }
        
        $Consignee->addChild('PostalCode', $row['shipping_address_po_code']);
        $Consignee->addChild('CountryCode', $row['shipping_address_country_code']);
        $Consignee->addChild('CountryName', $row['shipping_country_name']);

        $Contact = $Consignee->addChild('Contact');
        $Contact->addChild('PersonName', $personName);
        $Contact->addChild('PhoneNumber', $row['shipping_phone']);
        //$Contact->addChild('PhoneExtension', '45232');
        //$Contact->addChild('FaxNumber', '11234325423');
        //$Contact->addChild('Email', $row['shipping_email']);
        
        return $xml;
    }
    
    /**
     *
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getSVRCommodityXMLTags(SimpleXMLElement $xml){
        $c = &$this->controller;
        
        $Commodity = $xml->addChild('Commodity');
        $Commodity->addChild('CommodityCode', $c->commodityCode);
        $Commodity->addChild('CommodityName', $c->commodityName);
        
        return $xml;
    }
    
    /**
     *
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getSVRReferenceXMLTags(SimpleXMLElement $xml){
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $row = $c->orderArr; 
        
        $Reference = $xml->addChild('Reference');
        $ReferenceID = $c->referenceIDPrefix . $fn->getIssetParam($row, 'order_code' , $row['order_id']);
        $Reference->addChild('ReferenceID', $ReferenceID);
        //$Reference->addChild('ReferenceType', 'St');
        
        return $xml;
    }
    
    /**
     *
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getSVRShipmentDetailsXMLTags(SimpleXMLElement $xml){
        $fn = Zend_Registry::get('fn');        
        $dbUtil = Zend_Registry::get('dbUtil');        
        $c = &$this->controller;
        $row = $c->orderArr; 
        
        $SQLTotal = "
        SELECT  SUM(oi.qty) AS number_of_pieces,
                SUM(oi.qty * p.weight_grams) AS total_weight_grams
        FROM order_item oi
        LEFT JOIN product p ON (oi.record_id = p.product_id)
        WHERE oi.order_id =  {$row['order_id']}   
        ";
        $rowTotal = $fn->getRecordBySQL($SQLTotal);
        
        $ShipmentDetails = $xml->addChild('ShipmentDetails');
        $ShipmentDetails->addChild('NumberOfPieces', $rowTotal['number_of_pieces']);
        $ShipmentDetails->addChild('CurrencyCode', $fn->getIssetParam($row, 'currency', $c->currencyCode));        
        $ShipmentDetails = $this->getPiecesXMLTags($ShipmentDetails);
        
        $ShipmentDetails->addChild('PackageType', $c->packageType);
        $ShipmentDetails->addChild('Weight', $rowTotal['total_weight_grams'] / 1000);
        $ShipmentDetails->addChild('DimensionUnit', $c->dimensionUnit);
        $ShipmentDetails->addChild('WeightUnit', $c->weightUnit);
        $ShipmentDetails->addChild('GlobalProductCode', $c->globalProductCode);
        $ShipmentDetails->addChild('LocalProductCode', $c->localProductCode);
        $ShipmentDetails->addChild('DoorTo', $c->doorTo);
        $ShipmentDetails->addChild('Date', $fn->getIssetParam($row, 'ship_date', date('Y-m-d')));
        
        //$ShipmentDetails->addChild('Contents', 'For testing purpose only. Please do not ship');
        $ShipmentDetails->addChild('Contents', $c->shipmentContents);
    
        return $xml;
    }
    
    /**
     *
     * @param SimpleXMLElement $xml 
     */
    function getPiecesXMLTags(SimpleXMLElement $xml){
        $fn = Zend_Registry::get('fn');        
        $dbUtil = Zend_Registry::get('dbUtil');        
        $c = &$this->controller;
        $row = $c->orderArr;     
        
        $SQL = "
        SELECT  oi.*,
                p.weight_grams
        FROM order_item oi
        LEFT JOIN product p ON (oi.record_id = p.product_id)
        WHERE oi.order_id =  {$row['order_id']}   
        ";
        $order_items = $dbUtil->getSQLResultAsArray($SQL);   
        
        $Pieces = $xml->addChild('Pieces');
        $pieceCount = 0;
        $totalWeight = 0;
        foreach($order_items AS $rowItems){
            for($i = 0; $i < $rowItems['qty']; $i++){
                $pieceCount++;
                $Piece = $Pieces->addChild('Piece');
                $weight = $rowItems['weight_grams'] / 1000;
                $totalWeight += $weight;
                $Piece->addChild('PieceID', $pieceCount);
                $Piece->addChild('Weight', $weight);
                //$Piece->addChild('PieceContents', 'description about the piece');
                //$Piece->addChild('PackageType', 'EE');
                //$Piece->addChild('Depth', '2');
                //$Piece->addChild('Width', '2');
                //$Piece->addChild('Height', '2');
                //$Piece->addChild('DimWeight', '10.0');
            }
        }   
        
        return $xml;
    }
    
    /**
     *
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getSVRShipperXMLTags(SimpleXMLElement $xml){
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $row = $c->orderArr; 
                
        $Shipper = $xml->addChild('Shipper');
        
        $ShipperID = ($c->shipperID != '') ? $c->shipperID : $c->shipperAccountNumber;
        $Shipper->addChild('ShipperID', $ShipperID);
        
        $Shipper->addChild('CompanyName', $row['stockist_company_name']);
        $Shipper->addChild('AddressLine', $row['stockist_address1']);
        $Shipper->addChild('AddressLine', $row['stockist_address2']);
        $Shipper->addChild('City', $row['stockist_address_city']);
        //$Consignee->addChild('Division', 'Maharashtra');
        if($fn->getIssetParam($row, 'stockist_address_state')){
            $DivisionElemnetName = ($c->region == 'EA') ? "Division" : "DivisionCode";
            $Shipper->addChild($DivisionElemnetName, $row['stockist_address_state']);
        }        
        $Shipper->addChild('PostalCode',  $row['stockist_address_po_code']);
        $Shipper->addChild('CountryCode', $row['stockist_address_country_code']);
        $Shipper->addChild('CountryName', $row['stockist_country_name']);

        $Contact = $Shipper->addChild('Contact');
        $personName = $row['stockist_company_name'];
        if($row['stockist_first_name'] != '' || $row['stockist_last_name'] != ''){
            $personName = $row['stockist_first_name']. ' ' . $row['stockist_last_name'];
        }
        
        $Contact->addChild('PersonName', $personName);
        $Contact->addChild('PhoneNumber', $row['stockist_phone']);
//        $Contact->addChild('PhoneExtension', '45232');
//        $Contact->addChild('FaxNumber', '11234325423');
//        $Contact->addChild('Telex', '454586');
        
        return $xml;
    }

    /**
     * Origin address of the shipment
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getDCTFromXMLTags(SimpleXMLElement $xml){
        $c = &$this->controller;
        $row = $c->orderArr; 
                
        $From = $xml->addChild('From');
        
        $From->addChild('CountryCode', $row['stockist_address_country_code']);
        $From->addChild('Postalcode',  $row['stockist_address_po_code']);
        $From->addChild('City', $row['stockist_address_city']);
        
        return $xml;
    }    
    
    /**
     * Destination address of the shipment
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getDCTToXMLTags(SimpleXMLElement $xml){
        $c = &$this->controller;
        $row = $c->orderArr; 
                
        $To = $xml->addChild('To');
        
        $To->addChild('CountryCode', $row['shipping_address_country_code']);
        $To->addChild('Postalcode',  $row['shipping_address_po_code']);
        $To->addChild('City', $row['shipping_address_city']);
        
        return $xml;
    }    
    
    /**
     * 
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getSVRDutiableXMLTags(SimpleXMLElement $xml){
        $c = &$this->controller;
        $row = $c->orderArr; 
        
        $declaredValue = number_format($row['order_amount'] - $row['shipping_charge'], 2);
        
        $Dutiable = $xml->addChild('Dutiable');
        
        $Dutiable->addChild('DeclaredValue', $declaredValue);
        $Dutiable->addChild('DeclaredCurrency', $c->declaredCurrency);
        if($c->shipperEIN != ''){
            $Dutiable->addChild('ShipperEIN', $c->shipperEIN);
        }
        $Dutiable->addChild('TermsOfTrade', $c->termsOfTrade);
        
        return $xml;
    }    
    
    /**
     * 
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getDCTDutiableXMLTags(SimpleXMLElement $xml){
        $c = &$this->controller;
        $row = $c->orderArr; 
        
        $declaredValue = number_format($row['order_amount'] - $row['shipping_charge'], 2);
        
        $Dutiable = $xml->addChild('Dutiable');
        
        $Dutiable->addChild('DeclaredCurrency', $c->declaredCurrency);
        $Dutiable->addChild('DeclaredValue', $declaredValue);
        
        return $xml;
    }      

    /**
     * Origin address of the shipment
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement 
     */
    function getDCTBkgDetailsXMLTags(SimpleXMLElement $xml){
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $row = $c->orderArr; 
                
        $BkgDetails = $xml->addChild('BkgDetails');
        
        $BkgDetails->addChild('PaymentCountryCode', $row['stockist_address_country_code']);
        $BkgDetails->addChild('Date', $fn->getIssetParam($row, 'ship_date', date('Y-m-d')));
        $BkgDetails->addChild('ReadyTime', $c->readyTime);
        //$BkgDetails->addChild('ReadyTimeGMTOffset', "+01:00");
        $BkgDetails->addChild('DimensionUnit', $row['stockist_dimensional_unit']);
        $BkgDetails->addChild('WeightUnit', $row['stockist_weight_unit']);
        $BkgDetails = $this->getPiecesXMLTags($BkgDetails);
        $BkgDetails->addChild('IsDutiable', $c->isDutiable);
        $BkgDetails->addChild('NetworkTypeCode', $c->networkTypeCode);
        
        return $xml;
    }    
    
    /**
     *
     * @param string $xml
     * @return array 
     */
    function postXMLUsingCURL($xml ){
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;
        
        $url = ($cpCfg[CP_ENV]['DHLMode'] == 'live') ? $c->liveURL : $c->testURL;
        
        //CURL
        $headers[] = "Content-Type: text/xml" ;        
        $ch = curl_init() ;
        curl_setopt($ch,CURLOPT_URL,$url) ;
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch,CURLOPT_HEADER,0) ;
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers) ;
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,30) ;
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // to bypass the certificate

        $data = curl_exec($ch);
        if($data === false){
            echo 'Curl error: ' . curl_error($ch);
        }

        curl_close($ch) ;        

        $responseXML = new SimpleXMLElement($data);
        
        return  $responseXML;
    }
    
}