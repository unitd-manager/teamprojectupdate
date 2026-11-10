<?
class CP_Admin_Modules_EnterpriseIms_SubsidyDiscount_View extends CP_Common_Lib_ModuleViewAbstract
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
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['category_type'])}
            {$listObj->getListDataCell($row['value'])}
            {$listObj->getListDateCell($row['valid_from_date'])}
            {$listObj->getListDateCell($row['valid_to_date'])}
            {$listObj->getListDataCell($row['subsidy_discount_id'], 'center')}
            {$listObj->getListRowEnd($row['subsidy_discount_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'sd.title')}
        {$listObj->getListHeaderCell('Type', 'sd.category_type')}
        {$listObj->getListHeaderCell('Value', 'sd.value')}
        {$listObj->getListHeaderCell('Valid From Date', 'sd.valid_from_date')}
        {$listObj->getListHeaderCell('Valid To Date', 'sd.valid_to_date')}
        {$listObj->getListHeaderCell('ID', 'sd.subsidy_discount_id' , 'headerCenter')}
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

        $sqlBatchStatus = $fn->getValueListSQL('subsidyDiscountType');
        $sqlCalculation = $fn->getValueListSQL('subsidyDiscountCalculation');
        $expVl = array('sqlType' => 'OneField');
        
        $fielset1 = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
  		{$formObj->getDDRowBySQL('Type', 'category_type', $sqlBatchStatus, $row['category_type'], $expVl)}
  		{$formObj->getDDRowBySQL('Mode of Calculation', 'mode_of_calculation', $sqlCalculation, $row['mode_of_calculation'], $expVl)}
        {$formObj->getTBRow('Value', 'value', $row['value'])}
        {$formObj->getDateRow('Valid From Date', 'valid_from_date', $row['valid_from_date'])}
        {$formObj->getDateRow('Valid To Date', 'valid_to_date', $row['valid_to_date'])}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Subsidy / Discount Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){

        $text ="
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $category_type = $fn->getReqParam('category_type');

        $sqlBatchStatus = $fn->getValueListSQL('subsidyDiscountType');

        $text = "
        <td>
            <select name='category_type' >
                <option value=''>Type</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlBatchStatus, $category_type)}
            </select>
        </td>
        ";        
        
        return $text;
    }
}