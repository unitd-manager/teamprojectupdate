<?
class CP_Admin_Modules_Account_AccCompany_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .="
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['company_name'])}
            {$listObj->getListDataCell($row['account_year'])}
            {$listObj->getListDataCell($row['currency_title'])}
            {$listObj->getListDataCell($row['acc_company_id'], 'center')}
            {$listObj->getListRowEnd($row['acc_company_id'])}
			";
        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Company Name', 'ac.company_name')}
        {$listObj->getListHeaderCell('Account Year', 'ac.account_year')}
        {$listObj->getListHeaderCell('Currency', 'currency_title')}
        {$listObj->getListHeaderCell('ID', 'ac.acc_company_id', 'headerCenter')}
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
        {$formObj->getTBRow('Company Name', 'company_name')}
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

        $expCurrency = array('detailValue' => $row['currency_title']);
        $currencyObj = getCPModuleObj('account_currency');
        $sqlCurrency = $currencyObj->model->getCurrencySQL();

        $fielset1  = "
        {$formObj->getTBRow('Company Name', 'company_name', $row['company_name'])}
        {$formObj->getTBRow('Account Year', 'account_year', $row['account_year'])}
        {$formObj->getDDRowBySQL('Currency', 'base_currency_id', $sqlCurrency, $row['base_currency_id'], $expCurrency)}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Account Company Details', $fielset1)}
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