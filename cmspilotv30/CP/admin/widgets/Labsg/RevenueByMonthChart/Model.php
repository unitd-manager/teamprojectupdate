<?
class CP_Admin_Widgets_Labsg_RevenueByMonthChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DATE_FORMAT(i.invoice_date, '%b %Y') AS invoice_month
              ,SUM(i.invoice_amount) AS total_invoice_amount
              ,SUM(i.discount) AS total_discount
        FROM invoice i
        LEFT JOIN (`order` o) ON (o.order_id = i.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'i';

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $searchVar->sqlSearchVar[] = "(i.invoice_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->groupBy = "DATE_FORMAT(i.invoice_date, '%Y-%m')";
    }

    /**
     *
     */
    function getDataArray() {
        $modelHelper = Zend_Registry::get('modelHelper');

        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_revenueByMonthChart');
        $this->dataArray = $dataArray;
        return $this->dataArray;
    }
}