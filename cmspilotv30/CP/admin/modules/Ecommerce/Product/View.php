<?
class CP_Admin_Modules_Ecommerce_Product_View extends CP_Common_Modules_Ecommerce_Product_View
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

            $sectionCol = '';
            if ($cpCfg['m.ecommerce.product.hasSection']) {
                $sectionCol = $listObj->getListDataCell($row['section_title']);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$sortOrder}
            {$listObj->getListDataCell($row['product_code'])}
            {$sectionCol}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$extaFlds}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['product_id'], 'center')}
            {$listObj->getListPublishedImage($row['published'], $row['product_id'])}
            {$listObj->getListDataCell($fn->getYesNo($row['latest']), 'center')}
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

        $sectionCol = '';
        if ($cpCfg['m.ecommerce.product.hasSection']) {
            $sectionCol = $listObj->getListHeaderCell('Section', 's.title');
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'p.title')}
        {$sortOrder}
        {$listObj->getListHeaderCell('Item Code', 'p.product_code')}
        {$sectionCol}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$extaFlds}
        {$listObj->getListHeaderCell('Price', 'p.price')}
        {$listObj->getListHeaderCell('ID', 'p.product_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Published', 'p.published', 'headerCenter')}
        {$listObj->getListHeaderCell('Latest', 'p.latest', 'headerCenter')}
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

        $latest = '';
        if ($cpCfg['m.ecommerce.product.showLatest'] == 1) {
            $latest = $formObj->getYesNoRRow("Latest", "latest", $row['latest']);
        }

        $favourite = '';
        if ($cpCfg['m.ecommerce.product.showFavourite'] == 1) {
            $favourite = $formObj->getYesNoRRow("Favourite", "favourite", $row['favourite']);
        }

        $slideshow = '';
        if ($cpCfg['m.ecommerce.product.showSlidehshow'] == 1) {
            $slideshow = $formObj->getYesNoRRow("Show in Slideshow", "slideshow", $row['slideshow']);
        }

        $metaData = '';
        if ($cpCfg['m.ecommerce.product.showMetaData'] == 1) {
            $metaData .= $formObj->getMetaData($row);
        }

        $priceText = '';
        if ($cpCfg['m.ecommerce.product.isCountryBased'] == 0) {
            $priceText = $formObj->getTBRow('Price', 'price', $row['price']);
        }

        $embedCode = '';
        if ($cpCfg['m.ecommerce.product.showEmbedCode'] == 1) {
            $embedCode = $formObj->getTARow('Embed Code', 'embed_code', $ln->gfv($row, 'embed_code', '0'));
        }

        $weight = '';
        if ($cpCfg['m.ecommerce.product.showWeight']) {
            $weight = $formObj->getTBRow("Shipping Weight in Grams", "weight_grams", $row['weight_grams']);
        }

        $sqlCategory = '';
        $sectionRow = '';
        $modCat = getCPModuleObj('webBasic_category');
        if ($cpCfg['m.ecommerce.product.hasSection']) {
            $modSec = getCPModuleObj('webBasic_section');
            $sqlSection = $modSec->model->getSectionSQL();
            $expSection = array('detailValue' => $row['section_title']);
            $sectionRow = $formObj->getDDRowBySQL($ln->gd('m.ecommerce.product.lbl.section', 'Section'), 'section_id', $sqlSection, $row['section_id'], $expSection);
            if ($row['section_id'] != ''){
                $sqlCategory = $modCat->model->getCategorySQLBySection($row['section_id']);
            }
        } else {
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

        $shortDesc = '';
        if ($cpCfg['m.ecommerce.product.showShortDesc']){
            $shortDesc = "
            {$formObj->getTARow('Short Description', 'description_short', $ln->gfv($row, 'description_short', '0'))}
            ";
        }

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTBRow('Item Code', 'product_code', $row['product_code'])}
        {$sectionRow}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$stockFld}
        {$priceText}
        {$weight}
        {$shortDesc}
        {$embedCode}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$latest}
        {$favourite}
        {$slideshow}
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
        {$metaData}
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
        {$media->getRightPanelMediaDisplay('Picture', 'ecommerce_product', 'picture', $row)}
        {$media->getRightPanelMediaDisplay('Related Picture', 'ecommerce_product', 'relatedPicture', $row)}
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

        $company_id     = $fn->getReqParam('company_id');
        $special_search = $fn->getReqParam('special_search');
        $subCatOptions  = '';

        $sectionText ='';
        $SQLCat ='';
        if ($cpCfg['m.ecommerce.product.hasSection']) {
            $modSec = getCPModuleObj('webBasic_section');
            $sqlSection = $modSec->model->getSectionSQL();
            $sectionText = "
            <td class='fieldValue'>
                <select name='section_id'>
                    <option value=''>Section</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSection, $tv['section_id'])}
                </select>
            </td>";

            if ($tv['section_id'] != "") {
                $modCat = getCPModuleObj('webBasic_category');
                $SQLCat = $modCat->model->getCategorySQL($tv['section_id']);

            }
        } else {

            $SQLCat = "
            SELECT a.category_id
                  ,a.title
            FROM category a
            LEFT JOIN (section b) ON (a.section_id  = b.section_id)
            WHERE b.section_type ='Product'
            ORDER BY a.title
            ";
        }
        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);

        if ($tv['category_id'] != '') {
            $modCat = getCPModuleObj('webBasic_subCategory');
            $SQLSubCat = $modCat->model->getSubCategorySQL($tv['category_id']);
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLSubCat, $tv['sub_category_id']);
        }


        $text = "
        {$sectionText}
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
}