<?
class CP_Admin_Widgets_Account_TrialBalance_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget($exp = array()){
        $c = &$this->controller;
        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist trialBalance'>
                <thead>
                <tr>
                    <th class='account'>Account Name</th>
                    <th class='currency'>Currency</th>
                    <th class='txtRight'>Debit</th>
                    <th class='txtRight'>Credit</th>
                    <th class='txtRight'>Debit (HKD)</th>
                    <th class='txtRight'>Credit (HKD)</th>
                </tr>
                </thead>
                {$rowsHTML}
            </table>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');

        $rows = '';

        $dataArray = $this->model->dataArray;

        $debit_sum = 0;
        $credit_sum = 0;
        $debit_base_sum = 0;
        $credit_base_sum = 0;
        $opening_balance_base_sum = 0;
        $closing_balance_base_sum = 0;
        foreach ($dataArray as $key => $value) {
            $row = $dataArray[$key];

            $space = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $row['level'] - 1);
            $spaceCat = str_repeat('&nbsp;&nbsp;&nbsp;', $row['level']);
            $title = '';
            if ($row['account'] != '') {
                $ledgerUrl = "index.php?_topRm=account&module=account_ledger&acc_head_id={$row['acc_head_id']}";
                $title = "{$space}<a href='{$ledgerUrl}'>[{$row['code']}] {$row['account']}</a>";
            } else {
                $title = "{$spaceCat}<b>{$row['acc_category']}</b>";
            }
            $debit = $fn->getFormatNumberNegative($row['debit']);
            $credit = $fn->getFormatNumber($row['credit']);
            $debit_base = $fn->getFormatNumberNegative($row['debit_base']);
            $credit_base = $fn->getFormatNumber($row['credit_base']);

            $rows .= "
            <tr>
                <td>{$title}</td>
                <td class='currency'>{$row['currency_code']}</td>
                <td class='debit-color txtRight'>{$debit}</td>
                <td class='credit-color txtRight'>{$credit}</td>
                <td class='debit_base-color txtRight'>{$debit_base}</td>
                <td class='credit_base-color txtRight'>{$credit_base}</td>
            </tr>
            ";
        }

        $sumArr = $this->model->getSummaryDataArr();
        $debit_base_sum = $sumArr['debit_base_sum'];
        $credit_base_sum = $sumArr['credit_base_sum'];
        $debit_base_sum = $fn->getFormatNumberNegative($debit_base_sum);
        $credit_base_sum = $fn->getFormatNumber($credit_base_sum);

        $rows = "
        <thead>
        <tr>
            <th></th>
            <th></th>
            <th class='txtRight debit_base-color'></th>
            <th class='txtRight credit_base-color'></th>
            <th class='txtRight debit_base-color'>{$debit_base_sum}</th>
            <th class='txtRight credit_base-color'>{$credit_base_sum}</th>
        </tr>
        </thead>
        {$rows}
        ";

        return $rows;
    }
}