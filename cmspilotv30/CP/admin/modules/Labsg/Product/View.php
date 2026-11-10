<?
class CP_Admin_Modules_Labsg_Product_View extends CP_Common_Lib_ModuleViewAbstract
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

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['item_code'], 'center')}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['price'])}
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
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Product Name', 'p.title')}
        {$listObj->getListHeaderCell('List Price', 'p.price')}
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


        //$priceText = '';
        //if ($cpCfg['m.labsg.product.isCountryBased'] == 0) {
            $priceText = $formObj->getTBRow('<b>List Price</b>', 'price', $row['price']);
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

        $StockSql = "
        SELECT
            (SELECT SUM(qty) FROM po_product
            WHERE product_id = {$row['product_id']}) as product_qty_purchased
            ,(SELECT SUM(oi.qty) FROM order_item oi
            LEFT JOIN (`order` o) ON (o.order_id = oi.order_id)
            WHERE record_id = {$row['product_id']}
              AND o.order_status = 'Paid'
              AND o.record_type = 'POS'
            ) as product_qty_sold_pos
            ,(SELECT SUM(invItem.qty) FROM invoice_item invItem
            LEFT JOIN (invoice inv) ON (inv.invoice_id = invItem.invoice_id AND inv.status != 'Cancelled' )
            LEFT JOIN (`order` o) ON (o.order_id = inv.order_id)
            WHERE record_id = {$row['product_id']}
              AND o.record_type != 'POS'
              AND o.link_stock = 1
            ) as product_qty_sold_from_quote
            ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            WHERE ini.record_id = {$row['product_id']}
              AND srh.status IS NULL
            ) as sales_return_qty
        ";
        $resultStockSql = $db->sql_query($StockSql);
        $rowStockSql    = $db->sql_fetchrow($resultStockSql);

        $stock = $rowStockSql['product_qty_purchased']- $rowStockSql['product_qty_sold_pos'] - $rowStockSql['product_qty_sold_from_quote'] + $rowStockSql['sales_return_qty'];

        $stockFld = '';
        //if (!$cpCfg['m.labsg.product.hasProductItem']){
            $stockFld = "
            {$formObj->getTBRow('Quantity in Stock', 'qty_in_stock', $stock, $expNoEdit)}
            ";
        //}
        $expNoEdit  = array('isEditable' => 0);

        $validatedProduct = "{$formObj->getTBRow('Product Name *', 'title', $ln->gfv($row, 'title', '0'))}
                            {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
                            {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
                            ";


        $fielset1 = "
        {$formObj->getTBRow('Item Code', 'item_code', $row['item_code'], $expNoEdit)}
        {$validatedProduct}
        {$stockFld}
        {$priceText}
        {$formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'))}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        //$fieldset3 = '';

        /*if($cpCfg['m.labsg.product.showDescription2']){
            $fieldset3 = "
            {$formObj->getFieldSetWrapped('Description 2',
             $formObj->getHTMLEditor('Description 2', 'description2', $ln->gfv($row, 'description2', '0'))
            )}
            ";
        {$fieldset3}

        }*/

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
        {$media->getRightPanelMediaDisplay('Picture', 'labsg_product', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Related Picture', 'labsg_product', 'relatedPicture', $row)}
        ";
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
        $product_group_id	 = $fn->getReqParam('product_group_id');
        $general_quotation   = $fn->getReqParam('general_quotation');
        $subCatOptions  = '';
        $catOptions  = '';

        //$sqlProductGroup = $fn->getDDSql('labsg_productGroup');
        $sqlProductGroup = "
        SELECT a.product_group_id
              ,a.title
        FROM product_group a
        ORDER BY a.product_group_id
        ";

        $sqlSupplier = "
        SELECT c.company_id
        	  ,c.company_name
        FROM company c
        WHERE c.category = 'Supplier'
        ORDER BY c.company_name
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


        $text = "
        <td>
            <select name='supplier_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$catOptions}
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