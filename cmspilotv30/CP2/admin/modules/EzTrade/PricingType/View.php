<?
class CP_Admin_Modules_EzTrade_PricingType_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getListRowEnd($row['pricing_type_id'])}
            ";

            $count++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Pricing Type', 'pt.pricing_type')}
        {$listObj->getListHeaderCell('Discount Percentage', 'pt.discount_percent')}
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

        $fieldset = "
        {$formObj->getTBRow('Pricing Type', 'pricing_type', $row['pricing_type'])}
        {$formObj->getTBRow('Discount Percentage', 'discount_percent', $row['discount_percent'])}
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
             'roomName' => 'ezTrade_pricingType'
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