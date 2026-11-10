<?
class CP_Admin_Modules_Ecommerce_ProductItem_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['sku_no'])}
            {$listObj->getListDataCell($row['color_code'])}
            {$listObj->getListDataCell($row['size'])}
            {$listObj->getListDataCell($row['stock'])}
            {$listObj->getListRowEnd($row['product_item_id'])}
            ";

            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('SKU No', 'pi.sku_no')}
        {$listObj->getListHeaderCell('Colour', 'pi.color_code')}
        {$listObj->getListHeaderCell('Size', 'pi.size')}
        {$listObj->getListHeaderCell('Stock', 'pi.stock')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    //==================================================================//
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fnMod = includeCPClass('ModuleFns', 'ecommerce_product');
        $sqlProduct = $fnMod->getProductSQL();

        $fieldset = "
        {$formObj->getDDRowBySQL('Product Name', 'product_id', $sqlProduct)}
        {$formObj->getTBRow('SKU No', 'sku_no')}
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
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $fnMod = includeCPClass('ModuleFns', 'ecommerce_color');
        $sqlColor = $fnMod->getColorSQL();

        $fielset1 = "
        {$formObj->getTBRow('SKU No', 'sku_no', $row['sku_no'])}
        {$formObj->getDDRowBySQL('Colour', 'color_code', $sqlColor, $row['color_code'])}
        {$formObj->getTBRow('Size', 'size', $row['size'])}
        {$formObj->getTBRow('Stock', 'stock', $row['stock'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Product Item Details', $fielset1)}
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

        ";
        return $text;
    }

    //==================================================================//
    function getQuickSearch() {
    }
}