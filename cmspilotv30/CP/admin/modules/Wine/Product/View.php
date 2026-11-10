<?

class CP_Admin_Modules_Wine_Product_View extends CP_Common_Lib_ModuleViewAbstract {

    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $ln = Zend_Registry::get('ln');

        $rowCounter = 0;
        $rows = '';

        foreach ($dataArray as $row) {

            $description_short = '';
            if ($row['description_short'] != '') {
                $description_short = 'OK';
            } else {
                $description_short = 'NOT-OK';
            }

            $description = '';
            if ($row['description'] != '') {
                $description = 'OK';
            } else {
                $description = 'NOT-OK';
            }
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['product_code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['color'])}
            {$listObj->getListDataCell($row['grape'])}
            {$listObj->getListDataCell($row['vintage'])}
            {$listObj->getListDataCell($row['country_name'])}
            {$listObj->getListDataCell($row['region_name'])}
            {$listObj->getListDataCell($row['appellation_title'])}
            {$listObj->getListDataCell($row['producer_title'])}
            {$listObj->getListDataCell($row['bottle_size'])}
            {$listObj->getListDataCell($row['brand_code'])}
            {$listObj->getListDataCell($description_short)}
            {$listObj->getListDataCell($description)}
            {$listObj->getListDataCell($row['picture_ref'])}
            {$listObj->getListPublishedImage($row['published'], $row['product_id'])}
            {$listObj->getListRowEnd($row['product_id'])}
            ";
            $rowCounter++;
        }
        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.productCode', 'Item Code'), 'p.product_code')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.title', 'Title'), 'p.title')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.category', 'Category'), 'c.title')}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.subCategory', 'Sub Category'), 'sc.title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.color', 'Color'), 'p.color')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.grape', 'Grape'), 'p.grape')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.vintage', 'Vintage'), 'p.vintage')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.country', 'Country'), 'country_name')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.region', 'Region'), 'gr.name')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.appelation', 'Appelation'), 'appellation_title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.producer', 'Producer'), 'producer_title')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.bottleSize', 'Bottle Size'), 'p.bottle_size')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.brandCode', 'Brand Code'), 'p.brand_code')}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.shortDescription', 'Short Desc'))}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.longDescription', 'Long Desc'))}
        {$listObj->getListHeaderCell($ln->gd('m.wine.header.product.lbl.picRef', 'Pic Ref'))}
        {$listObj->getListHeaderCell($ln->gd('cp.header.lbl.published', 'Published'), 'p.published', 'headerCenter')}
    	{$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";
        return $text;
    }

    /**
     *
     */
    function getNew() {
        $formObj = Zend_Registry::get('formObj');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');

        $sqlCategory = "
        SELECT c.category_id
              ,c.title
        FROM category c
        LEFT JOIN (section s) ON (s.section_id  = c.section_id)
        WHERE s.section_type ='Product'
           OR s.section_type ='Gifts & Accessories'
        ORDER BY FIELD(s.section_type, 'Product', 'Gifts & Accessories'), c.section_id, c.title
        ";        
        
        $row = $db->sql_fetchrow($sqlCategory);
        $expCategory = array('detailValue' => $row['category_title']);

        $fieldset = "
        {$formObj->getTBRow($ln->gd('cp.lbl.title', 'Title'), 'title')}
        {$formObj->getDDRowBySQL($ln->gd('cp.lbl.category', 'Category'), 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getTBRow($ln->gd('m.wine.product.lbl.productCode', 'Item Code'), 'product_code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $formObj->mode = $tv['action'];

        $exp = array('sqlType' => 'OneField');

        //$sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $sqlCountry = "
        SELECT gc.country_code
              ,gc.name
        FROM geo_country gc
        -- WHERE gc.ok_for_wine = 1
        ORDER BY gc.name
        ";

        $expCountry = array('detailValue' => $row['country_name']);

        $sqlColor = $fn->getValueListSQL('wineColor');
        $sqlGrape = $fn->getValueListSQL('wineGrape');
        $sqlProducer = $fn->getDDSql('wine_producer');
        $sqlBottleSize = $fn->getValueListSQL('wineBottleSize');

        $expApp = array('detailValue' => $row['appellation_title']);
        $expProducer = array('detailValue' => $row['producer_title']);

        $latest = '';
        if ($cpCfg['m.wine.product.showLatestProduct'] == 1) {
            $latest = $formObj->getYesNoRRow($ln->gd('cp.lbl.latest', "Latest"), "latest", $row['latest']);
        }

        $metaData = '';
        if ($cpCfg['m.wine.product.showMetaData'] == 1) {
            $metaData .= $formObj->getMetaData($row);
        }

        $expRegion = array('detailValue' => $row['region_name']);
        $sqlRegion = ($row['country_code'] != '') ? $fn->getDDSql('common_region', array('condn' => "country_code = '{$row['country_code']}'")) : '';
        $sqlAppellation = ($row['region_id'] != '') ? $fn->getDDSql('wine_appellation', array('condn' => "region_id = {$row['region_id']}")) : '';

        $expCode = ($row['product_code'] != '') ? array('isEditable' => $cpCfg['m.wine.product.codeEditable']) : '';

        $sqlCategory = "
        SELECT c.category_id
              ,c.title
        FROM category c
        LEFT JOIN (section s) ON (s.section_id  = c.section_id)
        WHERE s.section_type ='Product'
           OR s.section_type ='Gifts & Accessories'
        ORDER BY c.section_id, c.title
        "; 
        $expCategory = array('detailValue' => $row['category_title'], 'isEditable' => 0);

        $sqlSubCategory = '';
        if ($row['category_id'] != '') {
            $modSubCat = getCPModuleObj('webBasic_subCategory');
            $sqlSubCategory = $modSubCat->model->getSubCategorySQL($row['category_id']);
        }
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $expBrandCode = '';
        if ($row['category_type'] == 'Wine') {
            $expBrandCode = array('isEditable' => 0);
            $country = $formObj->getDDRowBySQL($ln->gd('cp.lbl.country', 'Country'), 'country_code', $sqlCountry, $row['country_code'], $expCountry);
            $region = $formObj->getDDRowBySQL($ln->gd('m.wine.product.lbl.region', 'Region'), 'region_id', $sqlRegion, $row['region_id'], $expRegion);
            $appellation = $formObj->getDDRowBySQL($ln->gd('m.wine.product.lbl.appelation', 'Appellation'), 'appellation_id', $sqlAppellation, $row['appellation_id'], $expApp);
            $color = $formObj->getDDRowBySQL($ln->gd('m.wine.product.lbl.color', 'Color'), 'color', $sqlColor, $row['color'], $exp);
            $grape = $formObj->getTBRow($ln->gd('m.wine.product.lbl.grape', 'Grape'), 'grape', $row['grape']);
            $vintage = $formObj->getTBRow($ln->gd('m.wine.product.lbl.vintage', 'Vintage'), 'vintage', $row['vintage']);
            $producer = $formObj->getDDRowBySQL($ln->gd('m.wine.product.lbl.producer', 'Producer'), 'producer_id', $sqlProducer, $row['producer_id'], $expProducer);
            $bottleSize = $formObj->getDDRowBySQL($ln->gd('m.wine.product.lbl.bottleSize', 'Bottle Size'), 'bottle_size', $sqlBottleSize, $row['bottle_size'], $exp);
        } else {
            $expBrandCode = '';
            $country = '';
            $region = '';
            $appellation = '';
            $color = '';
            $grape = '';
            $vintage = '';
            //$producer = $formObj->getTBRow($ln->gd('m.wine.product.lbl.producer', 'Producer'), 'producer', $row['producer']);
            $producer = $formObj->getDDRowBySQL($ln->gd('m.wine.product.lbl.producer', 'Producer'), 'producer_id', $sqlProducer, $row['producer_id'], $expProducer);
            $bottleSize = '';
        }

        $fieldset1 = "
        <input type='hidden' name='category_id' value='{$row['category_id']}'>
        {$formObj->getTBRow($ln->gd('m.wine.product.lbl.productCode', 'Item Code'), 'product_code', $row['product_code'], $expCode)}
        {$formObj->getTBRow($ln->gd('cp.lbl.title', 'Title'), 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowBySQL($ln->gd('cp.lbl.category', 'Category'), 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL($ln->gd('cp.lbl.subCategory', 'Sub Category'), 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$country}
        {$region}
        {$appellation}
        {$formObj->getTBRow($ln->gd('m.wine.product.lbl.brandCode', 'Brand Code'), 'brand_code', $row['brand_code'], $expBrandCode)}
        {$color}
        {$grape}
        {$vintage}
        {$producer}
        {$bottleSize}
        ";
        //{$formObj->getTBRow($ln->gd('m.wine.product.lbl.quantity', 'Quantity'), 'qty_in_stock', $row['qty_in_stock'])}
        //{$formObj->getTBRow($ln->gd('m.wine.product.lbl.stockThreshold', 'Stock Threshold'), 'stock_threshold', $row['stock_threshold'])}-->

        $fieldset2 = "
        {$latest}
        {$formObj->getYesNoRRow($ln->gd('cp.lbl.published', 'Published'), 'published', $row['published'])}
        {$formObj->getTARow($ln->gd('m.wine.product.lbl.shortDescription', 'Short Description'), 'description_short', $ln->gfv($row, 'description_short', '0'))}
        {$formObj->getTARow($ln->gd('m.wine.product.lbl.specialOfferDescription', 'Special Offer Description'), 'special_offer_description', $ln->gfv($row, 'special_offer_description', '0'))}
        ";
        
        $fieldset3 = "
        {$this->getSpecialSearchRows($row['product_id'])}    
        ";

        $fieldset4 = "
        {$formObj->getHTMLEditor($ln->gd('cp.lbl.description', 'Description'), 'description', $ln->gfv($row, 'description', '0'))}
        ";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('cp.lbl.mainDetails', 'Main Details'), $fieldset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.wine.product.lbl.specialProperties', 'Special Properties'), $fieldset2)}
        {$formObj->getFieldSetWrapped($ln->gd('m.wine.product.lbl.specialSearches', 'Special Searches'), $fieldset3)}
        {$formObj->getFieldSetWrapped($ln->gd('cp.lbl.description', 'Description'), $fieldset4)}
        {$metaData}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $links = '';

        if ($cpCfg['m.wine.product.hasProductRelatedProduct'] == 1) {
           // $links = $displayLinkData->getLinkPortalMain('wine_product', 'ecommerce_productLink', $ln->gd('m.wine.product.lbl.relatedProducts', 'Related Products'), $row);
        }

        $links .= $displayLinkData->getLinkPortalMain('wine_product', 'ecommerce_countryLink', $ln->gd('m.wine.product.lbl.countryLinked', 'Country Linked'), $row);
//        {$media->getRightPanelMediaDisplay($ln->gd('m.wine.product.lbl.relatedOffer', 'Related Offer'), 'wine_product', 'relatedOffer', $row)}

        $text = "
        {$media->getRightPanelMediaDisplay($ln->gd('cp.lbl.Picture', 'Picture'), 'wine_product', 'picture', $row)}
        {$media->getRightPanelMediaDisplay($ln->gd('m.wine.product.lbl.relatedPicture', 'Related Picture'), 'wine_product', 'relatedPicture', $row)}
        {$links}
        {$displayLinkData->getLinkPortalMain('wine_product', 'ecommerce_ratingLink', $ln->gd('m.wine.product.lbl.rating', 'Rating'), $row)}
        {$displayLinkData->getLinkPortalMain('wine_product', 'wine_tastingNotesLink', $ln->gd('m.wine.product.lbl.tastingNotes', 'Tasting Notes'), $row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');

        $producer_id = $fn->getReqParam('producer_id');
        $country = $fn->getReqParam('country');
        $color = $fn->getReqParam('color');
        $grape = $fn->getReqParam('grape');
        $region = $fn->getReqParam('region');
        $bottle_size = $fn->getReqParam('bottle_size');
        $brand_code = $fn->getReqParam('brand_code');
        $appellation_id = $fn->getReqParam('appellation_id');

        $whereRegion = "";
        if ($country != '') {
            $whereRegion = "WHERE country_code = '{$country}'";
        }

        $whereAPP = '';
        if ($region != '') {
            $whereAPP = "WHERE region_id = {$region}";
        }

        $sqlProducer = "
        SELECT producer_id
            ,title
        FROM producer
        ORDER BY title
        ";

        $SQLCountry = "
        SELECT gc.country_code
              ,gc.name
        FROM geo_country gc
        WHERE gc.country_code IN  (
            SELECT DISTINCT country_code FROM region
        )
        ORDER BY gc.name
        ";

        $SQLRegion = "
        SELECT region_id
            ,title
        FROM region
        {$whereRegion}
        ORDER BY title
        ";

        $SQLAppellation = "
        SELECT appellation_id
            ,title
        FROM appellation
        {$whereAPP}
        ORDER BY title
        ";

        $sqlBrandCode = "
        SELECT DISTINCT brand_code
        FROM product
        WHERE brand_code != ''
        AND brand_code IS NOT NULL
        ORDER BY brand_code
        ";

        $sqlColor = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'wineColor'
        ORDER BY sort_order
        ";

        $sqlGrape = "
        SELECT DISTINCT grape
        FROM product
        WHERE grape != ''
        AND grape IS NOT NULL
        ORDER BY grape
        ";

        $sqlBottleSize = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'wineBottleSize'
        ORDER BY sort_order
        ";

        $subCatOptions = '';
        $SQLCat = "
        SELECT a.category_id
              ,a.title
        FROM category a
        LEFT JOIN (section b) ON (a.section_id  = b.section_id)
        WHERE b.section_type ='Product'
           OR b.section_type ='Gifts & Accessories'
        ORDER BY a.section_id, a.title
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

        $spArray = array(
            "Flagged"
            , "Not-Flagged"
            , "Published"
            , "Not-Published"
            , "Latest"
            , "Records Missing in Source (JDE)"
            , "New Records (from JDE)"
            , "Fault"
        );

        $text = "
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>{$ln->gd('cp.lbl.category', 'Category')}</option>
                {$catOptions}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='sub_category_id'>
                <option value=''>{$ln->gd('cp.lbl.subCategory', 'Sub Category')}</option>
                {$subCatOptions}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='color'>
                <option value=''>{$ln->gd('m.wine.product.lbl.color', 'Color')}</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlColor, $color)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='grape'>
                <option value=''>{$ln->gd('m.wine.product.lbl.grape', 'Grape')}</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlGrape, $grape)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='bottle_size'>
                <option value=''>{$ln->gd('m.wine.product.lbl.bottleSize', 'Bottle Size')}</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlBottleSize, $bottle_size)}
            </select>
        </td>
        </tr>
        <tr>

        <td class='fieldValue'>
            <select name='country'>
                <option value=''>{$ln->gd('m.wine.product.lbl.country', 'Country')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLCountry, $country)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='region'>
                <option value=''>{$ln->gd('m.wine.product.lbl.region', 'Region')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLRegion, $region)}
            </select>
        </td>

        <!--<td class='fieldValue'>
            <select name='brand_code'>
                <option value=''>{$ln->gd('m.wine.product.lbl.appelation', 'Appelation')}</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLAppellation, $appellation_id)}
            </select>
        </td>-->

        <td class='fieldValue'>
            <select name='brand_code'>
                <option value=''>{$ln->gd('m.wine.product.lbl.brandCode', 'Brand Code')}</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlBrandCode, $brand_code)}
            </select>
        </td>

        <td class='fieldValue'>
            <select name='producer_id'>
                <option value=''>{$ln->gd('m.wine.product.lbl.producer', 'Producer')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlProducer, $producer_id)}
            </select>
        </td>

        <td>
            <select name='special_search'>
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
    function getImportInstructions() {
        $cpPaths = Zend_Registry::get('cpPaths');

        $fn = Zend_Registry::get('fn');
        $importType = $fn->getReqParam('importType');
        
        if ($importType == 'threshold'){
            $fileName = 'threshold-import-template.xls';
        } else if ($importType == 'specialSearch'){
            $fileName = 'specail-search-import-template.xls';
        } else {
            $fileName = 'product-import-template.xls';
        } 
        
        $url = "index.php?_spAction=streamFile&showHTML=0&modname=wine_product&filename={$fileName}";
        
        $text = "
        <p>Accepted file type: xls</p>
        <p>Template: <a href='{$url}'>Download</a></p>
        ";
        
        return $text;
    }    
    
    /**
     * 
     * @param type $product_id
     */
    function getSpecialSearchRows($product_id){
        $fn = Zend_Registry::get('fn');   
        $ln = Zend_Registry::get('ln');   
        $cpCfg = Zend_Registry::get('cpCfg');   
        $formObj = Zend_Registry::get('formObj');        
        
        $SQL = "
        SELECT pc.*, c.title
        FROM   product_country pc
        LEFT JOIN country c ON (c.country_id = pc.country_id)
        WHERE  pc.product_id = {$product_id}
        ORDER BY c.title";     
        
        $countryArr = $fn->getArrBySQL($SQL);
        
        $rowStyle = "style='padding:0 5px;'";
        //---------------------- SPECIALS ------------------------------
        $rows  = "";
        $total_fields = $cpCfg['m.wine.product.totalProductCountrySpecialFlds'];
        for($i = 1; $i <= $total_fields; $i++){
            $cols = '';
            foreach ($countryArr as $row) {
                if($row['country_id'] != ''){
                    $dbFldName = "special_{$i}";
                    $formFldName = "{$dbFldName}_{$row['product_country_id']}";
                    $cols  .= "
                    <td {$rowStyle}>
                        {$formObj->getYesNoRRow('&nbsp;', $formFldName, $row[$dbFldName])}
                    </td>
                    ";
                }
            }
            $rows  .= "
            <tr>    
                <th {$rowStyle}>{$ln->gd('m.wine.product.lbl.special', 'Special')} {$i}</th>
                {$cols}
            </tr>    
            ";
            
        }
        
        //======================= SPECIAL OFFER ========================
        $cols = '';
        foreach ($countryArr as $row) {
            if($row['country_id'] != ''){
                $dbFldName = "special_offer";
                $formFldName = "{$dbFldName}_{$row['product_country_id']}";
                $cols  .= "
                <td {$rowStyle}>
                    {$formObj->getYesNoRRow('&nbsp;', $formFldName, $row[$dbFldName])}
                </td>
                ";
            }
        }        
        $rows  .= "
        <tr>    
            <th {$rowStyle}>{$ln->gd('m.wine.product.lbl.specialOffer', 'Special Offer')}</th>
            {$cols}
        </tr>    
        ";        
        //==================== COUNTRY HEADER ====================
        $headerCols = "
        <th style='width:120px;padding:5px;'>
            {$formObj->getButtonImageRow('Refresh Country List', 'javascript:location.reload();')}
        </th>";
        foreach ($countryArr as $row) {
            if($row['country_id'] != ''){
                $headerCols  .= "
                <th style='width:100px;padding:5px;'>
                    <input type='hidden' name='product_country_id[]' value='{$row['product_country_id']}'>
                    {$row['title']}
                </th>
                ";
            }
        }        
        
        $text = "
        <table border='1'>
            <tr>
                {$headerCols}
            </tr>
            {$rows}
        </table> 
        
        ";
            
        return $text;
    }
    
    /**
     * 
     * @return type
     */
    function getAdditionalImportFields(){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        $importType = $fn->getReqParam('importType');
        
        $text = '';
        
        if($importType == 'specialSearch'){
            $SQL = "
            SELECT country_id, title
            FROM country
            ";
            
            $text = $formObj->getDDRowBySQL($ln->gd('cp.lbl.country', 'Stock Country'), 'country_id', $SQL);
        }
        
        return $text;
    }
}