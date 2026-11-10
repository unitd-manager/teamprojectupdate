<?
class CPL_Admin_Modules_Tradingsg_PurchaseOrder_View extends CP_Common_Lib_ModuleViewAbstract
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
        $totalCost = 0;
        foreach ($dataArray as $row){
            $SQLTotal = "
            SELECT SUM(pop.qty * pop.cost_price) AS total_cost
                  ,SUM(((pop.qty * pop.cost_price) * pop.gst) / 100) AS GST_Total
            FROM po_product pop WHERE pop.purchase_order_id = {$row['purchase_order_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);
            $totalCost = number_format($rowTotal['total_cost']+$rowTotal['GST_Total'], 2);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getListDataCell($row['po_code'])}
               {$listObj->getListDataCell($row['notes'])}
            {$listObj->getListDataCell($row['title'])}
             {$listObj->getListDataCell($row['quote_code'])}
            {$listObj->getListDataCell($totalCost, 'Right')}
             {$listObj->getListDataCell($row['supplier_inv_code'])}
              {$listObj->getListDataCell($row['supplier_inv'])}
            {$listObj->getListDataCell($row['payment_status'])}
            {$listObj->getListDateCell($row['purchase_order_date'])}
           
            {$listObj->getListRowEnd($row['purchase_order_id'])}
            ";
            $count++ ;
        }

        $rows = $listObj->getDisplayListRows($rows);

        $formActionProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddMultipleLineItemList&showHTML=0";

        $addExistingProduct = "<div class='float_left'>
                    <a class='btn btn-warning' id='AddProductList' href='{$formActionProduct}'>Add Existing Product</a>
                </div>";

        $formActionNewProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddNewProductList&showHTML=0";

        $addNewProduct = "<div class='float_left'>
                    <a class='btn btn-primary' id='AddNewProductList' href='{$formActionNewProduct}'>Add New Products</a>
                </div>";

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('PO Code', 'po.po_code')}
        {$listObj->getListHeaderCell('Project Ref.', 'po.notes')}
        {$listObj->getListHeaderCell('Client Name', 'po.title')}
         {$listObj->getListHeaderCell('Quote Code', 'po.quote_code')}
        {$listObj->getListHeaderCell('PO Value', 'amount')}
         {$listObj->getListHeaderCell('Invoice Value', 'po.supplier_inv_code')}
          {$listObj->getListHeaderCell('Invoice', 'po.supplier_inv')}
        {$listObj->getListHeaderCell('Status', 'payment_status')}
        {$listObj->getListHeaderCell('PO Creation Date', 'po.purchase_order_date')}
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

        $newSupplierUrl = "index.php?module=tradingsg_purchaseOrder&_spAction=newSupplier&showHTML=0";
        //$newSupplierUrl = 'index.php?_spAction=newSupplier&lnkRoom=enggCrm_supplier&showHTML=0';

        $newSupplierUrl = "<a class='jqui-dialog-form float_left' formId='portalForm' title='New Supplier' 
            w=600 h=560 href='' link='{$newSupplierUrl}' callback='cpm.tradingsg.purchaseOrder.afterNewSupplier'>New</a>";

        $sqlSupplier = $fn->getDDSql('tradingsg_supplier');
        $expSupplier = array(
             'hideFirstOption' => 1
            ,'notesRight'      => $newSupplierUrl
        );

        $fieldset = "
        {$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, '', $expSupplier)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Purchase Order Header', $fieldset)}
        ";
        return $text;
    }

    function getNewSupplier(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addSupplier&lnkRoom=tradingsg_supplier&showHTML=0";
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Name', 'company_name')}
                 {$formObj->getTBRow('Email', 'email')}
                {$formObj->getTBRow('Website', 'website')}
                {$formObj->getTBRow('Phone', 'phone')}
                {$formObj->getTBRow('Fax', 'fax')}
                {$formObj->getTBRow('Office Address', 'address_flat')}
                {$formObj->getTBRow('Street Address', 'address_street')}
                {$formObj->getTBRow('District/ Town', 'address_town')}
                {$formObj->getTBRow('State/ Zip', 'address_state')}
                {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry)}
            </fieldset>
            
        </form>
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

        $statusArr = $cpCfg['m.trading.purchaseOrder.statusArr'];
        if($row['status'] == 'confirmed'){       //if po confirmed, remove option 'new'
            unset($statusArr[array_search('new', $statusArr)]);
        }

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $expStaff   = array('detailValue' => $row['staff_name']);

        $actionButtons = '';
        //$Patient_visit_link = "index.php?_topRm=main&module=tradingsg_patientVisit&_action=edit&patient_visit_id={$row['patient_visit_id']}";

        $urlPOtoExcel = "index.php?module=tradingsg_purchaseOrder&_spAction=printPOtoExcel&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $actionButtons .="
        <div class='float_left btn btn-primary mb5'>
            <a href='{$urlPOtoExcel}' id='printExcel'>Export to Excel</a>
        </div>
        ";

        $urlPOtoPDF = "index.php?module=tradingsg_purchaseOrder&_spAction=printPOtoPDF&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $urlPOwiwthpricetoPDF = "index.php?module=tradingsg_purchaseOrder&_spAction=printPOwithpricetoPDF&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
        $actionButtons .="
        
        <div class='float_left btn btn-info mb5'>
            <a href='{$urlPOwiwthpricetoPDF}' target='blank' id='printPDF'>Purchase Order with price PDF</a>
        </div>
    
        ";

        $print ="
        <div class='floatbox actionBtnsDetail'>
            <div class='purchaseOrderRightpanelButtons floatbox'>
                {$actionButtons}
            </div>
        </div>
        ";

        $newSupplierUrl = 'index.php?_spAction=newSupplier&lnkRoom=enggCrm_supplier&showHTML=0';
        $newSupplierUrl = "<a class='jqui-dialog-form float_left' formId='portalForm' title='New Supplier' 
            w=600 h=560 href='' link='{$newSupplierUrl}' callback='cpm.tradingsg.purchaseOrder.afterNewSupplier'>New</a>";

        $sqlSupplier = $fn->getDDSql('tradingsg_supplier');
        $expSupplier = array(
             'hideFirstOption' => 1
            ,'notesRight'      => $newSupplierUrl
        );

        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');

        $paymentstatusArr = array(
              "Due"
             ,"Paid"
             ,"Partially Paid"
             ,"Cancelled"
        );

        $text = "
        {$print}
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Purchase Order Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
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
                                <td>{$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, $row['company_id_supplier'], $expSupplier)}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getDDRowBySQL('Priority', 'priority', $sqlPriority, $row['priority'], $expVl)}</td>
                                <td>{$formObj->getDateRow('PO Date', 'purchase_order_date', $row['purchase_order_date'])}</td>
                                <td>{$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}</td>
                                <td>{$formObj->getTARow('Project Reference', 'notes', $row['notes'])}</td>
                            </tr>
                            <tr>
                                <td>{$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'])}</td>
                                <td>{$formObj->getDDRowByArr('Payment Status', 'payment_status', $paymentstatusArr, $row['payment_status'])}</td>
                               <td>{$formObj->getTBRow('Invoice', 'supplier_inv', $row['supplier_inv'])}</td>
                               <td>{$formObj->getTBRow('Invoice Value', 'supplier_inv_code', $row['supplier_inv_code'])}</td>
                            </tr>
                            <tr>
                              <td>{$formObj->getTBRow('Quote Code', 'quote_code', $row['quote_code'])}</td>
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
             'roomName' => 'tradingsg_purchaseOrder'
            ,'recordId' => $record_id
        ))}
        {$media->getRightPanelMediaDisplay('Picture', 'tradingsg_purchaseOrder', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_purchaseOrder', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getDeliveryOrderPortal($purchase_order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        if($purchase_order_id == ''){
            $purchase_order_id = $fn->getReqParam('purchase_order_id');
        }

        $po_product_id = $fn->getReqParam('po_product_id');

        $rows  = "";

        $SQL="
        SELECT do.*
        FROM delivery_order do
        WHERE purchase_order_id = '{$purchase_order_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');
            $urlPrintLinkPdf  = "index.php?_topRm=project&module=tradingsg_purchaseOrder&_spAction=printDeliveryOrderPdf&purchase_order_id={$purchase_order_id}&delivery_order_id={$row['delivery_order_id']}&showHTML=0";
            $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
            $edit_image = $cpCfg['cp.localPath']."images/edit.png";

            $editUrl = "index.php?_topRm=finance&module=tradingsg_purchaseOrder&_spAction=editDeliveryOrder&delivery_order_id={$row['delivery_order_id']}&showHTML=0";
            $editDO = "
            <a href='{$editUrl}' delivery_order_id='{$row['delivery_order_id']}' class='deliveryOrderEdit' title='Edit Delivery Order'><img src='{$edit_image}' class='icon'></a>";

            $rows .= "
            <tr>
                <td>{$date}</td>
                <td>
                    <div class='float_box clearfix'>
                        <div class='float_left'>
                            {$editDO}
                        </div>
                        <div class='float_left'>
                            <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Delivery Order'><img src='{$print_image}' class='icon'></a>
                        </div>
                    </div>
                </td>
            </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal' colspan='2'><font>No Records Linked</font></td>
                </tr>
            ";

        }

        $text = "
        <div class='linkPortalWrapper tradingsg_purchaseOrder__tradingsg_po_productLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Delivery Order</div>
                    <div class='txtRight'>
                        <span class='count'>({$numRows})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form class='deliveryOrderPoPortal'>
                    <table class='renewallist room-poProduct-table'>
                        <thead>
                            <th>Date</th>
                            <th></th>
                        </thead>
                        <tbody id='AddProductPortal'>
                            {$rows}
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
    function getEditDeliveryOrder(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $delivery_order_id = $fn->getReqParam('delivery_order_id');

        $rows  = "";

        $statusArr = array(
            "In Progress"
           ,"Delivered"
           ,"On-hold"
           ,"Cancelled"
        );

        $SQL="
        SELECT doh.*
              ,p.title
        FROM delivery_order_history doh
        LEFT JOIN product p ON (p.product_id = doh.product_id)
        WHERE doh.delivery_order_id = '{$delivery_order_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
                <tr>
                    <td>{$row['title']}</td>
                    <td><input type='text' value='{$row['quantity']}' id='quantity' class='text lineItemQuantity' name='quantity[]'></td>
                    <td>
                        <select name='do_status[]'>
                            <option value=''>Please Select</option>
                            {$cpUtil->getDropDown1($statusArr, $row['status'])}
                        </select>
                    </td>
                    <td>
                        <textarea type='text' value='{$row['remarks']}' id='remarks' class='text lineItemDescription' name='remarks[]'>{$row['remarks']}</textarea>
                    </td>
                </tr>
                <input type='hidden' name='delivery_order_history_id[]' value='{$row['delivery_order_history_id']}' />
            ";
            $count++;
        }

        $formActionEditForDO = "index.php?module=tradingsg_purchaseOrder&_spAction=editForDOSubmit&showHTML=0";

        $text = "
        <form id='editForDO' class='yform columnar editForDO' method='post' action='{$formActionEditForDO}'>
            <table class='renewallist room-poProduct-table thinlist'>
                <thead>
                    <tr style='background-color:#EAEAE8'>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>    
                </thead>
                <tbody id='AddProductPortal'>
                    {$rows}
                </tbody>
            </table>
        </form>
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
        $supplier_id = $fn->getReqParam('supplier_id');

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );
    
     $SQLComp = "
        SELECT DISTINCT a.supplier_id
              ,a.company_name
        FROM supplier a
        ORDER BY company_name
        ";

        $text = "
         <td>
            <select name='supplier_id' class='w100'>
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $supplier_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.purchaseOrder.statusArr'], $status)}
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
      
            
            <th>S.No</th>
            <th>Item Code</th>
            <th>Item Description</th>
             <th>UOM</th>
            <th>Qty</th>
            <th class='txtRight'>Unit Price</th>
            <th class='txtRight'>Total Amount</th>
            
          
            <th>Edit</th>
            <th>Delete</th>
        </thead>
        ";

        $formActionProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddMultipleLineItem&purchase_order_id={$purchase_order_id}&supplier_id={$supplier_id}&showHTML=0";

        $add = "<div class='float_left'>
                    <a class='btn btn-info' id='AddProduct' href='{$formActionProduct}' supplier_id='{$supplier_id}' purchase_order_id='{$purchase_order_id}'>Add Product</a>
                </div>";

        $formActionNewProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddNewProduct&purchase_order_id={$purchase_order_id}&supplier_id={$supplier_id}&showHTML=0";

        $addNewProduct = "<div class='float_left'>
                    <a class='btn btn-primary' id='AddNewProduct' href='{$formActionNewProduct}' supplier_id='{$supplier_id}' purchase_order_id='{$purchase_order_id}'>Add New Products</a>
                </div>";

        $allQtyDelivered = "<a class='btn btn-warning qtyAllDeliveredPo' purchase_order_id='{$purchase_order_id}'>Add all Qty to Stock</a>";

        $deliveryOrder = "<a class='btn btn-primary deliveryOrder' purchase_order_id='{$purchase_order_id}'>Delivery Order</a>";

        $SQLGrandTotal = "
        SELECT  SUM(qty_requested * cost_price) AS Grand_Total
               ,SUM(qty * cost_price) AS Grand_Total_Delivered
        FROM po_product
        WHERE purchase_order_id = '{$purchase_order_id}'
        ";
        $resultGrandTotal = $db->sql_query($SQLGrandTotal);
        $rowGrandTotal    = $db->sql_fetchrow($resultGrandTotal);

        $Grand_Total = number_format($rowGrandTotal['Grand_Total'], 2);
        $Grand_Total_Delivered = number_format($rowGrandTotal['Grand_Total_Delivered'], 2);

        $text = "
        <div class='linkPortalWrapper tradingsg_purchaseOrder__tradingsg_po_productLink'>
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
                <div class='header'>
                    <div class='floatbox'>
                        {$add}
                       
                       
                        <div class='float_right grandTotalPurchasePo'>
                            Grand Total: {$Grand_Total_Delivered}
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

        $po_product_id = $fn->getReqParam('po_product_id');

        $rows  = "";

        $SQL="
        SELECT pop.*
              ,pop.cost_price AS price
              ,pop.selling_price
              ,p.title AS product
              ,p.item_code
              ,p.product_code
        FROM po_product pop
        LEFT JOIN (product p) ON (p.product_id = pop.product_id)
        LEFT JOIN (company com) ON (pop.supplier_id = com.company_id)
        WHERE purchase_order_id = '{$purchase_order_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $qty_balance = '';
        $totalAmount = 0;
        $actualTotalAmount = 0;

        while ($row = $db->sql_fetchrow($result)) {

        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');


            $creation                = $row['created_by'].' '.$creation_date;
            $modification            = $row['modified_by'].' '.$modification_date;
            $formActionEditPOProduct = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=editPoProductRecordForm&po_product_id={$row['po_product_id']}&showHTML=0";
            $editPORecordLink        = "<a class='EditPoProduct' href='{$formActionEditPOProduct}' po_product_id='{$row['po_product_id']}' purchase_order_id='{$row['purchase_order_id']}'>
                                            <img src='/cmspilotv30/CP/admin/images/icons/btn_edit.png'>
                                        </a>";
            $viewHistoryUrl = "index.php?_topRm=finance&module=tradingsg_purchaseOrder&_spAction=previousOrderForClient&po_product_id={$row['po_product_id']}&showHTML=0";
            $viewRecordHistoryLink = "
            <a href='{$viewHistoryUrl}' po_product_id='{$row['po_product_id']}' class='productViewHistory'><u>View History</u></a>";

            $deletePORecordLink      = "<div class='float_right'>
                                            <a class='deletePoProduct' href='#'  po_product_id='{$row['po_product_id']}' purchase_order_id='{$row['purchase_order_id']}'>
                                                <img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                                            </a>
                                        </div>
                                        ";

            $qty_balance = $row['qty_requested'] - $row['qty'];

           
            $stockDetail = '';
            $inputRow = "<input class='poProductId' type='checkbox' name='poProductId[]' value='{$row['po_product_id']}'>";
            $doInputRow = "<input class='deliveryOrderId' type='checkbox' name='deliveryOrderId[]' value='{$row['po_product_id']}'>";

            $totalAmount = $row['qty_requested'] * $row['price'];
            $actualTotalAmount = $row['qty'] * $row['price'];

            $totalAmountFormatted       = number_format($totalAmount, 2);
            $actualTotalAmountFormatted = number_format($actualTotalAmount, 2);
            $unitPrice                  = number_format($row['price'], 2);

            $gstVal = $row['price'] * $row['gst'] / 100;
            $gstVal = number_format($gstVal, 2);

            $productCodeTd = "<td>{$row['item_title']}</td>";

            $rows .= "
                <tr poRowProduct[] = {$row['po_product_id']}>
                    
                    <td>{$count}</td>
                    {$productCodeTd}
                    <td>{$row['description']}</td>
                        <td>{$row['unit']}</td>
                            <td>{$row['qty']}</td>
                    <td align='Right'>{$row['cost_price']}</td>
                    <td align='Right'>{$row['selling_price']}</td>
                 
                  
                    <td>{$editPORecordLink}</td>
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

        $po_product_id = $fn->getReqParam('po_product_id');
        $po_productRec = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);
        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=editPoProductRecordSubmit&showHTML=0";

        if($po_productRec['status'] == ''){
            $po_productRec['status'] = 'In progress';        
        }

        $expNoEdit = '';

        if($po_productRec['qty'] > 0){
            $expNoEdit = array('isEditable' => 0);
        }

        $text = "
        <form id='EditPoProductForm' class='EditPoProductForm yform columnar' method='post' action='{$formAction}'>
             {$formObj->getTBRow('Item Code', 'item_title', $po_productRec['item_title'])}
            {$formObj->getTBRow('Description', 'description', $po_productRec['description'])}
                   {$formObj->getTBRow('UOM', 'unit', $po_productRec['unit'])}
            {$formObj->getTBRow('Qty', 'qty', $po_productRec['qty'])}
                {$formObj->getTBRow('Unit Price', 'cost_price', $po_productRec['cost_price'])}
            {$formObj->getTBRow('Total Amount', 'selling_price', $po_productRec['selling_price'])}
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
        $supplierRec       = $fn->getRecordRowByID('supplier', 'supplier_id', $supplier_id);

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

        $sqlSupplier = $fn->getDDSql('tradingsg_company');

        $Supplier    = "
        <select name='company_id_supplier[]' class='poProduct'>
            <option value=''>Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";*/

        $status = $fn->getReqParam('status');

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title[]'>
        ";
        $description = "<textarea value='' id='description' class='text invoiceItemDescription' name='description[]'></textarea>";
               $unit        = "<input type='text' value='' id='unit' class='text poUnit' name='unit[]'>";

        $qty             = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $costPrice       = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]'>";

        $price           = "<input type='text' value='' id='price' class='text poSellingPrice' name='selling_price[]'>";
        $discount        = "<input type='text' value='' id='discount' class='text poDiscount' name='discount[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $inventory_code  = "<div class='inventoryCode'></div>";
        $remarks = "<textarea value='' id='remarks' class='text invoiceItemremarks' name='remarks[]'></textarea>";

        $productCodeTh = "";
        $productCodeTd = "";
        $item_code     = "<div class='itemCode'></div>";
        $productCodeTd = "<td>{$item_code}</td>";
        $productCodeTh = "<th>Item Code</th>";

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='productSize'>{$description}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='priceSize'>{$remarks}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='productSize'>{$description}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='priceSize'>{$remarks}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='productSize'>{$description}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='priceSize'>{$remarks}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='productSize'>{$description}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='priceSize'>{$remarks}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='productSize'>{$description}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='priceSize'>{$remarks}</td>
            <td>{$clear}</td>
        </tr>
        ";

        $newRow = "
        <tr><td colspan='8'><label style='font-weight: bold; font-size: 18px;'>Discount</label> <input type='text' name='discount' class='text' style='margin-left: 10px;'></td></tr>
        ";

        $formActionNewProduct = "index.php?module=tradingsg_purchaseOrder&_spAction=AddNewProductMaster&supplier_id={$supplier_id}&showHTML=0";
        
        $newProductRow = "
        <a href='{$formActionNewProduct}' class='addNewProductPopup btn btn-success mb10 mr20'>Add New Product</a>
        ";

        $expEdit = array('isEditable' => 0);

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Item Code</th>
            <th>Description</th>
            <th class='txtCenter'>UOM</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Unit Price</th>
            <th class='txtCenter'>Total Amount</th>
            <th>Remarks</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=AddMultipleLineItemSubmit&showHTML=0";

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
        $price          = "<input type='text' value='' id='price' class='text poSellingPrice' name='price[]'>";
        $qty            = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $clear          = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $inventory_code = "<div class='inventoryCode'></div>";
        $gst            = "<input type='text' value='' id='gst' class='text poGst' name='gst[]'>";
        $costPrice      = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]'>";

        $productCodeTd = "";
        $item_code     = "<div class='itemCode'></div>";
        $productCodeTd = "<td>{$item_code}</td>";
        $unit        = "<input type='text' value='' id='unit' class='text poUnit' name='unit[]'>";

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddMultipleLineItemList() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title_list[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='' id='price' class='text poSellingPrice' name='price[]' disabled>";
        $costPrice = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]' disabled>";
        $gst = "<input type='text' value='' id='gst' class='text poGst' name='gst[]' disabled>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $inventory_code = "<div class='inventoryCode'></div>";
        $item_code = "<div class='itemCode'></div>";
        $supplier = "<div class='supplier'></div>
        <input type='hidden' name='supplier_id[]' class='supplier_id_hidden' value=''>";

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        ";

        $newRow = "
        <a href='#' class='addSinglePoRowList button mb10'>Add Item</a>
        ";

        $expEdit = array('isEditable' => 0);

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Product</th>
            <th>Inventory code</th>
            <th>Item code</th>
            <th>Supplier</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Cost Price (without GST)</th>
            <th class='txtCenter'>Selling Price (without GST)</th>
            <th class='txtCenter'>GST %</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=AddMultipleLineItemListSubmit&showHTML=0";

        $text = "
        <form id='addMultipleLineItemListForm' class='addMultipleLineItemListForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='list' id=''>
            <tr>
                <td>{$newRow}</td>
                <td>Add Qty for all Products: <input type='text' value='' class='text allQty' name='qty_all'>
                <a class='btn btn-info applyQtyAll' href='#'>Apply</a></td>
                <!--<td>Search: <input type='text' value='' class='text findWordTitle' name='find'>
                <a class='btn btn-success findWord' href='#'>Find</a></td>-->

                <td><a class='btn btn-primary loadMOL' href='#'>Load products <= Mol</a></td>
                <td><a href='#' class='btn btn-danger clearAllItem'>Clear All</a></td>
            </table>
            </tr>
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleLineItemList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product_title_list[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='' id='price' class='text poSellingPrice' name='price[]' disabled>";
        $costPrice = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]' disabled>";
        $gst = "<input type='text' value='' id='gst' class='text poGst' name='gst[]' disabled>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $inventory_code = "<div class='inventoryCode'></div>";
        $item_code = "<div class='itemCode'></div>";
        $supplier = "<div class='supplier'></div>
        <input type='hidden' name='supplier_id[]' class='supplier_id_hidden' value=''>";


        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td>{$inventory_code}</td>
            <td>{$item_code}</td>
            <td>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$costPrice}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td>{$clear}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddNewProduct() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $supplier_id       = $fn->getReqParam('supplier_id');
        $status = $fn->getReqParam('status');
        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');

        $modSubCat = getCPModuleObj('webBasic_subCategory');
        $sqlSubCategory = $modSubCat->model->getSubCategorySQL('');

        $productTypeArr = array(
             "Purchasing and Selling"
            ,"Purchasing Product"
            ,"Selling Product"
        );

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product[]'>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price           = "<input type='text' value='0' id='price' class='text poSellingPrice' name='price[]'>";
        $cost_price      = "<input type='text' value='0' id='costPrice' class='text poCostPrice' name='cost_price[]'>";
        $gst             = "<input type='text' value='0' id='gst' class='text poGst' name='gst[]'>";
        $qty             = "<input type='text' value='0' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $hsn             = "<input type='text' value='' id='hsn' class='text hsn' name='hsn[]'>";
        $unit            = "{$formObj->getDDRowBySQL('', 'unit[]', $sqlUnit, '', $expVl)}";
        $category        = "{$formObj->getDDRowBySQL('', 'category[]', $sqlCategory)}";
        $subCategory     = "{$formObj->getDDRowBySQL('', 'sub_category[]', $sqlSubCategory)}";
        $type            = "{$formObj->getDDRowByArr('', 'type[]', $productTypeArr, 'Purchasing and Selling')}";
        $pack_size       = "<input type='text' value='' id='packSize' class='text packSize' name='pack_size[]'>";

        $productCodeTd = "";
        $productCodeTh = "";
        $rows = "
        <tr>
            {$productCodeTd}
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$subCategory}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$pack_size}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            {$productCodeTd}
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$subCategory}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$pack_size}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            {$productCodeTd}
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$subCategory}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$pack_size}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            {$productCodeTd}
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$subCategory}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$pack_size}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            {$productCodeTd}
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$subCategory}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$pack_size}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        ";

        $newRow = "
        <a href='#' class='addSinglePoRowNew button mb10'>Add Item</a>
        ";

        $expEdit = array('isEditable' => 0);

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            {$productCodeTh}
            <th>Product Name</th>
            <th class='txtCenter'>Category</th>
            <th class='txtCenter'>Sub Category</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Cost Price (without GST)</th>
            <th class='txtCenter'>Selling Price (without GST)</th>
            <th class='txtCenter'>GST %</th>
            <th class='txtCenter'>HSN Code</th>
            <th class='txtCenter'>Unit</th>
            <th class='txtCenter'>Pack Size</th>
            <th class='txtCenter'>Type</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=AddNewProductSubmit&showHTML=0";

        $text = "
        <form id='addNewproductForm' class='addNewproductForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', 'error_box1', '', $expEdit)}
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
    function getAddSingleLineItemNew() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $supplier_id       = $fn->getReqParam('supplier_id');
        $status = $fn->getReqParam('status');
        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');

        $modSubCat = getCPModuleObj('webBasic_subCategory');
        $sqlSubCategory = $modSubCat->model->getSubCategorySQL('');

        $productTypeArr = array(
             "Purchasing and Selling"
            ,"Purchasing Product"
            ,"Selling Product"
        );

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product[]'>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='0' id='price' class='text poSellingPrice' name='price[]'>";
        $cost_price = "<input type='text' value='0' id='costPrice' class='text poCostPrice' name='cost_price[]'>";
        $gst = "<input type='text' value='0' id='gst' class='text poGst' name='gst[]'>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear           = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $hsn = "<input type='text' value='' id='hsn' class='text hsn' name='hsn[]'>";
        $unit = "{$formObj->getDDRowBySQL('', 'unit[]', $sqlUnit, '', $expVl)}";
        $category = "{$formObj->getDDRowBySQL('', 'category[]', $sqlCategory)}";
        $subCategory = "{$formObj->getDDRowBySQL('', 'sub_category[]', $sqlSubCategory)}";
        $type = "{$formObj->getDDRowByArr('', 'type[]', $productTypeArr, 'Purchasing and Selling')}";
        $pack_size = "<input type='text' value='' id='packSize' class='text packSize' name='pack_size[]'>";

        $productCodeTd = "";

        $rows = "
        <tr>
            {$productCodeTd}
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$subCategory}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$pack_size}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddNewProductList() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');

        $productTypeArr = array(
             "Purchasing and Selling"
            ,"Purchasing Product"
            ,"Selling Product"
        );

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product[]'>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='' id='price' class='text poSellingPrice' name='price[]'>";
        $cost_price = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]'>";
        $gst = "<input type='text' value='' id='gst' class='text poGst' name='gst[]'>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $hsn = "<input type='text' value='' id='hsn' class='text hsn' name='hsn[]'>";
        $unit = "{$formObj->getDDRowBySQL('', 'unit[]', $sqlUnit, '', $expVl)}";
        $category = "{$formObj->getDDRowBySQL('', 'category[]', $sqlCategory, '')}";
        $sqlSupplier = $fn->getDDSql('tradingsg_supplier');
        $supplier = "{$formObj->getDDRowBySQL('', 'supplier_id[]', $sqlSupplier, '')}";
        $type = "{$formObj->getDDRowByArr('', 'type[]', $productTypeArr, 'Purchasing and Selling')}";

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$type}</td>
            <td>{$clear}</td>
        </tr>
        ";

        $newRow = "
        <a href='#' class='addSinglePoRowNewList button mb10'>Add Item</a>
        ";

        $expEdit = array('isEditable' => 0);

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Product</th>
            <th class='txtCenter'>Category</th>
            <th class='txtCenter'>Supplier</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Cost Price (without GST)</th>
            <th class='txtCenter'>Selling Price (without GST)</th>
            <th class='txtCenter'>GST %</th>
            <th class='txtCenter'>HSN Code</th>
            <th class='txtCenter'>Unit</th>
            <th class='txtCenter'>Type</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?_topRm=purchaseOrder&module=tradingsg_purchaseOrder&_spAction=AddNewProductListSubmit&showHTML=0";

        $text = "
        <form id='addNewproductListForm' class='addNewproductListForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='po_productTable'>
                {$header}
                {$rows}
            </table>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleLineItemNewList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');

        $productTypeArr = array(
             "Purchasing and Selling"
            ,"Purchasing Product"
            ,"Selling Product"
        );

        $product = "
        <input type='text' value='' id='poProduct' class='text poProductTitle' name='product[]'>
        ";
        $LastQuotedPrice = "<input type='text' value='' id='last_price' class='text last_price' name='last_price[]' disabled>";
        $price = "<input type='text' value='' id='price' class='text poSellingPrice' name='price[]'>";
        $cost_price = "<input type='text' value='' id='costPrice' class='text poCostPrice' name='cost_price[]'>";
        $gst = "<input type='text' value='' id='gst' class='text poGst' name='gst[]'>";
        $qty = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered   = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        $clear = "<a href='#' class='clearPoProductItem'><u>Clear</u></a>";
        $hsn = "<input type='text' value='' id='hsn' class='text hsn' name='hsn[]'>";
        $unit = "{$formObj->getDDRowBySQL('', 'unit[]', $sqlUnit, '', $expVl)}";
        $category = "{$formObj->getDDRowBySQL('', 'category[]', $sqlCategory)}";
        $sqlSupplier = $fn->getDDSql('tradingsg_supplier');
        $supplier = "{$formObj->getDDRowBySQL('', 'supplier_id[]', $sqlSupplier, '')}";
        $type = "{$formObj->getDDRowByArr('', 'type[]', $productTypeArr, 'Purchasing and Selling')}";

        $rows = "
        <tr>
            <td class='productSize'>{$product}</td>
            <td class='priceSize'>{$category}</td>
            <td class='priceSize'>{$supplier}</td>
            <td class='qtySize'>{$qty}</td>
            <td class='priceSize'>{$cost_price}</td>
            <td class='priceSize'>{$price}</td>
            <td class='qtySize'>{$gst}</td>
            <td class='qtySize'>{$hsn}</td>
            <td class='qtySize'>{$unit}</td>
            <td class='qtySize'>{$type}</td>
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

        $formAction = "index.php?_topRm=order&module=tradingsg_purchaseOrder&_spAction=ProductFormSubmit&showHTML=0";

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
              ,pop.cost_price AS price
              ,DATE_FORMAT(po.purchase_order_date, '%d-%m-%Y') AS purchase_order_date
              ,pop.qty
              ,s.company_name AS title
        FROM po_product pop
        LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN (supplier s) ON (s.supplier_id = po.company_id_supplier)
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
                $purchase_order = "<a target='_blank' href='index.php?_topRm=finance&module=tradingsg_purchaseOrder&purchase_order_id={$row['purchase_order_id']}&_action=edit'><u>{$row['po_code']}</u></a>";
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
                    <div class='float_left'>Purchase History from {$companyName_client}</div>
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
              ,pop.cost_price AS price
              ,DATE_FORMAT(po.purchase_order_date, '%d-%m-%Y') AS purchase_order_date
              ,pop.qty
              ,s.company_name AS title
        FROM po_product pop
        LEFT JOIN (purchase_order po) ON (po.purchase_order_id = pop.purchase_order_id)
        LEFT JOIN (supplier s) ON (s.supplier_id = po.company_id_supplier)
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
                $purchase_order = "<a target='_blank' href='index.php?_topRm=finance&module=tradingsg_purchaseOrder&purchase_order_id={$row['purchase_order_id']}&_action=edit'><u>{$row['po_code']}</u></a>";
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

    /**
     *
     */
    function getAddNewProductMaster() {
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $supplier_id = $fn->getReqParam('supplier_id');

        $formAction = "index.php?_topRm=finance&module=tradingsg_purchaseOrder&_spAction=AddNewProductMasterSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);

        $text = "
        <form id='NewProductPortalForm' class='NewProductPortalForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Product Name', 'title', '')}
            <input type='hidden' name='supplier_id' value='{$supplier_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintDeliveryOrderPdf() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        $pdf->setPrintFooter(false);

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(4);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 5);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $delivery_order_id = $fn->getReqParam('delivery_order_id');

        $SQL = "
        SELECT doh.*
              ,p.title AS product_title
              ,do.date
        FROM delivery_order_history doh
        LEFT JOIN (delivery_order do) ON (do.delivery_order_id = doh.delivery_order_id)
        LEFT JOIN (product p) ON (p.product_id = doh.product_id)
        WHERE do.delivery_order_id = {$delivery_order_id}
        ORDER BY doh.delivery_order_history_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $date   = $fn->getCPDate($company['date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#078205; text-decoration:underline; line-height:26px;">DELIVERY ORDER</td>
            </tr>
        </table>
        <table border="0">
            <tr>
                <td width="98%" align="right" style="font-weight:bold; font-size:10px; line-height:20px;">DATE : '.$date.'</td>
            </tr>
        </table>
        ';

        $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                    <thead>
                        <tr bgcolor="#92d14f">
                            <th width="8%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="45%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">PRODUCT NAME</th>
                            <th width="15%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                            <th width="32%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">REMARKS</th>
                        </tr>
                    </thead>
                    <tbody style="display: table; table-layout: fixed; height: 600px;">';
        
        $subtotalValue = 0;
        $count      = 1;
        $countCheck = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $tbl3 = $tbl3.'<tr>
                                <td width="8%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="45%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.$row['product_title'].'</td>
                                <td width="15%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                <td width="32%" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['remarks'].'</td>
                            </tr>
                    ';

            $countCheck++;
        }

        $emptyRow = 7 - $countCheck;

        for($i = 0; $i <= $emptyRow; $i++) {
          $tbl3 = $tbl3.'<tr>
                            <td width="8%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                            <td width="45%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="15%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="32%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                        </tr>
                  ';
        }

        $tbl3 = $tbl3.'<tr>
                            <td width="8%"  style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="45%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="15%"  style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="32%" style="border-bottom:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;"></td>
                        </tr>
                        </tbody>
                    </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <br/><br/>
            <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%">Yours Faithfully,</td>
            </tr>
        </table>
        ';

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td width="40%" style="border-bottom:1px solid black"></td>
                <td width="30%"></td>
                <td width="30%" style="border-bottom:1px solid black"></td>
            </tr>
            <tr>
                <td style="font-size:10px;">Authorised Signature / Date</td>
                <td></td>
                <td style="font-size:10px;">Accepted By / Date</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-DeliveryOrder.pdf';
        $pdf->Output($download_title, 'I');
    }
}