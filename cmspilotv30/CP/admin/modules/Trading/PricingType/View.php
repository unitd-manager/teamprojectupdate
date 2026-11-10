<?
class CP_Admin_Modules_Trading_PricingType_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');

        $count   = 0;
        $rows    = '';

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $count)}
            {$listObj->getGoToDetailText($count, $row['pricing_type'])}
            {$listObj->getListDataCell($row['discount_percent'])}
            {$listObj->getListDataCell($row['show_in_catalog_text'], 'center')}
            {$listObj->getListSortOrderField($row, 'pricing_type_id')}
            {$listObj->getListRowEnd($row['pricing_type_id'])}
            ";

            $count++ ;
        }
        $rows = $listObj->getDisplayListRows($rows);

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Pricing Type', 'pt.pricing_type')}
        {$listObj->getListHeaderCell('Discount Percentage', 'pt.discount_percent')}
        {$listObj->getListHeaderCell('Show In Catalog', 'pt.show_in_catalog', 'center')}
        {$listObj->getListSortOrderImage('pt')}
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
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Pricing Type', 'pricing_type')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Pricing Type Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $expRT = array('useKey' => 1);
        $fieldset = "
        {$formObj->getTBRow('Pricing Type', 'pricing_type', $row['pricing_type'])}
        {$formObj->getDDRowByArr('Record Type', 'record_type', 
                                 $cpCfg['m.trading.pricingType.recordTypeArr'],
                                 $row['record_type'], $expRT)}
        {$formObj->getTBRow('Discount Percentage', 'discount_percent', $row['discount_percent'])}
        {$formObj->getYesNoDropDownRow('Show In Catalog', 'show_in_catalog', $row['show_in_catalog'])}
        {$formObj->getYesNoDropDownRow('Hide In Company', 'hide_in_company', $row['hide_in_company'])}
        
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Pricing Type Details', $fieldset)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');

        $record_id = $fn->getIssetParam($row, 'pricing_type_id');

        $text = "
        {$comment->getView(array(
             'roomName' => 'trading_pricingType'
            ,'recordId' => $record_id
        ))}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $status       = $fn->getReqParam('status');

        $text = "
        ";

        return $text;
    }
}