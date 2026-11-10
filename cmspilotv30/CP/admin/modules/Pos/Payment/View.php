<?
class CP_Admin_Modules_Pos_Payment_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['code'])}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['payment_id'], 'center')}
            {$listObj->getListRowEnd($row['payment_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Payment Code', 'p.code')}
        {$listObj->getListHeaderCell('Payment Name', 'p.title')}
        {$listObj->getListHeaderCell('ID', 'p.payment_id', 'headerCenter')}
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
        {$formObj->getTBRow('Payment Code', 'code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    //==================================================================//
    function getEdit($row) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $formObj = Zend_Registry::get('formObj');
        
        $sqlPaymentType = $fn->getValueListSQL('paymentType');
        $expVl = array('sqlType' => 'OneField');
        
        $fielset1 = "
        {$formObj->getTBRow('Payment Code', 'code', $ln->gfv($row, 'code', '0'))}
        {$formObj->getTBRow('Payment Name', 'title', $ln->gfv($row, 'title', '0'))}
        {$formObj->getTARow('Description', 'description', $ln->gfv($row, 'description', '0'))}
  		{$formObj->getDDRowBySQL('Payment Type', 'payment_type', $sqlPaymentType, $row['payment_type'], $expVl)}
        {$formObj->getTBRow('Days', 'days', $row['days'])}
        ";
        
        $text = "
        {$formObj->getFieldSetWrapped('Payment Maintenance Details', $fielset1)}
        ";

        return $text;
    }

    //==================================================================//
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
                
        $text ="
        ";
        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
                
        $text = "
        ";

        
        return $text;
    }
}