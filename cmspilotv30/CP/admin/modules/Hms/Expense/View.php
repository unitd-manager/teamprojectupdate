<?
class CP_Admin_Modules_Hms_Expense_View extends CP_Common_Lib_ModuleViewAbstract
{
   function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $text = '';
        $rows = '';
        $readonly = '';
        $OrderItems = '';

        $rowCounter = 0;

        $SQLdeleteHistory ="
        DELETE FROM expense_product
        WHERE expense_id NOT IN (SELECT expense_id FROM expense)
        ";
        $resultdelhis = $db->sql_query($SQLdeleteHistory);
        $deletehistory = $db->sql_fetchrow($resultdelhis);

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $expense_date = $fn->getCPDate($row['creation_date'],"d-m-Y");

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $expense_date)}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['from_location'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Date', 'date')}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('Location', 'site_id')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $statusArr = array(
                     "In Progress"
                    ,"Confirm"
                    ,"Cancelled");

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQLSite = "
        SELECT title
        FROM site
        WHERE site_id = {$cpSiteIdSession}
        ";
        $resultSite = $db->sql_query($SQLSite);
        $rowSite    = $db->sql_fetchrow($resultSite);
        $title = $rowSite['title'].' Expense For '.date('F Y');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title', $title)}
        {$formObj->getDDRowByArr('Status', 'status', $statusArr, 'In Progress')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('New Expense', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $db = Zend_Registry::get('db');
        $comment = getCPPluginObj('common_comment');
        $media = Zend_Registry::get('media');
        $text = '';

        $record_id = $fn->getIssetParam($row, 'expense_id');

        $text .="
        {$comment->getView(array(
             'roomName' => 'hms_expense'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $current_date = date('Y-m-d');
        $text = '';

        $text = "
        <div id='editDisplayLoad'>{$this->getEditDisplay($row['expense_id'], $row['site_id'])}</div>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditDisplay($expense_id='', $site_id=''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $current_date = date('Y-m-d');
        $text = '';
        $rows = '';

        if($expense_id == ''){
            $expense_id = $fn->getReqParam('expense_id');
        }

        if($site_id == ''){
            $site_id = $fn->getReqParam('site_id');
        }

        $SQLExpense = "
        SELECT e.*
               ,s.title AS from_location
        FROM expense e
        LEFT JOIN site s ON (s.site_id = e.site_id)
        WHERE e.site_id = {$site_id}
        AND e.expense_id = {$expense_id}
        ORDER BY e.created_by DESC
        ";
        $resultExpense = $db->sql_query($SQLExpense);
        $row = $db->sql_fetchrow($resultExpense);

        $statusArr = array(
                     "In Progress"
                    ,"Confirm"
                    ,"Cancelled");
        $expense_id   = $row['expense_id'];
        $expense_date = $fn->getCPDate($row['creation_date'],"d-m-Y");
        $OrderItems = $this->getOrderItems($expense_id);

        $editableFalse = '';
        $buttonChange  = '';

        if($row['lock_record'] == 1){
            $editableFalse = "disabled = '1'";

            $buttonChange .= "
            <a class='btn btn-info rollbackChanges' expense_id= '{$row['expense_id']}' site_id= '{$row['site_id']}'>
                <span class='fa-refresh'></span>
                 Rollback Transaction
            </a>";

            $buttonChange .= "
            <a class='btn btn-danger deductFromStock' expense_id= '{$row['expense_id']}' site_id= '{$row['site_id']}'>
                <span class='fa-check'></span>
                Deduct From Stock
            </a>";
        }else{
            $buttonChange = "
            <a class='btn btn-success completeTransaction' expense_id= '{$row['expense_id']}' site_id= '{$row['site_id']}'>
                <span class='fa-lock'></span>
                Complete Transaction
            </a>";
        }

        $expNoEdit = '';
        if($row['stock_deducted'] == 1){
            $expNoEdit = array('isEditable' => 0);

            $buttonChange = "Products are Deducted from Stock succesfully.";
        }

        $text = "

        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Expense Details</div>
                    <div class='toggle'></div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                            <tr>
                                <td width='25%'>
                                    {$formObj->getTBRow('Title', 'title', $row['title'])}
                                </td>
                                <td width='25%'>
                                    <div class='locationTitle'>
                                        <label>Date</label>
                                        {$expense_date}
                                    </div>
                                </td>
                                <td width='25%'>
                                    <label>status</label>
                                    <div class='locationStatus'>
                                        {$formObj->getDDRowByArr('', 'status', $statusArr, $row['status'], $expNoEdit)}
                                    </div>
                                    <input  type='hidden' name='site_id' value='{$row['site_id']}'/>
                                </td>
                                <td width='25%'>
                                    <div class='locationTitle'>
                                        <label>Location</label>
                                        {$row['from_location']}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='2'>
                                    <div class='locationTitle'>
                                        <label>Created By</label>
                                        {$row['created_by']} {$row['creation_date']}
                                    </div>
                                </td>
                                <td colspan='2'>
                                    <div class='locationTitle'>
                                        <label>Modified By</label>
                                        {$row['modified_by']} {$row['modification_date']}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class='addProduct'>
            Search by Product : <input type='text' value='' id='fld_product_title' class='text' name='product_title' expense_id={$row['expense_id']} {$editableFalse}>
        </div>

        <div class = 'float_box'>
            <div class = 'float_left actionButtons'>
                {$buttonChange}
            </div>
        </div>

        <table class='list thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product Code</th>
                    <th>Product Name</th>
                    <th>Stock</th>
                    <th>Qty Used</th>
                    <th>Stock After Qty Used</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Modified By</th>
                    <th>Cancel</th>
                </tr>
            </thead>
            <tbody id='orderItems'>
                {$OrderItems}
            </tbody>
        </table>
        ";

        return $text;
    }

    /**
     *totalqty
     */
    function getOrderItems($expense_id=''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $text = '';
        $rows = '';
        $totalquantity = '';

        if ($expense_id == ''){
            $expense_id = $fn->getReqParam('expense_id');

        }

        $productStatusArr = array(
                            "In Progress"
                           ,"Added"
                           ,"Cancelled");


        $SqlExpenseCount = "
        SELECT expense_product_id
        FROM expense_product
        WHERE expense_id = '{$expense_id}'
        AND status != 'Cancelled'
        ";
        $resultExpenseCount  = $db->sql_query($SqlExpenseCount);
        $numRowsExpenseCount = $db->sql_numrows($resultExpenseCount);
        $stock  = 0;
        $StockSql = "
        SELECT p.title
              ,p.product_code
              ,ep.qty
              ,e.lock_record
              ,ep.expense_product_id
              ,ep.created_by
              ,ep.product_id
              ,ep.modified_by
              ,ep.creation_date
              ,ep.modification_date
              ,e.expense_id
              ,e.site_id
              ,e.status
              ,ep.status AS product_status
        FROM `expense_product` ep
        LEFT JOIN product p ON (ep.product_id = p.product_id)
        LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
        WHERE p.published = '1'
        AND p.product_id  = ep.product_id
        AND ep.expense_id = {$expense_id}
        ";
        $resultStockSql = $db->sql_query($StockSql);
        $rowCounter = 1;
        while ($rowz = $db->sql_fetchrow($resultStockSql)) {
            $SQLStockFrom = "
            SELECT actual_stock{$cpSiteIdSession} AS Stock_From
            FROM inventory
            WHERE product_id = {$rowz['product_id']}
            ";
            $resultStockFrom = $db->sql_query($SQLStockFrom);
            $rowStockFrom    = $db->sql_fetchrow($resultStockFrom);

            $stock = $rowStockFrom['Stock_From'];
            $noStock = '';
            if($stock == 0){
                $noStock = "disabled = '1'";
            }
            
            $actualStock = $stock - $rowz['qty'];

            $cancelTrClass = '';
            $editableFalse = '';
            $expNoEdit     = '';
            $cancelLink = "<a class='btn btn-danger cancelExpenseProduct' expense_product_id='{$rowz['expense_product_id']}' expense_id= '{$rowz['expense_id']}'>Cancel</a>";
            if($rowz['product_status'] == 'Cancelled'){
                $cancelTrClass = "class='cancelledExpenseProduct'";
                $cancelLink  = "<b>Cancelled</b>";
                $editableFalse = "disabled = '1'";
                $expNoEdit = array('isEditable' => 0);
            }

            if($rowz['product_status'] == 'Added'){
                $cancelLink  = "";
                $editableFalse = "disabled = '1'";
            }

            if($rowz['lock_record'] == 1 && $rowz['status'] == 'Confirm'){
                $expNoEdit = array('isEditable' => 0);
                $actualStock = $stock;
                $stock = $rowStockFrom['Stock_From'] + $rowz['qty'];
            }


            $rows .= "
            <tr {$cancelTrClass}>
            <td>
                {$rowCounter}
                <input  type='hidden' class='expense_product_count' name='expense_product_count' value='{$numRowsExpenseCount}'/>
            </td>
            <td class='w100'>PROD - {$rowz['product_code']}</td>
            <td class='w25p'>{$rowz['title']}</td>
            <td>{$stock}</td>
            <td class='w100'>
                <input type='text' value='{$rowz['qty']}' previousQtyValue='{$rowz['qty']}' id='fld_Expense_qty_{$rowz['expense_product_id']}' class='text w100 expense_qty_input' name='expense_qty' expense_product_id='{$rowz['expense_product_id']}' expense_id= '{$rowz['expense_id']}' stock='{$stock}' {$editableFalse} {$noStock}>
            </td>
            <td>{$actualStock}</td>
            <td class='w200 locationStatus'>{$formObj->getDDRowByArr('', 'product_status', $productStatusArr, $rowz['product_status'],$expNoEdit)}
            <input  type='hidden' class='expense_product_id_row' name='expense_product_id_row' value='{$rowz['expense_product_id']}'/>
            <input  type='hidden' class='expense_id_row' name='expense_id_row' value='{$rowz['expense_id']}'/>
            </td>
            <td>{$rowz['created_by']}  {$rowz['creation_date']}</td>
            <td>{$rowz['modified_by']}  {$rowz['modification_date']}</td>
            <td>{$cancelLink}</td>
            </tr>
            ";
            $rowCounter++ ;


        }

        $text = "
        {$rows}
        ";
        return $text;

    }
}