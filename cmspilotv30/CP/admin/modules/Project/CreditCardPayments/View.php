<?
class CP_Admin_Modules_Project_CreditCardPayments_View extends CP_Common_Lib_ModuleViewAbstract
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
			{$listObj->getGoToDetailText($rowCounter, $row['type_of_card'])}
			{$listObj->getListDataCell($row['due_date'])}
			{$listObj->getListDataCell($row['amount'])}
			{$listObj->getListDataCell($row['status'])}
			{$listObj->getListDataCell($row['paid_date'])}
            {$listObj->getListDataCell($row['credit_card_payment_id'], 'center')}
            {$listObj->getListRowEnd($row['credit_card_payment_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Type of Card', 'type_of_card')}
        {$listObj->getListHeaderCell('Due Date', 'due_date')}
        {$listObj->getListHeaderCell('Amount', 'amount')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('Paid Date', 'paid_date')}
        {$listObj->getListHeaderCell('Credit Card Payment id', 'credit_card_payment_id' , 'headerCenter')}
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
        $ln = Zend_Registry::get('ln');

        $sqlCreditCardTypeofCard = $fn->getValueListSQL('typeofCreditCard');
        $expVl = array('sqlType' => 'OneField');

        $fieldset = "
  		{$formObj->getDDRowBySQL('Type of Card', 'type_of_card', $sqlCreditCardTypeofCard, '', $expVl)}
        {$formObj->getTBRow('Amount', 'amount')}
        
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
        $ln = Zend_Registry::get('ln');

        $sqlCreditCardTypeofCard = $fn->getValueListSQL('typeofCreditCard');
        $sqlCreditCardPaymentStatus = $fn->getValueListSQL('creditCardPaymentStatus');
        $expVl = array('sqlType' => 'OneField');
        

        $fielset1 = "
  		{$formObj->getDDRowBySQL('Type of Card', 'type_of_card', $sqlCreditCardTypeofCard, $row['type_of_card'], $expVl)}
        {$formObj->getDateRow('Due Date', 'due_date', $row['due_date'])}
        {$formObj->getDateRow('Date of Statement', 'date_of_statement', $row['date_of_statement'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getTBRow('Minimum Amount', 'minimum_amount', $row['minimum_amount'])}
        {$formObj->getYesNoRRow('Reminder', 'reminder', $row['reminder'])}
        {$formObj->getDateRow('Reminder Date', 'reminder_date', $row['reminder_date'])}
        {$formObj->getDateRow('Paid Date', 'paid_date', $row['paid_date'])}
  		{$formObj->getDDRowBySQL('Status', 'status', $sqlCreditCardPaymentStatus, $row['status'], $expVl)}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Credit Card Payment Details', $fielset1)}
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
        {$media->getRightPanelMediaDisplay('Attachments', 'project_creditCardPayments', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $month = $fn->getReqParam('month');

        if ($month == '') {
            $month = 'Current Month';
        }
        
        
        $text = "
        <td>
            <select name='month'>
                <option value=''>Month</option>
                {$cpUtil->getDropDown1($cpCfg['m.project.creditCardPayments.monthArr'], $month)}
            </select>
        </td>
        ";        
        
        return $text;
    }
}