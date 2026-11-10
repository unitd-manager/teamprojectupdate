<?
class CP_Admin_Modules_Tradingin_Ledger_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT i.*
              ,o.record_type
        FROM invoice i
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        ";

        return $SQL;
    }

    /**
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'i';
        $start_date = date("Y-m-1", strtotime("first day of previous month") );
        $end_date = date("Y-m-t", strtotime("last day of previous month") );

        $searchVar->sqlSearchVar[] = "i.vat = 1";
        //$searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        //$searchVar->sqlSearchVar[] = "i.invoice_date BETWEEN '{$start_date}' AND '{$end_date}'";
        $searchVar->sortOrder = 'i.invoice_date DESC';
    }

    /**
     *
     */
    function getUpdateInvSeq() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $invoice_id = $fn->getReqParam('invoice_id');
        $invoice_date     = $fn->getReqParam('invoice_date');
        $code     = $fn->getReqParam('code');

        $append = '';
        if($code) {
            $append = "SET invoice_code_vat = '{$code}'";
        } else if ($invoice_date) {
            $append = "SET invoice_date = '{$invoice_date}'";
        }

        $SQLUpdate    = "
        UPDATE invoice
        {$append}
        WHERE invoice_id = {$invoice_id}
        ";
        $result = $db->sql_query($SQLUpdate);

    }
}
