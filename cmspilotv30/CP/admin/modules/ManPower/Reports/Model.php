<?
class CP_Admin_Modules_ManPower_Reports_Model extends CP_Common_Lib_ModuleModelAbstract
{
    var $reportsArray = array();

    function __construct() {
        $cpUtil  = Zend_Registry::get('cpUtil');

        $this->reportsArray = array(
            'marketingCallByStaffReport'  => $this->getReportObj('marketingCallByStaffReport', 'Marketing Call By Staff Report')
           ,'marketingCallOverallReport'  => $this->getReportObj('marketingCallOverallReport', 'Marketing Call Overall Report')
           ,'incomeExpenses'              => $this->getReportObj('incomeExpenses', 'Income Expenses')
           ,'staffAttendanceReport'       => $this->getReportObj('staffAttendanceReport', 'Staff Attendance Report')
           ,'staffAttendanceOverallReport'=> $this->getReportObj('staffAttendanceOverallReport', 'Staff Attendance Overall Report')
           ,'opportunityByMonthReport'    => $this->getReportObj('opportunityByMonthReport', 'Opportunity By Month Report')
           ,'marketingCallByStaffReport'  => $this->getReportObj('marketingCallByStaffReport', 'Marketing Call By Staff Report')
           ,'marketingCallOverallReport'  => $this->getReportObj('marketingCallOverallReport', 'Marketing Call Overall Report')
           ,'incomeExpenses'              => $this->getReportObj('incomeExpenses', 'Income Expenses')
           ,'opportunityPositionReport'   => $this->getReportObj('opportunityPositionReport', 'Opportunity PositionReport')
           ,'projectPositionReport'       => $this->getReportObj('projectPositionReport', 'Project Position Report')
           ,'taxReport'                   => $this->getReportObj('taxReport', 'Tax Report')
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

}
