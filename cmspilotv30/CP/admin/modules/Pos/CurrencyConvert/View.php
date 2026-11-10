<?
class CP_Admin_Modules_Pos_CurrencyConvert_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $rows .= "
			{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['from_currency'])}
            {$listObj->getListDataCell($row['from_currency_code'])}
            {$listObj->getListDataCell($row['to_currency'])}
            {$listObj->getListDataCell($row['to_currency_code'])}
            {$listObj->getListDataCell($row['exch_rate'])}
            {$listObj->getListDataCell($row['currency_convert_id'], 'center')}
            {$listObj->getListRowEnd($row['currency_convert_id'])}
			";
        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('From Currency', 'from_currency')}
        {$listObj->getListHeaderCell('From Code', 'from_currency_code')}
        {$listObj->getListHeaderCell('To Currency', 'to_currency')}
        {$listObj->getListHeaderCell('To Code', 'from_currency_code')}
        {$listObj->getListHeaderCell('Exchange Rate', 'cc.exch_rate')}
        {$listObj->getListHeaderCell('ID', 'cc.currency_convert_id', 'headerCenter')}
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

        $sqlCurrency = getCPModuleObj('pos_currency')->model->getCurrencySQL();

        $fielset1  = "
        {$formObj->getDDRowBySQL('From Currency', 'from_currency_id', $sqlCurrency)}
        {$formObj->getDDRowBySQL('To Currency', 'to_currency_id', $sqlCurrency)}
        {$formObj->getTBRow('Exchange Rate', 'exch_rate')}
		";
        $text = "
        {$formObj->getFieldSetWrapped('Currency Convert Details', $fielset1)}
        ";
        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $formObj = Zend_Registry::get('formObj');

        $expFromCurrency = array('detailValue' => $row['from_currency']);
        $expToCurrency = array('detailValue' => $row['to_currency']);
        $currencyObj = getCPModuleObj('pos_currency');
        $sqlCurrency = $currencyObj->model->getCurrencySQL();
        $sqlCurrencyCode = getCPModuleObj('pos_currency')->model->getCurrencyCodeSQL();
        
        if ($row['use_currency_exch_rate_base'] == '') {
            $use_currency_exch_rate_base = 1;
        } else {
            $use_currency_exch_rate_base = $row['use_currency_exch_rate_base'];       
        }

        $fielset1  = "
        {$formObj->getDDRowBySQL('From Currency', 'from_currency_id', $sqlCurrency, $row['from_currency_id'], $expFromCurrency)}
        {$formObj->getDDRowBySQL('To Currency', 'to_currency_id', $sqlCurrency, $row['to_currency_id'], $expToCurrency)}
        {$formObj->getTBRow('Exchange Rate', 'exch_rate', $row['exch_rate'])}
        {$formObj->getYesNoRRow('Use Currency Exchange Rate', 'use_currency_exch_rate', $row['use_currency_exch_rate'])}
        {$formObj->getTBRow('Use Currency Exchange Rate Base', 'use_currency_exch_rate_base', $use_currency_exch_rate_base)}
        {$formObj->getYesNoRRow('Show from currency sign in report', 'show_from_currency_sign_report', $row['show_from_currency_sign_report'])}
        {$formObj->getYesNoRRow('Show from currency sign in invoce receipt', 'show_from_currency_sign_inv_receipt', $row['show_from_currency_sign_inv_receipt'])}
        {$formObj->getYesNoRRow('Show to currency sign in report', 'show_to_currency_sign_report', $row['show_to_currency_sign_report'])}
        {$formObj->getYesNoRRow('Show to currency sign in invoce receipt', 'show_to_currency_sign_inv_receipt', $row['show_to_currency_sign_inv_receipt'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Currency Convert Details', $fielset1)}
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
    }
}