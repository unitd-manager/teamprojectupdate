<?
class CP_Admin_Modules_Tradingsg_Discount_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;
        

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['customer_type'])}
            {$listObj->getListDataCell($row['product_group'])}
            {$listObj->getListDataCell($row['discount_percent'])}
            {$listObj->getListDataCell($row['margin'])}
            {$listObj->getListDataCell($row['discount_id'], 'center')}
            {$listObj->getListRowEnd($row['discount_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Customer Type', 'customer_type')}
        {$listObj->getListHeaderCell('Product Group', 'd.product_group')}
        {$listObj->getListHeaderCell('Discount %', 'd.discount_percent')}
        {$listObj->getListHeaderCell('Margin %', 'd.margin')}
        {$listObj->getListHeaderCell('Discount ID', 'discount_id' , 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');

        $sqlCustomerType = $fn->getValueListSQL('customerType');
        $expVl = array('sqlType' => 'OneField');

        $fieldset = "
        {$formObj->getDDRowBySQL('Customer Type', 'customer_type', $sqlCustomerType, '', $expVl)}
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
        $fn = Zend_Registry::get('fn');
        
        $sqlCustomerType = $fn->getValueListSQL('customerType');
        $sqlProductGroup = "SELECT title FROM product_group";
        $expVl = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getDDRowBySQL('Customer Type', 'customer_type', $sqlCustomerType, $row['customer_type'], $expVl)}
        {$formObj->getDDRowBySQL('Product Group', 'product_group', $sqlProductGroup, $row['product_group'], $expVl)}
        {$formObj->getTBRow('Discount %', 'discount_percent', $row['discount_percent'])}
        {$formObj->getTBRow('Margin %', 'margin', $row['margin'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Discount Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');

        $text ="
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_discount', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $text = "
        ";        
        
        return $text;
    }
}