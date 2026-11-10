<?
class CP_Admin_Modules_EzTrade_Product_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['product_code'])}
            {$listObj->getListDataCell($row['collection_name'])}
            {$listObj->getGoToDetailText($count, $row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['color'])}
            {$listObj->getListDataCell($row['color_inside'])}
            {$listObj->getListDataCell($row['material'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListRowEnd($row['product_id'])}
            ";

            $count++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Product Code', 'p.product_code')}
        {$listObj->getListHeaderCell('Collection', 'p.collection_name')}
        {$listObj->getListHeaderCell('Product Name', 'p.title')}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Colour', 'p.color')}
        {$listObj->getListHeaderCell('Colour Inside', 'p.color_inside')}
        {$listObj->getListHeaderCell('Material', 'p.material')}
        {$listObj->getListHeaderCell('Status', 'p.status')}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $sectionType    = 'Product';
        $modCategory    = getCPModuleObj('webBasic_category');
        $sqlCategory    = $modCategory->model->getCategorySQLByType($sectionType);

        $expVl = array('sqlType' => 'OneField');
        $sqlCollection = $fn->getValueListSQL('Collection');

        //$modSubCategory = getCPModuleObj('webBasic_subCategory');
        //$sqlSubCategory = $modSubCategory->model->getSubCategorySQLByCategory($row['category_id']);

        //{$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory)}

        $fieldset = "
        {$formObj->getTBRow('Product Name', 'title')}
        {$formObj->getTBRow('Product Code', 'product_code')}
        {$formObj->getDDRowBySQL('Collection Name', 'collection_name', $sqlCollection, '', $expVl)}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $expVl = array('sqlType' => 'OneField');
        $sqlCollection = $fn->getValueListSQL('collection');
        $sqlUnit = $fn->getValueListSQL('productUnit');
        $sqlHardware = $fn->getValueListSQL('hardware');

        $fnModCat      = includeCPClass('ModuleFns', 'webBasic_category');
        $fnModSubCat   = includeCPClass('ModuleFns', 'webBasic_subCategory');
        $fnsModProduct = includeCPClass('ModuleFns', 'ezTrade_product');

        $sectionType    = 'Product';
        $modCategory    = getCPModuleObj('webBasic_category');
        $sqlCategory    = $modCategory->model->getCategorySQLByType($sectionType);
        $expCategory    = array('detailValue' => $row['category_title']);

        $modSubCategory = getCPModuleObj('webBasic_subCategory');
        $sqlSubCategory = $modSubCategory->model->getSubCategorySQLByCategory($row['category_id']);
        $expSubCategory = array('detailValue' => $row['sub_category_title']);

        $expNoEdit = array('isEditable' => 0);

        $fieldset1 = "
        {$formObj->getTBRow('Product Code', 'product_code', $row['product_code'])}
        {$formObj->getTBRow('Web Code', 'web_code', $row['web_code'])}
        {$formObj->getTBRow('Product Name', 'title', $row['title'])}
        {$formObj->getDDRowBySQL('Collection Name', 'collection_name', $sqlCollection, $row['collection_name'], $expVl)}
        {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory, $row['category_id'], $expCategory)}
        {$formObj->getDDRowBySQL('Sub Category', 'sub_category_id', $sqlSubCategory, $row['sub_category_id'], $expSubCategory)}
        {$formObj->getDDRowBySQL('UOM', 'unit', $sqlUnit, $row['unit'], $expVl)}
        {$formObj->getDDRowBySQL('Hardware', 'hardware', $sqlHardware, $row['hardware'], $expVl)}
        {$formObj->getTBRow('UOM Quantity', 'unit_qty', $row['unit_qty'])}
        {$formObj->getTBRow('Origin', 'origin', $row['origin'])}
        {$formObj->getTBRow('Dynasty', 'dynasty', $row['dynasty'])}
        {$formObj->getTBRow('Circa', 'circa', $row['circa'])}
        {$formObj->getTBRow('Material', 'material', $row['material'])}
        {$formObj->getTBRow('Colour', 'color', $row['color'])}
        {$formObj->getTBRow('Colour Inside', 'color_inside', $row['color_inside'])}
        {$formObj->getTBRow('Dimension H', 'dimension_h', $row['dimension_h'])}
        {$formObj->getTBRow('Dimension W', 'dimension_w', $row['dimension_w'])}
        {$formObj->getTBRow('Dimension D', 'dimension_d', $row['dimension_d'])}
        {$formObj->getTBRow('CBM per pc', 'cbm_per_pc', $row['cbm_per_pc'])}
        {$formObj->getTBRow('Wholesale Price', 'wholesale_price', $row['wholesale_price'])}
        {$formObj->getTBRow('Trade Price', 'trade_price', $row['trade_price'])}
        {$formObj->getTBRow('Retail Price', 'retail_price', $row['retail_price'])}
        {$formObj->getTBRow('Contract Price', 'contract_price', $row['contract_price'])}
        {$formObj->getDDRowByArr('Status', 'status', $cpCfg['m.trading.product.statusArr'], $row['status'])}
        {$formObj->getYesNoRRow('OK for Web', 'ok_for_web', $row['ok_for_web'])}
        {$formObj->getTARow('Website Comments', 'website_comments', $row['website_comments'])}
        ";

        $fieldset2 = $formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'));

        $text = "
        {$formObj->getFieldSetWrapped('Item Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $comment = getCPPluginObj('common_comment');
        $fn = Zend_Registry::get('fn');

        $record_id = $fn->getIssetParam($row, 'product_id');

//        $rows = "
//        {$displayLinkData->getLinkPortalMain('ezTrade_product', 'ezTrade_rfqItemsLink', 'Selected RFQs', $row)}
//        ";

        $rows = "";
        $text = "
        {$media->getRightPanelMediaDisplay('Pictures', 'ezTrade_product', 'picture', $row)}
        {$rows}
        {$comment->getView(array(
             'roomName' => 'ezTrade_product'
            ,'recordId' => $record_id
        ))}
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
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $company_id     = $fn->getReqParam('company_id');
        $special_search = $fn->getReqParam('special_search');
        $subCatOptions  = '';
        $collection_name   = $fn->getReqParam('collection_name');
        $status   = $fn->getReqParam('status');

        $SQLCat = "
        SELECT DISTINCT
               c.category_id
              ,c.title
        FROM category c
        JOIN section s ON (s.section_id  = c.section_id)
        JOIN product p ON (p.category_id = c.category_id)
        WHERE s.section_type ='Product'
        ORDER BY c.title
        ";
        $catOptions = $dbUtil->getDropDownFromSQLCols2($db, $SQLCat, $tv['category_id']);

        if ($tv['category_id'] != '') {
            $sqlCombo = "
            SELECT sc.sub_category_id
                  ,sc.title
            FROM sub_category sc
            JOIN product p ON (p.sub_category_id = sc.category_id)
            WHERE sc.category_id = {$tv['category_id']}
            ORDER BY sc.title
            ";
            $subCatOptions = $dbUtil->getDropDownFromSQLCols2($db, $sqlCombo, $tv['sub_category_id']);
        }

        $sqlCollection = $fn->getValueListSQL('collection');

        $text = "
        <td>
            <select name='collection_name'>
                <option value=''>Collection</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCollection, $collection_name)}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Category</option>
                {$catOptions}
            </select>
        </td>
        <td class='fieldValue'>
            <select name='category_id'>
                <option value=''>Sub Category</option>
                {$subCatOptions}
            </select>
        </td>
        <td>
            <select name='status' >
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.trading.product.statusArr'], $status)}
            </select>
        </td>
        ";

        return $text;
    }


}
