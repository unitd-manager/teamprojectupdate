<?
class CP_Admin_Modules_Web2_Tags_Model extends CP_Common_Modules_Web2_Tags_Model
{
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();
        $validate->validateData('tag_text', 'Please enter the tag text');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'tag_text', '', true);
        $fa = $fn->addToFieldsArray($fa, 'published');
        
        return $fa;
    }
    
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'tag_text' => $phpExcel->getFldObj('Tag')
        );

        $config = array(
             'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }     
}
