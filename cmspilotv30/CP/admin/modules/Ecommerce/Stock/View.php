<?
class CP_Admin_Modules_Ecommerce_Stock_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');

        $rows  = "";
        $pic  = "";
        $rowCounter = 0;

        foreach ($dataArray as $row){
            
            $exp = array('childId' => $row['product_item_id'], 'limit' => 1, 'folder' => 'thumb');
            $pic = $media->getMediaPicture('ecommerce_product', 'picture', $row['product_id'], $exp);
            $stock = $row['stock_in_total'] - $row['stock_out_total'];
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['sku_no'])}
            {$listObj->getGoToDetailText($rowCounter, $row['product_title'])}
            {$listObj->getListDataCell($pic)}
            {$listObj->getListDataCell($stock)}
            {$listObj->getListRowEnd($row['product_item_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('SKU NO', 's.sku_no')}
        {$listObj->getListHeaderCell('Product Name', 'p.product_title')}
        {$listObj->getListHeaderCell('Picture')}
        {$listObj->getListHeaderCell('Stock', 's.stock')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getPrint($result){
        return $this->getList($result);
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fielset = "
        {$formObj->getTBRow('SKU NO', 'sku_no')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formObj->mode = $tv['action'];
        
        $stock = $row['stock_in_total'] - $row['stock_out_total'];
        $expstock = array('isEditable' => 0, 'detailValue' => $stock);
        
        $expNoEdit = array('isEditable' => 0);

        $fieldset1 = "
        {$formObj->getTBRow('SKU NO', 'sku_no', $row['sku_no'], $expNoEdit)}
        {$formObj->getTBRow('Product Name', 'product_title', $row['product_title'], $expNoEdit)}
        {$formObj->getTBRow('Stock', 'stock', $row['stock'], $expstock)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Stock Details', $fieldset1)}
        {$formObj->getCreationModificationText2($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');

        $text = "
        {$displayLinkData->getLinkPortalMain('ecommerce_stock', 'ecommerce_stockInLink', 'Stock In', $row)}
        {$this->getStockOut($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getStockOut($row){
        $db = Zend_Registry::get('db');

        $rows = '';

        $SQL = "
        SELECT oi.qty
              ,o.order_id       
              ,o.creation_date
        FROM order_item oi
        JOIN `order` o ON (o.order_id = oi.order_id)
        WHERE oi.child_id = '{$row['product_item_id']}'
        ORDER BY o.order_date
        ";
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
                <tr class='portal-row2'>
                    <td>{$row['order_id']}</td>
                    <td>{$row['creation_date']}</td>
                    <td>{$row['qty']}</td>
                </tr>
            ";
        }

        $text = "
        <div class='header' expanded='1'>
            <div class='floatbox'>
                <div class='floatbox'>Stock Out</div>
            </div>
        </div>
        <div class='linkPortalWrapper'>
            <table class='grid'>
                <thead>
                    <tr>
                        <th class='w75'>Order ID</th>
                        <th class='w125'>Order Date</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>
        </div>
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
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_id = $fn->getReqParam('product_id');
        $sqlProduct = $fn->getDDSQL('ecommerce_product');
                
        $text = "
        <td>
            <select name='product_id'>
                <option value=''>Product</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlProduct, $product_id)}
            </select>
        </td>
        ";

        
        return $text;
    }
}