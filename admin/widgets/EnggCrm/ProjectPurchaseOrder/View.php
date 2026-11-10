<?
class CPL_Admin_Widgets_enggCrm_projectPurchaseOrder_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        return $text;
    }

    /**
     *
     */
    function getPurchaseOrderPortal($project_id = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $sql = "
        SELECT DISTINCT po.company_id_supplier
              ,s.company_name
              ,po.purchase_order_id
        FROM purchase_order po
        LEFT JOIN (supplier s) ON (po.company_id_supplier = s.supplier_id)
        WHERE po.project_id = {$project_id}
        ORDER BY po.purchase_order_id ASC
        ";
        $result  = $db->sql_query($sql);
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $editForPo              = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editForPo&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
            $urlPrintpurchaseorder  = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=Printpurchaseorder&project_id={$project_id}&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
            $editPoMultipleLineItem = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editPoMultipleLineItem&project_id={$project_id}&purchase_order_id={$row['purchase_order_id']}&showHTML=0";
            
            $rows .= "
            <tr class='header'>
                <td colspan='9'>
                    {$row['company_name']}
                    <u><a class='editForPo ml10' style='color:#fff;' href='{$editForPo}'>Edit PO</a></u>
                    <u><a class='editPoMultipleLineItem ml10' href='{$editPoMultipleLineItem}'>Edit Line Items</a></u>
                    <u><a target='_blank' class='ml10' style='color: #fff;' href='{$urlPrintpurchaseorder}'>Print pdf</a></u>
                </td>
            </tr>
            {$this->getMaterialsofPurchaseOrderForSupplier($row['purchase_order_id'], $row['company_id_supplier'])}
            ";
        }

        $deliveryOrder   = "<a class='btn btn-primary deliveryOrder' project_id='{$project_id}'>Create Delivery Order</a>";
        $allQtyDelivered = "<a class='btn btn-success qtyAllDelivered' project_id='{$project_id}'>Add all Qty to Stock</a>";

        $text = "
       <!-- <div id='materialRequestPortal' class='linkPortalWrapper'>
            {$this->getMaterialRequesPortal($project_id)}
        </div>-->

        <div id='poPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th colspan='9' align='left' class='rightPanelHeading'>
                          <div class='float_left mt5 rightPanelHeading'>
                              Materials Purchased
                          </div>
                          <div class='float_left'>
                              <a  class='btn btn-primary addMultiplePurchaseOrder' project_id='{$project_id}'>Add Purchase Order</a>
                          </div>
                          <div class='float_left'>
                              {$deliveryOrder}
                          </div>
                          <div class='float_left'>
                              {$allQtyDelivered}
                          </div>
                        </th>
                    </tr>
                </thead>
                <tbody class='poItemsRow'>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getMaterialsofPurchaseOrderForSupplier($purchase_order_id, $supplier_id) {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT po.*
        FROM po_product po
        WHERE po.purchase_order_id = {$purchase_order_id}
        ORDER BY po.item_title ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rowsPo = '';
        while ($row = $db->sql_fetchrow($result)) {
            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            $companyRec = $fn->getRecordRowByID('company', 'company_id', $supplier_id);

            $cancelLink = '';
            if ($row['status'] != 'Cancelled') {
                $cancelLink = "<a  class='cancelPoItem' po_product_id={$row['po_product_id']}><u>Cancel</u></a>";
            }

            $add_class = '';
            if ($row['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $edit_image = $cpCfg['cp.localPath']."images/edit.png";

            $editForPoLineItem = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editPoLineItem&purchase_order_id={$purchase_order_id}&po_product_id={$row['po_product_id']}&showHTML=0";

            $editPo = "
            <div class='float_left'>
                <a class='editForPoLineItem' href='{$editForPoLineItem}' title='Edit PO Line Item'><img src='{$edit_image}' class='icon'></a>
            </div>
            ";
            
            $doInputRow = "<input class='deliveryOrderId' type='checkbox' name='deliveryOrderId[]' value='{$row['po_product_id']}'>";

            $unitPriceFormatted = number_format($row['cost_price'], 2);
            $amountFormatted    = number_format(($row['qty'] * $row['cost_price']), 2);

            //<td>{$editPo}</td>
            $rowsPo .= "
            <tr>
                <td>{$doInputRow}</td>
                <td class='{$add_class}'>
                    <a class='creationModificationPo' po_product_id='{$row['po_product_id']}'>
                        <u>{$row['item_title']}</u>
                    </a>
                </td>
                <td class='{$add_class}'>{$row['unit']}</td>
                <td class='{$add_class}'>{$row['qty']}</td>
                <td class='txtRight {$add_class}'>{$unitPriceFormatted}</td>
                <td class='txtRight {$add_class}'>{$amountFormatted}</td>
                <td class='{$add_class}'>{$row['status']}</td>
                <td class='{$add_class}'>
                    <a class='transferPo' po_product_id='{$row['po_product_id']}' product_id='{$row['product_id']}'>
                        <u>Transfer</u>
                    </a>
                </td>
            </tr>
            ";
        }

        $rowsPoPrint = "
        <tr>
            <th>D.O.</th>
            <th>Title</th>
            <th>UoM</th>
            <th>Quantity</th>
            <th class='txtRight'>Unit Price</th>
            <th class='txtRight'>Amount</th>
            <th>Status</th>
            <th></th>
        </tr>
        {$rowsPo}
        ";

        return $rowsPoPrint;
    }

    /**
     *
     */
    function getAddMultiplePurchaseOrder() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $project_id = $fn->getReqParam('project_id');

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";

        $supplier    = "
        <select name='supplier_id' class='poSupplier'>
            <option value=''>Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";

        $product = "
        <input type='text' value='' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";

        $title       = "<textarea type='text' value='' id='title' class='text poTitle' name='title[]'></textarea>";
        $productType = "<td class='productType'  name='productType[]'></td>";
        $quantity    = "<input type='text' value='' id='quantity' class='text poQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text poUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text poAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $remarks     = "<textarea type='text' value='' id='description' class='text poDescription' name='description[]'></textarea>";
        $clear       = "<td class='text'><a  class='clearPo'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            {$clear}
        </tr>
        ";

        $newRow = "<a  class='addSinglePoRow btn btn-info mb10 mr10'>Add More Items</a>";

        $formActionNewProduct = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=AddNewProductMaster&showHTML=0";
        
        $newProductRow = "
        <a href='{$formActionNewProduct}' class='addNewProductProductPopup btn btn-primary mb10'>Add New Product</a>
        ";

        $header = "
        <tr>
            <div class='float_left'>{$newProductRow}</div>
            <div class='float_left'>{$newRow}</div>
            <div class='float_left'>{$formObj->getDDRowBySQL('Supplier', 'supplier_id', $sqlSupplier)}</div>
            <div class='float_left'>{$formObj->getDateRow('PO Date', 'po_date',date('Y-m-d'))}</div>
            <div class='float_left gstField'>{$formObj->getYesNoRRow('GST', 'gst')}</div>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Title</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Unit Price</th>
            <th class='txtCenter'>Amount</th>
           
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addMultiplePurchaseOrderSubmit&showHTML=0";

        $text = "
        <form id='addMultiplePurchaseOrderForm' class='yform addMultiplePurchaseOrderForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPoMultipleLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $project_id        = $fn->getReqParam('project_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $PORec = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";

        $supplier    = "
        <select name='supplier_id' class='poSupplier'>
            <option value=''>Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";


        $SQLPoProduct = "
        SELECT po.*
              ,p.title
        FROM po_product po
        LEFT JOIN product p ON (p.product_id = po.product_id)
        WHERE po.purchase_order_id = '{$purchase_order_id}'
        ORDER BY po.item_title ASC
        ";
        $resultPoProduct = $db->sql_query($SQLPoProduct);
        $rows = "";
        while ($rowPoProduct = $db->sql_fetchrow($resultPoProduct)) {
          $product = "
          <input type='text' value='{$rowPoProduct['title']}' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]'>
          <input type='hidden' name='product_id[]' class='product_id_hidden' value='{$rowPoProduct['product_id']}'>
          <input type='hidden' name='po_product_id[]' class='po_product_id_hidden' value='{$rowPoProduct['po_product_id']}'>
          ";

          $totalCost  = number_format(($rowPoProduct['qty'] * $rowPoProduct['cost_price']), 2); 
          $quantity   = "<input type='text' value='{$rowPoProduct['qty']}' id='quantity' class='text poQuantity' name='quantity[]'>";
          $unit       = "<input type='text' value='{$rowPoProduct['unit']}' id='unit' class='text poUnit' name='unit[]'>";
          $amount     = "<input type='text' value='{$rowPoProduct['cost_price']}' id='amount' class='text poAmount' name='amount[]'>";
          $total_cost = "<td class='txtRight text totalCost' name='totalCost[]'>{$totalCost}</td>";
          $title       = "<textarea type='text' value='' id='title' class='text poTitle' name='title[]'>{$rowPoProduct['item_title']}</textarea>
           <input type='hidden' name='po_product_id[]' class='po_product_id_hidden' value='{$rowPoProduct['po_product_id']}'>";
          $remarks    = "<textarea type='text' value='' id='description' class='text poDescription' name='description[]'>{$rowPoProduct['description']}</textarea>";
          $clear      = "<td class='text'><a  class='clearPo'><u>Clear</u></a></td>";

          $rows .= "
          <tr>
              <td>{$title}</td>
              <td>{$unit}</td>
              <td>{$quantity}</td>
              <td>{$amount}</td>
              {$total_cost}
              {$clear}
          </tr>
          ";
        }

        $newRow = "<a  class='addSinglePoRow btn btn-info mb10 mr10'>Add More Items</a>";

        $formActionNewProduct = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=AddNewProductMaster&showHTML=0";
        
        $newProductRow = "
        <a href='{$formActionNewProduct}' class='addNewProductProductPopup btn btn-primary mb10'>Add New Product</a>
        ";

        $expNoEdit = array('isEditable' => 0);
        $SQLSupplierRec = "
        SELECT company_name
        FROM supplier
        WHERE supplier_id = '{$PORec['company_id_supplier']}'
        ";
        $resultSupplierRec = $db->sql_query($SQLSupplierRec);
        $rowSupplierRec    = $db->sql_fetchrow($resultSupplierRec);

        $header = "
        <tr>
            <div class='float_left'>{$newProductRow}</div>
            <div class='float_left'>{$newRow}</div>
            <div class='float_left'>{$formObj->getTBRow('Supplier', 'supplier_id', $rowSupplierRec['company_name'], $expNoEdit)}</div>
            <div class='float_left'>{$formObj->getDateRow('PO Date', 'po_date', $PORec['po_date'], $expNoEdit)}</div>
            <div class='float_left'>{$formObj->getTBRow('PO No.', 'po_code', $PORec['po_code'])}</div>
            <div class='float_left gstField'>{$formObj->getYesNoRRow('GST', 'gst', $PORec['gst'])}</div>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Product Name</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Unit Price</th>
            <th class='txtCenter'>Amount</th>
           
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editMultiplePurchaseOrderSubmit&showHTML=0";

        $text = "
        <form id='editPoMultipleLineItem' class='yform editPoMultipleLineItem' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSinglePurchaseOrderRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $product = "
        <input type='text' value='' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        <input type='hidden' name='po_product_id[]' class='po_product_id_hidden' value=''>
        ";

        $title       = "<textarea type='text' value='' id='title' class='text poTitle' name='title[]'></textarea>";
        $productType = "<td class='productType'  name='productType[]'></td>";
        $quantity    = "<input type='text' value='' id='quantity' class='text poQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text poUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text poAmount' name='amount[]'>";
        $remarks     = "<textarea type='text' value='' id='description' class='text poDescription' name='description[]'></textarea>";
        $clear       = "<td class='text'><a  class='clearPo'><u>Clear</u></a></td>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

        /**
     * Add PO Line Item Edit
     */
    function getEditPoLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $tv      = Zend_Registry::get('tv');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $po_product_id  = $fn->getReqParam('po_product_id');
        $purchase_order_id  = $fn->getReqParam('purchase_order_id');

        $rowPoItem   = $fn->getRecordRowByID('po_product', 'po_product_id', $po_product_id);

        $expProd = array('placeholder' => 'Please type and select');
        $formActionEditLineItem = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editPoLineItemSubmit&lnkRoom={$tv['lnkRoom']}&po_product_id={$po_product_id}&purchase_order_id={$purchase_order_id}&showHTML=0";

        $text = "
        <form id='editForPoLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>
            <fieldset>
                {$formObj->getTBRow('Item Description', 'item_title', $rowPoItem['item_title'], $expProd)}
                <input type='hidden' name='product_id' value='{$rowPoItem['product_id']}' />
                {$formObj->getTBRow('UoM', 'unit',$rowPoItem['unit'])}
                {$formObj->getTBRow('Quantity', 'qty', $rowPoItem['qty'])}
                {$formObj->getTBRow('Unit Price', 'cost_price', $rowPoItem['cost_price'])}
                <input type='hidden' name='po_product_id' value='{$po_product_id}' />
                <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     * Purchase Order Edit
     */
    function getEditForPo() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $purchase_order_id = $fn->getReqParam('purchase_order_id');
        $rowPo = $fn->getRecordRowByID('purchase_order', 'purchase_order_id', $purchase_order_id);

        $formActionEditForPo = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editForPoSubmit&lnkRoom={$tv['lnkRoom']}&purchase_order_id={$purchase_order_id}&showHTML=0";

        $shipping_address_fields = "";
        if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
            $shipping_address_fields = "
                {$formObj->getTBRow('Address 1', 'shipping_address_flat', $rowPo['shipping_address_flat'])}
                {$formObj->getTBRow('Address 2', 'shipping_address_street', $rowPo['shipping_address_street'])}
                {$formObj->getTBRow('Country', 'shipping_address_country', $rowPo['shipping_address_country'])}
                {$formObj->getTBRow('Postal Code', 'shipping_address_po_code', $rowPo['shipping_address_po_code'])}
            ";
        }
        $expVl   = array('sqlType' => 'OneField');

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";
                $sqlPayment = $fn->getValueListSQL('popaymentType');
                $sqlWarranty = $fn->getValueListSQL('warrantyType');
                $sqlDelivery = $fn->getValueListSQL('deliveryType');
                $sqlPriceBasis = $fn->getValueListSQL('priceBasisType');
                $sqlDocument = $fn->getValueListSQL('documentType');



        $text = "
        <form id='editForPoForm' class='yform columnar' method='post' action='{$formActionEditForPo}'>
            <fieldset>
            <table>
            <tr>
               <td> {$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, $rowPo['company_id_supplier'])}</td>
               <td> {$formObj->getDateRow('PO Date', 'po_date', $rowPo['po_date'])}</td>
               <td>{$formObj->getTBRow('Delivery To', 'delivery_to', $rowPo['delivery_to'])}</td>
               
                </tr>
                <tr>
                <td> {$formObj->getDateRow('Delivery Date', 'delivery_date', $rowPo['delivery_date'])}</td>
                <td>{$formObj->getTBRow('Delivery Contact', 'contact', $rowPo['contact'])}</td>
                <td>{$formObj->getTBRow('Mobile', 'mobile', $rowPo['mobile'])}</td>

                </tr>
                <tr>
                 <td>{$formObj->getTBRow('Payment', 'payment', $rowPo['payment'])}</td>
                <td>{$formObj->getTBRow('Shipping method', 'shipping_method', $rowPo['shipping_method'])}</td>
                 <td>{$formObj->getTBRow('Project', 'project', $rowPo['project'])}</td>
               
                </tr>
                <tr>
                <td>{$formObj->getDDRowBySQL('Payment', 'payment_type', $sqlPayment, $rowPo['payment_type'], $expVl)}</td>
                <td>{$formObj->getDDRowBySQL('Warranty', 'warranty_type', $sqlWarranty, $rowPo['warranty_type'], $expVl)}</td>
                <td>{$formObj->getDDRowBySQL('Delivery', 'delivery_type', $sqlDelivery, $rowPo['delivery_type'], $expVl)}</td>
                
                </tr>
                <tr>
                <td>{$formObj->getDDRowBySQL('Price Basis', 'price_basis', $sqlPriceBasis, $rowPo['price_basis'], $expVl)}</td>
                <td>{$formObj->getDDRowBySQL('Document', 'document', $sqlDocument, $rowPo['document'], $expVl)}</td>
                </tr>
                <tr>
                <td>{$formObj->getHTMLEditor('General Instructions', 'payment_terms', $rowPo['payment_terms'])}</td>
                 </tr>
                 </table>
                <input type='hidden' name='purchase_order_id' value='{$purchase_order_id}' />
            </fieldset>
            
        </form>
        ";

        return $text;
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

        $formAction = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=AddNewProductMasterSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);

        $typeArray = array(
           "Materials"
          ,"Tools"
        );

        $text = "
        <form id='NewProductPortalForm' class='NewProductPortalForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Product Name *', 'title', '')}
            {$formObj->getDropDownRowByArray('Product Type *', 'product_type', $typeArray, 'Materials')}
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintpurchaseorder() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

         ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootquote.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        //$pdf->setPrintFooter(false);

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
        $pdf->SetFooterMargin(5);
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

        $project_id        = $fn->getReqParam('project_id');
        $purchase_order_id = $fn->getReqParam('purchase_order_id');

        $SQL = "
        SELECT DISTINCT pop.po_product_id
              ,pop.item_title
              ,pop.quantity
              ,pop.qty AS quantity
              ,pop.cost_price AS amount
              ,pop.description
              ,pop.unit
              ,po.company_id_supplier
              ,po.delivery_terms
              ,c.company_name
              ,c.category
              ,c.address_flat
              ,c.address_state
              ,c.address_street
              ,c.address_country
              ,c.address_po_code
              ,c.company_id
              ,c.phone AS supplier_phone
              ,c.fax AS supplier_fax
              ,c.contact_person
              ,p.project_code
              ,p.title
              ,prod.item_code
              ,po.supplier_reference_no
              ,po.our_reference_no
              ,po.shipping_method
              ,po.payment_terms
              ,po.delivery_date
              ,po.po_date
              ,po.po_code
              ,po.contact
              ,po.mobile
              ,c.email
              ,po.project
              ,po.payment
              ,po.delivery_to
              ,po.shipping_address_flat
              ,po.shipping_address_street
              ,po.shipping_address_country
              ,po.shipping_address_po_code
              ,po.payment_type
              ,po.delivery_type
              ,po.price_basis
              ,po.warranty_type
              ,po.document
              ,gc.name AS country_name
              ,con.first_name
        FROM po_product pop
        LEFT JOIN (purchase_order po) ON (pop.purchase_order_id = po.purchase_order_id)
        LEFT JOIN (project p) ON (po.project_id = p.project_id)
        LEFT JOIN (product prod) ON (prod.product_id = pop.product_id)        
        LEFT JOIN (company c) ON (po.company_id_supplier = c.company_id)
                LEFT JOIN (contact con) ON (con.company_id = c.company_id)

        LEFT JOIN geo_country gc ON (c.address_country = gc.country_code)
        WHERE po.project_id = {$project_id}
          AND po.purchase_order_id = '{$purchase_order_id}'
        ORDER BY pop.po_product_id ASC;
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //
                $po_date   = $fn->getCPDate($company['po_date'], 'd-M-Y');

        $po_code = $company['po_code'];
        $delivery_date = $dateUtil->formatDate($company['delivery_date'], 'DD/MM/YYYY');
        
        $tbl1 = '
        <table border="0" width="100%"  cellpadding="5">
            <tr>
                <td align="center" style="font-size:12px;font-weight:bold;">PURCHASE ORDER</td>
            </tr>
            <tr>
                <td></td>
            </tr>
        </table>
        ';
           
        $tbl2 = '<table border="0" width="100%" cellpadding="0" >
         <tr>
                        <td >
                            <table border="1" cellpadding="3" >
                                <tr>
                                    <td width="50%" style="font-size:12px;">PO Number: '.$po_code.'</td>
                                    <td width="50%" align="right" style="font-size:12px;">PO Date: '.$po_date.'</td>
                                </tr>
                              

                                </table>
                            </td>
                        </tr><br/><br/>
                                                    <table border="1" cellpadding="4" >

                         <tr>
                                    <td style="font-size:10px;background-color:#ededf0; font-weight:bold;">Supplier : </td>
                                </tr>
                                </table>
                    <tr >
                        <td border="1" width="50%">
                            <table border="0" cellpadding="3" >
                               
                                <tr>
                                    <td style="font-size:10px;font-weight:bold;">'.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td  style="font-size:10px;">'.$company['address_flat'] . ', ' . $company['address_street'] . '<br/> ' . $company['country_name'] . ' ' . $company['address_state'] .'</td>
                                </tr>
                                <tr>
                                    <td style="font-size:10px;">Tel : '.$company['supplier_phone'].'</td>
                                </tr>
                                <tr>
                                    <td style="font-size:10px;">Fax : '.$company['supplier_fax'].'</td>
                                </tr>
                               
                            </table>
                        </td>
                        
                        <td border="1" width="50%">
                            <table border="0" cellpadding="3" >
                                <tr>
                                    <td width="30%" style=" font-size:10px;font-weight:bold; ">Attention</td>

                                    <td style="font-size:10px;">: '.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="30%" style=" font-size:10px;font-weight:bold; ">Ref Quote</td>
                                    <td width="70%" style=" font-size:10px; ">: </td>
                                </tr>
                                <tr>
                                    <td width="30%" style=" font-size:10px;font-weight:bold;">Contact </td>
                                    <td width="70%" style=" font-size:10px; ">: '.$company['mobile'].'   </td>
                                </tr>
                                <tr>
                                    <td width="30%" style=" font-size:10px;font-weight:bold;">Email </td>
                                    <td width="70%" style=" font-size:10px; ">: '.$company['email'].'   </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table>
                    <tr>
                        <td style="line-height:10px;">&nbsp;</td>
                    </tr>
                </table>

                <table width="100%" >
                <tr>
                        <td width="50%" style="font-size:10px;font-weight:bold;"><u>We are pleased to place order with you for the following</u></td>
                       
                    </tr>
                    <tr>
                        <td width="50%" style="font-size:10px; line-height:20px;">Project:'.$company['title'].' </td>
                        <td width="50%" style="font-size:10px; line-height:20px; text-align:right;">Currency:<span style="font-weight:bold;"> KWD</span> </td>
                    </tr>
                </table>';

        $tbl3 ='<table border="0" cellpadding="2"  width="100%" style="font-size:10px;border-left:1px soild #000;border-right:1px soild #000;border-bottom:1px soild #000;">
                    <thead>
                        <tr>
                            <th width="10%" align="center" style="font-weight:bold; border:1px solid #000;">Item No</th>
                            <th width="45%" style="border:1px solid #000;font-weight:bold;">Item Description</th>
                            <th width="10%" align="center" style="border:1px solid #000;font-weight:bold;">UOM</th>
                            <th width="10%" align="center" style="border:1px solid #000;font-weight:bold;">Qty</th>
                            <th width="10%" align="center" style="border:1px solid #000;font-weight:bold;">Unit Price (S$)</th>
                            <th width="15%" align="center" style="border:1px solid #000;font-weight:bold;">Total Price</th>
                            
                        </tr>
                    </thead>';

        $subtotalValue = 0;
        $gstvalue      = 0;
        $totalvalue    = 0;
        $count = 1;
        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {

            $companyRec      = $fn->getRecordRowByID('company', 'company_id', $company['company_id_supplier']);
            $subtotal_amount = $row['quantity'] * $row['amount'];

            $tbl3 = $tbl3.'<tr>
                            <td width="10%" align="center" style="border-left:1px solid #000; font-size:10px; border-bottom:1px solid #000;border-right:1px solid #000;">'.$count.'</td>
                            <td width="45%" style="border-bottom:1px solid #000;">'.$row['item_title'].'</td>
                            <td width="10%" align="center" style="border-left:1px solid #000;border-bottom:1px solid #000;">'.$row['unit'].'</td>
                            <td width="10%" align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;">'.$row['quantity'].'</td>
                            <td width="10%" align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;">'.$row['amount'].'</td>
                            <td width="15%" align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;">'.number_format($subtotal_amount, 2).'</td>
                            
                        </tr>

                    ';
            $subtotalValue += $subtotal_amount;
            $gsttaxvalue    = $cpCfg['cp.gstPercentage'] ;
            $gstvalue       = $subtotalValue * $gsttaxvalue / 100;
            $totalvalue     = $gstvalue + $subtotalValue;

            $count++;
        }
                $amount_in_words   = $fn->getConvertNumber($totalvalue);

        
        $tbl3 = $tbl3.'<tr>
                          <td colspan="5" align="right" style="border-right:1px solid #000;">TOTAL</td>
                          <td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;font-weight:bold;">'.number_format($subtotalValue,2).'</td>
                      </tr>
                      <tr>
                          <td colspan="5" align="right" style="border:1px solid #000;">'.$cpCfg['cp.gstPercentage'].'% GST</td>
                          <td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;font-weight:bold;">'.number_format($gstvalue, 2).'</td>
                       </tr>
                       <tr>
                          <td colspan="5" align="right" style="font-size:10px; border:1px solid #000;">TOTAL INCLUDING GST</td>
                          <td align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;font-weight:bold;">'.number_format($totalvalue, 2).'</td>
                       </tr>
                    </table>';
         $tbl4 = '
        <table  width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px; font-weight:bold; margin:0px; ">AMOUNT IN WORDS :'.$amount_in_words.'</td>
            </tr>
           
        </table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td style="font-family: Roboto;font-size:13px; font-weight:bold;"><u>Terms & Condition :</u></td>
              
            </tr>
            <tr>
                <td style="font-family: Roboto;font-size:11px; ">Payment      :'.$company['payment_type'].'<br/><br/>Warranty      :'.$company['warranty_type'].'<br/><br/>Delivery      :'.$company['delivery_type'].'<br/><br/>Price Basis      :'.$company['price_basis'].'<br/><br/>Document     :'.$company['document'].'</td>
              
            </tr>
           
        </table>
        ';

        $tbl6 =  '<br/><br/><table>
                      <tr>
                          <td width="30%" align="center" style="font-size:12px;border-top:1px solid black;">Approved By:</td>
                      </tr>
                     
                     
                  </table>';
                
                  /*<td colspan="2" style="font-weight:bold;">Approved By:</td>
                  <td colspan="2" style="font-weight:bold;">'.$cpCfg['cp.companyProjectManagerName'].'</td>
                  <td colspan="2">Project Manager</td>*/

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-8);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-6);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
                $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $po_code . '-PO.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getMaterialRequesPortal($project_id = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $sql = "
        SELECT mr.materials_request_id
              ,mr.mr_code
        FROM materials_request mr
        WHERE mr.project_id = {$project_id}
        ORDER BY mr.materials_request_id DESC
        ";
        $result  = $db->sql_query($sql);
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $editForMaterialsRequest  = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editForMaterialsRequest&materials_request_id={$row['materials_request_id']}&showHTML=0";
            $urlPrintMaterialsRequest = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=PrintMaterialsRequest&materials_request_id={$row['materials_request_id']}&project_id={$project_id}&showHTML=0";
            $editMRMultipleLineItem   = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editMRMultipleLineItem&project_id={$project_id}&materials_request_id={$row['materials_request_id']}&showHTML=0";

            $rows .= "
            <tbody class='requestItemsRow'>
                <tr class='header'>
                    <td colspan='10'>
                        {$row['mr_code']}
                        <u><a class='editForMaterialsRequest ml10' style='color:#fff;' href='{$editForMaterialsRequest}'>Edit MR</a></u>
                        <u><a class='editMRMultipleLineItem ml10' materials_request_id='{$row['materials_request_id']}'>Edit Line Items</a></u>
                        <u><a target='_blank' class='ml10' style='color: #fff;' href='{$urlPrintMaterialsRequest}'>Print pdf</a></u>
                    </td>
                </tr>
                {$this->getMaterialRequesPortalLineItems($row['materials_request_id'])}
            </tbody>
            ";
        }

        $deliveryOrder   = "<a class='btn btn-primary deliveryOrder' project_id='{$project_id}'>Create Delivery Order</a>";
        $allQtyDelivered = "<a class='btn btn-success qtyAllDelivered' project_id='{$project_id}'>Add all Qty to Stock</a>";

        $approveMaterialRequest = '';
        if ($_SESSION['userGroupName'] != 'Projects' && $_SESSION['userGroupName'] != 'Admin and Purchase' ) {
            $approveMaterialRequest = "
            <div class='float_left'>
              <a  class='btn btn-success approveMaterialRequest' project_id='{$project_id}'>Approve Materials Request</a>
            </div>
            ";
        }

        $requestForApproval = '';
        if ($_SESSION['userGroupName'] != 'Projects') {
            $requestForApproval = "
            <div class='float_left'>
              <a  class='btn btn-info requestMaterialsForApproval' project_id='{$project_id}'>Request For Approval</a>
            </div>
            ";
        }

        $text = "
        <table class ='list'>
            <thead>
                <tr>
                    <th colspan='10' align='left' class='rightPanelHeading'>
                        <div class='float_left mt5 rightPanelHeading'>
                          Materials Request
                        </div>
                        <div class='float_left'>
                          <a  class='btn btn-primary addMaterialsRequest' project_id='{$project_id}'>Add Materials Request</a>
                        </div>
                        {$requestForApproval}
                        {$approveMaterialRequest}
                    </th>
                </tr>
            </thead>
            {$rows}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getMaterialRequesPortalLineItems($materials_request_id) {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT mrli.*
              ,s.company_name
        FROM materials_request_line_items mrli
        LEFT JOIN supplier s ON (s.supplier_id = mrli.supplier_id)
        WHERE mrli.materials_request_id = {$materials_request_id}
        ORDER BY mrli.item_title ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rowsPo = '';
        while ($row = $db->sql_fetchrow($result)) {
            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            $companyRec = $fn->getRecordRowByID('company', 'company_id', $row['supplier_id']);

            $cancelLink = '';
            if ($row['status'] != 'Cancelled') {
                $cancelLink = "<a  class='cancelMRItem' materials_request_line_items_id={$row['materials_request_line_items_id']}><u>Cancel</u></a>";
            }

            $add_class = '';
            if ($row['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $edit_image = $cpCfg['cp.localPath']."images/edit.png";

            $editForMRLineItem = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editPoLineItem&materials_request_id={$materials_request_id}&materials_request_line_items_id={$row['materials_request_line_items_id']}&showHTML=0";

            $editPo = "
            <div class='float_left'>
                <a class='editForMRLineItem' href='{$editForMRLineItem}' title='Edit PO Line Item'><img src='{$edit_image}' class='icon'></a>
            </div>
            ";
            
            $unitPriceFormatted = number_format($row['cost_price'], 2);
            $amountFormatted    = number_format(($row['qty'] * $row['cost_price']), 2);
            $matReqInputRow     = "<input class='materailRequestId' type='checkbox' name='materailRequestId[]' value='{$row['materials_request_line_items_id']}'>";

            $disaleStatus = array(
                 'Approved by Company Admin'
                ,'PO generated'
                ,'Material delivered'
            );

            if (in_array($row['status'], $disaleStatus)) {
                $matReqInputRow = "";
            }

            $rowsPo .= "
            <tr>
                <td align='center'>{$matReqInputRow}</td>
                <td class='{$add_class}'>
                    <a class='creationModificationMR' materials_request_line_items_id='{$row['materials_request_line_items_id']}'>
                        <u>{$row['item_title']}</u>
                    </a>
                </td>
                <td>{$row['brand']}</td>
                <td>{$row['company_name']}</td>
                <td class='{$add_class}' align='center'>{$row['unit']}</td>
                <td class='{$add_class}' align='center'>{$row['qty']}</td>
                <td class='txtRight {$add_class}'>{$unitPriceFormatted}</td>
                <td class='txtRight {$add_class}'>{$amountFormatted}</td>
                <td class='{$add_class}'>{$row['status']}</td>
                <td class='{$add_class}'>{$row['description']}</td>
            </tr>
            ";
        }

        $rowsPoPrint = "
        <tr>
            <th class='click-all-top txtCenter' width='5%'>
                <a href='#' class='check-all'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'>
                </a>
                <a href='#' class='uncheck-all'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'>
                </a>
            </th>
            <th>Description</th>
            <th>Brand</th>
            <th>Supplier</th>
            <th class='txtCenter'>UoM</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtRight'>Unit Price</th>
            <th class='txtRight'>Amount</th>
            <th>Status</th>
            <th>Remarks</th>
        </tr>
        {$rowsPo}
        ";

        return $rowsPoPrint;
    }

    /**
     *
     */
    function getAddMultipleMaterialRequest() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $project_id = $fn->getReqParam('project_id');

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";

        $supplier    = "
        <select name='supplier_id[]' class='poSupplier'>
            <option value=''>Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";

        $product = "
        <input type='text' value='' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        ";

        $statusArray = array(
             'Request for Materials'
            ,'Supplier request sent'
            ,'Supplier confirmed'
            ,'Sent for approval'
            ,'Approved by Company Admin'
            ,'PO generated'
            ,'Material delivered'
        );

        $status = "
        <select name='poStatus[]' class='poStatus'>
            {$cpUtil->getDropDown1($statusArray, '')}
        </select>
        ";

        $title       = "<textarea type='text' value='' id='title' class='text poTitle' name='title[]'></textarea>";
        $productType = "<td class='productType'  name='productType[]'></td>";
        $quantity    = "<input type='text' value='' id='quantity' class='text poQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text poUnit' name='unit[]'>";
        $brand       = "<input type='text' value='' id='brand' class='text poBrand' name='brand[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text poAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $remarks     = "<textarea type='text' value='' id='description' class='text poDescription' name='description[]'></textarea>";
        $clear       = "<td class='text'><a  class='clearMR'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$product}</td>
            {$productType}
            <td>{$brand}</td>
            <td>{$supplier}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$status}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$product}</td>
            {$productType}
            <td>{$brand}</td>
            <td>{$supplier}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$status}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$product}</td>
            {$productType}
            <td>{$brand}</td>
            <td>{$supplier}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$status}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$product}</td>
            {$productType}
            <td>{$brand}</td>
            <td>{$supplier}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$status}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$product}</td>
            {$productType}
            <td>{$brand}</td>
            <td>{$supplier}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$status}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        $newRow = "<a  class='addSingleMRRow btn btn-info mb10 mr10'>Add More Items</a>";

        $formActionNewProduct = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=AddNewProductMaster&showHTML=0";
        
        $newProductRow = "
        <a href='{$formActionNewProduct}' class='addNewProductProductPopup btn btn-primary mb10'>Add New Material</a>
        ";

        $formActionNewSupplier = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addNewSupplier&showHTML=0";
        $addNewSupplier = "
        <a href='{$formActionNewSupplier}' class='addNewSupplierPopup btn btn-success'>Add New Supplier</a>
        ";

        $header = "
        <tr>
            <div class='float_left'>{$newProductRow}</div>
            <div class='float_left'>{$addNewSupplier}</div>
            <div class='float_left'>{$newRow}</div>
            <div class='float_left'>{$formObj->getDateRow('MR Date', 'po_date')}</div>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th width='15%'>Description</th>
            <th width='10%'>Type</th>
            <th width='10%'>Brand</th>
            <th width='10%'>Supplier</th>
            <th width='7%'  class='txtCenter'>UoM</th>
            <th width='8%'  class='txtCenter'>Quantity</th>
            <th width='12%' class='txtCenter'>Unit Price</th>
            <th width='13%' class='txtCenter'>Amount</th>
            <th width='8%'>Status</th>
            <th width='15%'>Remarks</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addMultipleMaterialRequestSubmit&showHTML=0";

        $text = "
        <form id='addMultipleMaterialRequestForm' class='yform addMultipleMaterialRequestForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleMaterialsRequestRecord() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";

        $supplier    = "
        <select name='supplier_id[]' class='poSupplier'>
            <option value=''>Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";

        $product = "
        <input type='text' value='' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]'>
        <input type='hidden' name='product_id[]' class='product_id_hidden' value=''>
        <input type='hidden' name='materials_request_line_items_id[]' class='materials_request_line_items_id_hidden' value=''>
        ";

        $statusArray = array(
             'Request for Materials'
            ,'Supplier request sent'
            ,'Supplier confirmed'
            ,'Sent for approval'
            ,'Approved by Company Admin'
            ,'PO generated'
            ,'Material delivered'
        );

        $status = "
        <select name='poStatus[]' class='poStatus'>
            {$cpUtil->getDropDown1($statusArray, '')}
        </select>
        <input class='poStatus' type='hidden' name='poStatusHidden[]' value=''>
        ";

        $title       = "<textarea type='text' value='' id='title' class='text poTitle' name='title[]'></textarea>";
        $productType = "<td class='productType'  name='productType[]'></td>";
        $quantity    = "<input type='text' value='' id='quantity' class='text poQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text poUnit' name='unit[]'>";
        $brand       = "<input type='text' value='' id='brand' class='text poBrand' name='brand[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text poAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $remarks     = "<textarea type='text' value='' id='description' class='text poDescription' name='description[]'></textarea>";
        $clear       = "<td class='text'><a  class='clearMR'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$product}</td>
            {$productType}
            <td>{$brand}</td>
            <td>{$supplier}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            <td>{$status}</td>
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     * Purchase Order Edit
     */
    function getEditForMaterialsRequest() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $materials_request_id = $fn->getReqParam('materials_request_id');
        $rowPo = $fn->getRecordRowByID('materials_request', 'materials_request_id', $materials_request_id);

        $formActionEditForPo = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editForMaterialsRequestSubmit&lnkRoom={$tv['lnkRoom']}&materials_request_id={$materials_request_id}&showHTML=0";

        $shipping_address_fields = "";
        if ($cpCfg['m.enggCrm.project.addShippingAddressInPO'] == 1) {
            $shipping_address_fields = "
                {$formObj->getTBRow('Address 1', 'shipping_address_flat', $rowPo['shipping_address_flat'])}
                {$formObj->getTBRow('Address 2', 'shipping_address_street', $rowPo['shipping_address_street'])}
                {$formObj->getTBRow('Country', 'shipping_address_country', $rowPo['shipping_address_country'])}
                {$formObj->getTBRow('Postal Code', 'shipping_address_po_code', $rowPo['shipping_address_po_code'])}
            ";
        }

        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";

        $text = "
        <form id='editForMaterialsRequestForm' class='yform columnar' method='post' action='{$formActionEditForPo}'>
            <fieldset>
                <table>
                    <tr>
                        <td>{$formObj->getDateRow('MR Date', 'po_date', $rowPo['mr_date'])}</td>
                        <td>{$formObj->getTBRow('Project Name', 'project_name', $rowPo['project_name'])}</td>
                        <td>{$formObj->getTBRow('Site Reference', 'site_reference', $rowPo['site_reference'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getTBRow('Request By', 'request_by', $rowPo['request_by'])}</td>
                        <td>{$formObj->getDateRow('Request Date', 'request_date', $rowPo['request_date'])}</td>
                        <td>{$formObj->getTBRow('Approved By', 'approved_by', $rowPo['approved_by'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getDateRow('Approved Date', 'approved_date', $rowPo['approved_date'])}</td>
                        <td>{$formObj->getTBRow('Shipping method', 'shipping_method', $rowPo['shipping_method'])}</td>
                        <td>{$formObj->getTextAreaRow('Payment Terms', 'payment_terms', $rowPo['payment_terms'])}</td>
                    </tr>
                    <tr>
                        <td colspan='2'>{$formObj->getTextAreaRow('Delivery Terms', 'delivery_terms',$rowPo['delivery_terms'])}</td>
                    </tr>
                </table>
                <input type='hidden' name='materials_request_id' value='{$materials_request_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditMRMultipleLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $project_id           = $fn->getReqParam('project_id');
        $materials_request_id = $fn->getReqParam('materials_request_id');

        $sql = "
        SELECT mrli.*
               ,mr.materials_request_id
               ,mr.mr_code
               ,p.product_type
               ,s.company_name
        FROM materials_request_line_items mrli
        LEFT JOIN product p ON (p.product_id = mrli.product_id)
        LEFT JOIN supplier s ON (s.supplier_id = mrli.supplier_id)
        LEFT JOIN materials_request mr ON (mr.materials_request_id = mrli.materials_request_id)
        WHERE mr.materials_request_id = {$materials_request_id}
          AND mrli.status NOT IN('Approved by Company Admin', 'PO generated', 'Material delivered')
        ORDER BY mr.materials_request_id ASC
        ";
        $result  = $db->sql_query($sql);
        $rows = '';
        $sqlSupplier = "SELECT supplier_id, company_name FROM supplier";
        while ($row = $db->sql_fetchrow($result)) {
            $supplier    = "
            <select name='supplier_id[]' class='poSupplier'>
                <option value=''>Select</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $row['supplier_id'])}
            </select>
            ";

            $statusArray = array(
                 'Request for Materials'
                ,'Supplier request sent'
                ,'Supplier confirmed'
                ,'Sent for approval'
                ,'Approved by Company Admin'
                ,'PO generated'
                ,'Material delivered'
            );

            $status = "
            <select name='poStatus[]' class='poStatus'>
                {$cpUtil->getDropDown1($statusArray, $row['status'])}
            </select>
            <input class='poStatus' type='hidden' name='poStatusHidden[]' value='{$row['status']}'>
            ";

            $clear = "<td class='text'><a  class='clearMR'><u>Clear</u></a></td>";

            $disabled = "";
            $disaleStatus = array(
                 'Sent for approval'
                ,'Approved by Company Admin'
                ,'PO generated'
                ,'Material delivered'
            );

            /*if (in_array($row['status'], $disaleStatus)) {
                $supplier = $row['company_name'];
                $status   = $row['status']."<input class='poStatus' type='hidden' name='poStatusHidden[]' value='{$row['status']}'>";
                $disabled = "disabled='disabled'";
                $clear    = "<td></td>";
            }*/

            $product = "
            <input type='text' value='{$row['item_title']}' id='poProduct' placeholder='Please type and select' class='text poProductTitle' name='product_title[]' {$disabled}>
            <input type='hidden' name='product_id[]' class='product_id_hidden' value='{$row['product_id']}'>
            <input type='hidden' name='materials_request_line_items_id[]' class='materials_request_line_items_id_hidden' value='{$row['materials_request_line_items_id']}'>
            ";

            $totalCost  = $row['qty'] * $row['cost_price'];
            $totalCost  = number_format($totalCost, 2);

            $productType = "<td class='productType'  name='productType[]'>{$row['product_type']}</td>";
            $quantity    = "<input type='text' value='{$row['qty']}' id='quantity' class='text poQuantity' name='quantity[]'>";
            $unit        = "<input type='text' value='{$row['unit']}' id='unit' class='text poUnit' name='unit[]'>";
            $brand       = "<input type='text' value='{$row['brand']}' id='brand' class='text poBrand' name='brand[]' {$disabled}>";
            $amount      = "<input type='text' value='{$row['cost_price']}' id='amount' class='text poAmount' name='amount[]'>";
            $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'>{$totalCost}</td>";
            $remarks     = "<textarea type='text' value='' id='description' class='text poDescription' name='description[]'>{$row['description']}</textarea>";

            $rows .= "
            <tr>
                <td>{$product}</td>
                {$productType}
                <td>{$brand}</td>
                <td>{$supplier}</td>
                <td>{$unit}</td>
                <td>{$quantity}</td>
                <td>{$amount}</td>
                {$total_cost}
                <td>{$status}</td>
                <td>{$remarks}</td>
                {$clear}
            </tr>
            ";
        }

        $matrlsRec = $fn->getRecordRowByID('materials_request', 'materials_request_id', $materials_request_id);
        $newRow = "<a  class='addSingleMRRow btn btn-info mb10 mr10'>Add More Items</a>";

        $formActionNewProduct = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=AddNewProductMaster&showHTML=0";
        
        $newProductRow = "
        <a href='{$formActionNewProduct}' class='addNewProductProductPopup btn btn-primary mb10'>Add New Product</a>
        ";

        $formActionNewSupplier = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=addNewSupplier&showHTML=0";
        $addNewSupplier = "
        <a href='{$formActionNewSupplier}' class='addNewSupplierPopup btn btn-success'>Add New Supplier</a>
        ";

        $header = "
        <tr>
            <div class='float_left'>{$newProductRow}</div>
            <div class='float_left'>{$addNewSupplier}</div>
            <div class='float_left'>{$newRow}</div>
            <div class='float_left'>{$formObj->getDateRow('MR Date', 'po_date', $matrlsRec['mr_date'])}</div>
            <div class='float_left'>{$formObj->getTBRow('MR No.', 'po_code', $matrlsRec['mr_code'], $expEdit)}</div>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th width='15%'>Description</th>
            <th width='10%'>Type</th>
            <th width='10%'>Brand</th>
            <th width='10%'>Supplier</th>
            <th width='7%'  class='txtCenter'>UoM</th>
            <th width='8%'  class='txtCenter'>Quantity</th>
            <th width='12%' class='txtCenter'>Unit Price</th>
            <th width='13%' class='txtCenter'>Amount</th>
            <th width='8%'>Status</th>
            <th width='15%'>Remarks</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=editMultipleMaterialRequestSubmit&showHTML=0";

        $text = "
        <form id='editMultipleMaterialRequestForm' class='yform editMultipleMaterialRequestForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='materials_request_id' value='{$materials_request_id}' />
            <input type='hidden' name='project_id' value='{$project_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintMaterialsRequest() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootPrintClaim.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        //$pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(8, 12, 8);
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
        $pdf->AddPage("L");

        $project_id           = $fn->getReqParam('project_id');
        $materials_request_id = $fn->getReqParam('materials_request_id');

        $SQL = "
        SELECT mrli.*
               ,mr.project_name
               ,mr.site_reference
               ,mr.request_by
               ,mr.request_date
               ,mr.approved_by
               ,mr.approved_date
               ,mr.mr_date
               ,p.project_code
               ,s.company_name
        FROM materials_request_line_items mrli
        LEFT JOIN materials_request mr ON (mr.materials_request_id = mrli.materials_request_id)
        LEFT JOIN supplier s ON (s.supplier_id = mrli.supplier_id)
        LEFT JOIN project p ON (p.project_id = mr.project_id)
        WHERE mrli.materials_request_id = '{$materials_request_id}'
          AND mr.project_id = '{$project_id}'
        ORDER BY mrli.item_title ASC
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $request_date  = $fn->getCPDate($company['request_date'], 'd/M/Y');
        $approved_date = $fn->getCPDate($company['approved_date'], 'd/M/Y');
        $mr_date       = $fn->getCPDate($company['mr_date'], 'd/M/Y');

        $tbl1 = '
        <table border="1" width="100%" cellpadding="0" style="font-size:10px;">
            <tr>
                <td>
                    <table border="0" width="100%" cellpadding="4" style="font-size:10px;">
                        <tr style="line-height:20px;">
                            <td width="9%"  style="font-weight:bold;">Project Name&nbsp;&nbsp;:</td>
                            <td width="40%" align="center" style="border-bottom:1px solid #000000;">'.$company['project_name'].'</td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                        </tr>
                        <tr style="line-height:20px;">
                            <td width="9%"  style="font-weight:bold;">Date Required :</td>
                            <td width="40%" align="center" style="border-bottom:1px solid #000000;">'.$mr_date.'</td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                        </tr>
                        <tr style="line-height:20px;">
                            <td width="9%" style="font-weight:bold;">Requested By&nbsp;:</td>
                            <td width="25%" align="center" style="border-bottom:1px solid #000000;">'.$company['request_by'].'</td>
                            <td width="5%"></td>
                            <td width="10%" align="center" style="border-bottom:1px solid #000000;">'.$request_date.'</td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                        </tr>
                        <tr style="line-height:20px;">
                            <td width="9%"></td>
                            <td width="25%" align="center">(Staff Name & Signature)</td>
                            <td width="5%"></td>
                            <td width="10%" align="center">(Date)</td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                        </tr>
                        <tr style="line-height:20px;">
                            <td width="9%" style="font-weight:bold;">Approved By&nbsp;&nbsp;&nbsp;:</td>
                            <td width="25%" align="center" style="border-bottom:1px solid #000000;">'.$company['approved_by'].'</td>
                            <td width="5%"></td>
                            <td width="10%" align="center" style="border-bottom:1px solid #000000;">'.$approved_date.'</td>
                            <td width="5%"></td>
                            <td width="9%"  style="font-weight:bold;">Site Reference :</td>
                            <td width="15%" align="center" style="border-bottom:1px solid #000000;">'.$company['site_reference'].'</td>
                            <td width="5%"></td>
                            <td width="5%"  style="font-weight:bold;">Pages :</td>
                            <td width="10%" align="right" style="border-bottom:1px solid #000000;">'.$pdf->getAliasNumPage() .' of '.$pdf->getAliasNbPages().'</td>
                        </tr>
                        <tr style="line-height:20px;">
                            <td width="9%"></td>
                            <td width="25%" align="center">(Manager / Project Engineer Name & Signature)</td>
                            <td width="5%"></td>
                            <td width="10%" align="center">(Date)</td>
                            <td width="15%"></td>
                            <td width="15%"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="1" width="100%" cellpadding="4">
            <tr style="font-size:10px;font-weight:bold;">
                <td width="4%"  align="center">S.No</td>
                <td width="36%" align="center">Item Description</td>
                <td width="14%" align="center">Brand</td>
                <td width="14%" align="center">Supplier</td>
                <td width="13%" align="center">Quantity<br/>(Eg: meter/Nos/Length)</td>
                <td width="19%" align="center">Remarks</td>
            </tr>
        ';

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $tbl2 = $tbl2.'<tr style="font-size:10px;">
                                <td width="4%"  align="center">'.$count.'</td>
                                <td width="36%">'.$row['item_title'].'</td>
                                <td width="14%" align="center">'.$row['brand'].'</td>
                                <td width="14%" align="center">'.$row['company_name'].'</td>
                                <td width="13%" align="center">'.$row['qty'].'</td>
                                <td width="19%">'.$row['description'].'</td>
                            </tr>
                ';

            $count++;
        }

        $tbl2 = $tbl2.'</table>';

        $pdf->ln(6);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(3);
        $pdf->writeHTML($tbl2, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Materials-Request.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getAddNewSupplier() {
        $fn      = Zend_Registry::get('fn');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $db      = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $sqlStatus  = $fn->getValueListSQL('companyStatus');
        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $formAction = "index.php?widget=enggCrm_projectPurchaseOrder&_spAction=AddNewSupplierSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
        $expVl      = array('sqlType' => 'OneField');

        $text = "
        <form id='AddNewSupplierPortalForm' class='AddNewSupplierPortalForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Supplier Name *', 'company_name', '')}
            {$formObj->getTBRow('Email', 'email')}
            {$formObj->getTBRow('Fax', 'fax')}
            {$formObj->getTBRow('Mobile', 'mobile')}
            {$formObj->getTBRow('Address 1', 'address_flat')}
            {$formObj->getTBRow('Address 2', 'address_street')}
            {$formObj->getTBRow('State/ Zip', 'address_state')}
            {$formObj->getDDRowBySQL('Country', 'address_country', $sqlCountry, '')}
        </form>
        ";

        return $text;
    }
}