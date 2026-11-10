<?
class CP_Admin_Widgets_Tradingsg_TodaysActivity_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $current_date = date('Y-m-d');

        $appendSql1 = '' ;
        $appendSql2 = '' ;

        if ($cpSiteIdSession  != '') {
          $appendSql1 = "AND q.site_id = {$cpSiteIdSession}";
          $appendSql2 = "AND o.site_id = {$cpSiteIdSession}";
        }


        $SQL = "
          SELECT 'QUOTE' AS MODULE 
                ,q.quote_code AS CODE
                ,q.quote_date AS DATES
                ,q.created_by AS CREATED_BY
                ,q.modified_by AS MODIFIED_BY
                ,CONCAT_WS(' ', s.first_name, s.last_name) AS STAFF_NAME
                ,q.quote_id AS id
                FROM quote q
                LEFT JOIN staff s ON (s.staff_id=q.staff_id)
          WHERE quote_date = '{$current_date}' {$appendSql1}
          UNION

          SELECT case o.record_type
                 when 'POS' then 'ORDER - POS'
                 when 'Quote' then 'ORDER - QUOTE'
                 end as MODULE 
                ,o.order_id AS CODE
                ,o.order_date AS DATES
                ,o.created_by AS CREATED_BY
                ,o.modified_by AS MODIFIED_BY
                ,NULL AS STAFF_NAME 
                ,o.order_id AS id
          FROM `order` o
          WHERE order_date = '{$current_date}' {$appendSql2}
          UNION 

          SELECT case o.record_type
                 when 'POS' then 'INVOICE - POS'
                 when 'Quote' then 'INVOICE - QUOTE'
                 end as MODULE
                ,i.invoice_code AS CODE
                ,i.invoice_date AS DATES
                ,i.created_by AS CREATED_BY
                ,NULL AS MODIFIED_BY
                ,CONCAT_WS(' ', s.first_name, s.last_name) AS STAFF_NAME 
                ,o.order_id AS id 
          FROM invoice i
          LEFT JOIN staff s ON (s.staff_id=i.staff_id)
          LEFT JOIN `order` o on o.order_id=i.order_id 
          WHERE i.status!='Cancelled' AND o.order_date='{$current_date}' {$appendSql2}
          UNION
          
          SELECT case o.record_type
                 when 'POS' then 'RECEIPT - POS'
                 when 'Quote' then 'RECEIPT - QUOTE'
                 end as MODULE
                ,r.receipt_code AS CODE
                ,r.date AS DATES
                ,r.created_by AS CREATED_BY
                ,r.modified_by AS MODIFIED_BY
                ,NULL AS STAFF_NAME
                ,o.order_id AS id 
          FROM receipt r
          LEFT JOIN `order` o ON o.order_id = r.order_id 
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
        $searchVar->sqlSearchVar[] = "o.order_date='{$current_date}'";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_todaysActivity');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}