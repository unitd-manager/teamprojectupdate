<?
class CP_Admin_Widgets_Pms_SummaryDisplay_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT c.title AS course_title
              ,b.title AS batch_title
              ,b.venue 
              ,b.start_time 
              ,b.end_time 
              ,t.first_name as trainer_name
			  ,(SELECT COUNT(*)
				FROM course_contact cc
				WHERE cc.batch_id = b.batch_id
                AND b.status = 'Current'
				) AS attendee_count
        FROM batch b
        JOIN course c ON (c.course_id = b.course_id)
        JOIN teacher t ON (t.teacher_id = b.teacher_id)
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $searchVar->mainTableAlias = 'b';

        $searchVar->sqlSearchVar[] = "b.status = 'Current'";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_traineeByCourse');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }
    
    function getExportToExcel($dataArray = ''){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!is_array($dataArray)){
            $dataArray = $this->getDataArray();
        }

        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');
         
        $fa = array(
              'course_title'  => $phpExcel->getFldObj('Course')
             ,'total'         => $phpExcel->getFldObj('Total')
        );

        $file_name = "IncomeByCourse_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}