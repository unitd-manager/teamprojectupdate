<?
class CP_Admin_Modules_Labsg_Labs_View extends CP_Common_Lib_ModuleViewAbstract
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

            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['labs_code'])}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['supplier_name'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($rowTotal['total_cost'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($row['labs_date'])}
            {$listObj->getListDateCell($row['creation_date'])}
            {$listObj->getListRowEnd($row['labs_id'])}
            ";
            $count++ ;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Labs Code', 'l.labs_code')}
        {$listObj->getListHeaderCell('Title', 'l.title')}
        {$listObj->getListHeaderCell('Supplier Name', 'supplier_name')}
        {$listObj->getListHeaderCell('Client', 'company_name')}
        {$listObj->getListHeaderCell('Labs Value', 'amount')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('Labs Date', 'labs_date')}
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

        $sqlSupplier = "
        SELECT company_id, company_name FROM company
        WHERE category = 'Supplier'
        ORDER BY company_name
        ";
        $sqlSupplier = $fn->getDDSql('labsg_company');
        $expSupplier = array('hideFirstOption' => 1);

        $fieldset = "
        {$formObj->getDDRowBySQL('Supplier', 'company_id_supplier', $sqlSupplier, '', $expSupplier)}
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

        $fnsModGrp = includeCPClass('ModGroup', 'Trading', 'Functions');

        $expNoEdit = array('isEditable' => 0);

        $expContact = array('detailValue' => $row['contact_name_supplier']);
        $modContact = getCPModuleObj('trading_contact');

        $expCompany = array('sqlType' => 'OneField');
        $expDeliveryTerms = $fnsModGrp->getTermsParamArr('trading_paymentTermsLink',
                                                        $row['company_id_supplier'],
                                                        'fld_delivery_terms'
                                                        );

        $expPaymentTerms = $fnsModGrp->getTermsParamArr('trading_paymentTermsLink',
                                                        $row['company_id_supplier'],
                                                        'fld_payment_terms'
                                                        );

        $expVl = array('sqlType' => 'OneField');
        $sqlPriority = $fn->getValuelistSql('labsPriority');
        $sqlCurrency = $fn->getValueListSQL('currency');

        $statusArr = $cpCfg['m.labsg.labs.statusArr'];
        if($row['status'] == 'confirmed'){       //if po confirmed, remove option 'new'
            unset($statusArr[array_search('new', $statusArr)]);
        }

        $modContact = getCPModuleObj('core_staff');
        $sqlSalesManager = $modContact->model->getStaffByGroupSQL();

        $expStaff   = array('detailValue' => $row['staff_name']);


        $fieldset1 = "
        {$formObj->getTBRow('Labs Code', 'labs_code', $row['labs_code'], $expNoEdit)}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDDRowByArr('Status', 'status', $statusArr, $row['status'])}
        {$formObj->getDDRowBySQL('Priority', 'priority', $sqlPriority, $row['priority'], $expVl)}
        {$formObj->getDDRowBySQL('Staff Member Responsible', 'staff_id', $sqlSalesManager,
                                 $row['staff_id'], $expStaff)}
        {$formObj->getDateRow('Follow up Date', 'follow_up_date', $row['follow_up_date'])}
        {$formObj->getDDRowBySQL('Currency', 'buy_currency', $sqlCurrency, $row['buy_currency'], $expVl)}
        {$formObj->getTARow('Notes to Supplier', 'notes', $row['notes'])}
        {$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'], $expDeliveryTerms)}
        {$formObj->getTARow('Payment Terms', 'payment_terms', $row['payment_terms'], $expPaymentTerms)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Labs Header', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
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
        $media = Zend_Registry::get('media');

        $record_id = $fn->getIssetParam($row, 'labs_id');
        $labs_id  = $fn->getReqParam('labs_id');

        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'labsg_labs', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'labsg_labs', 'attachment', $row)}
        ";

        $sqllabs = "
        SELECT l.*
        FROM labs l 
        WHERE l.labs_id = {$row['labs_id']}
        ";

        $resultlabs = $db->sql_query($sqllabs);
        $rowlabs = $db->sql_fetchrow($resultlabs);

        $printText ="";
        if ($rowlabs['labs_id'] != '') {
            $printText .="
            <div id='renewalLinkPortal'>{$this->getAddProduct($row['labs_id'])}</div>
            ";
        }
        $text=$text.$printText;      
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
                {$cpUtil->getDropDown1($cpCfg['m.labsg.labs.statusArr'], $status)}
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

        $formActionProduct = "index.php?module=labsg_labs&_spAction=AddMultipleLineItem&labs_id={$labs_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddProduct' href='{$formActionProduct}' labs_id={$labs_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper labsg_labs__labsg_labs_productLink'>
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

        $sqlSupplier = $fn->getDDSql('labsg_company');

        $Supplier    = "
        <select name='company_id_supplier[]' class='labsProduct'>
            <option value=''>Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";

        $status = $fn->getReqParam('status');

        $status    = "
            <select name='prod_status[]'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.labsg.labs.statusprodArr'], $status)}
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

        $formAction = "index.php?_topRm=labs&module=labsg_labs&_spAction=AddMultipleLineItemSubmit&showHTML=0";

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

        $sqlSupplier = $fn->getDDSql('labsg_company');

        $Supplier    = "
        <select name='company_id_supplier[]' class='labsProduct'>
            <option value=''>Supplier</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier)}
        </select>
        ";

        $status = $fn->getReqParam('status');

        $status    = "
            <select name='prod_status[]'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.labsg.labs.statusprodArr'], $status)}
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



}