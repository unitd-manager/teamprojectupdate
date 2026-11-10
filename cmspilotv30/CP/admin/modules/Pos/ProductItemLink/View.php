<?
class CP_Admin_Modules_Pos_ProductItemLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
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

}