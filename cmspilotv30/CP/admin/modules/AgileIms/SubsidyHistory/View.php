<?
class CP_Admin_Modules_AgileIms_SubsidyHistory_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listObj = Zend_Registry::get('listObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows  = "";
        $email = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){             
            $editText = "
            <a class='editFromList' dialogTitle=\"Edit - {$row['subsidy_code']}\" href='javascript:void(0);' link='{$fn->getEditFromListUrl($row)}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['subsidy_code'])}
            {$listObj->getListDataCell($row['enrollment_type'])}
            {$listObj->getListDataCell($row['name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($row['paid_date'])}
            {$listObj->getListDataCell($row['order_id'], 'center')}
            {$listObj->getListDataCell($row['subsidy_history_id'], 'center')}
            {$listObj->getListDataCell($editText, 'center')}
            {$listObj->getListRowEnd($row['subsidy_history_id'])}
            ";
            $rowCounter++ ;
        }
        
        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Subsidy Code', 'sh.subsidy_code')}
        {$listObj->getListHeaderCell('Enrollment Type', 'enrollment_type')}
        {$listObj->getListHeaderCell('Company/Student Name', 'o.cust_first_name')}
        {$listObj->getListHeaderCell('Status', 'sh.status')}
        {$listObj->getListHeaderCell('Paid Date', 'sh.paid_date')}
        {$listObj->getListHeaderCell('Order ID', 'sh.order_id', 'headerCenter')}
        {$listObj->getListHeaderCell('ID', 'sh.subsidy_history_id', 'headerCenter')}
        {$listObj->getListHeaderCell('Edit', '', 'headerCenter')}
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
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        
        $exp = array('isEditable' => 0);
        
        $sqlStatus = $fn->getValueListSQL('subsidyStatus');
        $expVl = array('sqlType' => 'OneField');
        
        $fielset1 = "
        {$formObj->getTBRow('Subsidy Code', 'subsidy_code', $row['subsidy_code'])}
        {$formObj->getTBRow('Enrollment Type', 'enrollment_type', $row['enrollment_type'])}
        {$formObj->getTBRow('Company/Student Name', 'name', $row['name'])}
        {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
        {$formObj->getDateRow('Paid Date', 'paid_date', $row['paid_date'])}
        {$formObj->getTBRow('Order ID', 'order_id', $row['order_id'], $exp)}
		";
		
        $text = "
        {$formObj->getFieldSetWrapped('Subsidy History Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
        
        $text = "";
        
        return $text;
    }

    /**
     *
     */
    function getEditFromList() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $id  = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('subsidy_paid_history', 'subsidy_history_id', $id);

        $sqlStatus  = $fn->getValueListSQL('subsidyStatus');
        $exp = array('sqlType' => 'OneField');

        $formAction = "index.php?_spAction=saveFromList&module={$tv['module']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
                {$formObj->getDateRow('Paid Date', 'paid_date', $row['paid_date'])}
            </fieldset>
            <input type='hidden' name='subsidy_paid_history_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $status = $fn->getReqParam('status');
        
        $sqlStatus = $fn->getValueListSQL('subsidyStatus');

        $text = "
        <td class='fieldValue'>
            <select name='status'>
                <option value=''>Status</option>
                    {$dbUtil->getDropDownFromSQLCols1($db, $sqlStatus, $status)}
            </select>
        </td>
        ";        
        
        return $text;
    }
}