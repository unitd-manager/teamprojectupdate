<?
class CP_Admin_Modules_Ecommerce_ProductGroup_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['dicount_percent_vip'])}
            {$listObj->getListDataCell($row['dicount_percent_staff'])}
            {$listObj->getListDataCell($row['dicount_percent_opl'])}
            {$listObj->getListRowEnd($row['product_group_id'])}
            ";

            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Product Group Name', 'pg.title')}
        {$listObj->getListHeaderCell('VIP Discount %', 'pg.dicount_percent_vip')}
        {$listObj->getListHeaderCell('Staff Discount %', 'pg.dicount_percent_staff')}
        {$listObj->getListHeaderCell('OPL Discount %', 'pg.dicount_percent_opl')}
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
        {$formObj->getTBRow('Product Group Name', 'title')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //========================================================//
    //==================================================================//
    //==================================================================//
    //==================================================================//
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $fielset1 = "
        {$formObj->getTBRow('Product Group Name', 'title', $row['title'])}
        {$formObj->getTBRow('VIP Discount %', 'dicount_percent_vip', $row['dicount_percent_vip'])}
        {$formObj->getTBRow('Staff Discount %', 'dicount_percent_staff', $row['dicount_percent_staff'])}
        {$formObj->getTBRow('OPL Discount %', 'dicount_percent_opl', $row['dicount_percent_opl'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Product Item Details', $fielset1)}
        ";
        return $text;
    }

    //========================================================//
    //==================================================================//
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
    //==================================================================//
}