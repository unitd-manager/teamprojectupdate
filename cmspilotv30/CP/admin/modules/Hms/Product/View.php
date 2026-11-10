<?
class CP_Admin_Modules_Hms_Product_View extends CP_Common_Lib_ModuleViewAbstract
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

            $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
            $sqlProduct = "
            SELECT price
            FROM product_price
            WHERE site_id = {$cpSiteIdSession}
            AND product_id = {$row['product_id']}
            ";
            $resultproduct = $db->sql_query($sqlProduct);
            $rowproduct = $db->sql_fetchrow($resultproduct);

            $item_code = '';
            if($row['item_code'] != ''){
                $item_code ='PROD - '.$row['item_code'];
            }

            $SQLStockTransfer = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$cpSiteIdSession}";

            $resultStockTransfer = $db->sql_query($SQLStockTransfer);
            $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


            $SQLStockTransferto = "
            SELECT  st.from_location
                    ,st.to_location
                    ,sh.product_id
                    ,SUM(sh.qty) AS Transfer_qty_to
            FROM stock_transfer st
            LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
            WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$cpSiteIdSession}";

            $resultStockTransferto = $db->sql_query($SQLStockTransferto);
            $rowStockTransferto = $db->sql_fetchrow($resultStockTransferto);

            $SQLOthersite = "
            SELECT
                (SELECT SUM(qty) FROM po_product pp
                 LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
                 WHERE pp.product_id = {$row['product_id']} AND po.site_id = {$cpSiteIdSession}
                 ) as product_qty_purchased

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

            $SqlExpenseProduct = "
            SELECT SUM(ep.qty) AS qty
            FROM expense_product ep
            LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
            WHERE ep.product_id = {$row['product_id']}
            AND ep.status = 'Added'
            AND e.site_id = {$cpSiteIdSession}
            AND ep.stock_deducted = 1
            ";
            $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
            $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

            $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowothersite['damaged_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($item_code, 'center')}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($stock)}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($rowproduct['price'])}
            {$listObj->getListQuotePublishedImage($row['general_quotation'], $row['product_id'])}
            {$listObj->getListDataCell($row['modified_by'] . ' ' . $row['modification_date'])}
            {$listObj->getListPublishedImage($row['published'], $row['product_id'])}
            {$listObj->getListRowEnd($row['product_id'])}
            ";
            $rowCounter++;
        }


        $sortOrder = $listObj->getListSortOrderImage('p');

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Item Code', 'item_code' , 'headerCenter')}
        {$listObj->getListHeaderCell('Product Name', 'p.title')}
        {$listObj->getListHeaderCell('Qty in Stock', '')}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('List Price', '')}
        {$listObj->getListHeaderCell('Show GQ', 'p.general_quotation', 'headerCenter')}
        {$listObj->getListHeaderCell('Updated By', 'p.modified_by')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";
        //{$this->getUpdateProductItemCodeNumber()}

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
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

        $expNoEdit = array('isEditable' => 0);
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        
        $sqlProduct = "
        SELECT price
        FROM product_price
        WHERE site_id = {$cpSiteIdSession}
        AND product_id = {$row['product_id']}
        ";
        $resultproduct = $db->sql_query($sqlProduct);
        $rowproduct = $db->sql_fetchrow($resultproduct);

        //$priceText = '';
        //if ($cpCfg['m.hms.product.isCountryBased'] == 0) {
            $priceText = $formObj->getTBRow('<b>List Price</b>', 'price', $rowproduct['price'], $expNoEdit);
        //}


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

        $SQLStockTransfer = "
        SELECT  st.from_location
                ,st.to_location
                ,sh.product_id
                ,SUM(sh.qty) AS Transfer_qty
        FROM stock_transfer st
        LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
        WHERE sh.product_id = {$row['product_id']} AND st.from_location = {$cpSiteIdSession}";

        $resultStockTransfer = $db->sql_query($SQLStockTransfer);
        $rowStockTransfer = $db->sql_fetchrow($resultStockTransfer);


        $SQLStockTransferto = "
        SELECT  st.from_location
                ,st.to_location
                ,sh.product_id
                ,SUM(sh.qty) AS Transfer_qty_to
        FROM stock_transfer st
        LEFT JOIN stock_transfer_history sh ON (sh.stock_transfer_id = st.stock_transfer_id)
        WHERE sh.product_id = {$row['product_id']} AND st.to_location = {$cpSiteIdSession}";

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

        $SqlExpenseProduct = "
        SELECT SUM(ep.qty) AS qty
        FROM expense_product ep
        LEFT JOIN expense e ON (e.expense_id = ep.expense_id)
        WHERE ep.product_id = {$row['product_id']}
        AND ep.status = 'Added'
        AND e.site_id = {$cpSiteIdSession}
        AND ep.stock_deducted = 1
        ";
        $resultExpenseProduct = $db->sql_query($SqlExpenseProduct);
        $rowExpenseProduct    = $db->sql_fetchrow($resultExpenseProduct);

        $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_quote'] + $rowothersite['sales_return_qty'] - $rowStockTransfer['Transfer_qty'] + $rowStockTransferto['Transfer_qty_to'] - $rowExpenseProduct['qty'];
        $stockFld = '';
        //if (!$cpCfg['m.hms.product.hasProductItem']){
            $stockFld = "
            {$formObj->getTBRow('Quantity in Stock', 'qty_in_stock', $stock, $expNoEdit)}
            ";
        //}
        $expNoEdit  = array('isEditable' => 0);

        $validatedProduct = "{$formObj->getTBRow('Product Name *', 'title', $ln->gfv($row, 'title', '0'))}
                             {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
                             {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
                            ";

        $item_code = '';
        if($row['item_code'] != ''){
            $item_code ='PROD - '.$row['item_code'];
        }
            
        $fielset1 = "
        {$formObj->getTBRow('Item Code', 'item_code', $item_code, $expNoEdit)}
        {$validatedProduct}
        {$stockFld}
        {$priceText}
        {$formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getYesNoRRow('General Quotation', 'general_quotation', $row['general_quotation'])}
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

    //==================================================================//
    function getRightPanel($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');

        $text ="
        <div id='productPriceLinkPortal'>
            {$this->getProductPriceDetail($row['product_id'])}
        </div>
        <div id='productPriceHistoryLinkPortal'>
            {$this->getProductPriceHistory($row['product_id'])}
        </div>
        {$media->getRightPanelMediaDisplay('Picture', 'hms_product', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Related Picture', 'hms_product', 'relatedPicture', $row)}
        ";
        return $text;
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
                <th>Site</th>
                <th>Price</th>
                <th>Edit</th>
                <th>Created / Modified</th>
            </tr>
        </thead>
        ";

        $formActionProductPrice = "index.php?module=hms_product&_spAction=AddProductPrice&product_id={$product_id}&showHTML=0";

        $add = "<div class='actBtns'>
                    <a id='AddProductPrice' href='{$formActionProductPrice}' product_id={$product_id}>Add</a>
                </div>";

        $text = "
        <div class='linkPortalWrapper hms_product_productPriceLink'>
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
              ,s.title AS site_name
        FROM product_price pp
        LEFT JOIN (site s) ON (s.site_id = pp.site_id)
        WHERE product_id = '{$product_id}'
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $qty_balance = '';
        while ($row = $db->sql_fetchrow($result)) {

            $creation = $row['created_by'].' '.$row['creation_date'];
            if($row['modification_date']){
                $creation = $row['modified_by'].' '.$row['modification_date'];
            }

            $formEditProductPrice  = "index.php?_topRm=inventory&module=hms_product&_spAction=editProductPrice&product_price_id={$row['product_price_id']}&showHTML=0";
            $editPriceRecordLink   = "<a class='EditProductPrice' href='{$formEditProductPrice}' product_price_id='{$row['product_price_id']}' product_id='{$row['product_id']}'><u>Edit</u></a>";
            
            $rows .= "
                <tr>
                    <td>{$row['site_name']}</td>
                    <td>{$row['price']}</td>
                    <td>{$editPriceRecordLink}</td>   
                    <td>{$creation}</td>  
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noProductPrice' colspan='4'><font>No Records Linked</font></td>
                </tr>
            ";

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

        $sqlSite = "
        SELECT site_id
              ,title
        FROM site
        ORDER BY title
        ";

        $formAction = "index.php?_topRm=inventory&module=hms_product&_spAction=AddProductPriceSubmit&showHTML=0";

        $text = "
        <form id='AddProductPriceForm' class='AddProductPriceForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Site', 'site_id', $sqlSite)}
            {$formObj->getTBRow('Price', 'price', '')}
            <input type='hidden' name='product_id' value='{$product_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditProductPrice() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        $product_price_id = $fn->getReqParam('product_price_id');

        $sqlProduct = "
        SELECT price
              ,site_id
        FROM product_price
        WHERE product_price_id = {$product_price_id}
        ";
        $resultproduct = $db->sql_query($sqlProduct);
        $rowproduct = $db->sql_fetchrow($resultproduct);

        $sqlSite = "
        SELECT site_id
              ,title
        FROM site
        ORDER BY title
        ";

        $expNoEdit = array('disabled' => 1);
        $formAction = "index.php?_topRm=inventory&module=hms_product&_spAction=EditProductPriceSubmit&showHTML=0";

        $text = "
        <form id='EditProductPriceForm' class='AddProductPriceForm yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Site', 'site_id', $sqlSite, $rowproduct['site_id'], $expNoEdit)}
            {$formObj->getTBRow('Price', 'price', $rowproduct['price'])}
            <input type='hidden' name='product_price_id' value='{$product_price_id}' />
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getProductPriceHistory($product_id=''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($product_id == ''){
            $product_id = $fn->getReqParam('product_id');
        }

        $Product = $this->getProductPriceHistoryList($product_id);

        $header ="
        <thead>
            <tr>
                <th>Site</th>
                <th>Date</th>
                <th>Price</th>
                <th>Created / Modified</th>
            </tr>
        </thead>
        ";

        $text = "
        <div class='linkPortalWrapper hms_product_productPriceLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>Product Price History</div>
                    <div class='txtRight'>
                        <span class='count' id='AddProductPricePortalCount'>
                            ({$fn->getRecordCount('product_price_history', "product_id = '{$product_id}'")})
                        </span>
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
        </div>
        ";

        return $text;

    }

    /**
     *
     */
    function getProductPriceHistoryList($product_id = ''){
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
              ,s.title AS site_name
        FROM product_price_history pp
        LEFT JOIN (site s) ON (s.site_id = pp.site_id)
        WHERE product_id = '{$product_id}'
        ORDER BY creation_date DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $count = 1;
        $qty_balance = '';
        while ($row = $db->sql_fetchrow($result)) {

            $creation = $row['created_by'].' '.$row['creation_date'];
            if($row['modification_date']){
                $creation = $row['modified_by'].' '.$row['modification_date'];
            }

            $creation_date = $fn->getCPDate($row['creation_date'], 'Y-m-d');
            
            $rows .= "
                <tr>
                    <td>{$row['site_name']}</td>
                    <td>{$creation_date}</td>
                    <td>{$row['price']}</td>
                    <td>{$creation}</td>  
                </tr>
            ";
            $count++;
        }


        if($numRows == 0){
            $rows .= "
                <tr>
                    <td class='noProductPrice' colspan='4'><font>No Records Linked</font></td>
                </tr>
            ";

        }

        $text="{$rows}";

        return $text;
    }

    //==================================================================//
    /**
     *
     * @return <type>
     */
    function getProductCountryLinkSQLxxx($id) {
        $SQL = "
        SELECT c.product_country_id
        	   ,sb.description
        FROM bank sb
        WHERE sb.company_id = {$id}
        ";

        return $SQL;
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
        $category_id         = $fn->getReqParam('category_id');
        $general_quotation   = $fn->getReqParam('general_quotation');
        $subCatOptions  = '';

        $modCat = getCPModuleObj('webBasic_category');
        $sqlCategory = $modCat->model->getCategorySQLByType('Product');
        $catOptions  = '';

        $sqlSupplier = "
        SELECT c.medical_supplier_id
        	  ,c.title
        FROM medical_supplier c
        ORDER BY c.title
        ";


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

        /*<td>
            <select name='supplier_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
            </select>
        </td>*/

        $text = "
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCategory, $category_id)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='sub_category_id'>
                <option value=''>Sub Category</option>
                {$subCatOptions}
            </select>
        </td>
        <td>
            <select class='w125' name='general_quotation'>
                <option value=''>Show GQ</option>
                {$cpUtil->getDropDown1($generalQuoteArray, $general_quotation)}
           </select>
        </td>
        ";


        return $text;
    }

    /**
     *
     */
    function getUpdateProductItemCodeSQL() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT product_id
              ,item_code
        FROM product
        ";
        $result = $db->sql_query($SQL);
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {

            /*if ($row['item_code'] != '' || $row['item_code'] != 0){
                $item_code_arr = explode('-', $row['item_code']);
                $item_code_no = $item_code_arr[1];
                $item_code = $fn->getSettingsValueByKey('productCodePrefix') . ($item_code_no + $count);
            } else {*/
                $item_code = $fn->getSettingsValueByKey('productCodePrefix') . ($fn->getSettingsValueByKey('nextProductItemCode') + $count);
            //}

            $sqlUpdate = "
            UPDATE product set item_code = '{$item_code}'
            WHERE product_id = {$row['product_id']}
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);

            $count++;
        }
    }

    /**
     *
     */
    function getUpdateProductItemCodeNumber(){
        $db = Zend_Registry::get('db');
        set_time_limit(50000);

        $SQL = "
        SELECT product_id
        FROM product
        ORDER BY product_id
        ";
        $result = $db->sql_query($SQL);
        $count = 10001;

        while ($row = $db->sql_fetchrow($result)) {
            $SQLUpdate    = "
            UPDATE product
            set item_code = {$count}
            WHERE product_id = {$row['product_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);

            $count++;
        }

        $SQL = "
        SELECT product_id, item_code
        FROM product
        ORDER BY product_id
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $SQLUpdate    = "
            UPDATE order_item
            set item_code = {$row['item_code']}
            WHERE record_id = {$row['product_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);

            $SQLUpdateII    = "
            UPDATE invoice_item
            set item_code = {$row['item_code']}
            WHERE record_id = {$row['product_id']}
            ";
            $resultUpdateII = $db->sql_query($SQLUpdateII);
        }
    }

}