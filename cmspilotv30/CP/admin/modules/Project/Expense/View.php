<?
class CP_Admin_Modules_Project_Expense_View extends CP_Common_Lib_ModuleViewAbstract
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
			{$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['amount'])}
            {$listObj->getListDataCell($row['date'])}
            {$listObj->getListDataCell($row['group'])}
            {$listObj->getListDataCell($row['type'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Amount', 'e.amount')}
        {$listObj->getListHeaderCell('Date', 'date')}
        {$listObj->getListHeaderCell('Group', 'group')}
        {$listObj->getListHeaderCell('Type', 'type')}
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
        {$formObj->getTBRow('Title', 'title')}
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


        $spArrayGroup = array(
            "Purchase"
           ,"Direct "
           ,"Indirect "
        );

        $spArraySource = array(
            "Cash"
           ,"Bank "
           ,"Credit "
           ,"Director "
        );

        $expVl = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getDateRow('Date', 'date', $row['date'])}
  		{$formObj->getDDRowByArr('Group', 'group', $spArrayGroup, $row['group'])}
  		{$formObj->getDDRowBySQL('Name of ledger', 'type', $row['type'])}
  		{$formObj->getDDRowByArr('Source', 'source', $spArraySource, $row['source'])}
        {$formObj->getTARow('Description', 'description', $row['description'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Expenses Details', $fielset1)}
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
        {$media->getRightPanelMediaDisplay('Attachments', 'project_expense', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');



        $text = "
        ";        
        
        return $text;
    }
}