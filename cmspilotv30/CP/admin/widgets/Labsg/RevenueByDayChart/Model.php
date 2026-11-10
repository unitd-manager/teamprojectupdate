<?
class CP_Admin_Widgets_Labsg_RevenueByDayChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT i.invoice_date
              ,DATE_FORMAT(i.invoice_date, '%d-%m-%y') AS day
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
        $month          = date('m');
        $year           = date('Y');

        $start_date = $year . '-' . $month . '-' . '01';
        $end_date = $year . '-' . $month . '-' . '31';
        $searchVar->sqlSearchVar[] = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
        $searchVar->sqlSearchVar[] = "i.status != 'Cancelled'";
        $searchVar->groupBy = "i.invoice_date";
    }

    /**
     *
     */
    function getDataArray() {
        $modelHelper = Zend_Registry::get('modelHelper');

        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_revenueByDayChart');
        $this->dataArray = $dataArray;
        return $this->dataArray;
    }
}