<?
class CP_Admin_Modules_Tradingsg_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array(
            'salesByMonth'	 			       => $this->getReportObj('salesByMonth', 'Sales by Month')
           ,'salesByYear' 				       => $this->getReportObj('salesByYear', 'Sales by Year')
           ,'invoiceByMonth'			       => $this->getReportObj('invoiceByMonth', 'Invoice by Month')
           ,'invoiceByYear' 			       => $this->getReportObj('invoiceByYear', 'Invoice by Year')
           ,'profitByMonth'				       => $this->getReportObj('profitByMonth', 'Profit by Month')
           ,'profitByYear'				       => $this->getReportObj('profitByYear', 'Profit by Year')
           ,'quoteByMonth'				       => $this->getReportObj('quoteByMonth', 'Quote by Month')
           ,'quoteByYear'				         => $this->getReportObj('quoteByYear', 'Quote by Year')
           ,'salesByClient'				       => $this->getReportObj('salesByClient', 'Sales by Client')
           ,'invoiceByClient'			       => $this->getReportObj('invoiceByClient', 'Invoice by Client')
           ,'enquiryByMonth'			       => $this->getReportObj('enquiryByMonth', 'Enquiry by Month')
           ,'enquiryByYear'				       => $this->getReportObj('enquiryByYear', 'Enquiry by Year')
           ,'leadByStaff'				         => $this->getReportObj('leadByStaff', 'Lead By Staff')
           ,'enquiryByStaff'			       => $this->getReportObj('enquiryByStaff', 'Enquiry By Staff')
           ,'enquiryActivityByStaff'	   => $this->getReportObj('enquiryActivityByStaff', 'Enquiry Activity By Staff')
           ,'salesSummaryByProduct'	     => $this->getReportObj('salesSummaryByProduct', 'Sales Summary By Product')
           ,'quoteByStaff'				       => $this->getReportObj('quoteByStaff', 'Quote By Staff')
           ,'gstReport'                  => $this->getReportObj('gstReport', 'GST Report')
           ,'salesSummaryByProduct'	     => $this->getReportObj('salesSummaryByProduct', 'Sales Summary By Product')
           ,'quoteByStaff'				       => $this->getReportObj('quoteByStaff', 'Quote By Staff')
           ,'gstReport'                  => $this->getReportObj('gstReport', 'GST Report')
           ,'summaryPurchaseSales'		   => $this->getReportObj('summaryPurchaseSales', 'Summary Purchase Sales')
           ,'summaryPurchase'		         => $this->getReportObj('summaryPurchase'     , 'Summary Purchase')
           ,'summarySales'		           => $this->getReportObj('summarySales'        , 'Summary Sales')
           ,'detailInvoiceByMonth'       => $this->getReportObj('detailInvoiceByMonth', 'Detail Invoice by Month')
           ,'salesSummaryByProductGroup' => $this->getReportObj('salesSummaryByProductGroup', 'Sales Summary By Product Group')
           ,'gstReport'               	 => $this->getReportObj('gstReport', 'GST Report')
           ,'summaryPurchaseSales'			 => $this->getReportObj('summaryPurchaseSales', 'Summary Purchase Sales')
           ,'summaryPurchase'		      	 => $this->getReportObj('summaryPurchase'     , 'Summary Purchase')
           ,'summarySales'		        	 => $this->getReportObj('summarySales'        , 'Summary Sales')
           ,'detailInvoiceByMonth'    	 => $this->getReportObj('detailInvoiceByMonth', 'Detail Invoice by Month')
           ,'salesSummaryByProductGroup' => $this->getReportObj('salesSummaryByProductGroup', 'Sales Summary By Product Group')
           ,'overallGstSummary'          => $this->getReportObj('overallGstSummary', 'Overall GST Summary')
           ,'overallSalesSummary'        => $this->getReportObj('overallSalesSummary', 'Overall Sales Summary')
           ,'ir8a'                       => $this->getReportObj('ir8a', 'IR8A Report')
           ,'employeePayslipGeneratedReport' => $this->getReportObj('employeePayslipGeneratedReport', 'Payslip Generated Report')
           ,'employeeSalaryReport'           => $this->getReportObj('employeeSalaryReport', 'Employee Salary Report')
           ,'cPFSummaryReport'               => $this->getReportObj('cPFSummaryReport', 'CPF Summary Report')
           ,'leaveReport'                    => $this->getReportObj('leaveReport', 'Leave Report')
        );

    }

    function getReportObj($name, $title, $searchFlds = array('dateRange')) {

        //searchFldType: uptoDate, dateRange, activeRange
        $arr = array(
             'name' => $name
            ,'title' => $title
            ,'searchFlds' => $searchFlds
        );

        return $arr;
    }
    /**
     *
     */
     function getIncomeByCourse($SQLNeeded = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $text = "";
        $rows = "";
        $sqlStartDate = "";
        $sqlEndDate = "";

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $status     = $fn->getReqParam('specialSearch');

        if ($status == ''){
            $status = 'Due';
        }

        if ($start_date != ''){
            $sqlStartDate = " AND o.creation_date >= '{$start_date}'";
        }

        if ($end_date != ''){
            $sqlEndDate = " AND o.creation_date <= '{$end_date}'";
        }

        //$SQL =  $this->getTraineeByCourseSQL();

        $SQL = "
        SELECT ABS( ABS( SUM( oi.unit_price ) ) ) AS total
              ,c.title as course_title
        FROM `order` o
        JOIN order_item oi ON oi.order_id = o.order_id
        LEFT JOIN course c ON c.course_id = oi.record_id
        WHERE o.order_status = '{$status}'
        {$sqlStartDate}
        {$sqlEndDate}
        GROUP BY oi.record_id
        ORDER BY course_title
        ";

        if ($SQLNeeded == 1){
            return $SQL;
        }

        $result = $db->sql_query($SQL);
        $resultTable = $db->sql_query($SQL);

        $rows = array(
         'course_title'
        ,'total'
        );

        $columns = array(
        'Course'
        ,'Total'
        );

        $text .= $fn->getTableRowsColumns($resultTable, $rows, $columns);

        return $text;
    }

}
