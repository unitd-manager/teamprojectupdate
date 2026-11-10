<?
class CP_Admin_Modules_Pos_ProductLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['sub_category_title'])}
            {$listObj->getListDataCell($row['currency'])}
            {$listObj->getListDataCell($row['price'])}
            {$listObj->getListDataCell($row['uom_code'])}
            {$this->getProductItems($row['product_id'], $linkRecType)}
            ";
            
            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Title", "a.title")}
        {$listObj->getListHeaderCell('Category', 'c.title')}
        {$listObj->getListHeaderCell('Sub Category', 'sc.title')}
        {$listObj->getListHeaderCell('Currency', 'p.currency')}
        {$listObj->getListHeaderCell('Unit Price', 'p.price')}
        {$listObj->getListHeaderCell('UOM', 'p.uom_code')}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getProductItems($product_id, $linkRecType) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        
        $SQL ="
        SELECT pt.*
        FROM product_item pt
        WHERE pt.product_id = {$product_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
        
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $styleRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $row['style_id']);
            $colorRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $row['color_id']);
            $sizeRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $row['size_id']);
            $seasonRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $row['season_id']);
            $elementRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $row['element_id']);
            
            $rows .="
            <tr>
                <td>{$row['sku_no']}</td>
                <td>{$styleRec['value']}</td>
                <td>{$colorRec['value']}</td>
                <td>{$sizeRec['value']}</td>
                <td>{$seasonRec['value']}</td>
                <td>{$elementRec['value']}</td>
                {$listLinkObj->getListRowEndLink($linkRecType, $row['sku_no'])}
            </tr>
            ";
        }
        $text = "
        <tr>
            <td colspan=7>
            <table class='list productItems'>
                {$rows}
            </table>
            </td>
        </tr>
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
        ";

        
        return $text;
    }
}
