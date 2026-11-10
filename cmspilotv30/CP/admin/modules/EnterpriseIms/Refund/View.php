<?
class CP_Admin_Modules_EnterpriseIms_Refund_View extends CP_Common_Lib_ModuleViewAbstract
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
			{$listObj->getGoToDetailText($rowCounter, $row['refund_code'])}
            {$listObj->getListDataCell($row['amount'])}
            {$listObj->getListDataCell($row['refund_id'], 'center')}
            {$listObj->getListRowEnd($row['refund_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Refund Code', 'refund_code')}
        {$listObj->getListHeaderCell('Amount', 'r.amount')}
        {$listObj->getListHeaderCell('Refund ID', 'refund_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Refund Code', 'refund_code')}
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
        

        $fielset1 = "
        {$formObj->getTBRow('Refund Code', 'refund_code', $row['refund_code'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Refund Details', $fielset1)}
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
        {$media->getRightPanelMediaDisplay('Attachments', 'enterpriseIms_refund', 'attachment', $row)}
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