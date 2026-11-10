<?
class CP_Admin_Modules_Tradingsg_Product_View extends CP_Common_Lib_ModuleViewAbstract
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
                $sortOrder = $listObj->getListSortOrderField($row, 'product_id');
            }

            $extaFlds = '';
            if (!$cpCfg['m.ecommerce.product.hasProductItem']){
                $extaFlds = "
                {$listObj->getListDataCell($row['qty_in_stock'])}
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['item_code'], 'center')}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['unit'])}
            {$sortOrder}
            {$listObj->getListDataCell($row['product_group_title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['company_records'])}
            {$listObj->getListQuotePublishedImage($row['general_quotation'], $row['product_id'])}
            {$listObj->getListDataCell($row['modified_by'] . ' ' . $row['modification_date'])}
            {$listObj->getListPublishedImage($row['published'], $row['product_id'])}
            {$listObj->getListRowEnd($row['product_id'])}
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

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Item Code', 'item_code' , 'headerCenter')}
        {$listObj->getListHeaderCell('Product Name', 'p.title')}
        {$listObj->getListHeaderCell('Buying Price', 'p.price')}
        {$listObj->getListHeaderCell('Unit', 'p.unit')}
        {$sortOrder}
        {$listObj->getListHeaderCell('Department', 'product_group_title')}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Supplier', 'co.company_name')}
        {$listObj->getListHeaderCell('Show GQ', 'p.general_quotation', 'headerCenter')}
        {$listObj->getListHeaderCell('Updated By', 'p.modified_by')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

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

        $favourite = '';
        if ($cpCfg['m.ecommerce.product.showFavourite'] == 1) {
            $favourite = $formObj->getYesNoRRow("Favourite", "favourite", $row['favourite']);
        }

        $priceText = '';
        if ($cpCfg['m.ecommerce.product.isCountryBased'] == 0) {
            $priceText = $formObj->getTBRow('<b>Buying Price *</b>', 'price', $row['price']);
        }

        $embedCode = '';
        if ($cpCfg['m.ecommerce.product.showEmbedCode'] == 1) {
            $embedCode = $formObj->getTARow('Embed Code', 'embed_code', $ln->gfv($row, 'embed_code', '0'));
        }

        $weight = '';
        if ($cpCfg['m.ecommerce.product.showWeight']) {
            $weight = $formObj->getTBRow("Shipping Weight in Grams", "weight_grams", $row['weight_grams']);
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
            $sqlCategory = "
            SELECT c.category_id
                  ,c.title
            FROM category c
            LEFT JOIN (product_group pg) ON (pg.product_group_id = c.product_group_id)
            WHERE c.product_group_id = {$row['product_group_id']}
            ORDER BY c.title
            ";
        }
        $expCategory = array('detailValue' => $row['category_title']);

        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $stockFld = '';
        $expNoEdit  = array('isEditable' => 0);
        if (!$cpCfg['m.ecommerce.product.hasProductItem']){
            $stockFld = "
            {$formObj->getTBRow('Quantity in Stock', 'qty_in_stock', $row['qty_in_stock'], $expNoEdit)}
            ";
        }

        if ($cpCfg['m.tradingsg.product.displayTradingmassProductName'] == 1){
		$validatedProduct =	"{$formObj->getTBRow('Product Name *', 'title', $ln->gfv($row, 'title', '0'))}
					        {$formObj->getDDRowBySQL('Product Group', 'product_group_id', $sqlProductGroup, $row['product_group_id'])}
        					{$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        					{$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
							";
		} else {

		$validatedProduct =	"{$formObj->getTBRow('Product Name *', 'title', $ln->gfv($row, 'title', '0'))}
					         {$formObj->getDDRowBySQL('Department *', 'product_group_id', $sqlProductGroup, $row['product_group_id'], $expProductGroup)}
       						 {$formObj->getDDRowBySQL('Category *', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
       						 {$formObj->getDDRowBySQL('Sub Category *', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
							 ";
		}

        $fielset1 = "
        {$formObj->getTBRow('Item Code', 'item_code', $row['item_code'], $expNoEdit)}
        {$validatedProduct}
        {$stockFld}
        ";

        $arrGst = array('useKey' => 1);
        $fieldset4 = "
        {$formObj->getDropDownRowByArray('GST type', 'gst_type', $cpCfg['m.tradingsg.product.gstType'], '', $arrGst)}
        {$priceText}
        {$formObj->getDDRowBySQL('Unit *', 'unit', $sqlUnit, $row['unit'], $expVl)}
        {$weight}
        {$formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'))}
        {$embedCode}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$latest}
        {$favourite}
        {$formObj->getYesNoRRow('General Quotation', 'general_quotation', $row['general_quotation'])}
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
        {$formObj->getFieldSetWrapped('Other Details', $fieldset4)}
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
            $url       = "index.php?module=ecommerce_product&_spAction=generateBulkVouchers&id={$row['product_id']}&showHTML=0";
            $printLink = "index.php?module=ecommerce_product&_spAction=printVoucher&id={$row['product_id']}&showHTML=0";

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
        {$media->getRightPanelMediaDisplay('Picture', 'tradingsg_product', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Related Picture', 'tradingsg_product', 'relatedPicture', $row)}
        {$displayLinkData->getLinkPortalMain('tradingsg_product', 'tradingsg_companyLink', 'Supplier Linked', $row)}
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
        $catOptions  = '';

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


        if ($product_group_id != "") {
            $SQLCat = "
            SELECT a.category_id
                  ,a.title
            FROM category a
            LEFT JOIN (product_group b) ON (a.product_group_id  = b.product_group_id)
            WHERE b.product_group_id = {$product_group_id}
            ORDER BY a.title
            ";
            $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);
        }

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
        <td>
            <select class='w125' name='general_quotation'>
                <option value=''>Show GQ</option>
                {$cpUtil->getDropDown1($generalQuoteArray, $general_quotation)}
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
}