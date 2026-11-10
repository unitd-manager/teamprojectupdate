<?
class CP_Admin_Modules_AgileIms_CreditNote_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $email = "";
        $rowCounter = 0;
        
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
		    {$listObj->getGoToDetailText($rowCounter, $row['contact_name'])}
            {$listObj->getGoToDetailText($rowCounter, $row['date'])}
            {$listObj->getListDataCell($row['amount'])}
            {$listObj->getListDataCell($row['order_id'])}
            {$listObj->getListDataCell($row['credit_note_id'], 'center')}
            {$listObj->getListRowEnd($row['credit_note_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Contact Name', 'contact_name')}
        {$listObj->getListHeaderCell('Date', 'cn.date')}
        {$listObj->getListHeaderCell('Amount', 'cn.amount')}
        {$listObj->getListHeaderCell('Order ID', 'order_id' , 'headerCenter')}
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
        {$formObj->getTBRow('Credit Note Date', 'date')}
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
        
        $expNoEdit = array('isEditable' => 0);

        $fielset1 = "
        {$formObj->getTBRow('Client Contact', 'contact_id', $row['contact_name'], $expNoEdit)}
        {$formObj->getTBRow('Credit Note Date', 'date', $row['date'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getTBRow('Order ID', 'order_id', $row['order_id'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Credit Note Details', $fielset1)}
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
        {$media->getRightPanelMediaDisplay('Attachments', 'agileIms_creditNote', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $date1 = $fn->getReqParam('date1');
        $date2 = $fn->getReqParam('date2');

        $text = "
        <td>
        	{$formObj->getDateRangeRow('Date:', 'date', $date1, $date2)}
        </td>        
        ";        
        
        return $text;
    }
}