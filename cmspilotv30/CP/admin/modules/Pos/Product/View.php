<?
class CP_Admin_Modules_Pos_Product_View extends CP_Common_Lib_ModuleViewAbstract
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
            $style= '';
            if ($cpCfg['prodEnableStyle'] == 1){
                $style = $listObj->getListDataCell($row['style']);
            }        
    
            $color= '';
            if ($cpCfg['prodEnableColor'] == 1){
                $color = $listObj->getListDataCell($row['color']);
            }        
    
            $size= '';
            if ($cpCfg['prodEnableSize'] == 1){
                $size = $listObj->getListDataCell($row['size']);
            }        
    
            $season= '';
            if ($cpCfg['prodEnableSeason'] == 1){
                $season = $listObj->getListDataCell($row['season']);
            }        
    
            $brand= '';
            if ($cpCfg['prodEnableBrand'] == 1){
                $brand = $listObj->getListDataCell($row['brand']);
            }        

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$style}
            {$color}
            {$size}
            {$season}
            {$brand}
            {$listObj->getListDataCell($row['currency'])}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['uom_code'])}
            {$listObj->getListDataCell($row['product_id'], 'center')}
            {$listObj->getListRowEnd($row['product_id'])}
            ";
            $rowCounter++;
        }

        $style= '';
        if ($cpCfg['prodEnableStyle'] == 1){
            $style = $listObj->getListHeaderCell('Style', 'p.style');
        }        

        $color= '';
        if ($cpCfg['prodEnableColor'] == 1){
            $color = $listObj->getListHeaderCell('Color', 'p.color');
        }        

        $size= '';
        if ($cpCfg['prodEnableSize'] == 1){
            $size = $listObj->getListHeaderCell('Size', 'p.size');
        }        

        $season= '';
        if ($cpCfg['prodEnableSeason'] == 1){
            $season = $listObj->getListHeaderCell('Season', 'p.season');
        }        

        $brand= '';
        if ($cpCfg['prodEnableBrand'] == 1){
            $brand = $listObj->getListHeaderCell('Brand', 'p.brand');
        }        

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$style}
        {$color}
        {$size}
        {$season}
        {$brand}
        {$listObj->getListHeaderCell('Currency', 'p.currency')}
        {$listObj->getListHeaderCell('Unit Price', 'p.price')}
        {$listObj->getListHeaderCell('UOM', 'p.uom_code')}
        {$listObj->getListHeaderCell('ID', 'p.product_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sqlCategory = $fn->getDDSQL('pos_category');        
        $sqlSubCategory = $fn->getDDSQL('pos_subCategory');
        
        $category= '';
        if ($cpCfg['category'] == 1){
            $category = $formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory);
        }        
        
        $subCategory= '';
        if ($cpCfg['subCategory'] == 1){
            $subCategory = $formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory);
        }        

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        {$category}
        {$subCategory}
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
        
        $sqlRegional = $fn->getValueListSQL('regional');
        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();
        $sqlUom = getCPModuleObj('pos_uom')->model->getUomCodeSQL();

        $sqlCategory = $fn->getDDSQL('pos_category');
        $expCategory = array('detailValue' => $row['category_title'], 'isEditable' => 0);
        
        $sqlSubCategory = '';
        if ($row['category_id'] != ''){
            $sqlSubCategory = $fn->getDDSQL('pos_subCategory', array('condn' => "category_id = {$row['category_id']}"));
        }        
        $expSubCategory = array('detailValue' => $row['sub_category_title'], 'isEditable' => 0);
        
        $category= '';
        if ($cpCfg['category'] == 1){
            $category = $formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory);
        }        
        
        $subCategory= '';
        if ($cpCfg['subCategory'] == 1){
            $subCategory = $formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory);
        }        
        
        $style= '';
        if ($cpCfg['prodEnableStyle'] == 1){
            $style = $formObj->getDDRowByVL('Style', 'style', 'style', $row['style']);
        }        

        $color= '';
        if ($cpCfg['prodEnableColor'] == 1){
            $color = $formObj->getDDRowByVL('Color', 'color', 'color', $row['color']);
        }        

        $size= '';
        if ($cpCfg['prodEnableSize'] == 1){
            $size = $formObj->getDDRowByVL('Size', 'size', 'size', $row['size']);
        }        

        $season= '';
        if ($cpCfg['prodEnableSeason'] == 1){
            $season = $formObj->getDDRowByVL('Season', 'season', 'season', $row['season']);
        }        

        $brand= '';
        if ($cpCfg['prodEnableBrand'] == 1){
            $brand = $formObj->getDDRowByVL('Brand', 'brand', 'brand', $row['brand']);
        }        

        $element= '';
        if ($cpCfg['prodEnableElement'] == 1){
            $element = $formObj->getDDRowByVL('Element', 'element', 'element', $row['element']);
        }        
        $expEdit = array('isEditable' => 0);

        /*{$formObj->getTBRow('SKU', 'sku', $row['sku'], $expEdit)}
        {$formObj->getTBRow('Barcode', 'bar_code', $row['bar_code'])}*/
        
        $fieldset1 = "
        {$formObj->getTBRow('Name', 'title', $row['title'])}
        {$formObj->getTBRow('Alias Name', 'alias_name', $row['alias_name'])}
        {$formObj->getTBRow('Tag Name', 'tag_name', $row['tag_name'])}
        {$formObj->getDDRowByVL('Status', 'status', 'productStatus', $row['status'])}
        {$category}
        {$subCategory}
        ";

        /*$fieldset2 = "
        {$style}
        {$color}
        {$size}
        {$season}
        {$brand}
        {$element}
        ";*/

        $fieldset3 = "
        {$formObj->getDDRowBySQL('Currency', 'currency', $sqlCurrency, $row['currency'], array('sqlType' => 'OneField'))}
        {$formObj->getTBRow('List Price', 'price', $row['price'])}
        {$formObj->getDDRowBySQL('UOM', 'uom_code', $sqlUom, $row['uom_code'], array('sqlType' => 'OneField'))}
        {$formObj->getDDRowByVL('Regional', 'regional', 'regional', $row['regional'])}
        {$formObj->getTBRow('Re-order Level', 'reorder_level', $row['reorder_level'])}
        {$formObj->getDateRow('Expiry Date From', 'expiry_date_from', $row['expiry_date_from'])}
        {$formObj->getDateRow('Expiry Date To', 'expiry_date_to', $row['expiry_date_to'])}
        {$formObj->getYesNoRRow('Redeeem', 'redeem', $row['redeem'])}
        {$formObj->getYesNoRRow('Fixed Price', 'fixed_price', $row['fixed_price'])}
        {$formObj->getYesNoRRow('Allow Discount', 'allow_discount', $row['allow_discount'])}
        {$formObj->getYesNoRRow('Allow Member Discount', 'allow_member_discount', $row['allow_member_discount'])}
        {$formObj->getYesNoRRow('Allow Package', 'allow_package', $row['allow_package'])}
        {$formObj->getYesNoRRow('Allow Gift', 'allow_gift', $row['allow_gift'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Product Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Price & Other', $fieldset3)}
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
        
        $text ="
        {$media->getRightPanelMediaDisplay('Picture', 'pos_product', 'picture', $row)}
        {$displayLinkData->getLinkPortalMain('pos_product', 'pos_productItemLink', 'Product Items', $row)}
        {$displayLinkData->getLinkPortalMain('pos_product', 'pos_shopLink', 'Shop Linked', $row)}        
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

        $company_id     = $fn->getReqParam('company_id');
        $special_search = $fn->getReqParam('special_search');
        $style     = $fn->getReqParam('style');
        $color     = $fn->getReqParam('color');
        $size      = $fn->getReqParam('size');
        $season    = $fn->getReqParam('season');
        $brand     = $fn->getReqParam('brand');
        $subCatOptions  = '';
        
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
        
        $styleFil= '';
        if ($cpCfg['prodEnableStyle'] == 1){
            $SQLStyle = $fn->getValuelistSql('style');
            $styleOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLStyle, $style);
            
            $styleFil ="
            <td class='fieldValue'>
                <select name='style'>
                    <option value=''>Style</option>
                    {$styleOptions}
                </select>
            </td>
            ";
        }        

        $colorFil= '';
        if ($cpCfg['prodEnableColor'] == 1){
            $SQLColor = $fn->getValuelistSql('color');
            $colorOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLColor, $color);
            
            $colorFil ="
            <td class='fieldValue'>
                <select name='color'>
                    <option value=''>Color</option>
                    {$colorOptions}
                </select>
            </td>
            ";
        }        

        $sizeFil= '';
        if ($cpCfg['prodEnableSize'] == 1){
            $SQLSize = $fn->getValuelistSql('size');
            $sizeOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLSize, $size);
            
            $sizeFil ="
            <td class='fieldValue'>
                <select name='size'>
                    <option value=''>Size</option>
                    {$sizeOptions}
                </select>
            </td>
            ";
        }        

        $seasonFil= '';
        if ($cpCfg['prodEnableSeason'] == 1){
            $SQLSeason = $fn->getValuelistSql('season');
            $seasonOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLSeason, $season);
            
            $seasonFil ="
            <td class='fieldValue'>
                <select name='season'>
                    <option value=''>Season</option>
                    {$seasonOptions}
                </select>
            </td>
            ";
        }        

        $brandFil= '';
        if ($cpCfg['prodEnableBrand'] == 1){
            $SQLBrand = $fn->getValuelistSql('brand');
            $brandOptions = $dbUtil->getDropDownFromSQLCols1($db, $SQLBrand, $brand);
            
            $brandFil ="
            <td class='fieldValue'>
                <select name='brand'>
                    <option value=''>Brand</option>
                    {$brandOptions}
                </select>
            </td>
            ";
        }        

        $text = "
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
        {$styleFil}
        {$colorFil}
        {$sizeFil}
        {$seasonFil}
        {$brandFil}
        ";

        
        return $text;
    }
}