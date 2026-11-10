<?
class CP_Admin_Widgets_Project_DomainHostingSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT r.*       	  	
      	      ,c.company_name
        FROM renewals r
        LEFT JOIN company c ON (r.company_id = c.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

        $searchVar->sortOrder = "r.company_id ASC, r.end_date DESC";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'project_domainHostingSummary');
        
        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}