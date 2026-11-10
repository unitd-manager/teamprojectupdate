<?
class CP_Admin_Modules_Accountsg_JournalMaster_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $userGroupType = $fn->getSessionParam('userGroupType');

        $rowCounter = 0;
        $rows = '';
        $expDebit = array('class' => 'debit-color');
        $expCredit = array('class' => 'credit-color');
        $expDebitBase = array('class' => 'debit_base-color');
        $expCreditBase = array('class' => 'credit_base-color');

        $listRowClass = 'odd';

        foreach ($dataArray as $row){
            $debit          = $fn->getFormatNumberNegative($row['debit']);
            $credit         = $fn->getFormatNumber($row['credit']);

            $ledgerAuthText = '';
            if ($userGroupType != 'User') {
                $ind = $this->getLedgerAuthorizeInd($row);
                $ledgerAuthText = $listObj->getListDataCell($ind, 'center');
            }

            $entry_date = $dateUtil->formatDate($row['entry_date'], 'DD-MM-YYYY');
            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter, $listRowClass)}
            {$listObj->getGoToDetailText($rowCounter, $entry_date)}
            {$listObj->getListDataCell($row['acc_head'])}
            {$listObj->getListDataCell($this->getNarration($row))}
            {$listObj->getListDataCell($debit, 'right', '', '', $expDebit)}
            {$listObj->getListDataCell($credit, 'right', '', '', $expCredit)}
            {$listObj->getListRowEnd($row['journal_id'])}
			";
        	$rowCounter++;
            $voucher_code_temp = $row['voucher_code'];
		}

        $ledgerAuthText = '';
        if ($userGroupType != 'User') {
            $ledgerAuthText = $listObj->getListHeaderCell('Auth.', 'jm.ledger_authorized', 'headerCenter');
        }

        $exp = array('noScrollableTable' => true);
        $text = "
    	{$listObj->getListHeader($exp)}
        {$listObj->getListHeaderCell('Date', 'jm.entry_date')}
        {$listObj->getListHeaderCell('Account', 'j.acc_head_id', 'accountListHeader')}
        {$listObj->getListHeaderCell('Narration')}
        {$listObj->getListHeaderCell('Debit', '', 'headerRight')}
        {$listObj->getListHeaderCell('Credit', '', 'headerRight')}
    	{$listObj->getListHeaderEnd()}
        {$rows}
	    {$listObj->getListFooter()}
		";
        return $text;
    }

    function getLedgerAuthorizeInd($rowJM, $clickable = true) {
        $class = 'red-dot';
        if ($rowJM['ledger_authorized']) {
            $class = 'green-dot';
        }
        $jmId = $rowJM['journal_master_id'];

        $text = '';
        if ($clickable) {
            $text = "<a href='#' class='ledger-auth {$class}' jm-id='{$jmId}'></a>";
        } else {
            $text = "<div class='ledger-auth {$class}' jm-id='{$jmId}'></div>";
        }

        return $text;
    }

    function getPendingInd($rowJM, $clickable = true) {
        $class = 'light-grey-dot';
        if ($rowJM['pending']) {
            $class = 'light-red-dot';
        }

        $text = '';
        if ($clickable) {
            $text = "<a href='#' class='ledger-pending {$class}' journal_id='{$rowJM['journal_id']}'></a>";
        } else {
            $text = "<div href='#' class='ledger-pending {$class}' journal_id='{$rowJM['journal_id']}'></div>";
        }

        return $text;
    }


    function getNew($rowJM = null){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $action = $tv['action'];

        $difference = '';
        $actionBtns = '';

        $expEntryDate = array('allowEdit' => 1);

		$entry_date = date('Y-m-d');
        $journal_master_id = '';
        $voucher_type = '';
        $narration_main = '';
        $voucher_code = $rowJM['voucher_code'];
        $short_code = '';

        if ($action != 'new') {
            $journal_master_id = $rowJM['journal_master_id'];
            $entry_date = $rowJM['entry_date'];
            $voucher_type = $rowJM['voucher_type'];
            $narration_main = $rowJM['narration_main'];
        }
        if ($action != 'detail') {
            $difference = "
            <div id='difference' class='floatbox'>
                <div class='value green-highlight'></div>
                <div class='label'>Difference</div>
            </div>
            ";
			$actionBtns = $this->getActionButtons();
        }

        $exp = array('hideFirstOption' => 1);
        $fieldset = "
        <div id='entry'>
            <div class='subcolumns mainFlds'>
                <div class='c80l'>
                    <div class='subcl'>
                        {$formObj->getDateRow('Entry Date', 'entry_date', $entry_date, $expEntryDate)}
                        {$formObj->getTBRow('Narration', 'narration_main', $narration_main)}
                    </div>
                </div>
                <div class='c20r'>
                    <div class='subcr'>
                        {$difference}
                    </div>
                </div>
            </div>
			<div class='jheader'>
				{$this->getJournalRowHeader()}
			</div>
			<div class='jbody'>
				{$this->getJournalRows($rowJM)}
			</div>
			<div class='jfooter'>
				{$this->getJournalRowFooter()}
			</div>
			<div class='subcolumns'>
    			<div class='c60l'>
        			<div class='subcl'>
        			    <table class=''>
        			        <tr>
        			            <td>&nbsp;&nbsp;&nbsp;Staff Code:</td>
        			            <th>&nbsp;{$short_code}</th>
        			        </tr>
        			    </table>
        			</div>
    			</div>
    			<div class='c40r'>
        			<div class='subcr'>
                        {$actionBtns}
        			</div>
    			</div>
			</div>
            <input type='hidden' name='journal_master_id' value='{$journal_master_id}'>
        </div>
        ";

        $text = "
		<div id='entryErrorBox'></div>
        {$formObj->getFieldSetWrapped('Journal Entry', $fieldset)}
        ";

        return $text;
    }

    private function getActionButtons(){
        $tv = Zend_Registry::get('tv');

        $url = "index.php?_topRm={$tv['topRm']}&module=accountsg_journalMaster";
        $text = "
        <div class='type-button float_right hid actBtnsInNewForm' style='display: block;'>
            <input type='button' id='actBtn_cancelNew' action='cancelNew' value='Cancel'  url='{$url}'>
            <input type='button' id='actBtn_saveJournal' action='save' value='Save' url='{$url}'>
            <input type='button' id='actBtn_saveContinue' action='saveContinue' value='Save & Continue'>
        </div>
        ";

        return $text;
    }

    private function getJournalRowHeader() {

        $text = "
        <div class='row'>
        <table>
        <tr>
        <th class='account txtCenter'>Account</th>
        <th class='debit txtRight'>Debit</th>
        <th class='credit txtRight'>Credit</th>
        </tr>
        </table>
        </div>
        ";
        return $text;
    }

    private function getJournalRows($rowJM) {
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $journal_master_id = $rowJM['journal_master_id'];
        $text = '';
        $action = $tv['action'];
        $editableClass2 = '';
        if ($action == 'new') {
            $text = $this->getJournalRow();
            $text .= $this->getJournalRow(null, 2);
            $editableClass2 = 'fld-not-editable';
        } else {
            $SQL = "
            SELECT j.acc_head_id
                  ,ah.title AS acc_head
                  ,j.journal_id
                  ,j.debit
                  ,j.credit
                  ,j.narration
            FROM journal j
            JOIN acc_head ah ON ah.acc_head_id = j.acc_head_id
            WHERE j.journal_master_id = {$journal_master_id}
            ";
            $result = $db->sql_query($SQL);
            $rowNum = 1;
            while ($row = $db->sql_fetchrow($result)) {
                $text .= $this->getJournalRow($row, $rowNum);
                $rowNum++;
            }
        }

        return $text;

    }

    private function getJournalRow($row = null, $rowNum = 1) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $journal_id        = ($row == null) ? '' : $row['journal_id'];
        $acc_head          = ($row == null) ? '' : $row['acc_head'];
        $acc_head_id       = ($row == null) ? '' : $row['acc_head_id'];
        $debit             = ($row == null) ? '' : $row['debit'];
        $credit            = ($row == null) ? '' : $row['credit'];
        $narration         = ($row == null) ? '' : $row['narration'];

        $action = $tv['action'];
        if ($action == 'detail') {
            $debit       = $fn->getFormatNumberNegative($row['debit']);
            $credit      = $fn->getFormatNumber($row['credit']);
        } else {
            $debit       = $fn->getFormatNumber($row['debit'], '', false);
            $credit      = $fn->getFormatNumber($row['credit'], '', false);
        }

        $editableClassDetail = '';
        $readonlyDetail = '';
        $narrationLabel = "rel='pptxt: Narration'";
        if ($action == 'detail') {
            $editableClassDetail = 'fld-non-editable';
            $readonlyDetail = "readonly='readonly'";
            $narrationLabel = '';
        }
        $bgClass = '';
        if ($debit > 0) {
            $bgClass = 'red-bg';
        } else if ($credit > 0){
            $bgClass = 'green-bg';
        }

        $text = "
        <div class='row {$bgClass}'>
        <table>
        <tr>
        <td class='account'>
            <input type='text' name='acc_head-{$rowNum}' class='fld-account {$editableClassDetail}'
                   {$readonlyDetail} value='{$acc_head}'> </br>
            <input type='text' name='narration-{$rowNum}' class='fld-narration {$editableClassDetail}'
                   {$readonlyDetail} value='{$narration}' {$narrationLabel}>
            <input type='hidden' name='acc_head_id-{$rowNum}' value='{$acc_head_id}' class='fld-acc_head_id'>
        </td>
        <td class='debit'>
            <input type='text' name='debit-{$rowNum}' class='fld-debit debit-color
                   {$editableClassDetail}' {$readonlyDetail} value='{$debit}'>
        </td>
        <td class='credit'>
            <input type='text' name='credit-{$rowNum}' class='fld-credit credit-color
            {$editableClassDetail}' {$readonlyDetail} value='{$credit}'>
        </td>
        </tr>
        </table>
        <input type='hidden' name='journal_id-{$rowNum}' value='{$journal_id}' class='fld-journal_id'>
        </div>
        ";
        return $text;
    }

    private function getJournalRowFooter() {
        $text = "
        <div class='row'>
        <table>
        <tr>
        <th>&nbsp;</th>
        <th class='debit_base' id='debit_base_total'></th>
        <th class='credit_base' id='credit_base_total'></th>
        </tr>
        </table>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        return $this->getNew($row);
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $special_search  = $fn->getReqParam('special_search');
        $entry_date_from = $fn->getReqParam('entry_date_from');
        $entry_date_to = $fn->getReqParam('entry_date_to');

        $arr = array('1' => 'Yes', '0' => 'No');
        $show_counter = $fn->getReqParam('show_counter', 0);

        $text = "
        <td style='width:290px'>
            <input allowEdit='1' type='text' name='entry_date_from' class='fld_date'
                    rel='pptxt:Start date' value='{$entry_date_from}'> to
            <input allowEdit='1' type='text' name='entry_date_to' class='fld_date'
                     rel='pptxt:End date' value='{$entry_date_to}'>
        </td>
        ";

        return $text;
    }

    function getNarration($row) {

        $text = "
        <div class='narration-main'>
            {$row['narration_main']}
        </div>
        <div class='narration'>
            {$row['narration']}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row) {
        $media = Zend_Registry::get('media');

        $text  = "
        {$media->getRightPanelMediaDisplay('Attachment', 'accountsg_journalMaster', 'attachment', $row)}
        ";

        return $text;
    }
}
