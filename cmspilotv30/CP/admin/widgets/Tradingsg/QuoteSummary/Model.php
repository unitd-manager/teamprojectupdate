<?
class CP_Admin_Widgets_Tradingsg_QuoteSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT  q.quote_code
                ,q.quote_id
                ,q.quote_date
                ,(SELECT SUM(qty*selling_price) AS Quote_Amount 
                         FROM quote_product 
                         WHERE quote_id=o.quote_id) AS Quote_Amount
                ,(SELECT SUM(invoice_amount) 
                         FROM `invoice` 
                         WHERE order_id=o.order_id AND status!='Cancelled') AS Invoice_Amount
                ,(SELECT SUM(amount) 
                         FROM `receipt` 
                         WHERE order_id=o.order_id) AS Receipt_Amount
                ,(SELECT CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name 
                         FROM staff s 
                         WHERE s.staff_id=q.staff_id)AS Staff_Name
        FROM `order` o
        LEFT JOIN quote q ON q.quote_id=o.quote_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');

        $start_date = $current_year . '-' . $current_month . '-' . '01';
        $end_date = $current_year . '-' . $current_month . '-' . '31';
        $searchVar->sqlSearchVar[] = "q.quote_date BETWEEN '{$start_date}' AND '{$end_date}' AND o.record_type='Quote'";
        $searchVar->sortOrder = 'q.quote_code DESC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_quoteSummary');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}