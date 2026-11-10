<?
class CP_Admin_Widgets_Project_TopCompanies_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DATE_FORMAT(enquiry_date, '%b %Y') AS yearMonth
              ,SUM(estimated_value{$this->getFldSfx()}) AS opportunity_value_ref
        FROM `opportunity`
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

        $searchVar->sqlSearchVar[] = "(enquiry_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->sqlSearchVar[] = "estimated_value > 0";
        $searchVar->groupBy = "DATE_FORMAT(enquiry_date, '%Y-%m')";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_topCompanies');

        $arr = array();
        foreach ($dataArray as $row){
            $tmpArr = &$arr[];
            $tmpArr['yearMonth'] = $row['yearMonth'];
            $tmpArr['opportunityValue'] = $row['opportunity_value_ref'];
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