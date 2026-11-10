<?
class CPL_Admin_Widgets_Project_RevenueFromPilot_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(p.start_date, '%b %Y') AS yearMonth
              ,SUM(p.project_value{$this->getFldSfx()}) AS project_value_ref
        FROM `project` p
        LEFT JOIN (company c) ON (p.company_id = c.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $lastMonth   = date('Y-m-t', strtotime('-1 months'));

        $searchVar->sqlSearchVar[] = "c.company_name = 'Pilot Simple Software'";
        $searchVar->sqlSearchVar[] = "(p.start_date BETWEEN '{$last12Month}' AND '{$lastMonth}')";
        $searchVar->groupBy = "DATE_FORMAT(p.start_date, '%Y-%m')";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_revenueFromPilot');

        $arr = array();
        foreach ($dataArray as $row){
            
            if ($row['project_value_ref'] > 0) {
                $tmpArr = &$arr[];
                $tmpArr['yearMonth'] = $row['yearMonth'];
                $tmpArr['projectValue'] = $row['project_value_ref'];
            }
        }

        $this->dataArray = $arr;
        return $this->dataArray;
    }

    //==================================================================//
    function getFldSfx() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($cpCfg['m.project.hasMultiCurrency'] == 1){
            return '_base';
        }
    }
}