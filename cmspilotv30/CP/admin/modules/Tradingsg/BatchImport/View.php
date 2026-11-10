<?
class CP_Admin_Modules_Tradingsg_BatchImport_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');

        $rows  = "";
        $rowCounter = 0;


        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

	        $SQLTotal = "
				SELECT SUM(round(
				(bhi.qty * bhi.price),2)) AS total_cost
				FROM batch_history bhi WHERE bhi.batch_import_id = {$row['batch_import_id']}
            ";
            $resultTotal = $db->sql_query($SQLTotal);
            $rowTotal = $db->sql_fetchrow($resultTotal);
            $total_cost = number_format($rowTotal['total_cost'] + $row['freight_cost']);
            
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
			{$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['batch_date'])}
            {$listObj->getListDataCell($total_cost)}
            {$listObj->getListDataCell($row['batch_import_id'], 'center')}
            {$listObj->getListRowEnd($row['batch_import_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'title')}
        {$listObj->getListHeaderCell('Batch Date', 'batch_date')}
        {$listObj->getListHeaderCell('Total Cost', 'total_cost')}
        {$listObj->getListHeaderCell('Batch Import ID', 'batch_import_id' , 'headerCenter')}
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
        $fn      = Zend_Registry::get('fn');
        
        $sqlBatchImportStatus = $fn->getValuelistSql('batchImportStatus');
        $expVl      = array('sqlType' => 'OneField');

        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
		{$formObj->getDateRow('Batch Date', 'batch_date', $row['batch_date'])}
        {$formObj->getTBRow('Freight Cost', 'freight_cost', $row['freight_cost'])}
		{$formObj->getDDRowBySQL('Status', 'status', $sqlBatchImportStatus, $row['status'], $expVl)}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Batch Import Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        $media = Zend_Registry::get('media');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $db = Zend_Registry::get('db');


        $text ="
        {$displayLinkData->getLinkPortalMain('tradingsg_batchImport', 'tradingsg_batchHistoryLink', 'Batch History Linked', $row)}
        {$media->getRightPanelMediaDisplay('Attachments', 'tradingsg_batchImport', 'attachment', $row)}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $product_id = $fn->getReqParam('product_id');
        $supplier_id = $fn->getReqParam('supplier_id');

        $sqlProduct  = $fn->getDDSql('tradingsg_product');
        $sqlSupplier = "
	        SELECT c.company_id
	        	,company_name
	        FROM company c
	        WHERE category = 'Supplier'
        ";

        $text = "
        ";        

			/*<td>
            <select name='product_id'>
                <option value=''>Product Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlProduct, $product_id)}
            </select>
        </td>
        <td>
            <select name='supplier_id'>
                <option value=''>Supplier Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlSupplier, $supplier_id)}
            </select>
        </td>*/
        
        return $text;
    }
}