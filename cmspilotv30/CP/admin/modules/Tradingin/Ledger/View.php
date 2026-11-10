<?
class CP_Admin_Modules_Tradingin_Ledger_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $sql = "
        (
        SELECT i.invoice_amount AS debit_amount
              ,0 AS credit_amount
              ,i.invoice_date AS date
              ,i.invoice_code AS code
        FROM invoice i
        WHERE i.status != 'Cancelled'
        ) UNION (
        SELECT 0 AS debit_amount
              ,r.amount AS credit_amount
              ,r.date AS date
              ,r.receipt_code AS code
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN (invoice i) ON (irh.invoice_id = i.invoice_id)
        WHERE i.status != 'Cancelled'
        )
        ORDER BY date ASC
        ";
        $result = $db->sql_query($sql);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        $rows = '';
        foreach ($dataArray as $row){
            $invoice_date = $dateUtil->formatDate($row['date'], 'DD-MM-YYYY');

            $rows .= "
            <tr>
                <td class='w100'>{$invoice_date}</td>
                <td class='txtRight w100'>Account</td>
                <td class='w100'>{$row['code']}</td>
				<td class='txtRight'>{$row['credit_amount']}</td>
				<td class='txtRight'>{$row['debit_amount']}</td>
            </tr>
            ";
        }

        $formAction = "";

        $text = "
        <form id='' class='yform columnar' method='post' action='{$formAction}'>
            <table class='thinlist room-invSeq-table'>
                <tr>
                    <thead>
                        <tr>
                            <th><strong>DATE</strong></th>
                            <th><strong>ACCOUNT</strong></th>
                            <th><strong>NARRATION</strong></th>
                            <th><strong>CREDIT</strong></th>
                            <th><strong>DEBIT</strong></th>
                        </tr>
                    </thead>

                    <tbody>
                        {$rows}
                    </tbody>
                </tr>
            </table>
        </form>
        ";

        return $text;
    }

}