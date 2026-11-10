<?
class CPL_Admin_Modules_Tradingsg_Product_View extends CP_Admin_Modules_Tradingsg_Product_View
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
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $sortOrder = '';
            $extaFlds = '';
            $productCodeTd = $listObj->getListDataCell($row['item_code'], 'center');

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$productCodeTd}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['product_type'])}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['unit'], 'center')}
            {$listObj->getListDataCell($row['qty_in_stock'], 'center')}
            {$sortOrder}
            {$listObj->getListDataCell($row['modified_by'] . ' ' . $row['modification_date'])}
            {$listObj->getListPublishedImage($row['published'], $row['product_id'])}
            {$listObj->getListRowEnd($row['product_id'])}
            ";
            $rowCounter++;
        }

        $sortOrder = '';

        $extaFlds = '';

        $productCodeTh = $listObj->getListHeaderCell('Item Code', 'item_code', 'txtCenter');

        $text = "
        {$listObj->getListHeader()}
        {$productCodeTh}
        {$listObj->getListHeaderCell('Product Name', 'p.title')}
        {$listObj->getListHeaderCell('Product Type', 'p.product_type')}
        {$listObj->getListHeaderCell('List Price', 'p.price')}
        {$listObj->getListHeaderCell('Unit', 'p.unit', 'txtCenter')}
        {$listObj->getListHeaderCell('Stock', 'p.qty_in_stock', 'txtCenter')}
        {$sortOrder}
        {$listObj->getListHeaderCell('Updated By', 'p.modified_by')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getUpdateProductCompany() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $company_id = 98;

        $SQLProduct = "SELECT product_id FROM product WHERE member_only = 1";
        $result = $db->sql_query($SQLProduct);

        while ($row = $db->sql_fetchrow($result)) {
            $fa1 = array();
            $fa1['item_code'] = $this->getUpdateProductCode();

            $whereCondition = "
            WHERE product_id = {$row['product_id']} AND item_code IS NULL
            ";
            $SQLProduct = $dbUtil->getUpdateSQLStringFromArray($fa1, 'product', $whereCondition);
            $db->sql_query($SQLProduct);

            $recCount = $fn->getRecordCount('product_company', "company_id = '{$company_id}' AND product_id = {$row['product_id']}");
            if (is_numeric ($company_id) && $recCount == 0) {
                $fa2 = array();
                $fa2['company_id'] = $company_id;
                $fa2['product_id']  = $row['product_id'];
                $fa2 = $fn->addCreationDetailsToFieldsArray($fa2, 'product_company');

                $SQL = $dbUtil->getInsertSQLStringFromArray($fa2, 'product_company');
                $result1 = $db->sql_query($SQL);
            }
        }
    }

    /**
     *
     */
    function getUpdateProductCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Product Code */
        $nextProductItemCode = $fn->getSettingsValueByKey("nextProductItemCode");

        if($nextProductItemCode < 10){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '000' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 99){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '00' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 999){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '0' . $nextProductItemCode;
        }
        else{
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . $nextProductItemCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductItemCode'";
        $result = $db->sql_query($SQL);

        return $ProCode;
    }

    /**
     *
     */
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $am = Zend_Registry::get('am');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $formObj->mode = $tv['action'];
        $sqlUnit = $fn->getValueListSQL('productUnit', 'value');
        $expVl = array('sqlType' => 'OneField');
        $expNoEdit  = array('isEditable' => 0);
        $sqlDiscountType = array("%", "Value");
        $expVlDisc   = array('sqlType' => 'OneField', 'firstOptionLabel' => 'No Discount');

        $priceText = $formObj->getTBRow('<b>List Price</b>', 'price', $row['price'],$expNoEdit);

        if ($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator' ) {
            $sqlProductGroup = "
            SELECT product_group_id
                  ,title
            FROM product_group
            ";
        }
        else{
            $sqlProductGroup = "
            SELECT pg.product_group_id
                  ,pg.title
            FROM product_group pg
            LEFT JOIN product_group_staff pgs ON (pg.product_group_id = pgs.product_group_id)
            WHERE pg.product_group_id = pgs.product_group_id
            AND pgs.staff_id = {$_SESSION['staff_id']}
            ";
        }

        $sqlCategory = '';
        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');
        $expCategory = array('detailValue' => $row['category_title']);

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }

        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $stockFld = "
        {$formObj->getTBRow('Quantity in Stock', 'qty_in_stock', $row['qty_in_stock'], $expNoEdit)}
        ";

        $modSec = getCPModuleObj('webBasic_section');
        $sqlSection = $modSec->model->getSectionSQL();
        $expSection = array('detailValue' => $row['section_title']);

		$validatedProduct =	"{$formObj->getTBRow('Product Name *', 'title', $ln->gfv($row, 'title', '0'))}
                             {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
							";

        $price_from_supplier =  '';

        if ($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator' ) {
            $price_from_supplier = $formObj->getTBRow('Price from Supplier', 'price_from_supplier', $row['price_from_supplier']);
        }

        $productTypeArr = array(
           "Materials"
          ,"Tools"
        );

        $SqlSupplier = "
        SELECT  s.supplier_id
               ,s.company_name
        FROM supplier s
        ";

        $fielset1 = "
        {$formObj->getTBRow('Item Code', 'item_code', $row['item_code'], $expNoEdit)}
        {$validatedProduct}
        {$formObj->getDDRowByArr('Type', 'product_type', $productTypeArr, $row['product_type'])}
        {$stockFld}
        {$priceText}
        {$formObj->getTBRow('Unit', 'unit', $row['unit'])}
        {$formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Product Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";
        return $text;
    }

    /**
     *
     */

    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $links = '';
        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_product', 'attachment', $row)}

        {$media->getRightPanelMediaDisplay('Picture', 'tradingsg_product', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Related Picture', 'tradingsg_product', 'relatedPicture', $row)}
        {$links}
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $am = Zend_Registry::get('am');
        $fn = Zend_Registry::get('fn');

        $supplier_id     	 = $fn->getReqParam('supplier_id');
        $special_search      = $fn->getReqParam('special_search');
        $special_search  	 = $fn->getReqParam('special_search');
        $product_group_id	 = $fn->getReqParam('product_group_id');
        $general_quotation   = $fn->getReqParam('general_quotation');
        $subCatOptions  = '';
        $catOptions  = '';
        $product_type         = $fn->getReqParam('product_type');

        //$sqlProductGroup = $fn->getDDSql('tradingsg_productGroup');
        $sqlProductGroup = "
        SELECT a.product_group_id
              ,a.title
        FROM product_group a
        ORDER BY a.product_group_id
        ";

        $sqlSupplier = "
        SELECT c.supplier_id
        	  ,c.company_name
        FROM supplier c
        ORDER BY c.company_name
        ";


        $SQLCat = "
        SELECT a.category_id
              ,a.title
        FROM category a
        ORDER BY a.title
        ";
        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);

        if ($tv['category_id'] != "") {
            $sqlCombo = "
            SELECT a.sub_category_id
                  ,a.title
            FROM sub_category a
            WHERE a.category_id = {$tv['category_id']}
            ORDER BY a.title
            ";
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $tv['sub_category_id']);
        }

        $generalQuoteArray = array(
            "Yes"
           ,"No"
        );

        $productTypeArr = array(
           "Materials"
          ,"Tools"
        );

        $text = "
       <!-- <td>
            <select name='supplier_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
            </select>
        </td>-->
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$catOptions}
            </select>
        </td>
        <td>
            <select name='product_type'>
                <option value=''>Product Type</option>
                {$cpUtil->getDropDown1($productTypeArr, $product_type)}
            </select>
        </td>
        ";


        return $text;
    }

    /**
     *
     */
    function getQuickAdd() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_topRm=admin&module=tradingsg_product&_spAction=quickAddSubmit&showHTML=0";

        $unit   = $fn->getReqParam('unit');
        $product_group_id   = $fn->getReqParam('product_group_id');
        $company_id   = $fn->getReqParam('company_id');

        $sqlUnit = "
        SELECT v.value
              ,v.value
        FROM valuelist v
        WHERE v.key_text = 'productUnit'
        ORDER BY v.value
        ";

        $sqlSupplier = "
        SELECT c.company_id
              ,c.company_name
        FROM company c
        WHERE c.category = 'Supplier'
        ORDER BY c.company_name
        ";

        if ($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator' ) {
            $sqlProductGroup = "
            SELECT product_group_id
                  ,title AS product_group_title
            FROM product_group
            ";
        }
        else{
            $sqlProductGroup = "
            SELECT pg.product_group_id
                  ,pg.title AS product_group_title
            FROM product_group pg
            LEFT JOIN product_group_staff pgs ON (pg.product_group_id = pgs.product_group_id)
            WHERE pg.product_group_id = pgs.product_group_id
            AND pgs.staff_id = {$_SESSION['staff_id']}
            ";
        }

        $product = "<input type='text' value='' id='title' class='text' name='title[]'>";

        $productGroup = "
        <select name='product_group_id[]'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlProductGroup, $product_group_id)}
        </select>
        ";

        $partNumber = "<input type='text' value='' id='part_number' class='text' name='part_number[]'>";
        $hsn = "<input type='text' value='' id='price' class='text' name='hsn[]'>";
        $listPrice = "<input type='text' value='' id='price' class='text' name='price[]'>";
        $priceFromSupplier = "<input type='text' value='' id='price_from_supplier' class='text' name='price_from_supplier[]'>";

        $uom = "
        <select name='unit[]'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlUnit, $unit)}
        </select>
        ";

        $supplier = "
        <select name='company_id[]'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $company_id)}
        </select>
        ";

        $newRow = "
        <a href='#' class='addRow button mb10'>Add Product</a>
        ";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$partNumber}</td>
            <td>{$hsn}</td>
            <td>{$productGroup}</td>
            <td class='supplier'>{$supplier}</td>
            <td class='uom'>{$uom}</td>
            <td>{$priceFromSupplier}</td>
            <td>{$listPrice}</td>
        </tr>
        ";

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
        <th>Product Title</th>
        <th>Part Number</th>
        <th>HSN</th>
        <th>Product Group</th>
        <th>Supplier</th>
        <th>UOM</th>
        <th>Price from Suppplier</th>
        <th>List Price</th>
        </tr>
        ";

        $text = "
        <form id='quickAddForm' class='' method='post' action='{$formAction}'>
            <table class='thinlist' id='productTable'>
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
    function getAddProductRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $unit   = $fn->getReqParam('unit');
        $product_group_id = $fn->getReqParam('product_group_id');
        $company_id = $fn->getReqParam('company_id');

        $sqlUnit = "
        SELECT v.value
              ,v.value
        FROM valuelist v
        WHERE v.key_text = 'productUnit'
        ORDER BY v.value
        ";

        $sqlSupplier = "
        SELECT c.company_id
              ,c.company_name
        FROM company c
        WHERE c.category = 'Supplier'
        ORDER BY c.company_name
        ";

        if ($_SESSION['userGroupType'] == 'Super Administrator' || $_SESSION['userGroupType'] == 'Administrator' ) {
            $sqlProductGroup = "
            SELECT product_group_id
                  ,title AS product_group_title
            FROM product_group
            ";
        }
        else{
            $sqlProductGroup = "
            SELECT pg.product_group_id
                  ,pg.title AS product_group_title
            FROM product_group pg
            LEFT JOIN product_group_staff pgs ON (pg.product_group_id = pgs.product_group_id)
            WHERE pg.product_group_id = pgs.product_group_id
            AND pgs.staff_id = {$_SESSION['staff_id']}
            ";
        }

        $product = "<input type='text' value='' id='title' class='text' name='title[]'>";

        $productGroup = "
        <select name='product_group_id[]'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlProductGroup, $product_group_id)}
        </select>
        ";

        $partNumber = "<input type='text' value='' id='part_number' class='text' name='part_number[]'>";
        $listPrice = "<input type='text' value='' id='price' class='text' name='price[]'>";
        $priceFromSupplier = "<input type='text' value='' id='price_from_supplier' class='text' name='price_from_supplier[]'>";

        $uom = "
        <select name='unit[]'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlUnit, $unit)}
        </select>
        ";

        $supplier = "
        <select name='company_id[]'>
            <option value=''>Please Select</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $company_id)}
        </select>
        ";

        $rows = "
        <tr>
            <td>{$product}</td>
            <td>{$partNumber}</td>
            <td>{$productGroup}</td>
            <td class='supplier'>{$supplier}</td>
            <td class='uom'>{$uom}</td>
            <td>{$priceFromSupplier}</td>
            <td>{$listPrice}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getProductPriceDetail($product_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $Product = $this->getProductPriceDetailList($product_id);

        $recCount = $fn->getRecordCount('product_price', "product_id = '{$product_id}'");

        $header ="
        <thead>
            <tr>
                <th>Date</th>
                <th>Price</th>
                <th>Product Weight(kg)</th>
                <th>GST %</th>
            </tr>
        </thead>
        ";

        $formActionProductPrice = "index.php?module=tradingsg_product&_spAction=AddProductPrice&product_id={$product_id}&showHTML=0";

        $add = '';
        if($cpCfg['cp.mrpProducts'] == 1){
            $add = "<div class='actBtns'>
                        <a id='AddProductPrice' href='{$formActionProductPrice}' product_id={$product_id}>Add</a>
                    </div>";
        }

        $text = "
        <div class='linkPortalWrapper tradingsg_product_productPriceLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Product Price Linked</div>
                    <div class='txtRight'>
                        <span class='count' id='AddProductPricePortalCount'>({$fn->getRecordCount('product_price', "product_id = '{$product_id}'")})</span>
                        <div class='toggle'></div>
                    </div>
                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='productPricelist'>
                        {$header}
                        <tbody id='AddProductPricePortal'>
                            {$Product}
                        </tbody>
                    </table>
                    <input type='hidden' name='product_id' value='{$product_id}' />
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
    function getProductPriceDetailList($product_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $rows  = "";

        $SQL="
        SELECT pp.price
              ,pp.created_by
              ,pp.creation_date
              ,pp.modified_by
              ,pp.modification_date
              ,pp.product_price_id
              ,pp.product_id
        FROM product_price pp
        WHERE product_id = '{$product_id}'
        ORDER BY pp.creation_date DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if($numRows == 0){
            $SQL="
            SELECT p.*
                  ,p.gst
                  ,p.product_weight
            FROM product p
            WHERE product_id = '{$product_id}'
            ";
            $result   = $db->sql_query($SQL);
        }

        $count = 1;
        $qty_balance = '';
        while ($row = $db->sql_fetchrow($result)) {
            $creation = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            $rows .= "
            <tr>
                <td>{$creation}</td>
                <td align='right'>{$row['price']}</td>
                <td align='right'>{$row['product_weight']}</td>
                <td>{$row['gst']}</td>
            </tr>
            ";
            $count++;
        }

        $text="{$rows}";

        return $text;
    }

    /**
     *
     */
    function getAddProductPrice() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $product_id = $fn->getReqParam('product_id');

        $productRec = $fn->getRecordRowByID('product', 'product_id', $product_id);

        $formAction = "index.php?_topRm=inventory&module=tradingsg_product&_spAction=AddProductPriceSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        if ($_SESSION['userGroupName'] != "Supplier") {
            $text = "
            <form id='AddProductPriceForm' class='AddProductPriceForm yform columnar' method='post' action='{$formAction}'>
                {$formObj->getTBRow('Price', 'price', $productRec['price'])}
                {$formObj->getTBRow('Product Weight(kg)', 'product_weight', $productRec['product_weight'])}
                {$formObj->getTBRow('GST %', 'gst', $productRec['gst'])}
                <input type='hidden' name='product_id' value='{$product_id}' />
            </form>
            ";
        } else{
            $text = "
            <form id='AddProductPriceForm' class='AddProductPriceForm yform columnar' method='post' action='{$formAction}'>
                {$formObj->getTBRow('Price', 'price', $productRec['price'])}
                {$formObj->getTBRow('Product Weight(kg)', 'product_weight', $productRec['product_weight'])}
                {$formObj->getTBRow('GST %', 'gst', $productRec['gst'])}
                <input type='hidden' name='product_id' value='{$product_id}' />
            </form>
            ";
        }
            //{$formObj->getTBRow('TP Commission(%)', 'tp_commission', $productRec['tp_commission'])}

        return $text;
    }

    /**
     *
     */

    /**
     *
     */
    function getUpdateStockHistoryFromPo() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        //http://cubobillpro.localhost/admin/index.php?_topRm=order&module=tradingsg_product&_spAction=updateStockHistoryFromPo&showHTML=0

        $SQL="
        SELECT p.*
        FROM po_product p
        LEFT JOIN purchase_order po ON (po.purchase_order_id = p.purchase_order_id)
        WHERE po.status != 'Cancelled'
        ";
        $result   = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            if($row['damage_qty'] == ''){
                $row['damage_qty'] = 0;
            }
            $fa = array();
            $fa['po_product_id'] = $row['po_product_id'];
            $fa['product_id']  = $row['product_id'];
            $fa['purchase_order_id']  = $row['purchase_order_id'];
            $fa['qty']  = $row['qty'];
            $fa['damage_qty']  = $row['damage_qty'];
            $fa['creation_date']  = $row['creation_date'];

            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_history');
            $resultInsert = $db->sql_query($SQLInsert);
        }

    }
}