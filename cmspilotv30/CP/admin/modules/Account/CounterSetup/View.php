<?
class CP_Admin_Modules_Account_CounterSetup_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['counter_setup_id'], 'center')}
            {$listObj->getListRowEnd($row['counter_setup_id'])}
			";
        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Counter Name', 'cs.title')}
        {$listObj->getListHeaderCell('ID', 'cs.counter_setup_id', 'headerCenter')}
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
        {$formObj->getTBRow('Counter Name', 'title')}
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

        $fielset1  = "
        {$formObj->getTBRow('Counter Name', 'title', $row['title'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Counter Setup Details', $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {

        $text  = "
		";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $currency_id  = $fn->getReqParam('currency_id');

        $modCurrency = getCPModuleObj('account_currency');
        $sqlCurrency = $modCurrency->model->getCurrencySQL();

        $text = "
        <td>
            <select name='currency_id'>
                <option value=''>Select Currency</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCurrency, $currency_id)}
            </select>
        </td>
        ";

        return $text;
    }
}