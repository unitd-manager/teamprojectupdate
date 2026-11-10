<?
class CP_Admin_Modules_Directory_Promotion_Model extends CP_Common_Modules_Directory_Promotion_Model
{
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'business_name' => $phpExcel->getFldObj('Business')
             ,'record_type' => $phpExcel->getFldObj('Record Type')
             ,'title' => $phpExcel->getFldObj('Promo Title')
             ,'start_date' => $phpExcel->getFldObj('Start Date')
             ,'end_date' => $phpExcel->getFldObj('End Date')
             ,'start_time' => $phpExcel->getFldObj('Start Time')
             ,'end_time' => $phpExcel->getFldObj('End Time')
             ,'custom_text' => $phpExcel->getFldObj('Custom Text')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }   
}
