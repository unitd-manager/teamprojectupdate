<?
class CP_Admin_Modules_Hms_PurchaseOrder_View extends CP_Common_Lib_ModuleViewAbstract
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
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $text = '';
        $rows = '';
        $count = 0;
        $totalCost = 0;
        foreach ($dataArray as $row){

            $SQLTotal = "
                SELECT SUM(round(
                (pop.qty_requested * pp.price),2)) AS total_cost
                FROM po_product pop 
                LEFT JOIN product_price pp ON (pp.product_id = pop.product_id)
                WHERE pop.purchase_order_id = {$row['purchase_order_id']}
                AND pp.site_id = {$cpSiteIdSession}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);
            $totalCost = number_format($rowTotal['total_cost'], 2);

            /* Updation of Purchase order Code */
            $SQLCheck = "
            SELECT po_code
            FROM purchase_order
            WHERE po_code = '{$row['po_code']}'
            ";
            $resultCheck = $db->sql_query($SQLCheck);
            $numRowsCheck = $db->sql_numrows($resultCheck);

            if($numRowsCheck >= 2){
                $poCode = $fn->getSettingsValueByKey("nextPurchaseOrderCode");
                $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;
                
                $SQLUpdatePoCode = "
                UPDATE purchase_order SET po_code = '{$POCode}'
                WHERE purchase_order_id = {$row['purchase_order_id']}
                ";
                $resultUpdatePoCode = $db->sql_query($SQLUpdatePoCode);

                $SQLPoCode = "
                UPDATE setting SET value = (value+1) 
                WHERE key_text = 'nextPurchaseOrderCode'
                AND site_id = {$cpSiteIdSession}
                ";
                $resultPoCode = $db->sql_query($SQLPoCode);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['po_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($totalCost, 'right')}
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

        $sqlSupplier = $fn->getDDSql('hms_medicalSupplier');
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
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $expNoEdit = array('isEditable' => 0);

        $expContact = array('detailValue' => $row['contact_name_supplier']);
        $modContact = getCPModuleObj('trading_contact');

        $expCompany = array('sqlType' => 'OneField');

        $expVl = array('sqlType' => 'OneField');
        $sqlPriority = $fn->getValuelistSql('quotePriority');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $statusArr = $cpCfg['m.hms.purchaseOrder.statusArr'];
        if($row['status'] == 'confirmed'){       //if po confirmed, remove option 'new'
            unset($statusArr[array_search('new', $statusArr)]);
        }

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $expStaff   = array('detailValue' => $row['staff_name']);

        $actionButtons = '';
        //$Patient_visit_link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$row['patient_visit_id']}";

        $urlPOtoExcel = "index.php?module=hms_purchaseOrder&_spAction=printPOtoExcel&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $actionButtons .="
        <div class='float_left button mb5'>
            <a href='{$urlPOtoExcel}' id='printExcel'>Export to Excel</a>
        </div>
        ";

        $urlPOtoPDF = "index.php?module=hms_purchaseOrder&_spAction=printPOtoPDF&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $urlPOwiwthpricetoPDF = "index.php?module=hms_purchaseOrder&_spAction=printPOwithpricetoPDF&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $actionButtons .="
        <div class='float_left button mb5'>
            <a href='{$urlPOtoPDF}' target='blank' id='printPDF'>Purchase Order</a>
        </div>
        <div class='float_left button mb5'>
            <a href='{$urlPOwiwthpricetoPDF}' target='blank' id='printPDF'>Purchase Order with price</a>
        </div>
        ";

        $print ="
        <div class='floatbox actionBtnsDetail'>
            <div class='purchaseOrderRightpanelButtons floatbox'>
                {$actionButtons}
            </div>
        </div>
        ";

        $supplierRow = $fn->getRecordRowByID('medical_supplier', 'medical_supplier_id', $row['company_id_supplier']);

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
                                <td>{$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}</td>
                                <td>{$formObj->getTARow('Notes to Supplier', 'notes', $row['notes'])}</td>
                                <td>{$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'])}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        ";

        //<td>{$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager,$row['staff_id'], $expStaff)}</td>
        //<td>{$formObj->getDDRowBySQL('Currency', 'buy_currency', $sqlCurrency, $row['buy_currency'], $expVl)}</td>
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
        $comment = getCPPluginObj('common_comment');
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
            <div id='productLinkPortal'>{$this->getAddProduct($row['purchase_order_id'], $row['company_id_supplier'])}</div>
            ";
        }

        $text .="
        {$comment->getView(array(
             'roomName' => 'hms_purchaseOrder'
            ,'recordId' => $record_id
        ))}
        {$media->getRightPanelMediaDisplay('Picture', 'hms_purchaseOrder', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_purchaseOrder', 'attachment', $row)}
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

        $sqlSupplier = "
        SELECT c.medical_supplier_id
              ,c.title
        FROM medical_supplier c
        ORDER BY c.title
        ";

        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $company_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.hms.purchaseOrder.statusArr'], $status)}
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
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

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
        <th class='click-all-top'>
                <a href='#' class='check-all'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'>
                </a>
                <a href='#' class='uncheck-all'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'>
                </a>
            </th>
            <th>S.No</th>
            <th>Product Code</th>
            <th>Product Title</th>
            <th>Cost Price</th>
            <th>Last Quoted Price</th>
            <th>Stock</th>
            <th>Quantity</th>
            <th>Qty Delivered</th>
            <th>Damaged Qty</th>
            <th>Qty Balance</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Actual Total Amount</th>   
            <th>Edit</th>
            <th>History</th>
            <th>Creation</th>
            <th>Modified</th>
            <th>Delete</th>
        </thead>
        ";

        $formActionProduct = "index.php?module=hms_purchaseOrder&_spAction=AddMultipleLineItem&purchase_order_id={$purchase_order_id}&supplier_id={$supplier_id}&showHTML=0";

        $add = "<div class='float_left'>
                    <a class='btn btn-info' id='AddProduct' href='{$formActionProduct}' supplier_id='{$supplier_id}' purchase_order_id='{$purchase_order_id}'>Add Product</a>
                </div>";

        $allQtyDelivered = "<a class='btn btn-info qtyAllDelivered' purchase_order_id='{$purchase_order_id}'>Make All Qty Deliverd</a>";


        $SQLGrandTotal = "
        SELECT  SUM(pop.qty_requested * pp.price) AS Grand_Total
               ,SUM(pop.qty * pp.price) AS Grand_Total_Delivered
        FROM po_product pop
        LEFT JOIN product_price pp ON (pp.product_id = pop.product_id)
        WHERE pop.purchase_order_id = '{$purchase_order_id}'
        AND pp.site_id = {$cpSiteIdSession}
        ";
        $resultGrandTotal = $db->sql_query($SQLGrandTotal);
        $rowGrandTotal    = $db->sql_fetchrow($resultGrandTotal);

        $Grand_Total = number_format($rowGrandTotal['Grand_Total'], 2);
        $Grand_Total_Delivered = number_format($rowGrandTotal['Grand_Total_Delivered'], 2);

        $text = "
        <div class='linkPortalWrapper hms_purchaseOrder__hms_po_productLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Product Linked</div>
                    <div class='txtRight'>
                        <div class='toggle float_right'></div>
                        <span class='count float_right'>({$recCount})</span>
                        <div class='float_right grandTotalPurchasePo'>
                            Grand Total: {$Grand_Total}
                        </div>
                        <div class='float_right grandTotalPurchasePo'>
                            Grand Total(For Delivered Qty): {$Grand_Total_Delivered}
                        </div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <div class='header'>
                    <div class='floatbox'>
                        {$add}
                        <div class='float_left'>
                            {$allQtyDelivered}
                        </div>
                        <div class='float_left'>
                            <input class='productSearchAuto' rel='pptxt: Search Product' type='text' value='' name='Treatment Search' />
                            <a class='poProductToggleSearchiCon'></a>
                        </div>
                    </div>
                </div>
                <form class='purchaseOrderPoProduct'>
                    <table class='renewallist room-poProduct-table'>
                        {$header}
                        <tbody id='AddProductPortal'>
                            {$Product}
                        </tbody>
                    </table>
                    <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
                </form>
            </div>
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
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if($purchase_order_id == ''){
            $purchase_order_id = $fn->getReqParam('purchase_order_id');
        }

        $searchTreatment = $fn->getReqParam('searchTreatment');
        
        $sqlAppend = '';
        if($searchTreatment != ''){
            $sqlAppend = "AND (p.product_code LIKE '%{$searchTreatment}%' OR p.title LIKE '%{$searchTreatment}%')";
        }

        $po_product_id = $fn->getReqParam('po_product_id');

        $rows  = "";

        $SQL="
        SELECT pop.*
              ,p.title AS product
              ,p.product_code
              ,(SELECT pp.price 
                FROM product_price pp
                WHERE pp.product_id = pop.product_id
                AND site_id = {$cpSiteIdSession}) AS price
        FROM po_product pop
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        LEFT JOIN (company com) ON (pop.supplier_id = com.company_id)
        WHERE purchase_order_id = '{$purchase_order_id}'
        {$sqlAppend}
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rowpurchaseorder = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);

        $count = 1;
        $qty_balance = '';
        $totalAmount = 0;
        $actualTotalAmount = 0;
        while ($row = $db->sql_fetchrow($result)) {

            $creation                = $row['created_by'].' '.$row['creation_date'];
            $modification            = $row['modified_by'].' '.$row['modification_date'];
            $formActionEditPOProduct = "index.php?_topRm=purchaseOrder&module=hms_purchaseOrder&_spAction=editPoProductRecordForm&po_product_id={$row['po_product_id']}&showHTML=0";
            $editPORecordLink        = "<a class='EditPoProduct' href='{$formActionEditPOProduct}' po_product_id='{$row['po_product_id']}' purchase_order_id='{$row['purchase_order_id']}'>
                                            <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                                        </a>";
            $viewHistoryUrl = "index.php?_topRm=inventory&module=hms_purchaseOrder&_spAction=previousOrderForClient&po_product_id={$row['po_product_id']}&showHTML=0";
            $viewRecordHistoryLink = "
            <a href='{$viewHistoryUrl}' po_product_id='{$row['po_product_id']}' class='productViewHistory'><u>View History</u></a>";

            $deletePORecordLink      = "<div class='float_right'>
                                            <a class='deletePoProduct' href='#'  po_product_id='{$row['po_product_id']}' purchase_order_id='{$row['purchase_order_id']}'>
                                                <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                                            </a>
                                        </div>
                                        ";

            if($rowpurchaseorder['status'] == 'closed'){
                $editPORecordLink  = '';
                $deletePORecordLink = '';
            }

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

            $qty_balance = $row['qty_requested'] - $row['qty'] - $row['damaged_qty'];

            $SQLStockTransfer = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$cpSiteIdSession}
            AND st.stock_deducted = 1
            ";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);

            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$cpSiteIdSession}
            AND st.stock_deducted = 1
            ";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

            $SQLOthersite = "
            SELECT
                (SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$cpSiteIdSession}) as product_qty_purchased

               ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$row['product_id']}
                  AND o.site_id = {$cpSiteIdSession}
                ) as product_qty_sold_from_quote

                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$row['product_id']}
                AND inv.site_id = {$cpSiteIdSession}
                ) as sales_return_qty

                ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                  LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                  WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$cpSiteIdSession}
                 ) as damaged_qty
            ";
            $resultothersite = $db->sql_query($SQLOthersite);
            $rowothersite = $db->sql_fetchrow($resultothersite);

            $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
            
            $stockDetail = '';
            if($stock != '' && $stock > 0){
                $stockDetailLink = "index.php?_topRm=purchaseOrder&module=hms_purchaseOrder&_spAction=stockDisplayByLocation&product_id={$row['product_id']}&showHTML=0";
                $stockDetail = "{$stock}-<a href='{$stockDetailLink}' class='stockDetailLocation'><u>View</u></a>";
            }

            $inputRow = "<input class='poProductId' type='checkbox' name='poProductId[]' value='{$row['po_product_id']}'>";

            $totalAmount = $row['qty_requested'] * $row['price'];
            $actualTotalAmount = $row['qty'] * $row['price'];

            $totalAmountFormatted       = number_format($totalAmount, 2);
            $actualTotalAmountFormatted = number_format($actualTotalAmount, 2);
            
            $rows .= "
                <tr poRowProduct[] = {$row['po_product_id']}>
                    <td>{$inputRow}</td>
                    <td>{$count}</td>
                    <td>PROD - {$row['product_code']}</td>
                    <td>{$row['product']}</td>
                    <td>{$row['price']}</td>
                    <td>{$rowLastPrice['price']}</td>
                    <td>{$stockDetail}</td>
                    <td>{$row['qty_requested']}</td>
                    <td>{$row['qty']}</td>
                    <td>{$row['damaged_qty']}</td>
                    <td>{$qty_balance}</td>
                    <td>{$row['status']}</td>
                    <td>{$totalAmountFormatted}</td>
                    <td>{$actualTotalAmountFormatted}</td>
                    <td>{$editPORecordLink}</td>   
                    <td>{$viewRecordHistoryLink}</td>   
                    <td>{$creation}</td>  
                    <td>{$modification}</td>  
                    <td>{$deletePORecordLink}</td>                               
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal' colspan='11'><font>No Records Linked</font></td>
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

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $po_product_id   = $fn->getReqParam('po_product_id');
        $po_productRec   = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
        $formAction = "index.php?_topRm=purchaseOrder&module=hms_purchaseOrder&_spAction=editPoProductRecordSubmit&showHTML=0";
        
        $SQLProdPrice = "
        SELECT pp.price 
        FROM product_price pp
        WHERE pp.product_id = {$po_productRec['product_id']}
        AND site_id = {$cpSiteIdSession}
        ";
        $resultProdPrice = $db->sql_query($SQLProdPrice);
        $rowProdPrice    = $db->sql_fetchrow($resultProdPrice);

        $text = "
        <form id='EditPoProductForm' class='EditPoProductForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Qty', 'qty', $po_productRec['qty_requested'])}
            {$formObj->getTBRow('Qty Delivered', 'qty_delivered', $po_productRec['qty'])}
            {$formObj->getTBRow('Damaged Qty', 'damaged_qty', $po_productRec['damaged_qty'])}
            {$formObj->getTBRow('Price', 'price', $rowProdPrice['price'])}
            {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.hms.purchaseOrder.statusArr'], $po_productRec['status'])}
            <input type='hidden' name='po_product_id' value='{$po_product_id}' />
            <input type='hidden' name='product_id' value='{$po_productRec['product_id']}' />
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

        /*$sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";

        $product    = "
        <select name='product_id[]' class='poProduct'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlproduct)}
        </select>
        ";*/

        /*$sqlSupplier = "
        SELECT company_id
             , company_name AS supplier_name
        FROM company
        WHERE category = 'Supplier'
        ORDER BY company_name
        ";

        $sqlSupplier = $fn->getDDSql('hms_company');

        $Supplier    = "
        <select name='company_id_supplier[]' class='poProduct'>
            <option value=''>Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";*/

        $status = $fn->getReqParam('status');

        $status    = "
            <select name='prod_status[]'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.hms.purchaseOrder.statusprodArr'], $status)}
            </select>
        ";

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price           = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]'>";
        $qty             = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        
        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$qty}</td>
            <td>{$price}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$qty}</td>
            <td>{$price}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$qty}</td>
            <td>{$price}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$qty}</td>
            <td>{$price}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$qty}</td>
            <td>{$price}</td>
            <td>{$clear}</td>
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
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Cost Price</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=hms_purchaseOrder&_spAction=AddMultipleLineItemSubmit&showHTML=0";

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

        /*$sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";

        $product    = "
        <select name='product_id[]' class='poProduct'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlproduct)}
        </select>
        ";*/

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' value=''>
        ";
        $price   = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]'>";
        $qty     = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $clear   = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$qty}</td>
            <td>{$price}</td>
            <td>{$clear}</td>
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

        $formAction = "index.php?_topRm=order&module=hms_purchaseOrder&_spAction=ProductFormSubmit&showHTML=0";

        $sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";


        $sqlCategory = '';

        $text = "
        <form id='addMultipleLineItemForm' class='yform columnar addLineItem' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Product', 'product_id', $sqlproduct,'')}
            {$formObj->getTBRow('Quantity', 'qty')}
            {$formObj->getTBRow('Qty Delivered', 'qty_delivered')}
            {$formObj->getTBRow('Cost Price', 'price')}
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
        LIMIT 0, 10
        ";

        $result     = $db->sql_query($sqlClient);
        $numRows    = $db->sql_numrows($result);

        if ($numRows == 0) {
            $clientRows =  "
            <div class='header mt10' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left' >Purchase History from This Suppliers</div>
                </div>
            </div>
            <table class='thinlist'>
                <td>Sorry, no previous Purchase History records for this Suppliers</td>
            </table>";
        }
        else{
            while ($row = $db->sql_fetchrow($result)) {
                $purchase_order = "<a target='_blank' href='index.php?_topRm=inventory&module=hms_purchaseOrder&purchase_order_id={$row['purchase_order_id']}&_action=edit'><u>{$row['po_code']}</u></a>";
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
                    <div class='float_left' >Purchase History from {$companyName_client}</div>
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
        LIMIT 0, 10
        ";

        $resultOtherClient     = $db->sql_query($sqlOtherClient);
        $numRowsOtherClient    = $db->sql_numrows($resultOtherClient);

        if ($numRowsOtherClient == 0) {
            $otherClientRows =  "
            <div class='header mt20' expanded='1'>
                <div class='floatboxQuoteHistory'>
                    <div class='float_left'>Purchase History from Other Suppliers</div>
                </div>
            </div>
            <table class='thinlist'>
                <td>Sorry, no previous Purchase History records for Other Suppliers</td>
            </table>";
        }
        else{
            $otherRows ='';
            while ($row = $db->sql_fetchrow($resultOtherClient)) {
                $purchase_order = "<a target='_blank' href='index.php?_topRm=inventory&module=hms_purchaseOrder&purchase_order_id={$row['purchase_order_id']}&_action=edit'><u>{$row['po_code']}</u></a>";
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
                    <div class='float_left'>Purchase History from Other Suppliers</div>
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
    function getStockDisplayByLocation(){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $product_id = $fn->getReqParam('product_id');
        
        $text = '';

        $SQLsitedetail="
        SELECT site_id
               ,title
        FROM site
        ";
        $resultsitedetail = $db->sql_query($SQLsitedetail);

        $stockDetailsRow     = '';
        $total_available_qty = '';
        $total_sold_qty      = '';
        $total_damaged_qty   = '';
        $total_sales_qty     = '';
        $total_purchased_qty = '';
        while($rowsitedetail = $db->sql_fetchrow($resultsitedetail)) {

            $SQLStockTransfer = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$product_id} AND st.from_location = {$rowsitedetail['site_id']}";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$product_id} AND st.to_location = {$rowsitedetail['site_id']}";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

            $SQLOthersite = "
            SELECT
                (SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$product_id} AND po.site_id = {$rowsitedetail['site_id']}) as product_qty_purchased

               ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
                LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
                LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
                WHERE record_id = {$product_id}
                  AND o.site_id = {$rowsitedetail['site_id']}
                ) as product_qty_sold_from_quote

                ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
                LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
                LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
                WHERE ini.record_id = {$product_id}
                AND inv.site_id = {$rowsitedetail['site_id']}
                ) as sales_return_qty

                ,(SELECT SUM(pp.damaged_qty) FROM po_product pp
                  LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                  WHERE pp.product_id = {$product_id} AND po.site_id = {$rowsitedetail['site_id']}
                 ) as damaged_qty
            ";

            $resultothersite = $db->sql_query($SQLOthersite);

            while ($rowothersite = $db->sql_fetchrow($resultothersite)){
                    if ($rowsitedetail['site_id'] == $cpSiteIdSession && $rowStockTransfer['from_location'] == $cpSiteIdSession){
                        $totalqty = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                    }
                    else if ($rowsitedetail['site_id'] != $cpSiteIdSession && $rowStockTransfer['to_location'] == $cpSiteIdSession){
                        $totalqty = $rowothersite['product_qty_purchased']  - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                    }
                    else {
                       $totalqty = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'];
                    }

                $soldQty = $rowothersite['product_qty_sold_from_quote'] - $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'];

                $stockDetailsRow .= "
                    <tr>
                        <td>{$rowsitedetail['title']}</td>
                        <td>{$rowothersite['product_qty_purchased']}</td>
                        <td>{$soldQty}</td>
                        <td>{$rowothersite['damaged_qty']}</td>
                        <td>{$rowothersite['sales_return_qty']}</td>
                        <td>{$totalqty}</td>
                    </tr>
                ";

                $total_available_qty += $totalqty;
                $total_purchased_qty += $rowothersite['product_qty_purchased'];
                $total_sold_qty      += $soldQty;
                $total_damaged_qty   += $rowothersite['damaged_qty'];
                $total_sales_qty     += $rowothersite['sales_return_qty'];

            }

        }

        $text ="
         <table class='thinlist'>
            <thead>
                <tr>
                    <th>Location Name</th>
                    <th>Total Purchased Qty</th>
                    <th>Total Sold Qty</th>
                    <th>Total Damaged Qty</th>
                    <th>Total Sales Return Qty</th>
                    <th>Total Available Qty</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    {$stockDetailsRow}
                </tr>
                <tr bgcolor='#EEEEEE'>
                    <th class='txtRight'>Total</th>
                    <th>{$total_purchased_qty}</th>
                    <th>{$total_sold_qty}</th>
                    <th>{$total_damaged_qty}</th>
                    <th>{$total_sales_qty}</th>
                    <th>{$total_available_qty}</th>
                </tr>
            </tbody>
        </table>
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