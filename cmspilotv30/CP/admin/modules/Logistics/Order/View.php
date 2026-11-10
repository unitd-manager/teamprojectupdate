<?
class CP_Admin_Modules_Logistics_Order_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
	        {$listObj->getListDataCell($row['order_code'])}
	        {$listObj->getListDataCell($row['title'])}
	        {$listObj->getListDataCell($row['company_name'])}
	        {$listObj->getListDataCell($row['contact_name'])}
	        {$listObj->getListDataCell($row['order_date'])}
            {$listObj->getListDataCell($row['amount'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row['order_id'], 'center')}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Order Code', 'o.order_code')}
        {$listObj->getListHeaderCell('Title', 'o.title')}
        {$listObj->getListHeaderCell('Company Name', 'company_name')}
        {$listObj->getListHeaderCell('Contact Person', 'contact_name')}
        {$listObj->getListHeaderCell('Order Date', 'order_date')}
        {$listObj->getListHeaderCell('Amount', 'amount')}
        {$listObj->getListHeaderCell('Status', 'status')}
        {$listObj->getListHeaderCell('Order Id', 'order_id' , 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Order Code', 'order_code')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $expVl = array('sqlType' => 'OneField');
        $sqlStatus       = $fn->getValueListSQL('orderStatus');

        $sqlContact = "
        SELECT contact_id
              ,CONCAT_WS(' ', first_name, last_name ) AS contact_name 
        FROM contact
        ";
        
        $expContact = array('detailValue' => $row['contact_name']);

        $fieldset1 = "
        {$formObj->getTBRow('Order Code', 'order_code', $row['order_code'])}
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        {$formObj->getDateRow('Order Date', 'order_date', $row['order_date'])}
        {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
        {$formObj->getDDRowBySQL('Contact Person', 'contact_id', $sqlContact, $row['contact_id'], $expContact)}        
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
		";
		
        $fieldset2 = "
        {$formObj->getHTMLEditor('Description', 'description', $ln->gfv($row, 'description', '0'))}
        ";

		
        $text = "
        {$formObj->getFieldSetWrapped('Order Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $comment = getCPPluginObj('common_comment');
        $displayLinkData = Zend_Registry::get('displayLinkData');


        $text ="
    ";
        
        return $text;
    }
    
    /**
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $status = $fn->getReqParam('status');

        $sqlStatus = $fn->getValueListSQL('vehicleStatus');

        $text = "
        ";        
        
        return $text;
    }
}