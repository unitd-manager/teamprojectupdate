<?
class CP_Admin_Widgets_AceIms_AttendeeByMonth_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
	    SELECT DISTINCT c.title AS course_title
	    	  ,c.course_id
	    	  ,(SELECT COUNT(*)
	    		FROM course_contact cc
	    		WHERE cc.course_id = c.course_id
	    		) AS attendee_count
	    FROM `course` c
	    JOIN course_contact cc ON ( cc.course_id = c.course_id )
        ";      
        
        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'cc';

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        //$searchVar->sqlSearchVar[] = "attendee_count > 0";
        //$searchVar->sqlSearchVar[] = "(start_date BETWEEN '{$last12Month}' AND '{$today}')";
        //$searchVar->groupBy = "DATE_FORMAT(start_date, '%Y-%m')";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'aceIms_attendeeByMonth');

        $arr = array();
        foreach ($dataArray as $row){
            $tmpArr = &$arr[];
            $tmpArr['course_title'] = $row['course_title'];
            $tmpArr['attendee_count'] = $row['attendee_count'];
        }

        $this->dataArray = $arr;
        return $this->dataArray;
    }

    /**
     *
     */
    function getInvoiceByMonthSQL(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id = $fn->getSessionParam('cp_site_id');
        
        $sqlAppend = '';
        if ($site_id) {
            $sqlAppend = "AND o.site_id = {$site_id}";
        }

        if ($cpCfg['w.aceIms.attendanceByMonth.invoiceByMonthForPvt']) {
            $SQL = "
            SELECT DATE_FORMAT(irh.invoice_date, '%M') AS month
                  ,ABS(SUM(irh.amount)) AS total
            FROM invoice_receipt_history irh
            WHERE irh.invoice_paid_status IS NULL
                  {$sqlAppend}
            GROUP BY DATE_FORMAT(irh.invoice_date, '%M')
            ORDER BY irh.invoice_date
            ";
        } else {
            $SQL = "
            SELECT DATE_FORMAT(o.order_date, '%M') AS month
                  ,ABS(SUM(oi.unit_price)) AS total
            FROM `order` o
            JOIN order_item oi ON oi.order_id = o.order_id
            WHERE o.order_status = 'Due'
              AND oi.module = 'aceIms_course'
              {$sqlAppend}
            GROUP BY DATE_FORMAT(o.order_date, '%M')
            ORDER BY o.order_date
            ";
        }

        $result  = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = array();
        foreach ($dataArray as $row){
            $tmpArr = &$arr[];
            $tmpArr['month'] = $row['month'];
            $tmpArr['total'] = $row['total'];
        }

        $this->dataArray = $arr;
        return $this->dataArray;
    }
    /**
     *
     */
    function getAttendeeByCourseSQL(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $site_id = $fn->getSessionParam('cp_site_id');

        $appendSql = '';
        if ($site_id) {
            $appendSql = " AND cc.site_id = {$site_id}";
        }
        
        $current_year = date('Y');

        $SQL = "
	    SELECT DISTINCT c.title AS course_title
	    	  ,c.course_id
	    	  ,(SELECT COUNT(*)
                FROM course_contact cc
	    		WHERE cc.course_id = c.course_id
	    		) AS attendee_count
	    FROM `course` c
	    JOIN course_contact cc ON ( cc.course_id = c.course_id )
        ";

        $result  = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = array();
        foreach ($dataArray as $row){
            $tmpArr = &$arr[];
            $tmpArr['course_title'] = $row['course_title'];
            $tmpArr['attendee_count'] = $row['attendee_count'];
        }

        $this->dataArray = $arr;
        return $this->dataArray;
    }
}