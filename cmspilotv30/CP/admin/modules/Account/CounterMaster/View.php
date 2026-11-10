<?
class CP_Admin_Modules_Account_CounterMaster_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('baBBQ');
    
    function getList($dataArray) {
        $listObj2 = Zend_Registry::get('listObj2');
        $cpCfg = Zend_Registry::get('cpCfg');

        $base_currency = getCPModelObj('account_accCompany')->getBaseCurrencyCode();
        
        $listObj2->addFld('entry_date', array(
            'type' => 'date'
           ,'sort' => 'cm.entry_date'
           ,'hasDetailLink' => true
        ));
        $listObj2->addFld('action', array('sort' => 'cm.action'));
        $listObj2->addFld('voucher_code', array('sort' => 'voucher_code'));
        $listObj2->addFld('currency_code', array(
            'title' => 'Curr.'
           ,'align' => 'center'
           ,'sort' => 'currency'
        ));
        $listObj2->addFld('v_buy_amount', array(
            'type' => 'number'
           ,'formatCb' => array($this, 'getFormatAmount')
        ));
        $listObj2->addFld('v_buy_rate', array(
            'type' => 'number'
           ,'displayDecimalLength' => $cpCfg['cp.displayDecimalLength2']
        ));
        $listObj2->addFld('v_sell_amount', array(
            'type' => 'number'
           ,'formatCb' => array($this, 'getFormatAmount')
        ));
        $listObj2->addFld('v_sell_rate', array(
            'type' => 'number'
           ,'displayDecimalLength' => $cpCfg['cp.displayDecimalLength2']
        ));
        $listObj2->addFld('amount_base', array(
            'title' => 'Amount - ' . $base_currency
           ,'type' => 'number'
           ,'dataCb' => array($this->model, 'getAmountBase')
           ,'formatCb' => array($this, 'getFormatAmountBase')
        ));
        //$listObj2->addFld('counter_name', array('sort' => 'counter_name'));
        $listObj2->addFld('narration');
        $listObj2->addFld('staff_short_code', array('title' => 'Staff', 'sort' => 's.short_code'));

        $listObj2->setDataArr($dataArray);
        $listObj2->setConfigArr(array('noScrollableTable' => true));

        $text = $listObj2->render();

        return $text;
    }

    function getFormatAmount($fieldName, $row, &$fieldObj) {
        $fn = Zend_Registry::get('fn');
        
        $debitClass = 'debit-color';
        $creditClass = 'credit-color';

        $value = $row[$fieldName];
        if ($row['action'] == 'sell') {
            $fieldObj['class'] = $creditClass;
            $value = $fn->getFormatNumber($value);
        } else { //buy
            $fieldObj['class'] = $debitClass;
            $value = $fn->getFormatNumberNegative($value);
        }
        return $value;
    }

    function getFormatAmountBase($fieldName, $row, $fieldObj) {
        $fn = Zend_Registry::get('fn');
        
        $debitClass = 'debit_base-color';
        $creditClass = 'credit_base-color';

        $value = $row[$fieldName];
        if ($row['action'] == 'sell') {
            $fieldObj['class'] = $creditClass;
            $value = $fn->getFormatNumber($value);
        } else { //buy
            $fieldObj['class'] = $debitClass;
            $value = $fn->getFormatNumberNegative($value);
        }
        return $value;
    }

    function getNew($rowMaster = null){
        $cpUtil = Zend_Registry::get('cpUtil');
        $fo = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $subAction = $fn->getReqParam('subAction', $rowMaster['action']);
        $title = ucfirst($subAction);

        $staff_id = $fn->getSessionParam('staff_id');
        $rowStaff = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);


        $expEntryDate = array('allowEdit' => 1);

        $actionBtns = '';

        $sqlCounterCurrency = getCPModuleObj('account_accHead')->model->getCounterCurrencySQL();

        $rowCounterCurr = $fn->getRecordBySQL($sqlCounterCurrency, MYSQL_ASSOC);

        //field values
        $counter_setup_id     = '';
        $entry_date           = '';
        $narration_main       = '';
        $staff_id             = '';
        if ($tv['action'] == 'new') {
            $journal_master_id = '';
            $staff_id          = $rowStaff['staff_id'];
            $entry_date        = $cpUtil->getISODateStr();
        } else {
            $journal_master_id = $rowMaster['journal_master_id'];
            $counter_setup_id  = $rowMaster['counter_setup_id'];
            $entry_date        = $rowMaster['entry_date'];
            $staff_id          = $rowMaster['staff_id'];
            $narration_main    = $rowMaster['narration'];
        }
        $actionBtns = $this->getActionButtons();

        $staff_id = $fn->getIssetParam($rowMaster, 'staff_id', $fn->getSessionParam('staff_id'));
        $rowStaff = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);

        $newRowText = '';
        if ($tv['action'] != 'detail') {
            $newRowText = "
            <div class='p5 new-row-div'>
                <a href='#' tabindex='-1' class='new-small'></a>
            </div>
            ";
        }
        $url = '/admin/index.php?_topRm=counter&module=account_counterMaster'
             . '&_spAction=add&subAction=sell&showHTML=0';
        $fieldset = "
        <div id='entry' class='mainFlds'>
            <form method='post' action='{$url}' class='yform columnar cpJqForm' id='frmNew'>
            {$fo->getDateRow('Entry Date', 'entry_date', $entry_date, $expEntryDate)}
            {$fo->getTBRow('Narration', 'narration_main', $narration_main)}
            {$fo->getTBRow('Staff Code', 'staff_id', $rowStaff['short_code'], $fo->expNoEdit)}

			<div class='jheader'>
				{$this->getEntryRowHeader($subAction)}
			</div>
			<div class='jbody'>
				{$this->getEntryRows($rowMaster)}
                {$newRowText}
			</div>
            {$actionBtns}
            <input type='hidden' name='journal_master_id' id='journal_master_id' 
                   value='{$journal_master_id}'>
            <input type='hidden' name='c_action' id='c_action' value='{$subAction}'>
            </form>
        </div>
        ";

        $exp = array('class' => $subAction);
        $text = "
		<div id='jEntryErrorBox'></div>
        {$fo->getFieldSetWrapped($title, $fieldset, $exp)}
        ";

        return $text;
    }

    private function getEntryRowHeader($subAction) {
        $base_currency = getCPModelObj('account_accCompany')->getBaseCurrencyCode();

        $actionLbl = 'V ' . ucfirst($subAction);
        $text = "
        <div class='row'>
        <table>
        <tr>
        <th class='account'>Currency</th>
        <th class='amount'>{$actionLbl} Amount</th>
        <th class='exch_rate'>{$actionLbl} Rate</th>
        <th class='amount_base'>{$base_currency} Amount</th>
        <th class='narration'>Narration Short</th>
        <th class='delete'></th>
        </tr>
        </table>
        </div>
        ";
        return $text;
    }

    private function getEntryRows($rowJM) {
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $journal_master_id = $rowJM['journal_master_id'];
        $text = '';
        $action = $tv['action'];
        if ($action == 'new') {
            $text = $this->getEntryRow();
        } else {
            $SQL = "
            SELECT j.acc_head_id
                  ,ah.title AS acc_head
                  ,c.code AS currency
                  ,j.journal_id
                  ,j.currency_id
                  ,j.exch_rate_to_base
                  ,j.debit
                  ,j.credit
                  ,j.debit_base
                  ,j.credit_base
                  ,j.narration
                  ,jm.action
            FROM journal j
            JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
            JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
            JOIN currency c ON c.currency_id = j.currency_id
            WHERE j.journal_master_id = {$journal_master_id}
              AND j.currency_type = 'foreign'
            ";
            $result = $db->sql_query($SQL);
            $rowNum = 1;
            while ($row = $db->sql_fetchrow($result)) {
                $text .= $this->getEntryRow($row, $rowNum);
                $rowNum++;
            }
        }

        return $text;

    }
    
    private function getEntryRow($row = null, $rowNum = 1) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $journal_id        = ($row == null) ? '' : $row['journal_id'];
        $acc_head_id       = ($row == null) ? '' : $row['acc_head_id'];
        $exch_rate_to_base = ($row == null) ? '' : $row['exch_rate_to_base'];
        $debit             = ($row == null) ? '' : $row['debit'];
        $credit            = ($row == null) ? '' : $row['credit'];
        $debit_base        = ($row == null) ? '' : $row['debit_base'];
        $credit_base       = ($row == null) ? '' : $row['credit_base'];
        $narration         = ($row == null) ? '' : $row['narration'];

        $currency_code = '';
        if ($tv['action'] == 'detail') {
            $currency_code = getCPModelObj('account_accHead')
                             ->getCurrencyCodeByAccHeadId($acc_head_id);
        }
        
        $amount = '';
        $amount_base = '';
        
        $c_action = '';
        if ($tv['action'] == 'new') {
            $c_action = $fn->getReqParam('subAction');
        } else {
            $c_action = $row['action'];
            $amount      = $c_action == 'sell' ? $credit : $debit;
            $amount_base = $c_action == 'sell' ? $credit_base : $debit_base;
        }

        $action = $tv['action'];
        $exch_rate_to_base = $fn->removeTrailingZeros($row['exch_rate_to_base']);

        if ($action == 'detail') {
            $debit       = $fn->getFormatNumberNegative($row['debit']);
            $credit      = $fn->getFormatNumber($row['credit']);
            $debit_base  = $fn->getFormatNumberNegative($row['debit_base']);
            $credit_base = $fn->getFormatNumber($row['credit_base']);
        } else {
            $debit       = $fn->getFormatNumber($row['debit'], '', false);
            $credit      = $fn->getFormatNumber($row['credit'], '', false);
            $debit_base  = $fn->getFormatNumberNegative($row['debit_base'], '', false);
            $credit_base = $fn->getFormatNumber($row['credit_base'], '', false);

        }

        $editableClassDetail = '';
        $readonlyDetail = '';
        $deleteText = "<a href='#' tabindex='-1' class='delete-small'></a>";
        if ($action == 'detail') {
            $editableClassDetail = 'fld-non-editable';
            $readonlyDetail = "readonly='readonly'";
            $deleteText = '';
        }
        
        $sqlCounterCurrency = getCPModuleObj('account_accHead')->model->getCounterCurrencySQL();
        $expAccHead = array(
            'ddSQL' => $sqlCounterCurrency
           ,'sqlType' => 'TwoFields'
           ,'class' => "fld-account {$editableClassDetail}"
           ,'detailText' => $currency_code
       );
        
        $text = "
        <div class='row'>
        <table>
        <tr>
        <td class='account'>
        {$formObj->getSelectFldObj("acc_head_id-{$rowNum}", $acc_head_id, '', $expAccHead)}
        </td>
        <td class='amount'>
            <input type='text' name='amount-{$rowNum}' class='fld-amount amount-color
            {$editableClassDetail}' {$readonlyDetail} value='{$amount}' />
        </td>
        <td class='exch_rate'>
            <input type='text' name='exch_rate_to_base-{$rowNum}' class='fld-exch_rate
                   {$editableClassDetail}' {$readonlyDetail} value='{$exch_rate_to_base}' />
        </td>
        <td class='amount_base'>
            <input type='text' name='amount_base-{$rowNum}' 
                   class='fld-amount_base amount_base-color fld-non-editable'
                   readonly='readonly' tabindex='-1' value='{$amount_base}' />
		</td>
        <td class='narration'>
            <input type='text' name='narration-{$rowNum}' 
                   class='fld-narration {$editableClassDetail}'
                   value='{$narration}' />
		</td>
        <td class='delete'>
            {$deleteText}
        </td>
        </tr>
        </table>
        <input type='hidden' name='journal_id-{$rowNum}' value='{$journal_id}' class='fld-journal_id' />
        </div>
        ";
        return $text;
    }
    
    private function getActionButtons(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        if ($tv['action'] == 'detail') {
            $text = "
            <div class='type-button float_right hid actBtnsInNewForm' style='display: block;'>
                <input type='button' id='actBtn_cancelCounter' class='cancel' 
                    action='cancelNew' value='Cancel'>
                <input type='button' id='actBtn_print' action='print' value='Print'>
            </div>
            ";
            
        } else {
            $text = "
            <div class='type-button float_right hid actBtnsInNewForm' style='display: block;'>
                <input type='button' id='actBtn_cancelCounter' class='cancel' 
                    action='cancelNew' value='Cancel'>
                <input type='button' id='actBtn_savePrint' action='savePrint' value='Save & Print'>
                <input type='button' id='actBtn_saveClose' action='saveClose' value='Save & Close'>
            </div>
            ";
        }

        return $text;
    }

    function getDetail() {
        $fn = Zend_Registry::get('fn');

        $journal_master_id = $fn->getReqParam('journal_master_id');
        $rowJM = $fn->getRecordRowByID('journal_master', 'journal_master_id',
                      $journal_master_id, array('fetchType' => MYSQL_ASSOC));
        
        return $this->getNew($rowJM);
    }

    function getEdit() {
        $fn = Zend_Registry::get('fn');

        $journal_master_id = $fn->getReqParam('journal_master_id');
        $rowJM = $fn->getRecordRowByID('journal_master', 'journal_master_id',
                      $journal_master_id, array('fetchType' => MYSQL_ASSOC));
        
        return $this->getNew($rowJM);
    }

    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $entry_date_from = $fn->getReqParam('entry_date_from');
        $entry_date_to = $fn->getReqParam('entry_date_to');
        $acc_head_id = $fn->getReqParam('acc_head_id', 0);

        $sqlCounterCurrency = getCPModuleObj('account_accHead')->model->getCounterCurrencySQL();

        $text = "
        <td>
            <select name='acc_head_id'>
                <option value=''>Foreign Currency</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $sqlCounterCurrency, $acc_head_id)}
            </select>
        </td>
        <td style='width:290px'>
            <input allowEdit='1' type='text' name='entry_date_from' class='fld_date'
                    rel='pptxt:Start date' value='{$entry_date_from}' /> to
            <input allowEdit='1' type='text' name='entry_date_to' class='fld_date'
                     rel='pptxt:End date' value='{$entry_date_to}' />
        </td>
        ";

        return $text;
    }

    function getAdditionalImportFields() {
        $formObj = Zend_Registry::get('formObj');

        $text = '';

        $entry_date = date('Y-m-d');
        $expEntryDate = array('allowEdit' => 1);
        $expHideFO = array('hideFirstOption' => true);

        $sqlCounterSetup = getCPModuleObj('account_counterSetup')->model->getCounterSetupSQL();

        $text = "
        {$formObj->getDateRow('Entry Date', 'entry_date', $entry_date, $expEntryDate)}
        {$formObj->getDDRowBySQL('Counter Name', 'counter_setup_id', $sqlCounterSetup, '', $expHideFO)}

        ";

        return $text;
    }

    function getListRightPanel(){
        $text = $this->getCurrencyStock();
        
        return $text;
    }    

    function getCurrencyStock(){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        $SQL = "
        SELECT code
              ,stock
              ,avg_buy_rate
              ,avg_stock_rate
        FROM currency
        ORDER BY code
        ";
        $result = $db->sql_query($SQL);

        $rows = '';
        $counter = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $class = $fn->getRowClass2($counter);
            
            $stock = $fn->getFormatNumber($row['stock']);
            $avg_buy_rate   = $row['avg_buy_rate'];
            $avg_stock_rate = $row['avg_stock_rate'];
            
            $rows .= "
            <tr class='{$class}'>
            <td>{$row['code']}</td>
            <td class='stock'>{$stock}</td>
            <td class='buy-rate'>{$avg_buy_rate}</td>
            <td class='stock-rate'>{$avg_stock_rate}</td>
            </tr>
            ";
            $counter++;
        }

        $text = "
        <table class='thinlist stock-list'>
        <tr>
        <th>Curr.</th>
        <th class='stock'>Stock</th>
        <th class='buy-rate'>Avg Buy Rate</th>
        <th class='stock-rate'>Avg Stock Rate</th>
        </tr>
        {$rows}
        </table>
        ";

        return $text;
    }    
}
