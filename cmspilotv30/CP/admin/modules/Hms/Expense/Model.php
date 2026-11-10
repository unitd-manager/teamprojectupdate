<?
class CP_Admin_Modules_Hms_Expense_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL ="
        SELECT e.*
               ,s.title AS from_location
        FROM expense e
        LEFT JOIN site s ON (s.site_id = e.site_id)
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
        $searchVar->mainTableAlias = 'e';

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "e.expense_id = {$tv['record_id']}";
        }

        $searchVar->sortOrder = "e.created_by DESC";

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
        $expense_id = $fn->getReqParam('expense_id');

        $SQLCheck  = "
        SELECT product_id
        FROM expense_product
        WHERE product_id = {$product_id}
        AND expense_id = {$expense_id}
        AND status != 'Cancelled'
        ";

        $resultCheck = $db->sql_query($SQLCheck);
        $numRows     = $db->sql_numrows($resultCheck);

        if($numRows >= 1){
            $arr['msg'] = "Please note the product is already added";
            return $cpUtil->getJsonFromArray($arr);
            exit();
        }

        $fa = array();
        $fa['expense_id']     = $expense_id;
        $fa['product_id']     = $product_id;
        $fa['qty']            = 0;
        $fa['status']         = 'In Progress';
        $fa['created_by']     = $fn->getSessionParam('userName');
        $fa['creation_date']  = date("Y-m-d H:i:s");
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'expense_product');
        $db->sql_query($SQL);
    }
     /**
     *
     */
     function getUpdateExpenseMod(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $expense_id = $fn->getReqParam('expense_id');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");

        $SQLtransmod    = "
        UPDATE expense
        set modified_by = '{$modified_by}',modification_date = '{$modification_date}'
        WHERE expense_id = {$expense_id}
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
        $expense_product_id = $fn->getReqParam('expense_product_id');
        $expense_id  = $fn->getReqParam('expense_id');
        $qty         = $fn->getReqParam('qty');

        $OrderItems = $this->getUpdateExpenseMod();

        $SQL    = "
        UPDATE expense_product
        set qty = {$qty} ,modified_by = '{$modified_by}',modification_date = '{$modification_date}'
        WHERE expense_product_id = {$expense_product_id}
        AND expense_id = {$expense_id}
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdateStatusOrderItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $expense_product_id = $fn->getReqParam('expense_product_id');
        $expense_id         = $fn->getReqParam('expense_id');
        $product_status     = $fn->getReqParam('product_status');

        $SQLExpense   = "
        SELECT lock_record
        FROM expense
        WHERE expense_id = {$expense_id}
        ";
        $resultExpense = $db->sql_query($SQLExpense);
        $rowExpense    = $db->sql_fetchrow($resultExpense);

        if($rowExpense['lock_record'] == 1){
            $SQLExpenseUpdate   = "
            UPDATE expense
            SET lock_record = 0 ,modified_by = '{$modified_by}',modification_date = '{$modification_date}'
            WHERE expense_id = {$expense_id}
            ";
            $resultExpenseUpdate = $db->sql_query($SQLExpenseUpdate);
        }

        $SQL    = "
        UPDATE expense_product
        SET status = '{$product_status}' ,modified_by = '{$modified_by}',modification_date = '{$modification_date}'
        WHERE expense_product_id = {$expense_product_id}
        AND expense_id = {$expense_id}
        ";
        $result = $db->sql_query($SQL);
    }

    /**
     *
     */
    function getUpdateCompleteTransactionProduct() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $expense_id = $fn->getReqParam('expense_id');

        $SQL    = "
        UPDATE expense_product
        SET status = 'Added'
           ,lock_record = 1
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE expense_id = {$expense_id}
        AND status = 'In Progress'
        ";
        $result = $db->sql_query($SQL);

        $SQLtransmod    = "
        UPDATE expense
        SET status = 'Confirm'
           ,lock_record = 1
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE expense_id = {$expense_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

    }

    /**
     *
     */
    function getRollbackCompleteTransactionProduct() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");
        $expense_id = $fn->getReqParam('expense_id');

        $SQL    = "
        UPDATE expense_product
        SET status = 'In Progress'
           ,lock_record = 0
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE expense_id = {$expense_id}
        AND status != 'Cancelled'
        ";
        $result = $db->sql_query($SQL);

        $SQLtransmod    = "
        UPDATE expense
        SET status = 'In Progress'
           ,lock_record = 0
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE expense_id = {$expense_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

    }

    /**
     *
     */
     function getUpdateDeductStockProduct(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $expense_id = $fn->getReqParam('expense_id');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");

        $SQL    = "
        UPDATE expense_product
        SET status = 'Added'
           ,stock_deducted = 1
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE expense_id = {$expense_id}
        AND status != 'Cancelled'
        ";
        $result = $db->sql_query($SQL);

        $SQLtransmod    = "
        UPDATE expense
        SET status = 'Confirm'
           ,stock_deducted = 1
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE expense_id = {$expense_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

        $SQLStockTrans = "
        SELECT product_id
              ,qty
        FROM expense_product
        WHERE expense_id = {$expense_id}
        AND status != 'Cancelled'
        ";
        $resultStockTrans  = $db->sql_query($SQLStockTrans);
        while($StockTrans = $db->sql_fetchrow($resultStockTrans)){
            $SQLStockFrom = "
            SELECT actual_stock{$cpSiteIdSession} AS Stock_From
            FROM inventory
            WHERE product_id = {$StockTrans['product_id']}
            ";
            $resultStockFrom = $db->sql_query($SQLStockFrom);
            $rowStockFrom    = $db->sql_fetchrow($resultStockFrom);

            $stock = $rowStockFrom['Stock_From'] - $StockTrans['qty'];

            $SQLUpdateProduct = "
            UPDATE product SET qty_in_stock{$cpSiteIdSession} = {$stock}
            WHERE product_id = '{$StockTrans['product_id']}'
            ";
            $resultUpdateProduct  = $db->sql_query($SQLUpdateProduct);

            $SQLUpdateInventory = "
            UPDATE inventory SET actual_stock{$cpSiteIdSession} = {$stock}
            WHERE product_id = '{$StockTrans['product_id']}'
            ";
            $resultUpdateInventory  = $db->sql_query($SQLUpdateInventory);
        }

     }

     /**
     *
     */
     function getUpdateStatusExpense(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $expense_id = $fn->getReqParam('expense_id');
        $status     = $fn->getReqParam('status');
        $modified_by = $fn->getSessionParam('userName');
        $modification_date = date("Y-m-d H:i:s");

        $SQLtransmod    = "
        UPDATE expense
        SET status = '{$status}'
           ,modified_by = '{$modified_by}'
           ,modification_date = '{$modification_date}'
        WHERE expense_id = {$expense_id}
        ";
        $result1 = $db->sql_query($SQLtransmod);

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
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $productTitle = $extractor[0];

        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,p.product_id AS id
              ,CONCAT_WS(' **** ', p.title, p.price) AS label
        FROM product p
        WHERE (p.title LIKE '%{$productTitle}%'
        OR p.item_code LIKE '%{$productTitle}%')
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
        $validate->validateData('title', 'Please enter title');
        $validate->validateData('status', 'Please select status');
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
        $expense_code = $fn->getSettingsValueByKey("nextExpenseCode");

        $fa = $this->getFields();
        $fa['site_id']      = $cpSiteIdSession;
        $fa['expense_code'] = $expense_code;

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $id = $fn->addRecord($fa);
        //To update expense code
        $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextExpenseCode'";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('status', 'Please select status');

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
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'status');

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
        $pdf->SetAuthor('HMS');
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
        $printType        = $fn->getReqParam('printType');

        $SQL = "
        SELECT st.date
               ,st.from_location
               ,st.to_location
               ,sth.product_id
               ,sth.qty
               ,sth.qty_requested
        FROM stock_transfer st
        LEFT JOIN stock_transfer_history sth ON (sth.stock_transfer_id = st.stock_transfer_id)
        WHERE st.stock_transfer_id = '{$stockTransfer_id}'
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $row2    = $db->sql_fetchrow($result);

        $from_location = $fn->getRecordRowByID('site', 'site_id', $row2['from_location']);
        $to_location   = $fn->getRecordRowByID('site', 'site_id', $row2['to_location']);
        $tblHeadingText = '';

        if($printType == 'Request Form'){
            $tblHeadingText = 'INTERNAL TRANSFER';
        }
        else{
            $tblHeadingText = 'DELIVERY ORDER';
        }

        $tblHeading = '
        <table border="0" width="100%" cellpadding="5">
            <tr>
                <td border="0" align="center" height="30"><font style="font-size:20px; font-weight:bold">'.$tblHeadingText.'</font>
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

        if($printType == 'Reuqest Form'){
            $tblproducts = $tblproducts.'
                <thead>
                    <tr bgcolor="#FDCA9C" style="font-weight:bold;">
                        <th width = "10%">SNo</th>
                        <th width = "15%">code</th>
                        <th width = "60%">Item Name</th>
                        <th width = "15%" align = "center">Request Qty</th>
                    </tr>
                </thead>
            ';
        }else{
            $tblproducts = $tblproducts.'
                <thead>
                    <tr bgcolor="#FDCA9C" style="font-weight:bold;">
                        <th width = "10%">SNo</th>
                        <th width = "15%">Code</th>
                        <th width = "45%">Item Name</th>
                        <th width = "15%" align = "center">Request Qty</th>
                        <th width = "15%" align = "center">Qty Delivered</th>
                    </tr>
                </thead>
            ';
        }

        $serialNo = 1;

        while($row = $db->sql_fetchrow($result2)){

            $product_id = $row['product_id'];

            if($product_id != ''){
                $sqlProduct ="
                SELECT CONCAT_WS('::', item_code) AS code
                       ,title
                FROM product
                WHERE product_id = {$row['product_id']}
                ";
                $resultProduct = $db->sql_query($sqlProduct);
                $rowProduct    = $db->sql_fetchrow($resultProduct);

                if($printType == 'Reuqest Form'){

                    $tblproducts = $tblproducts.'
                    <tbody>
                        <tr nobr="true">
                            <td width = "10%">'.$serialNo.'</td>
                            <td width = "15%">'.$rowProduct['code'].'</td>
                            <td width = "60%">'.$rowProduct['title'].'</td>
                            <td width = "15%" align = "center">'.$row['qty_requested'].'</td>
                        </tr>
                    </tbody>';
                }else{
                    $tblproducts = $tblproducts.'
                    <tbody>
                        <tr nobr="true">
                            <td width = "10%">'.$serialNo.'</td>
                            <td width = "15%">'.$rowProduct['code'].'</td>
                            <td width = "45%">'.$rowProduct['title'].'</td>
                            <td width = "15%" align = "center">'.$row['qty_requested'].'</td>
                            <td width = "15%" align = "center">'.$row['qty'].'</td>
                        </tr>
                    </tbody>';
                }

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

        if($printType == 'Reuqest Form'){
            $tblSignature = '
            <table width = "100%" border = "0" cellpadding = "5">
                <tr style="font-weight:bold;">
                    <th width = "40%">From Location: '.$from_location['title'].'</th>
                    <th width = "20%"></th>
                </tr>
                <tr>
                    <td width = "14%"><br/><br/>Signature:</td>
                    <td width = "26%"><br/><br/><hr></td>
                </tr>
            </table>
            ';
        }else{
            $tblSignature = '
            <table width = "100%" border = "0" cellpadding = "5">
                <tr style="font-weight:bold;">
                    <th width = "40%">From Location: '.$from_location['title'].'</th>
                    <th width = "20%"></th>
                    <th width = "40%" align = "left">To Location: '.$to_location['title'].'</th>
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
        }

        $pdf->writeHTML($tblHeading, true, false, false, false, '');
        $pdf->writeHTML($tblFromTo, true, false, false, false, '');
        $pdf->writeHTML($tblproducts, true, false, false, false, '');
        $pdf->writeHTML($tblSignature, true, false, false, false, '');
        $pdf->Output('Internal Transfer.pdf', 'I');

    }

}