<?
class CP_Admin_Modules_Accountsg_Ledger_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $userGroupType = $fn->getSessionParam('userGroupType');

        $viewJM = getCPViewObj('accountsg_journalMaster');

        $expDebit = array('class' => 'debit-color');
        $expCredit = array('class' => 'credit-color');

        $acc_head_id = $fn->getReqParam('acc_head_id');
        if ($acc_head_id == '') {
            $text = "
            <div id='ledgerHome' class='ui-corner-all'>
                <div class='container'>
                    <h3>Please type in the account name and click GO</h3>
                    <input type='text' name='acc_head' class='fld-acc_head' value='' />
                    <a class='button' href='#'>GO</a>
                </div>
            </div>
            ";
            return $text;
        }

        $rowCounter = 0;
        $rows = '';

        $accHead = getCPModelObj('accountsg_accHead');
        $rowAccHead = $accHead->getAccHeadRow();

        $debit_sum  = $rowAccHead['debit_sum'];
        $credit_sum  = $rowAccHead['credit_sum'];

        $debit_sum  = $fn->getFormatNumber($debit_sum);
        $credit_sum = $fn->getFormatNumber($credit_sum);

        $running_bal_prev = $this->model->getRunningBalancePrevious($rowAccHead);
        $this->model->addRunningBalanceToDataArray($dataArray, $running_bal_prev);
        $running_bal_prev = $fn->getFormatNumber($running_bal_prev);

        $exp = array('hasEditInList' => false);

        foreach ($dataArray as $row){
            $debit = $fn->getFormatNumber($row['debit']);
            $credit = $fn->getFormatNumber($row['credit']);

            $runningBalCls = 'credit-color';
            if ($row['running_bal'] < 0) {
                $runningBalCls = 'debit-color';
            }
            $running_bal = "<i class='{$runningBalCls}'>" . $fn->getFormatNumber($row['running_bal']) . "</i>";

            $editUrl = "index.php?module=accountsg_journalMaster&_action=edit&record_id={$row['journal_master_id']}";
            $journalEditText = "<a class='edit-journal' href='{$editUrl}' title='Edit Journal'>Edit</a>";

            $entry_date = $dateUtil->formatDate($row['entry_date'], 'DD-MM-YYYY');

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter, '', $exp)}
            {$listObj->getGoToDetailText($rowCounter, $entry_date)}
            {$listObj->getListDataCell($this->getNarration($row))}
            {$listObj->getListDataCell($debit, 'right', '', '', $expDebit)}
            {$listObj->getListDataCell($credit, 'right', '', '', $expCredit)}
            {$listObj->getListDataCell($running_bal, 'right')}
            {$listObj->getListDataCell($journalEditText, 'center')}
            {$listObj->getListDataCell($row['journal_id'], 'center')}
            {$listObj->getListRowEnd($row['journal_id'])}
            ";
            $rowCounter++;
        }

        $text = "
    	{$this->getAccountInfo($rowAccHead)}
    	{$listObj->getListHeader($exp)}
        {$listObj->getListHeaderCell('Date', 'jm.entry_date')}
        {$listObj->getListHeaderCell('Narration')}
        {$listObj->getListHeaderCell('Debit', '', 'headerRight')}
        {$listObj->getListHeaderCell('Credit', '', 'headerRight')}
        {$listObj->getListHeaderCell('Balance', '', 'headerRight')}
        {$listObj->getListHeaderCell()}
        {$listObj->getListHeaderCell('ID', 'j.journal_id', 'headerCenter')}
    	{$listObj->getListHeaderEnd()}

        </tbody>
        <tr class='ledger-header'>
        <td></td>
        <td></td>
        <td align='right'>Total:</td>
        <td align='right' class='debit-color'>{$debit_sum}</td>
        <td align='right' class='credit-color'>{$credit_sum}</td>
        <td align='right'>{$running_bal_prev}</td>
        <td></td>
        <td></td>
        <td></td>
        </tr>

        {$rows}
        {$listObj->getListFooter()}
        ";
        return $text;
    }

    function getAccountInfo($rowAccHead) {
        $fn = Zend_Registry::get('fn');

        //print_r($rowAccHead);

        $brough_forward = $rowAccHead['brought_forward'] ? $rowAccHead['brought_forward'] : 0;
        if ($brough_forward != 0) {
            $brough_forward = $fn->getFormatNumber($brough_forward);
        }
        $ledger_balance = $fn->getFormatNumber($rowAccHead['ledger_balance']);
        $available_balance = $fn->getFormatNumber($rowAccHead['available_balance']);
        $ledger_balance_class = $rowAccHead['ledger_balance'] > 0 ? 'credit-color' : 'debit-color';
        $available_balance_class = $rowAccHead['available_balance'] > 0 ? 'credit-color' : 'debit-color';
        $ledger_balance = $ledger_balance ? $ledger_balance : 0;
        $available_balance = $available_balance ? $available_balance : 0;

        $text = "
        <div id='ledgerList' class='ui-corner-all'>
            <div class='container'>
                <h3>Please type in the account name and click GO</h3>
                <input type='text' name='acc_head' class='fld-acc_head' value='' />
                <a class='button' href='#'>GO</a>
            </div>
        </div>
        <div id='accountInfo' class='ui-corner-all'>
            <table>
            <tr>
            <th>Account:</th>
            <td class='accountName'>{$rowAccHead['account']}</td>
            <td>|</td>
            <th>B/F:</th>
            <td>{$brough_forward}</td>
            <td>|</td>
            <th>Ledger Bal.:</th>
            <td><span class='bold {$ledger_balance_class}'>{$ledger_balance}</span></td>
            <td>|</td>
            <th>Available Bal.:</th>
            <td><span class='bold {$available_balance_class}'>{$available_balance}</span></td>
            </tr>
            </table>
        </div>
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

        $acc_head_id = $fn->getReqParam('acc_head_id');
        $entry_date_from = $fn->getReqParam('entry_date_from');
        $entry_date_to = $fn->getReqParam('entry_date_to');

        $text = '';
        if ($acc_head_id != '') {
            $text = "
            <td style='width:290px'>
                <input allowEdit='1' type='text' name='entry_date_from' class='fld_date'
                        rel='pptxt:Start date' value='{$entry_date_from}' /> to
                <input allowEdit='1' type='text' name='entry_date_to' class='fld_date'
                         rel='pptxt:End date' value='{$entry_date_to}' />
                <input type='hidden' name='acc_head_id' value='{$acc_head_id}' />
            </td>
            ";
            // <td>
            //     <input type='text' name='acc_head' class='fld-acc_head' value='' rel='pptxt: Account' />
            // </td>
        }

        return $text;
    }

    function getNarration($row) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $acc_head_id = $fn->getReqParam('acc_head_id');

        $SQLJourn    = "SELECT a.title as alt_title
                     FROM journal j
                         LEFT JOIN (acc_head a) ON (a.acc_head_id = j.acc_head_id)
                     where j.journal_master_id = {$row['journal_master_id']}
                     AND j.acc_head_id != {$acc_head_id}
                     ";

        $resultJourn = $db->sql_query($SQLJourn);
        $rowJourn    = $db->sql_fetchrow($resultJourn);
        $narration_main = $row['narration_main'] . ' [From Ac : ' . $rowJourn['alt_title'] . ']';

        $text = "
        <div class='narration-main'>
            {$narration_main}
        </div>
        <div class='narration'>
            {$row['narration']}
        </div>
        ";

        return $text;
    }

}
