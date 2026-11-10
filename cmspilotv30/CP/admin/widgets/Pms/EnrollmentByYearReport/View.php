<?
class CP_Admin_Widgets_Pms_EnrollmentByYearReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $site_id      = $fn->getReqParam('site_id');
        
        if(is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th>Summary</th>
                </thead>
                <tr>
                    <td>Branch : {$branch_name}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
					<th>Year</th>
					<th>No of Students</th>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        
        $rows = '';
        $serial_no = 0;

        foreach($this->model->dataArray as $row){
            $rows .= "
            <tr>
                <td>{$row['year_of_enrollment']}</td>
                <td>{$row['no_of_students']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}