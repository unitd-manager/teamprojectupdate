<?
class CP_Admin_Modules_Labsg_PurchaseOrder_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');

        $text = '';
        $rows = '';
        $count = 0;

        foreach ($dataArray as $row){

            $SQLTotal = "
                SELECT SUM(round(
                (pop.qty * pop.price),2)) AS total_cost
                FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['po_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($rowTotal['total_cost'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($row['purchase_order_date'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListRowEnd($row['purchase_order_id'])}
            ";
            $count++ ;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('PO Code', 'po.po_code')}
        {$listObj->getListHeaderCell('Title', 'po.title')}
        {$listObj->getListHeaderCell('Supplier Name', 'supplier_name')}
        {$listObj->getListHeaderCell('Client', 'company_name')}
        {$listObj->getListHeaderCell('PO Value', 'amount')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('PO Date', 'purchase_order_date')}
        {$listObj->getListHeaderCell('Creation Date', 'creation_date')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $sqlSupplier = $fn->getDDSql('labsg_medicalSupplier');
        $expSupplier = array('hideFirstOption' => 1);

        $fieldset = "
        {$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, '', $expSupplier)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Purchase Order Header', $fieldset)}
        ";
        return $text;
    }

    //==================================================================//
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $expNoEdit = array('isEditable' => 0);

        $expContact = array('detailValue' => $row['contact_name_supplier']);
        $modContact = getCPModuleObj('trading_contact');

        $expCompany = array('sqlType' => 'OneField');

        $expVl = array('sqlType' => 'OneField');
        $sqlPriority = $fn->getValuelistSql('quotePriority');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $statusArr = $cpCfg['m.labsg.purchaseOrder.statusArr'];
        if($row['status'] == 'confirmed'){       //if po confirmed, remove option 'new'
            unset($statusArr[array_search('new', $statusArr)]);
        }

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $expStaff   = array('detailValue' => $row['staff_name']);

        $actionButtons = '';
        //$Patient_visit_link = "index.php?_topRm=main&module=labsg_patientVisit&_action=edit&patient_visit_id={$row['patient_visit_id']}";

        $urlPOtoExcel = "index.php?module=labsg_purchaseOrder&_spAction=printPOtoExcel&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $actionButtons .="
        <div class='float_left button mb5'>
            <a href='{$urlPOtoExcel}' id='printExcel'>Export to Excel</a>
        </div>
        ";

        $urlPOtoPDF = "index.php?module=labsg_purchaseOrder&_spAction=printPOtoPDF&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $actionButtons .="
        <div class='float_left button mb5'>
            <a href='{$urlPOtoPDF}' target='blank' id='printPDF'>Export as PDF</a>
        </div>
        ";


        $print ="
        <div class='floatbox actionBtnsDetail'>
            <div class='purchaseOrderRightpanelButtons floatbox'>
                {$actionButtons}
            </div>
        </div>
        ";

        $text = "
        {$print}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Purchase Order Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$row['creation_date']} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$row['modification_date']}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td>{$formObj->getTBRow('PO Code', 'po_code', $row['po_code'], $expNoEdit)}</td>
                                <td>{$formObj->getTBRow('Title', 'title', $row['title'])}</td>
                                <td>{$formObj->getDDRowByArr('Status', 'status', $statusArr, $row['status'])}</td>
                                <td>{$formObj->getTBRow('Supplier', 'supplier_name', $row['supplier_name'], $expNoEdit)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Priority', 'priority', $sqlPriority, $row['priority'], $expVl)}</td>
                                <td>{$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager,$row['staff_id'], $expStaff)}</td>
                                <td>{$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}</td>
                                <td>{$formObj->getDDRowBySQL('Currency', 'buy_currency', $sqlCurrency, $row['buy_currency'], $expVl)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTARow('Notes to Supplier', 'notes', $row['notes'])}</td>
                                <td>{$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'])}</td>
                                <td>{$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'])}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        /*$text = "
        {$formObj->getFieldSetWrapped('Purchase Order Header', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";*/

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        $text = '';
        $record_id = $fn->getIssetParam($row, 'purchase_order_id');
        $purchase_order_id  = $fn->getReqParam('purchase_order_id');

        $sqlpurchaseorder = "
        SELECT po.*
        FROM purchase_order po
        WHERE po.purchase_order_id = {$row['purchase_order_id']}
        ";

        $resultpurchaseorder = $db->sql_query($sqlpurchaseorder);
        $rowpurchaseorder = $db->sql_fetchrow($resultpurchaseorder);

        if ($rowpurchaseorder['purchase_order_id'] != '') {
            $text .="
            <div id='renewalLinkPortal'>{$this->getAddProduct($row['purchase_order_id'], $row['company_id_supplier'])}</div>
            ";
        }

        $text .="
        {$media->getRightPanelMediaDisplay('Picture', 'labsg_purchaseOrder', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'labsg_purchaseOrder', 'attachment', $row)}
        ";

        return $text;
    }

    //==================================================================//
    /**
     *
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $status = $fn->getReqParam('status');
        $company_id = $fn->getReqParam('company_id');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        WHERE category = 'Supplier'
        ORDER BY company_name
        ";

        $sqlCompany = $fn->getDDSql('labsg_company');

        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.labsg.purchaseOrder.statusArr'], $status)}
            </select>
        </td>
        <td>
            <select class='w125' name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
           </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddProduct($purchase_order_id='' ,$supplier_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($purchase_order_id == ''){
            $purchase_order_id = $fn->getReqParam('purchase_order_id');
        }

        if($supplier_id == ''){
            $supplier_id = $fn->getReqParam('supplier_id');
        }

        $Product = $this->getAddProductDetail($purchase_order_id);

        $recCount = $fn->getRecordCount('po_product', "purchase_order_id = '{$purchase_order_id}'");

        $header ="
        <thead>
            <tr>
            <th>Product</th>
            <th>Cost Price</th>
            <th>Quantity</th>
            <th>Qty Delivered</th>
            <th>Last Quoted Price</th>
            <th>Qty Balance</th>
            <th>Status</th>
            <th>Edit</th>
            <th>History</th>
            <th>Creation</th>
            <th>Modified</th>
            </tr>
        </thead>
        ";

        $formActionProduct = "index.php?module=labsg_purchaseOrder&_spAction=AddMultipleLineItem&purchase_order_id={$purchase_order_id}&supplier_id={$supplier_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddProduct' href='{$formActionProduct}' purchase_order_id={$purchase_order_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper labsg_purchaseOrder__labsg_po_productLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Product Linked</div>
                    <div class='txtRight'>
                        <span class='count'>({$recCount})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='renewallist'>
                        {$header}
                        <tbody id='AddProductPortal'>
                            {$Product}
                        </tbody>
                    </table>
                    <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
                </form>
            </div>
            {$add}
        </div>
        ";

        return $text;

    }
    /**
     *
     */
    function getAddProductDetail($purchase_order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');


        if($purchase_order_id == ''){
            $purchase_order_id = $fn->getReqParam('purchase_order_id');
        }

        $po_product_id = $fn->getReqParam('po_product_id');

        $rows  = "";

        $SQL="
        SELECT pop.*
              ,p.title AS product
        FROM po_product pop
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        LEFT JOIN (company com) ON (pop.supplier_id = com.company_id)
        WHERE purchase_order_id = '{$purchase_order_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $qty_balance = '';
        while ($row = $db->sql_fetchrow($result)) {

            $creation                = $row['created_by'].' '.$row['creation_date'];
            $modification            = $row['modified_by'].' '.$row['modification_date'];
            $formActionEditPOProduct = "index.php?_topRm=purchaseOrder&module=labsg_purchaseOrder&_spAction=editPoProductRecordForm&po_product_id={$row['po_product_id']}&showHTML=0";
            $editPORecordLink        = "<a class='EditPoProduct' href='{$formActionEditPOProduct}' po_product_id='{$row['po_product_id']}' purchase_order_id='{$row['purchase_order_id']}'><u>Edit</u></a>";
            $viewHistoryUrl = "index.php?_topRm=inventory&module=labsg_purchaseOrder&_spAction=previousOrderForClient&po_product_id={$row['po_product_id']}&showHTML=0";
            $viewRecordHistoryLink = "
            <a href='{$viewHistoryUrl}' po_product_id='{$row['po_product_id']}' class='productViewHistory'><u>View History</u></a>";

            $SQLLastPrice = "
            SELECT  po.product_id
                   ,po.price
            FROM  po_product po
            WHERE po.product_id = {$row['product_id']}
            AND po.purchase_order_id < {$row['purchase_order_id']}
            ORDER BY po.product_id DESC
            LIMIT 0,1
            ";

            $resultLastPrice = $db->sql_query($SQLLastPrice);
            $rowLastPrice    = $db->sql_fetchrow($resultLastPrice);

            $qty_balance = $row['qty'] - $row['qty_delivered'];

            $rows .= "
                <tr>
                    <td>{$row['product']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['qty']}</td>
                    <td>{$row['qty_delivered']}</td>
                    <td>{$rowLastPrice['price']}</td>
                    <td>{$qty_balance}</td>
                    <td>{$row['status']}</td>
                    <td>{$editPORecordLink}</td>
                    <td>{$viewRecordHistoryLink}</td>
                    <td>{$creation}</td>
                    <td>{$modification}</td>
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal' colspan='9'><font>No Records Linked</font></td>
                </tr>
            ";

        }
        $text="{$rows}";

        return $text;
    }

    /**
     *
     */
    function getEditPoProductRecordForm() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $po_product_id = $fn->getReqParam('po_product_id');
        $po_productRec = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
        $formAction = "index.php?_topRm=purchaseOrder&module=labsg_purchaseOrder&_spAction=editPoProductRecordSubmit&showHTML=0";

        $text = "
        <form id='EditPoProductForm' class='EditPoProductForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Qty Delivered', 'qty_delivered', $po_productRec['qty_delivered'])}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.labsg.purchaseOrder.statusprodArr'], $po_productRec['status'])}
            <input type='hidden' name='po_product_id' value='{$po_product_id}' />
        </form>
        ";

        return $text;
    }
    /**
     *
     */
    function getAddMultipleLineItem() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $supplier_id       = $fn->getReqParam('supplier_id');

        $sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";

        $product    = "
        <select name='product_id[]' class='poProduct'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlproduct)}
        </select>
        ";

        $sqlSupplier = "
        SELECT company_id
             , company_name AS supplier_name
        FROM company
        WHERE category = 'Supplier'
        ORDER BY company_name
        ";

        $sqlSupplier = $fn->getDDSql('labsg_company');

        $Supplier    = "
        <select name='company_id_supplier[]' class='poProduct'>
            <option value=''>Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";

        $status = $fn->getReqParam('status');

        $status    = "
            <select name='prod_status[]'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.labsg.purchaseOrder.statusprodArr'], $status)}
            </select>
        ";

        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price           = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]'>";
        $qty             = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$LastQuotedPrice}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$LastQuotedPrice}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$LastQuotedPrice}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$LastQuotedPrice}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$LastQuotedPrice}</td>
        </tr>
        ";

        $newRow = "
        <a href='#' class='addSinglePoRow button mb10'>Add Item</a>
        ";

        $expEdit = array('isEditable' => 0);

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Product</th>
            <th class='txtCenter'>Cost Price</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Last Quoted Price</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=labsg_purchaseOrder&_spAction=AddMultipleLineItemSubmit&showHTML=0";

        $text = "
        <form id='addMultipleLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
            <input type='hidden' name='supplier_id' value='{$supplier_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleLineItem() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";

        $product    = "
        <select name='product_id[]' class='poProduct'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlproduct)}
        </select>
        ";

        $price         = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]'>";
        $qty           = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
        </tr>
        ";

        return $rows;
    }
    /**
     *
     */

    function getProduct() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        //echo "Testing";
        $purchase_order_id  = $fn->getReqParam('purchase_order_id');

        $formAction = "index.php?_topRm=order&module=labsg_purchaseOrder&_spAction=ProductFormSubmit&showHTML=0";

        $sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";


        $sqlCategory = '';

        $text = "
        <form id='addMultipleLineItemForm' class='yform columnar addLineItem' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Product', 'product_id', $sqlproduct,'')}
            {$formObj->getTBRow('Cost Price', 'price')}
            {$formObj->getTBRow('Quantity', 'qty')}
            {$formObj->getTBRow('Qty Delivered', 'qty_delivered')}
            {$formObj->getTBRow('Status', 'status')}
            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
        </form>
        ";
        return $text;
    }

    /**
     *
     */
    function getPreviousOrderForClient() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $po_product_id    = $fn->getReqParam('po_product_id');
        $poProductRec     = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
        $purchaseOrderRec = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $poProductRec['purchase_order_id']);

        $rows = '';
        $errorText = '';

        $sqlClient = "
        SELECT po.po_code
              ,po.purchase_order_id
              ,m.title
              ,pop.price
              ,DATE_FORMAT(po.purchase_order_date, '%d-%m-%Y') AS purchase_order_date
              ,pop.qty
        FROM po_product pop
        LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN (medical_supplier m) ON (m.medical_supplier_id = po.company_id_supplier)
        WHERE po.company_id_supplier = {$purchaseOrderRec['company_id_supplier']}
          AND pop.product_id = {$poProductRec['product_id']}
          AND pop.purchase_order_id != {$poProductRec['purchase_order_id']}
        ORDER BY pop.po_product_id DESC
        LIMIT 0, 5
        ";

        $result     = $db->sql_query($sqlClient);
        $numRows    = $db->sql_numrows($result);

        if ($numRows == 0) {
            $clientRows =  "
            <div class='header mt10' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left' >Sales History from This Client</div>
                </div>
            </div>
            <table class='thinlist'>
                <td>Sorry, no previous Sales History records for this client</td>
            </table>";
        }
        else{
            while ($row = $db->sql_fetchrow($result)) {
                $purchase_order = "<a target='_blank' href='index.php?_topRm=inventory&module=labsg_purchaseOrder&purchase_order_id={$row['purchase_order_id']}&_action=edit'><u>{$row['po_code']}</u></a>";
                $rows .= "
                <tr>
                    <td>{$purchase_order}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['purchase_order_date']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['qty']}</td>
                </tr>
                ";
                $companyName_client = $row['title'];
            }

            $clientRows = "
            <div class='header mt10' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left' >Sales History from {$companyName_client}</div>
                </div>
            </div>
            <table class='thinlist'>
                <thead>
                    <th>PO Code</th>
                    <th>Supplier Name</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Qty</th>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>
            ";
        }


        $sqlOtherClient = "
        SELECT po.po_code
              ,po.purchase_order_id
              ,m.title
              ,pop.price
              ,DATE_FORMAT(po.purchase_order_date, '%d-%m-%Y') AS purchase_order_date
              ,pop.qty
        FROM po_product pop
        LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN (medical_supplier m) ON (m.medical_supplier_id = po.company_id_supplier)
        WHERE po.company_id_supplier != {$purchaseOrderRec['company_id_supplier']}
          AND pop.product_id = {$poProductRec['product_id']}
          AND pop.purchase_order_id != {$poProductRec['purchase_order_id']}
        ORDER BY pop.po_product_id DESC
        LIMIT 0, 5
        ";

        $resultOtherClient     = $db->sql_query($sqlOtherClient);
        $numRowsOtherClient    = $db->sql_numrows($resultOtherClient);

        if ($numRowsOtherClient == 0) {
            $otherClientRows =  "
            <div class='header mt20' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left'>Sales History from Other Clients</div>
                </div>
            </div>
            <table class='thinlist'>
                <td>Sorry, no previous Sales History records for Other clients</td>
            </table>";
        }
        else{
            $otherRows ='';
            while ($row = $db->sql_fetchrow($resultOtherClient)) {
                $purchase_order = "<a target='_blank' href='index.php?_topRm=inventory&module=labsg_purchaseOrder&purchase_order_id={$row['purchase_order_id']}&_action=edit'><u>{$row['po_code']}</u></a>";
                $otherRows .= "
                <tr>
                    <td>{$purchase_order}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['purchase_order_date']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['qty']}</td>
                </tr>
                ";
            }

            $otherClientRows = "
            <div class='header mt20' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left'>Sales History from Other Clients</div>
                </div>
            </div>
            <table class='thinlist'>
                <thead>
                    <th>PO Code</th>
                    <th>Supplier Name</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Qty</th>
                </thead>

                <tbody>
                    {$otherRows}
                </tbody>
            </table>
            ";
        }

        $text ="
        {$clientRows}
        {$otherClientRows}
        ";

        return $text;
    }

    /**
     *
     */
    function getLastQuotedPrice(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $product_id        = $fn->getReqParam('product_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $json  = array();

        if($product_id == ""){
            return json_encode($json);
        }


        $SQL    = "
        SELECT  po.product_id
               ,po.price
        FROM  po_product po
        WHERE po.product_id = {$product_id}
        AND po.purchase_order_id < {$purchase_order_id}
        ORDER BY po.product_id DESC
        LIMIT 0,1
        ";

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        $json[] = $row['price'];

        return json_encode($json);
    }


}