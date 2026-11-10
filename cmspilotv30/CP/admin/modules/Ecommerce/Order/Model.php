<?
class CP_Admin_Modules_Ecommerce_Order_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $orgName = '';
        $orgTable = '';
        if ($cpCfg['m.ecommerce.order.showOrganization']) {
            $orgName = ",org.name AS organization_name";
            $orgTable = "LEFT JOIN organization org ON (o.organization_id = org.organization_id)";
        }
        
        $sumTxt = '';
        if ($cpCfg['m.ecommerce.order.hasDiscount']){
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge - o.discount";
        } else {
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge";
        }

        $SQL = "
        SELECT o.*
              ,gc1.name AS cust_country_name
              ,gc2.name AS shipping_country_name
              ,(SELECT ($sumTxt)
               FROM order_item oi
               WHERE oi.order_id = o.order_id
               ) AS order_amount
              {$orgName}
        FROM `order` o
        LEFT JOIN geo_country gc1 ON (o.cust_address_country_code = gc1.country_code)
        LEFT JOIN geo_country gc2 ON (o.shipping_address_country_code = gc2.country_code)
        {$orgTable}
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'o';

        $business_id = $fn->getReqParam('business_id');
        $organization_id = $fn->getReqParam('organization_id');
        $business_contact_id = $fn->getReqParam('business_contact_id');
        $creation_date1 = $fn->getReqParam('creation_date_1');
        $creation_date2 = $fn->getReqParam('creation_date_2');
        $order_status = $fn->getReqParam('order_status');
        $shipment_status = $fn->getReqParam('shipment_status');
        $order_type   = $fn->getReqParam('order_type');
        $ok_to_ship   = $fn->getReqParam('ok_to_ship');
        $shipping_address_country_code = $fn->getReqParam('shipping_address_country_code');
        
        if ($business_id != '') {
            $searchVar->sqlSearchVar[] = "o.business_id = '{$business_id}'";
        }

        if ($organization_id != '') {
            $searchVar->sqlSearchVar[] = "org.organization_id = '{$organization_id}'";
        }

        if ($business_contact_id != '') {
            $searchVar->sqlSearchVar[] = "o.business_contact_id = '{$business_contact_id}'";
        }

        if ($creation_date1 != "" && $creation_date2 != "" ) {
            $searchVar->sqlSearchVar[] = "(o.creation_date BETWEEN '{$creation_date1} 00:00:00' AND '{$creation_date2} 23:59:59')";
        }

        if ($order_status != '') {
            $searchVar->sqlSearchVar[] = "o.order_status = '{$order_status}'";
        }
        
        if ($shipment_status != '') {
            $searchVar->sqlSearchVar[] = "o.shipment_status = '{$shipment_status}'";
        }

        if ($order_type != '') {
            $searchVar->sqlSearchVar[] = "o.order_type = '{$order_type}'";
        }
        
        if ($shipping_address_country_code != '') {
            $searchVar->sqlSearchVar[] = "o.shipping_address_country_code = '{$shipping_address_country_code}'";
        }

        if ($ok_to_ship != '') {
            $searchVar->sqlSearchVar[] = "o.ok_to_ship = '{$ok_to_ship}'";
        }        

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
                o.cust_first_name LIKE '%{$tv['keyword']}%'  OR
                o.order_id        LIKE '%{$tv['keyword']}%'  OR
                o.cust_last_name  LIKE '%{$tv['keyword']}%'  OR
                o.order_code      LIKE '%{$tv['keyword']}%'  OR
                o.memo            LIKE '%{$tv['keyword']}%'  OR
                o.shipping_first_name LIKE '%{$tv['keyword']}%'  OR
                o.shipping_last_name LIKE '%{$tv['keyword']}%'
            )";
        }

        $searchVar->sortOrder = "o.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $validate->resetErrorArray();
        $validate->validateData('order_date', 'Please enter the order date');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['order_status'] = 'New';
        $id = $fn->addRecord($fa);

        $order_code = $cpCfg['m.ecommerce.order.codePrefix'] . $id;

        $SQL = "
        UPDATE `order`
        SET order_code = '{$order_code}'
        WHERE order_id = {$id}
        ";
        $result = $db->sql_query($SQL);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('order_id', 'Please enter the title');

        if($fn->getPostParam('shipping_email') != ''){
            $validate->validateData('shipping_email' , 'Please enter valid shipping email', 'email');
        }
        
        if($fn->getPostParam('cust_email') != ''){
            $validate->validateData('cust_email' , 'Please enter valid customer email', 'email');
        }        
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'order_date');
        $fa = $fn->addToFieldsArray($fa, 'order_status');
        $fa = $fn->addToFieldsArray($fa, 'order_code');
        $fa = $fn->addToFieldsArray($fa, 'memo');
        $fa = $fn->addToFieldsArray($fa, 'payment_method');
        $fa = $fn->addToFieldsArray($fa, 'shipping_charge');
        $fa = $fn->addToFieldsArray($fa, 'organization_id');
        $fa = $fn->addToFieldsArray($fa, 'ok_to_ship');

        $fa = $fn->addToFieldsArray($fa, 'cust_first_name');
        $fa = $fn->addToFieldsArray($fa, 'cust_last_name');
        $fa = $fn->addToFieldsArray($fa, 'cust_email');
        $fa = $fn->addToFieldsArray($fa, 'cust_phone');
        $fa = $fn->addToFieldsArray($fa, 'cust_address1');
        $fa = $fn->addToFieldsArray($fa, 'cust_address2');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_city');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_area');
        $fa = $fn->addToFieldsArray($fa, 'cust_address_state');
        $fa = $fn->addToFieldsArray($fa, 'cust_po_code');
        $fa = $fn->addToFieldsArray($fa, 'cust_country_code');

        $fa = $fn->addToFieldsArray($fa, 'shipping_first_name');
        $fa = $fn->addToFieldsArray($fa, 'shipping_last_name');
        $fa = $fn->addToFieldsArray($fa, 'shipping_email');
        $fa = $fn->addToFieldsArray($fa, 'shipping_phone');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address1');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address2');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_area');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_city');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_state');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'shipping_address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'shipment_status');

        return $fa;
    }
    
    /**
     *
     * @param array $row
     * @return array 
     */
    function getDHLCapabilityService($row){
        set_time_limit(50000); 

        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');        
        
        $isDutiable = 'Y';
        if($row['stockist_address_country_code'] == $row['shipping_address_country_code']){
            $isDutiable = 'N';
        }
        
        $wDHL = getCPWidgetObj('ecommerce_DHL');
        $capabilityResultArr = $wDHL->getWidget(array(
             'siteID'    => $cpCfg['cp.DHL_siteID']
            ,'password'  => $cpCfg['cp.DHL_password']
            ,'shipperID' => $cpCfg['cp.DHL_shipperID']
            ,'helperFn'    => 'getCapability'
            ,'orderArr'    => $row
            ,'wrapWidget'  => false
            ,'isDutiable'  => $isDutiable
        )); 
        
        $responseXML = new SimpleXMLElement($capabilityResultArr['responseXML']);
        $responseArr = $cpUtil->simpleXMLToArray($responseXML);
        
//        print $row['order_id']."<br />";
//        print $capabilityResultArr['requestXML'];
//        print '<pre>';
//        print_r($responseArr);
//        print '</pre>';            
//        print '<hr />';

        
        $fa = array();
        $fa['dhl_dct_request_xml']  = $capabilityResultArr['requestXML'];
        $row['dhl_dct_request_xml'] = $fa['dhl_dct_request_xml'];
        
        $fa['dhl_dct_response_xml'] = $responseXML->asXML();
        $row['dhl_dct_response_xml'] = $fa['dhl_dct_response_xml'];

        $responseArrGCR = $responseArr['GetCapabilityResponse'];
        $BkgDetails = $fn->getIssetParam($responseArrGCR, 'BkgDetails');

        $fa['dhl_dct_success'] = is_array($BkgDetails) ? 1 : 0;
        $row['dhl_dct_success'] = $fa['dhl_dct_success'];
        
        $fa['dhl_dct_status_condition_code'] = !$fa['dhl_dct_success'] ? $responseArrGCR['Note']['Condition']['ConditionCode'] : '';
        $row['dhl_dct_status_condition_code']= $fa['dhl_dct_status_condition_code'];
        
        $fa['dhl_dct_status_condition_data']  = !$fa['dhl_dct_success'] ? $responseArrGCR['Note']['Condition']['ConditionData'] : '';
        $row['dhl_dct_status_condition_data'] = $fa['dhl_dct_status_condition_data'];
        

        $fa['modification_date'] = $current_date   = $cpUtil->getISODateStr();

        $whereCondition = "WHERE order_id = {$row['order_id']}";
        $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, 'order', $whereCondition);
        $result = $db->sql_query($SQLUpdate);   
        
        return $row;
    }
    
    /**
     * http://quarkie.localhost/admin/index.php?_spAction=uploadToDHL&module=ecommerce_order&showHTML=0
     */
    function getUploadToDHL(){
        set_time_limit(50000);
        
        $dbz = Zend_Registry::get('dbz');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $SQL = $this->getSQL()."
        WHERE o.order_status = ?
          AND o.ok_to_ship = ?
          AND (o.dhl_airway_bill_number = '' OR o.dhl_airway_bill_number IS NULL)
        -- AND o.order_id = 8
        ";        

        $stmt = $dbz->query($SQL, array('Paid', 1));

        while ($row = $stmt->fetch()) {

            $row = $this->getDHLCapabilityService($row); 
            if(!$row['dhl_dct_success']){ // Has Capablity request error
                continue;
            }
            
            $DHLProductCodes = $this->getDHLProductCodesFromCapablityResponse($row['dhl_dct_response_xml']);
            
            $isDutiable = 'Y';
            if($row['stockist_address_country_code'] == $row['shipping_address_country_code'] && $row['dhl_region'] != 'AM'){
                $isDutiable = 'N';
            }
            
            $wDHL = getCPWidgetObj('ecommerce_DHL');
            $svResultArr = $wDHL->getWidget(array(
                 'siteID'    => $cpCfg['cp.DHL_siteID']
                ,'password'  => $cpCfg['cp.DHL_password']
                ,'shipperID' => $cpCfg['cp.DHL_shipperID']
                ,'region'    => $row['dhl_region']
                ,'shipperAccountNumber' => $cpCfg['cp.DHL_shipperAccountNumber']
                ,'helperFn'    => 'getShipmentValidate'
                ,'orderArr'    => $row
                ,'wrapWidget'  => false
                ,'globalProductCode' => $DHLProductCodes['globalProductCode']
                ,'localProductCode'  => $DHLProductCodes['localProductCode']
                ,'isDutiable'   => $isDutiable
                ,'referenceIDPrefix' => $cpCfg['cp.DHL_referenceIDPrefix']
            ));        

            
            $responseXML = new SimpleXMLElement($svResultArr['responseXML']);
            $responseArr = $cpUtil->simpleXMLToArray($responseXML);
            
//            print $row['order_id']."<br />";
//            print '<pre>';
//            print $svResultArr['requestXML'];
//            print_r($responseArr);
//            print '</pre>';
//            print '<hr />';

            
            $fa = array();
            $fa['dhl_sv_request_xml']  = $svResultArr['requestXML'];
            $fa['dhl_sv_response_xml'] = $responseXML->asXML();
            
            $awb = $fn->getIssetParam($responseArr, 'AirwayBillNumber');
            $fa['dhl_airway_bill_number'] = $awb;
            $fa['dhl_sv_status_condition_code'] = ($awb == '') ? $responseArr['Response']['Status']['Condition']['ConditionCode'] : '';
            $fa['dhl_sv_status_condition_data'] = ($awb == '') ? $responseArr['Response']['Status']['Condition']['ConditionData'] : '';

            if($awb != ''){
                $fa['shipment_status'] = 'Ready to Ship';
            }
            
            $fa['modification_date'] = $current_date   = $cpUtil->getISODateStr();
            
            $whereCondition = "WHERE order_id = {$row['order_id']}";
            $SQLUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, 'order', $whereCondition);
            $result = $db->sql_query($SQLUpdate);   
            
            if($awb != ''){
                $responseXMLPath = realpath("{$cpCfg['cp.mediaFolder']}/DHL/ResponseXMLS");
                $filename = "{$responseXMLPath}/{$awb}.xml";
                
                $fh = fopen($filename, "w");
                fwrite($fh, $responseXML->asXML());
                fclose ($fh);
                $this->sendDHLAWBTrackingEmailToUser($row['order_id']);
            }
            
        }        
        $this->getGenerateDHLLabel();
        $this->getAttachDHLLabelToOrder();
        print "<br />Successfully uploaded to DHL
        <script>
           window.opener.location = window.opener.location;
        </script>            
        ";
        
    }
    
    /**
     *
     * @param type $responseXML
     * @return type 
     */
    function getDHLProductCodesFromCapablityResponse($responseXML){
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        
        $resultArr = array();
        
        $responseXMLObj = new SimpleXMLElement($responseXML);
        $responseArr = $cpUtil->simpleXMLToArray($responseXMLObj);
        
        $QtdShp = $responseArr['GetCapabilityResponse']['BkgDetails']['QtdShp'];
        
        foreach ($QtdShp as $DHLProduct) {
            foreach ($cpCfg['cp.DHL_defaultProductCodesArray'] as $key => $value) {
                if($DHLProduct['GlobalProductCode'] == $key){
                    $resultArr['globalProductCode'] = $DHLProduct['GlobalProductCode'];
                    $resultArr['localProductCode']  = $DHLProduct['LocalProductCode'];
                    return $resultArr;
                }
            }
        }
        
        //if no match found return the first product codes
        $resultArr['globalProductCode'] = $QtdShp[0]['GlobalProductCode'];
        $resultArr['localProductCode']  = $QtdShp[0]['LocalProductCode'];  
        return $resultArr;
        
    }
    
    /**
     * http://quarkie.localhost/admin/index.php?_spAction=generateDHLLabel&module=ecommerce_order&showHTML=0
     * run the cmd or sh script to generate lable from xml file located in the folder /media/DHL/ResponseXMLS
     * after the label generation the labael pdf will be placed in the folder /media/DHL/PDFReports
     * the xml files in the folder /media/DHL/ResponseXMLS will be moved to /media/DHL/ProcessedXMLS
     */
    function getGenerateDHLLabel(){
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $commandPath = CP_LIBRARY_PATH."DHL/clientSoftware/SOPLabel/generateSOPLabel";
        $sopRoot  = CP_LIBRARY_PATH . "DHL/clientSoftware/SOPLabel";
        $propPath = realpath($cpCfg['cp.siteRoot'])."/DHL/properties/".CP_ENV."";
        
        $jrePath = $cpCfg[CP_ENV]['jreInstallationPath'];

        if(CP_ENV == 'local'){
            exec("{$commandPath} {$sopRoot} {$propPath}");        
        } else if(CP_ENV == 'testing'){
            exec("{$commandPath}.sh {$sopRoot} {$propPath} {$jrePath}");
        } else if(CP_ENV == 'production'){
            exec("{$commandPath}.sh {$sopRoot} {$propPath} {$jrePath}");
        }
    }
    
    /**
     * http://quarkie.localhost/admin/index.php?_spAction=attachDHLLabelToOrder&module=ecommerce_order&showHTML=0
     *  locate pdf file with airway bill number in the folder /media/DHL/PDFReports
     *  attach the file to order record in the media
     *  delete the pdf file from /media/DHL/PDFReports
     * 
     */
    function getAttachDHLLabelToOrder(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbz = Zend_Registry::get('dbz');
        $media = Zend_Registry::get('media');
        
        $PDFReportsPath = realpath("../media/DHL/PDFReports");
        $dir = opendir($PDFReportsPath);

        while ($file = readdir($dir)) {
            if (preg_match('/\.pdf/i', $file)) {
                $awb = preg_replace("/\\.[^.\\s]{3,4}$/", "", $file); //remove the file extension
                
                $SQL = $this->getSQL()."
                WHERE o.dhl_airway_bill_number = ?
                ";        
                $stmt = $dbz->query($SQL, array($awb));       
                while ($row = $stmt->fetch()) {
                    $order_id = $row['order_id'];
                    $sourceFilePath = $PDFReportsPath . "/{$file}";
                    $exp = array(
                        'srcFile' => $sourceFilePath
                        ,'actualFileName' => $file
                    );
                    $media->model->createMedia('ecommerce_order', 'label', $order_id, $exp);    
                    unlink($sourceFilePath);
                }
            }
        }        
        
        print "<br />All the labels are successfully attached to the order";
    }
    
    /**
     *
     */
    function sendDHLAWBTrackingEmailToUser($order_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $row = $fn->getRecordRowByID('order', 'order_id', $order_id);

        if(!is_array($row)){
            return;
        }

        $message = $ln->gd2('m.ecommerce.order.dhl.email.trackingNotifyUserBody');
        if($message == ''){
            $message = $cpCfg['m.ecommerce.order.dhl.email.trackingNotifyUserBody'];
        }
       
        $message = str_replace("[[first_name]]"     , $row["shipping_first_name"], $message );
        $message = str_replace("[[last_name]]"      , $row["shipping_last_name"] , $message );
        $message = str_replace("[[email]]"          , $row["shipping_email"]     , $message );
        $message = str_replace("[[dhl_airway_bill_number]]", $row['dhl_airway_bill_number'], $message );
        $message = str_replace("[[currentDate]]"    , $currentDate               , $message );
        $message = str_replace("[[siteUrl]]"        , $cpCfg['cp.siteUrl']       , $message );

        $subject   = $ln->gd2('m.ecommerce.order.dhl.email.trackingNotifyUserSubject');
        if($subject == ''){
            $subject = $cpCfg['m.ecommerce.order.dhl.email.trackingNotifyUserSubject'];            
        }
        
        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.adminEmail'];
        $toName    = $row["shipping_first_name"] . " " . $row["shipping_last_name"];
        $toEmail   = $row["shipping_email"];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();
    }    

    //==================================================================//
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'order_id'        => $phpExcel->getFldObj('Order ID')
             ,'shipping_first_name'           => $phpExcel->getFldObj('First Name')
             ,'shipping_last_name'            => $phpExcel->getFldObj('Last Name')
             ,'shipping_email'                => $phpExcel->getFldObj('Email')
             ,'shipping_phone'                => $phpExcel->getFldObj('Phone')
             ,'shipping_address1'             => $phpExcel->getFldObj('Address 1')
             ,'shipping_address2'             => $phpExcel->getFldObj('Address 2')
             ,'shipping_address_city'         => $phpExcel->getFldObj('City')
             ,'shipping_address_area'         => $phpExcel->getFldObj('Area')
             ,'shipping_address_state'        => $phpExcel->getFldObj('State')
             ,'shipping_address_country_code' => $phpExcel->getFldObj('Country')
             ,'shipping_address_po_code'      => $phpExcel->getFldObj('Zip Code')
             ,'payment_method'                => $phpExcel->getFldObj('Payment Method')
             ,'order_amount'                  => $phpExcel->getFldObj('Amount')
             ,'order_status'                  => $phpExcel->getFldObj('Payment Status')
             ,'creation_date'                 => $phpExcel->getFldObj('Order Date')
        );

        $file_name = "Order_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}
