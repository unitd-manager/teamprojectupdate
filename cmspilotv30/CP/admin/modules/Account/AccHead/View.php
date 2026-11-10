<?
class CP_Admin_Modules_Account_AccHead_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fnsModGrp = includeCPClass('ModGroup', 'Account', 'Functions');

        $rowCounter = 0;
        $rows       = "";

        foreach ($dataArray as $row){
            $opening_balance = $fnsModGrp->getFormatCreditDebit($row['opening_balance']);
            $rows .= "
    		{$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['title'])}
            {$listObj->getListDataCell($row['category_title'])}
            {$listObj->getListDataCell($row['currency_title'])}
            {$listObj->getListDataCell($opening_balance, 'right')}
            {$listObj->getListDataCell($row['code'])}
            {$listObj->getListDataCell($row['counter_title'])}
            {$listObj->getListDataCell($row['acc_head_id'], 'center')}
            {$listObj->getListRowEnd($row['acc_head_id'])}
			";
        	$rowCounter++;
		}

        $text = "
    	{$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Title', 'ah.title')}
        {$listObj->getListHeaderCell('Category', 'category_title')}
        {$listObj->getListHeaderCell('Currency', 'currency_title')}
        {$listObj->getListHeaderCell('Opening Balance', 'opening_balance', 'headerRight')}
        {$listObj->getListHeaderCell('Code', 'ah.code')}
        {$listObj->getListHeaderCell('Counter', 'counter_title')}
        {$listObj->getListHeaderCell('ID', 'ah.acc_head_id', 'headerCenter')}
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

        $fnsModGrp = includeCPClass('ModGroup', 'Account', 'Functions');

        $fieldset = "
        {$formObj->getTBRow('Title', 'title')}
        <div class='type-select'>
            <label for='fld_acc_category_id'>Category</label>
            <select id='fld_acc_category_id' name='acc_category_id'>
                <option selected='selected' value=''>Please Select</option>
                {$fnsModGrp->getAccCatDropdown()}
            </select>
        </div>
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
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $expCategory = array('detailValue' => $row['category_title']);
        $categoryObj = getCPModuleObj('account_accCategory');
        $sqlCategory = $categoryObj->model->getAccCategorySQL();

        $expCurrency = array('detailValue' => $row['currency_title']);
        $sqlCurrency = getCPModuleObj('account_currency')->model->getCurrencySQL();

        $expCounter = array('detailValue' => $row['counter_title']);
        $sqlCounter = getCPModuleObj('account_counterSetup')->model->getCounterSetupSQL();

        $fnsModGrp = includeCPClass('ModGroup', 'Account', 'Functions');

        $opBalArr = array('Credit', 'Debit');
        $opBalTypeVal = ($row['opening_balance_credit'] > 0) ? 'Credit' : 'Debit';
        $opBalVal     = ($row['opening_balance_credit'] > 0) ? $row['opening_balance_credit'] : $row['opening_balance_debit'];
        $opBalValBase = ($row['opening_balance_credit_base'] > 0) ? $row['opening_balance_credit_base'] : $row['opening_balance_debit_base'];

        $isDetail = $tv['action'] == 'detail' ? true : false;
        $disabledText = $tv['action'] == 'detail' ? "disabled='disabled'" : '';
        if ($isDetail) {

        }

        $fieldset1  = "
        {$formObj->getTBRow('Title', 'title', $row['title'])}
        <div class='type-select'>
            <label for='fld_acc_category_id'>Category</label>
            <select id='fld_acc_category_id' name='acc_category_id' {$disabledText}>
                <option selected='selected' value=''>Please Select</option>
                {$fnsModGrp->getAccCatDropdown($row['acc_category_id'])}
            </select>
        </div>
        {$formObj->getTBRow('Code', 'code', $row['code'])}
        {$formObj->getDDRowBySQL('Currency', 'currency_id', $sqlCurrency, $row['currency_id'], $expCurrency)}
        {$formObj->getDDRowBySQL('Counter Name', 'counter_setup_id', $sqlCounter, $row['counter_setup_id'], $expCounter)}
		";

        $fieldset2  = "
        {$formObj->getDDRowByArr('Opening Balance Type', 'op_balance_type', $opBalArr, $opBalTypeVal)}
        {$formObj->getTBRow('Opening Balance', 'opening_balance', $opBalVal)}
        {$formObj->getTBRow('Opening Balance (Base)', 'opening_balance_base', $opBalValBase)}
		";
        
        $fieldset3 = '';
        if ($cpCfg['m.account.accHead.showContact']){
            $append = ($row['company_id'] > 0) ? "AND company_id = {$row['company_id']}" : '';
            $sqlCont = $fn->getDDSql('account_contact', array('condn' => "CONCAT_WS('', first_name, last_name) != '' {$append}"));

            $contact  = "<a href='index.php?_topRm=crm&module=account_contact&_action=detail&contact_id={$row['contact_id']}'>{$row['contact_name']}</a>";
            $company  = "<a href='index.php?_topRm=crm&module=account_company&_action=detail&company_id={$row['company_id']}'>{$row['company_name']}</a>";

            $compLink = '';
            if ($formObj->mode == 'edit'){
                $compLink = "<a class='editLinkSingle' href='' link='{$fn->getOpenLinkUrl('account_accHead', 'common_companyLink', 'fld_company_id')}'>Choose</a>";
            }
            $expComp  = array('notesRight' => $compLink, 'detailValue' => $company);
    
            $expCont  = array('detailValue' => $contact);

            $fieldset3 = "
            {$formObj->getDDRowBySQL('Company', 'company_id', $fn->getDDSql('account_company'), $row['company_id'], $expComp)}
            {$formObj->getDDRowBySQL('Key Contact', 'contact_id', $sqlCont, $row['contact_id'], $expCont)}
    		";

            $fieldset3 = "
            {$formObj->getFieldSetWrapped('Contact Details', $fieldset3)}
    		";
        }
        
        $text = "
        {$formObj->getFieldSetWrapped('Account Head Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Opening Balance', $fieldset2)}
        {$fieldset3}
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

        $acc_category_id  = $fn->getReqParam('acc_category_id');
        $currency_id  = $fn->getReqParam('currency_id');

        $modCurrency = getCPModuleObj('account_currency');
        $sqlCurrency = $modCurrency->model->getCurrencySQL();
        $fnsModGrp = includeCPClass('ModGroup', 'Account', 'Functions');

        $text = "
        <td>
            <select name='acc_category_id'>
                <option value=''>Category</option>
                {$fnsModGrp->getAccCatDropdown($acc_category_id)}
            </select>
        </td>

        <td>
            <select name='currency_id'>
                <option value=''>Currency</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCurrency, $currency_id)}
            </select>
        </td>
        ";

        return $text;
    }

    function getAccountHeadsAsJSON($accHeadsArr) {
        $arr = json_encode($accHeadsArr);
        return $arr;
    }
}