<?
class CP_Admin_Widgets_EnggCrm_InvoiceChartByMonth_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%b %Y') AS invoice_month
              ,(SUM(i.invoice_amount)) AS invoice_amount_monthly
        FROM invoice i
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $searchVar->sqlSearchVar[] = "(i.invoice_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->groupBy = "DATE_FORMAT(i.invoice_date, '%Y-%m')";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_invoiceChartByMonth');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }
}