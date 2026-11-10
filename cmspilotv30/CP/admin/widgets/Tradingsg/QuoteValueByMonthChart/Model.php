<?
class CP_Admin_Widgets_Tradingsg_QuoteValueByMonthChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "        
        SELECT DATE_FORMAT(q.quote_date, '%M') AS quote_value_month
              ,(SUM(qp.selling_price * qp.qty)) AS total_selling_price
			  ,(SELECT (SUM(qp.selling_price * qp.qty))
				FROM quote_product qp
				WHERE q.status = 'Customer Confirmed'
				) AS confirmed_selling_price
        FROM quote_product qp
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $searchVar->sqlSearchVar[] = "(q.quote_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->groupBy = "DATE_FORMAT(q.quote_date, '%Y-%m')";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_quoteValueByMonthChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}