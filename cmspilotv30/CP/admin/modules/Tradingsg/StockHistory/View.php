<?
class CP_Admin_Modules_Tradingsg_StockHistory_View extends CP_Common_Lib_ModuleViewAbstract
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

            if($cpCfg['m.ecommerce.product.hasSortOrderFld']){
                $sortOrder = $listObj->getListSortOrderField($row, 'stock_history_id');
            }

            $extaFlds = '';
            if (!$cpCfg['m.ecommerce.product.hasProductItem']){
                $extaFlds = "
                {$listObj->getListDataCell($row['qty_in_stock'])}
                ";
            }

			$url = "index.php?_topRm=inventory&module=tradingsg_supplierQuote&_action=detail&supplier_quote_id={$row['supplier_quote_id']}";
            $supplierQuoteTitle = "
            <a href='{$url}' id=''>{$row['supplier_quote_title']}</a>
            ";

            $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y H:i:s');

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($creation_date)}
            {$listObj->getGoToDetailText($rowCounter, $row['product_title'])}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['qty'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['product_group_title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$sortOrder}
            {$listObj->getListDataCell($supplierQuoteTitle)}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['stock_history_id'])}
            ";
            $rowCounter++;
        }


        $sortOrder = '';
        if($cpCfg['m.ecommerce.product.hasSortOrderFld']){
            $sortOrder = $listObj->getListSortOrderImage('p');
        }

        $extaFlds = '';
        if (!$cpCfg['m.ecommerce.product.hasProductItem']){
            $extaFlds = "
            {$listObj->getListHeaderCell('Stock', 'p.qty_in_stock')}
            ";
        }

        $quantitySQL = "
        SELECT SUM(qty)
        FROM stock_history sh
        WHERE status != 'Updated'
        ";
        $quantityResult = $db->sql_query($quantitySQL);
		$quantityrow = $db->sql_fetchrow($quantityResult);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Date', 'creation_date')}
        {$listObj->getListHeaderCell('Product Name', 'product_title')}
        {$listObj->getListHeaderCell('Price', 'price')}
        {$listObj->getListHeaderCell('Quantity', 'qty')}
        {$listObj->getListHeaderCell('Supplier', 'co.company_name')}
        {$listObj->getListHeaderCell('Group', 'product_group_title')}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$sortOrder}
        {$listObj->getListHeaderCell('Supplier Quote Title', 'supplier_quote_title')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getListFooter() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $db = Zend_Registry::get('db');

        $text = "";

        $mode = ($tv['spAction'] == 'link') ? 'link' : '';

        $modOpp = getCPModuleObj('project_opportunity');
        $SQLSum  ="
	        SELECT
			SUM(qty) AS total
			FROM stock_history
            WHERE status != 'Updated'
        ";
        $resSum = $db->sql_query($SQLSum);
        $row = $db->sql_fetchrow($resSum);
        $total = $row['total'];

        $text = "
        </tbody>
        <tfoot>
            <tr class='header'  background='{$cpCfg['cp.masterImagesPathAlias']}body/header_bg.jpg'>
               <td class='header' colspan='5'></td>
               <td class='header' style='text-align:left'>{$total}</td>
               <td class='header' colspan='8'></td>
            </tr>
            <input type='hidden' name='boxChecked' value='0' />
        </tfoot>
        </table>
        ";
        $text = "";

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

        $latest = '';
        if ($cpCfg['m.ecommerce.product.showLatest'] == 1) {
            $latest = $formObj->getYesNoRRow("Latest", "latest", $row['latest']);
        }

        $priceText = '';
        if ($cpCfg['m.ecommerce.product.isCountryBased'] == 0) {
            $priceText = $formObj->getTBRow('<b>BC</b>', 'price', $row['price']);
        }


		$sqlProductGroup = "
		SELECT product_group_id
			  ,title
		FROM product_group
		";
        $expProductGroup = array('detailValue' => $row['product_group_title']);

        $sqlCategory = '';
        if ($row['product_group_id'] != ''){
            $modCat = getCPModuleObj('webBasic_category');
            $sqlCategory = $modCat->model->getCategorySQLByType('Product');
        }
        $expCategory = array('detailValue' => $row['category_title']);

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $stockFld = '';
        if (!$cpCfg['m.ecommerce.product.hasProductItem']){
            $stockFld = "
            {$formObj->getTBRow('Quantity in Stock', 'qty_in_stock', $row['qty_in_stock'])}
            ";
        }
        $expNoEdit  = array('isEditable' => 0);

        if ($cpCfg['m.tradingsg.product.displayTradingmassProductName'] == 1){
		$validatedProduct =	"{$formObj->getTBRow('Product Name *', 'product_title', $ln->gfv($row, 'product_title', '0'))}
					        {$formObj->getDDRowBySQL('Department *', 'product_group_id', $sqlProductGroup, $row['product_group_id'], $expProductGroup)}
        					{$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        					{$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
							";
		} else {

		$validatedProduct =	"{$formObj->getTBRow('Product Name *', 'product_title', $ln->gfv($row, 'product_title', '0'))}
					         {$formObj->getDDRowBySQL('Department *', 'product_group_id', $sqlProductGroup, $row['product_group_id'], $expProductGroup)}
       						 {$formObj->getDDRowBySQL('Category *', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
       						 {$formObj->getDDRowBySQL('Sub Category *', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
							 ";
		}


        $fielset1 = "
        {$validatedProduct}
        {$formObj->getTBRow('Item Code', 'item_code', $row['item_code'], $expNoEdit)}
        {$priceText}
        {$formObj->getTBRow('Quantity', 'Quantity', $row['qty'], $expNoEdit)}
        {$formObj->getDDRowBySQL('Unit', 'unit', $sqlUnit, $row['unit'], $expVl)}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$latest}
        ";

        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $fieldset3 = '';

        if($cpCfg['m.ecommerce.product.showDescription2']){
            $fieldset3 = "
            {$formObj->getFieldSetWrapped('Description 2',
             $formObj->getHTMLEditor('Description 2', 'description2', $ln->gfv($row, 'description2', '0'))
            )}
            ";
        }

        $text = "
        {$formObj->getFieldSetWrapped('Product Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$fieldset3}
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

        $links = '';

        if ($cpCfg['m.ecommerce.product.hasRelatedProduct'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('ecommerce_product', 'ecommerce_productLink', 'Related Products', $row);
        }

        if ($cpCfg['m.ecommerce.product.hasVoucherHistory']){
            $url       = "index.php?module=ecommerce_product&_spAction=generateBulkVouchers&id={$row['stock_history_id']}&showHTML=0";
            $printLink = "index.php?module=ecommerce_product&_spAction=printVoucher&id={$row['stock_history_id']}&showHTML=0";

            $links .= "
            <div class='floatbox'>
                <div class='float_right'>
                    <a href='{$url}' id='bulkAddVouchers'>Bulk Generate</a>
                </div>
                <div class='float_right'>
                    <a href='{$printLink}' id='printVoucher' target='_blank'>Print Voucher &nbsp;|</a>
                </div>
            </div>
            {$displayLinkData->getLinkPortalMain('ecommerce_product', 'ecommerce_productVoucherLink', 'Product Voucher Link', $row)}
            ";
        }

        if ($cpCfg['m.ecommerce.product.hasProductItem'] == 1){
            $links .= $displayLinkData->getLinkPortalMain('ecommerce_product', 'ecommerce_productItemLink', 'Product Item', $row);
        }

        if ($cpCfg['m.ecommerce.product.hasCountry'] == 1){
            //$links .= "
            //{$displayLinkData->getLinkPortalMain('ecommerce_product', 'country', 'Countries', $row)}
            //{$productItem}
            //";
        }

        if ($cpCfg['m.ecommerce.product.hasContentHistory'] == 1){
            //$links .= $displayLinkData->getLinkPortalMain('ecommerce_product', 'webBasic_contentHistoryLink', 'Content History', $row);
        }

        $text ="
        {$links}
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

        //$sqlProductGroup = $fn->getDDSql('tradingsg_productGroup');
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


        $SQLCat = "
        SELECT a.category_id
              ,a.title
        FROM category a
        LEFT JOIN (section b) ON (a.section_id  = b.section_id)
        WHERE b.section_type ='Product'
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

        $text = "
        <td>
            <select name='supplier_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
            </select>
        </td>
        <td>
            <select name='product_group_id'>
                <option value=''>Product Group</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlProductGroup, $product_group_id)}
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
        <td class='fieldValue'>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($cpCfg['m.ecommerce.product.btnPosArr'], $special_search)}
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
        SELECT stock_history_id
              ,item_code
        FROM stock_history
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
            WHERE stock_history_id = {$row['stock_history_id']}
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);

            $count++;
        }
    }
}