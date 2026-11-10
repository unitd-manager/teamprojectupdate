<?
class CP_Admin_Modules_Tradingsg_StockTransfer_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() { 
        $fn = Zend_Registry::get('fn');   
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $SQL ="
        SELECT st.*
        ,s.title AS location_name     
        FROM stock_transfer st
        LEFT JOIN site s ON (s.site_id = st.to_location)
        ";

        return $SQL;

    }

    /**
     *
     */

    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'st';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "st.stock_transfer_id = {$tv['record_id']}";
        } 

        $searchVar->sortOrder = "st.date DESC";

    }

    /**
     *
     */
    function getUpdateOrderLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $arr['msg'] = '';

        $product_id = $fn->getReqParam('product_id');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');

        $SQLCheck  = "
        SELECT product_id
        FROM stock_transfer_history
        WHERE product_id = {$product_id}
        AND stock_transfer_id = {$stock_transfer_id}
        ";

        $resultCheck = $db->sql_query($SQLCheck);
        $numRows     = $db->sql_numrows($resultCheck);
        
        if($numRows >= 1){
            $arr['msg'] = "Please note the product is already added";
            return $cpUtil->getJsonFromArray($arr);
            exit();
        }

       /* $SQL = "
                SELECT MAX(stock_transfer_history_id) AS stock_transfer_history_id 
                FROM stock_transfer_history
                ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        $stock_transfer_history_id = $row['stock_transfer_history_id'] + 1;*/

        $fa = array();
        //$fa['stock_transfer_history_id'] = $stock_transfer_history_id;
        $fa['stock_transfer_id']   = $stock_transfer_id;
        $fa['product_id']  = $product_id;
        $fa['qty']        = 0;
        $fa['created_by'] = $fn->getSessionParam('userName');
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_transfer_history');
        $db->sql_query($SQL);
    }
     /**
     *
     */
     function getUpdateStockTransferMod(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");

        $SQLtransmod    = "
        UPDATE stock_transfer
        set modified_by = '{$modified_by}',modification_date = '{$modification_date}'
        WHERE stock_transfer_id = {$stock_transfer_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

     }
    /**
     *
     */
    function getUpdateQtyOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $stock_transfer_history_qty = $fn->getReqParam('stock_transfer_history_id');
        $stock_transfer_id = $fn->getReqParam('stock_transfer_id');
        $qty = $fn->getReqParam('qty');

        $OrderItems = $this->getUpdateStockTransferMod();

        $SQL    = "
        UPDATE stock_transfer_history
        set qty = {$qty} ,modified_by = '{$modified_by}',modification_date = '{$modification_date}'
        WHERE stock_transfer_history_id = {$stock_transfer_history_qty}
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title, p.price, p.carton_no, p.batch_no, p.model, p.unit) AS label
        FROM product p
        LEFT JOIN product_company pc ON (pc.product_id = p.product_id)
        WHERE (p.title LIKE '%{$productTitle}%'
        OR p.item_code LIKE '%{$productTitle}%'
        OR p.model LIKE '%{$productTitle}%'
        OR p.carton_no LIKE '%{$productTitle}%')
        AND p.published = 1
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
    *
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('to_location', 'Please Select Site');

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

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $current_date = date('Y-m-d');

        $fa = $this->getFields();
        $fa['date']  = $current_date;
        $fa['from_location'] = $cpSiteIdSession;
        
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('site_id', 'Please select site');

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
        $db = Zend_Registry::get('db');

        $fa = array();
        //$fa = $fn->addToFieldsArray($fa, 'stock_transfer_id');
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'to_location');
        $fa = $fn->addToFieldsArray($fa, 'from_location');

        return $fa;
    }
    /**
     *
     */
    function getDeleteItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $stock_transfer_historyid    = $fn->getReqParam('stock_transfer_history_id');

            $deleteSQL    = "
            DELETE FROM stock_transfer_history
            WHERE stock_transfer_history_id = '{$stock_transfer_historyid}'
            ";
            $result = $db->sql_query($deleteSQL);

        return $deleteSQL;
    }

    /**
     *
     */
    function getPrintExportAsPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot.php');

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Blossoms');
        $pdf->SetSubject('Internal Transfer');
        $pdf->SetTitle('Internal Transfer');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $pdf->SetFont('Courier','',10);
        $pdf->AddPage();

        $stockTransfer_id = $fn->getReqParam('id');

        $SQL = "
        SELECT st.date
               ,st.from_location
               ,st.to_location
               ,sth.product_id
               ,sth.qty
        FROM stock_transfer st
        LEFT JOIN stock_transfer_history sth ON (sth.stock_transfer_id = st.stock_transfer_id)
        WHERE st.stock_transfer_id = '{$stockTransfer_id}'
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $row2    = $db->sql_fetchrow($result);

        $from_location = $fn->getRecordRowByID('site', 'site_id', $row2['from_location']);
        $to_location   = $fn->getRecordRowByID('site', 'site_id', $row2['to_location']);

        $tblHeading = '
        <table border="0" width="100%" cellpadding="5">
            <tr>
                <td border="0" align="center" height="30"><font style="font-size:20px; font-weight:bold">INTERNAL TRANSFER</font>
                </td>
            </tr>
        </table>
        ';

        $stock_transfer_date = $fn->getCPDate($row2['date'],"d-m-Y");

        $tblFromTo = '
        <table border = "1" width = "100%" cellpadding="5">
            <tr bgcolor="#FDCA9C" style="font-weight:bold;">
                <th>Date</th>
                <th>Transfer From</th>
                <th>Transfer To</th>
            </tr>
            <tr>
                <td>'.$stock_transfer_date.'</td>
                <td>'.$from_location['title'].'</td>
                <td>'.$to_location['title'].'</td>
            </tr>
        </table>
        ';

        $tblproducts = '<table border = "1" width = "100%" cellpadding="5">';

        $tblproducts = $tblproducts.'
            <thead>
                <tr bgcolor="#FDCA9C" style="font-weight:bold;">
                    <th width = "10%">SNo</th>
                    <th width = "40%">Item Name</th>
                    <th width = "35%">Item Reference</th>
                    <th width = "15%" align = "center">Qty</th>
                </tr>
            </thead>
        ';

        $serialNo = 1;

        while($row = $db->sql_fetchrow($result2)){

        $product_id = $row['product_id'];

            if($product_id != ''){
                $sqlProduct ="
                SELECT CONCAT_WS('::', item_code, carton_no, model) AS code
                       ,title
                FROM product
                WHERE product_id = {$row['product_id']}
                ";
                $resultProduct = $db->sql_query($sqlProduct);
                $rowProduct    = $db->sql_fetchrow($resultProduct);

                $tblproducts = $tblproducts.'
                <tbody>
                    <tr nobr="true">
                        <td width = "10%">'.$serialNo.'</td>
                        <td width = "40%">'.$rowProduct['title'].'</td>
                        <td width = "35%">'.$rowProduct['code'].'</td>
                        <td width = "15%" align = "center">'.$row['qty'].'</td>
                    </tr>
                </tbody>';

                $serialNo++;
            }else{

                $tblproducts = $tblproducts.'
                    <tbody>
                        <tr>
                            <td width = "100%"> No items has been transfered </td>
                        </tr>
                    </tbody>';
            }


        }

        $tblproducts = $tblproducts.'</table>';

        $tblSignature = '
        <table width = "100%" border = "0" cellpadding = "5">
            <tr style="font-weight:bold;">
                <th width = "40%">From Location</th>
                <th width = "20%"></th>
                <th width = "40%" align = "left">To Location</th>
            </tr>
            <tr>
                <td width = "14%"><br/><br/>Signature:</td>
                <td width = "26%"><br/><br/><hr></td>
                <td width = "20%"></td>
                <td width = "14%"><br/><br/>Signature:</td>
                <td width = "26%"><br/><br/><hr></td>
            </tr>
        </table>
        ';

        $pdf->ln(14);
        $pdf->writeHTML($tblHeading, true, false, false, false, '');
        $pdf->writeHTML($tblFromTo, true, false, false, false, '');
        $pdf->writeHTML($tblproducts, true, false, false, false, '');
        $pdf->writeHTML($tblSignature, true, false, false, false, '');
        $pdf->Output('Internal Transfer.pdf', 'I');

    }

}