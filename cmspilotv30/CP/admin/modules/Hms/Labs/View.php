<?
class CP_Admin_Modules_Hms_Labs_View extends CP_Common_Lib_ModuleViewAbstract
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
                (lp.qty * lp.price),2)) AS total_cost
                FROM labs_product lp WHERE lp.labs_id = {$row['labs_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);

            $SQLLabs = "
            SELECT l.*
                  ,(SELECT SUM(i.payments_amount) FROM payments i
                    WHERE i.labs_id = l.labs_id
                    AND i.status != 'Cancelled'
                    ) AS invoice_amount
                  ,(SELECT SUM(r.amount)
                    FROM payments_receipt r
                    WHERE r.labs_id = l.labs_id
                    AND r.receipt_status != 'Cancelled'
                    )AS receipt_amount
                  ,(SELECT SUM(invHist.amount) AS prev_sum
                    FROM payments_receipt_history invHist
                    LEFT JOIN payments_receipt r ON (r.payments_receipt_id = invHist.payments_receipt_id)
                    LEFT JOIN `payments` i ON (i.labs_id = {$row['labs_id']})
                    WHERE invHist.payments_id =  i.payments_id
                    AND r.receipt_status != 'Cancelled'
                    AND i.status != 'Cancelled'
                    ) as Amount_Paid
            FROM `labs` l
            WHERE l.labs_id = {$row['labs_id']}
            ";

            $resultLabs = $db->sql_query($SQLLabs);
            $rowLabs    = $db->sql_fetchrow($resultLabs);

            $total_invoice_amount = 0;
            if($rowLabs['invoice_amount'] != ''){
                $total_invoice_amount = $rowLabs['invoice_amount'];
                $balance_Amount = $total_invoice_amount - $rowLabs['Amount_Paid'];
                $balance_Amount = number_format($balance_Amount, 2);
                $invoiced_Paid_Amount = number_format($rowLabs['Amount_Paid'], 2);
            }else{
                $total_invoice_amount = $rowLabs['invoice_amount'];
                $invoiced_Paid_Amount = number_format($rowLabs['Amount_Paid'], 2);
                $balance_Amount = $total_invoice_amount - $rowLabs['Amount_Paid'];
                $balance_Amount = number_format($balance_Amount, 2);
            }

            $total_invoice_amount = number_format($total_invoice_amount, 2);
            $overallBalanceAmt    = number_format($rowLabs['invoice_amount'] - $rowLabs['receipt_amount'], 2);
            $labsAmount = number_format($row['amount'], 2);

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['labs_code'])}
            {$listObj->getListDateCell($row['labs_date'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDataCell($row['category'])}
            {$listObj->getListDataCell($row['Patient_Name'])}
            {$listObj->getListDataCell($row['nric'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($labsAmount, 'right')}
            {$listObj->getListDataCell($invoiced_Paid_Amount, 'right')}
            {$listObj->getListDataCell($overallBalanceAmt, 'right')}
            {$listObj->getListRowEnd($row['labs_id'])}
            ";
            $count++ ;
        }
        $rows = $listObj->getDisplayListRows($rows);
        
        //{$listObj->getListHeaderCell('Client', 'company_name')}
        
        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Labs Code', 'l.labs_code')}
        {$listObj->getListHeaderCell('Labs Date', 'labs_date')}
        {$listObj->getListHeaderCell('Title', 'l.title')}
        {$listObj->getListHeaderCell('Supplier Name', 'supplier_name')}
        {$listObj->getListHeaderCell('Category', 'category')}
        {$listObj->getListHeaderCell('Patient Name', '')}
        {$listObj->getListHeaderCell('Nric', '')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('Total Amount', 'amount', 'headerRight')}
        {$listObj->getListHeaderCell('Amount Paid', '', 'headerRight')}
        {$listObj->getListHeaderCell('Balance', '', 'headerRight')}
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
        
        $expVl = array('sqlType' => 'OneField');
        $sqlCategory = $fn->getValueListSQL('labSupplierCategory');

        $fieldset = "
        {$formObj->getDDRowBySQL('Category', 'supplier_category', $sqlCategory, '', $expVl)}
        {$formObj->getDDRowBySQL('Supplier', 'supplier_id', '', '', $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Labs Header', $fieldset)}
        ";
        return $text;
    }

    //==================================================================//
    function getEdit($row){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $expNoEdit = array('isEditable' => 0);

        //$expContact = array('detailValue' => $row['contact_name_supplier']);
        $modContact = getCPModuleObj('trading_contact');

        $expCompany = array('sqlType' => 'OneField');

        $expVl = array('sqlType' => 'OneField');
        $sqlPriority = $fn->getValuelistSql('labsPriority');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $statusArr = $cpCfg['m.hms.labs.statusArr'];
        if($row['status'] == 'confirmed'){       //if po confirmed, remove option 'new'
            unset($statusArr[array_search('new', $statusArr)]);
        }

        //$modContact = getCPModuleObj('core_staff');
        //$sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $sqlEmployee = "
        SELECT employee_id AS staff_id
              ,CONCAT_WS(' ', first_name, middle_name, last_name) AS employee_name
        FROM employee
        WHERE (position = 'Doctor' OR position = 'Nurse')
        ORDER BY employee_name
        ";

        $sqlSupplier = "
        SELECT labs_supplier_id
              ,title
        FROM labs_supplier
        WHERE category = '{$row['supplier_category']}'
        ORDER BY title
        ";

        $expStaff   = array('detailValue' => $row['staff_name']);

        $text = "
        <div id='labsSummaryDisplay'>{$this->getSummaryInLabs($row['labs_id'])}</div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Labs Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$row['creation_date']} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$row['modification_date']}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='25%'>{$formObj->getTBRow('Labs Code', 'labs_code', $row['labs_code'], $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getTBRow('Title', 'title', $row['title'])}</td>
                                <td width='25%'>{$formObj->getTBRow('Patient Name', '', $row['Patient_Name'], $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getTBRow('Nric', '', $row['nric'], $expNoEdit)}</td>
                            </tr>
                            <tr>               
                                <td width='25%'>{$formObj->getDDRowByArr('Status', 'status', $statusArr, $row['status'])}</td>
                                <td width='25%'>{$formObj->getDDRowBySQL('Priority', 'priority', $sqlPriority, $row['priority'], $expVl)}</td>
                                <td width='25%'>{$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlEmployee, $row['staff_id'], $expStaff)}</td>
                                <td width='25%'>{$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}</td>
                            </tr>
                            <tr>
                                <td width='25%'>{$formObj->getTBRow('Category', 'category', $row['category'], $expNoEdit)}</td>
                                <td width='25%'>{$formObj->getDDRowBySQL('Supplier Name', 'supplier_id', $sqlSupplier, $row['supplier_id'])}
                                <td width='25%'>{$formObj->getTARow('Notes to Supplier', 'notes', $row['notes'])}</td>
                                <td width='25%'>{$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'])}</td>
                            </tr>
                            <tr>
                                <td width='25%'>{$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'])}</td>
                            </tr>
                        </tbody>
                    </table>
                    <input type='hidden' id='fld_patient_visit_id' value='{$row['patient_visit_id']}'>
                </div>
            </div>
        </div>
        ";

        //{$formObj->getCreationModificationText($row)}

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

        //$record_id = $fn->getIssetParam($row, 'labs_id');
        //$labs_id  = $fn->getReqParam('labs_id');
        
        $receiptPortal = '';
        if($row['order_id'] != ''){
            $receiptPortal = "<div id='orderReceiptPortal'>{$this->getReceiptPortalDisplay($row['order_id'], $row['labs_id'])}</div>";
        }

        $text ="
        <div id='labsDisplay'>{$this->getLabsDisplay($row['patient_visit_id'], $row['labs_id'])}</div>
        {$receiptPortal}
        {$media->getRightPanelMediaDisplay('Picture', 'hms_labs', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'hms_labs', 'attachment', $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getLabsDisplay($patient_visit_id='' ,$labs_id= '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($patient_visit_id == ''){
            $patient_visit_id = $fn->getReqParam('patient_visit_id');
        }

        if($labs_id == ''){
            $labs_id = $fn->getReqParam('labs_id');
        }

        $formAction = '';
        $rows  = "";
        $rowsPvt  = "";

        $SQL = "
        SELECT  l.supplier_category
               ,ls.title
               ,l.labs_id
               ,l.patient_visit_id
               ,l.labs_code
               ,l.order_id
               ,l.amount
        FROM labs l
        LEFT JOIN (labs_supplier ls) ON (ls.labs_supplier_id = l.supplier_id)
        WHERE l.patient_visit_id = {$patient_visit_id}
        AND l.labs_id = {$labs_id}
        ORDER BY l.labs_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result); 
        $serialNo = 1;
        $supplier_category_link = '';
        $supplier_category = '';
        while ($rowL = $db->sql_fetchrow($result)) {
            
            $receiptRec = $fn->getRecordByCondition('payments_receipt', "order_id = '{$rowL['order_id']}' AND labs_id = '{$rowL['labs_id']}' AND receipt_status != 'Cancelled'");
            if($receiptRec){
                $supplier_category = "<a href='#' id='supplier_categoryFormLink'><u>View Form</u></a>";
                $editRow = "<a href='#' id='supplier_categoryFormLink'><u>Edit</u></a>";

            }else{
                if($rowL['supplier_category'] == 'Acrylic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=acrylicDentureForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='acrylicFormDenture' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";
                }else if($rowL['supplier_category'] == 'Ceramic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addCeramicForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='addCeramicForm' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";

                }else if($rowL['supplier_category'] == 'Orthodontic'){
                    $supplier_category_link = "index.php?_topRm=main&module=hms_patientVisit&_spAction=addOrthodonticForm&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                    $supplier_category = "<a href='{$supplier_category_link}' id='addOrthodontic' patient_visit_id={$rowL['patient_visit_id']}><u>View Form</u></a>";

                }

                $editURL = "index.php?_topRm=main&module=hms_patientVisit&_spAction=editLabsRecord&showHTML=0&patient_visit_id={$rowL['patient_visit_id']}&labs_id={$rowL['labs_id']}";
                $editRow = "<a href='{$editURL}' id='editLabsRecord' patient_visit_id={$rowL['patient_visit_id']}><u>Edit</u></a>";

            }

            /*
                <td><a href='#' class='deleteLabsRecord' labs_id='{$rowL['labs_id']}' patient_visit_id={$rowL['patient_visit_id']}><u>Delete</u></a></td>
            */

            if($rowL['amount'] == ''){
                $rowL['amount'] = 0;
            }

            $labsAmount = number_format($rowL['amount'], 2);
            
            $SQLPatientvisit = "
            SELECT visit_code
            FROM patient_visit
            WHERE patient_visit_id = {$rowL['patient_visit_id']}
            ";
            $resultPatientvisit = $db->sql_query($SQLPatientvisit);
            $rowPatientvisit    = $db->sql_fetchrow($resultPatientvisit);
            $patient_visit_Link = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$rowL['patient_visit_id']}";
            $patient_visit_Code = "<a href='{$patient_visit_Link}'><u>PV - {$rowPatientvisit['visit_code']}</u></a>";
            $rows .= "
            <tr>
                <td>{$serialNo}</td>
                <td>{$patient_visit_Code}</td>
                <td>{$rowL['title']}</td>
                <td>{$rowL['supplier_category']} - {$supplier_category}</td>
                <td>{$labsAmount}</td>
                <td>{$editRow}</td>
            </tr>
            ";
            $serialNo++;

            $order_id = $rowL['order_id'];
            $amount   = $rowL['amount'];
        }

        /*  
            <th>Delete</th>
        */

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>S.No</th>
        <th>Patient Visit Code</th>
        <th>Supplier Name</th>
        <th>Category</th>
        <th>Amount</th>
        <th>Edit</th>
        </tr>
        ";

        $paymentButton = '';
        //if($numRows > 0 && $amount > 0){
            if($order_id != ''){
                if($amount > 0){
                    $paymentButton="
                    <div class='header'>
                        <div class='floatbox'>
                            <div class='float_left'>
                                <a href='#' patient_visit_id='{$patient_visit_id}'  order_id='{$order_id}' labs_id='{$labs_id}' class='btn btn-info' id='generateReceipt'>Payments</a>
                            </div>
                        </div>
                    </div>
                    ";
                }else{
                    $paymentButton="
                    <div class='header'>
                        <div class='floatbox'>
                            <div class='float_left'>
                                <a href='#' class='btn btn-info' id='generateReceiptnoAmount'>Payments</a>
                            </div>
                        </div>
                    </div>
                    ";
                }
            }else{
                $paymentButton="
                <div class='header'>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <a href='#' class='btn btn-info' id='generateReceiptnoOrder_Id'>Payments</a>
                        </div>
                    </div>
                </div>
                ";
            }
        //}

        $text = "
        <div class='linkPortalWrapper hms_labs__hms_labs_supplierLink'>
            {$paymentButton}
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Supplier Linked</div>
                    <div class='txtRight'>
                        <span class='count'>({$numRows})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form id='' class='' method='post' action='{$formAction}'>
                    <div id='labsPortalOuter'>
                        <table class='thinlist'>
                            {$header}
                            {$rows}
                        </table>
                    </div>
                </form>
            </div>
        </div>
        <div id='dialog-confirm'></div>
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

        $sqlCompany = $fn->getDDSql('hms_labsSupplier');

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
                {$cpUtil->getDropDown1($cpCfg['m.hms.labs.statusArr'], $status)}
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
    function getAddProduct($labs_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($labs_id == ''){
            $labs_id = $fn->getReqParam('labs_id');
        }

        $Product = $this->getAddProductDetail($labs_id);

        $recCount = $fn->getRecordCount('labs_product', "labs_id = '{$labs_id}'");

        $header ="
        <thead>
            <tr>
            <th>Product</th>
            <th>Supplier</th>
            <th>Cost Price</th>
            <th>Quantity</th>
            <th>Qty Delivered</th>
            <th>Status</th>
            </tr>
        </thead>
        ";

        if($recCount == 0){
            $header ="<thead></thead>";
        }

        $formActionProduct = "index.php?module=hms_labs&_spAction=AddMultipleLineItem&labs_id={$labs_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddProduct' href='{$formActionProduct}' labs_id={$labs_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper hms_labs__hms_labs_productLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Product Linked</div>
                    <div class='txtRight'>
                    <span class='count'>({$recCount})</span>
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
                    <input type='hidden' name='labs_id' value='{$labs_id}' />
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
    function getAddProductDetail($labs_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');


        if($labs_id == ''){
            $labs_id = $fn->getReqParam('labs_id');
        }

        $labs_product_id = $fn->getReqParam('labs_product_id');

        $rows  = "";

        $SQL="
        SELECT lp.*
              ,p.title AS product
              ,com.company_name AS supplier_name
        FROM labs_product lp
        LEFT JOIN (product p) ON (p.product_id = lp.product_id)
        LEFT JOIN (company com) ON (lp.supplier_id = com.company_id)
        WHERE labs_id = '{$labs_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {

            $rows .= "
                <tr>
                    <td>{$row['product']}</td>
                    <td>{$row['supplier_name']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['qty']}</td>
                    <td>{$row['qty_delivered']}</td>
                    <td>{$row['status']}</td>
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noRenewal'>No Records Linked</td>
                </tr>
            ";

        }
        $text="{$rows}";

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

        $labs_id = $fn->getReqParam('labs_id');
        //$product_id       = $fn->getReqParam('product_id');

        $sqlproduct = "
        SELECT product_id
              ,title AS  product
        FROM product
        ";

        $product    = "
        <select name='product_id[]' class='labsProduct'>
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

        $sqlSupplier = $fn->getDDSql('hms_company');

        $Supplier    = "
        <select name='supplier_id[]' class='labsProduct'>
            <option value=''>Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";

        $status = $fn->getReqParam('status');

        $status    = "
            <select name='prod_status[]'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.hms.labs.statusprodArr'], $status)}
            </select>
        ";

        $price         = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]'>";
        $qty           = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
        //$status        = "<input type='text' value='' id='status' class='text poStatus' name='prod_status[]'>";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$Supplier}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$qty_delivered}</td>
            <td>{$status}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$Supplier}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$qty_delivered}</td>
            <td>{$status}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$Supplier}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$qty_delivered}</td>
            <td>{$status}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$Supplier}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$qty_delivered}</td>
            <td>{$status}</td>
        </tr>
        <tr>
            <td>{$product}</td>
            <td>{$Supplier}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$qty_delivered}</td>
            <td>{$status}</td>
        </tr>
        ";

        $newRow = "
        <a href='#' class='addSinglePoRow button mb10'>Add Item</a>
        ";

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Product</th>
            <th>Supplier</th>
            <th class='txtCenter'>Cost Price</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Qty Delivered</th>
            <th>Status</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=labs&module=hms_labs&_spAction=AddMultipleLineItemSubmit&showHTML=0";

        $text = "
        <form id='addMultipleLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>
            <table class='thinlist' id='labs_productTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='labs_id' value='{$labs_id}' />
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
        <select name='product_id[]' class='labsProduct'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlproduct)}
        </select>
        ";

        $sqlSupplier = "
        SELECT company_id, company_name FROM company
        WHERE category = 'Supplier'
        ORDER BY company_name
        ";

        $sqlSupplier = $fn->getDDSql('hms_company');

        $Supplier    = "
        <select name='supplier_id[]' class='labsProduct'>
            <option value=''>Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";

        $status = $fn->getReqParam('status');

        $status    = "
            <select name='prod_status[]'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.hms.labs.statusprodArr'], $status)}
            </select>
        ";

        $price         = "<input type='text' value='' id='price' class='text lineItemDescription' name='price[]'>";
        $qty           = "<input type='text' value='' id='qty' class='text poQuantity' name='qty[]'>";
        $qty_delivered = "<input type='text' value='' id='qty_delivered' class='text poqty_delivered' name='qty_delivered[]'>";
       // $status        = "<input type='text' value='' id='status' class='text poStatus' name='prod_status[]'>";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$Supplier}</td>
            <td>{$price}</td>
            <td>{$qty}</td>
            <td>{$qty_delivered}</td>
            <td>{$status}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getGenerateReceiptForm() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');
        $labs_id = $fn->getReqParam('labs_id');
        $patient_visit_id = $fn->getReqParam('patient_visit_id');
        $_SESSION['selectedInvoiceIds'] = array();

        $rows = '';
        $today = date('Y-m-d');

        $SQL = "
        SELECT i.*
            ,(
            SELECT SUM(invHist.amount) AS prev_sum
            FROM payments_receipt_history invHist
            LEFT JOIN payments_receipt r ON (r.payments_receipt_id = invHist.payments_receipt_id)
            WHERE invHist.payments_id =  i.payments_id
            AND r.receipt_status != 'Cancelled'
            ) as prev_inv_amount
        FROM payments i
        WHERE i.order_id = {$order_id}
        AND i.labs_id = {$labs_id}
            AND (i.status = 'Due' || i.status = 'Partial Payment')
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return "Sorry no invoice is available or all the invoices are paid" ;
        }


        $invoice_amount = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            $invoice_amount = $row['payments_amount'] - $row['discount'];

            $rows .= "
            <div class='form-row-wrapper'>
                <div class='floatbox'>
                    <div class='float_left'>
                        <input type='checkbox' name='invoiceCode[]' value='{$row['payments_code']}' class='invoiceCode'>
                    </div>
                    <div class='float_left'>{$row['payments_code']}({$invoice_amount})</div>
                    <div class=''>Paid:{$row['prev_inv_amount']}</div>
                </div>
            </div>
            ";
            $count++;
        }

        $formAction = "index.php?_topRm=finance&module=hms_labs&_spAction=generateReceiptFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar receiptForm' method='post' action='{$formAction}'>
            <h3>Please select Invoice</h3>
            {$rows}
            {$formObj->getTBRow('Amount', 'amount')}
            {$formObj->getDDRowByVL('Mode of Payment', 'mode_of_payment',  'paymentType')}
            {$formObj->getTextAreaRow('Note', 'remarks')}
            <input type='hidden' name='order_id' value='{$order_id}' />
            <input type='hidden' name='labs_id' value='{$labs_id}' />
        </form>
        ";

        return $text;

    }

    /**
     *
     */
    function getReceiptPortalDisplay($order_id = '', $labs_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        if($order_id == ''){
            $order_id = $fn->getReqParam('order_id');
        }

        if($labs_id == ''){
            $labs_id = $fn->getReqParam('labs_id');
        }

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordByCondition('payments_receipt', "order_id = '{$order_id}' AND labs_id = '{$labs_id}'");

        $SQL = "
        SELECT r.payments_receipt_id
              ,r.receipt_status
              ,r.payments_receipt_code
              ,r.date
              ,r.mode_of_payment
              ,r.amount
        FROM payments_receipt r
        LEFT JOIN (payments_receipt_history irh) ON (r.payments_receipt_id = irh.payments_receipt_id)
        WHERE r.order_id = {$order_id}
        AND r.labs_id = {$labs_id}
              {$sqlAppend}
        GROUP BY r.payments_receipt_id
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {
            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt' order_id ='{$order_id}' labs_id ='{$labs_id}' receipt_code='{$rowReceipt['payments_receipt_code']}'>Cancel Receipt</a>";
            }
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $cancelReceiptLink = "Cancelled";
            }

            $rows .= "
            <tr>
                <td>{$rowReceipt['payments_receipt_code']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$rowReceipt['amount']}</td>
                <td>{$cancelReceiptLink}</td>
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
        <th>Receipt Code</th>
        <th>Receipt Date</th>
        <th>Mode of Payment</th>
        <th class='txtRight'>Receipt Amount</th>
        <th>Cancel</th>
        </tr>
        ";

        $formAction = "";

        //InvoiceToggleHeading
        $text = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Receipt(s)</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <form id='orderItemPrint' class='' method='post'
                    action='{$formAction}'>
                        <table class='thinlist'>
                            {$header}
                            {$rows}
                        </table>
                        <input type='hidden' name='order_id' value='{$order_id}' />
                        <input type='hidden' name='receipt_id' value='{$receiptRec['payments_receipt_id']}' />
                    </form>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSummaryInLabs($labs_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND l.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT l.*
              ,(SELECT SUM(i.payments_amount) FROM payments i
                WHERE i.labs_id = l.labs_id
                AND i.status != 'Cancelled'
                ) AS invoice_amount
              ,(SELECT SUM(r.amount)
                FROM payments_receipt r
                WHERE r.labs_id = l.labs_id
                AND r.receipt_status != 'Cancelled'
                )AS receipt_amount
              ,(SELECT SUM(invHist.amount) AS prev_sum
                FROM payments_receipt_history invHist
                LEFT JOIN payments_receipt r ON (r.payments_receipt_id = invHist.payments_receipt_id)
                LEFT JOIN `payments` i ON (i.labs_id = {$labs_id})
                WHERE invHist.payments_id =  i.payments_id
                AND r.receipt_status != 'Cancelled'
                AND i.status != 'Cancelled'
                ) as Amount_Paid
        FROM `labs` l
        WHERE l.labs_id = {$labs_id}
        ";

        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $total_invoice_amount = 0;
        if($row['invoice_amount'] != ''){
            $total_invoice_amount = $row['invoice_amount'];
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
        }else{
            $total_invoice_amount = $row['invoice_amount'];
            $invoiced_Paid_Amount = number_format($row['Amount_Paid'], 2);
            $balance_Amount = $total_invoice_amount - $row['Amount_Paid'];
            $balance_Amount = number_format($balance_Amount, 2);
        }

        $total_invoice_amount = number_format($total_invoice_amount, 2);
        $overallBalanceAmt    = number_format($row['invoice_amount'] - $row['receipt_amount'], 2);
   
        $rows = "
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Bill Summary</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tr>
                            <th>Total Amount</th>
                            <th>Amount Paid</th>
                            <th>Amount Due</th>
                        </tr>
                        <tr>
                            <td>{$total_invoice_amount}</td>
                            <td>{$invoiced_Paid_Amount}</td>
                            <td>{$overallBalanceAmt}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        ";

        $text = "
        {$rows}
        ";

        return $text;

    }

}