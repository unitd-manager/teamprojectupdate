<?
class CP_Admin_Modules_Wine_Producer_Model extends CP_Common_Modules_Wine_Producer_Model
{
    /**
     *
     */
    function getSQL() {

        $SQL   = "
        SELECT p.*
        FROM producer p
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'p';


        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.producer_id = {$tv['record_id']}";

        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                    p.title LIKE '%{$tv['keyword']}%'  
                    OR p.producer_code LIKE '%{$tv['keyword']}%'  
                    OR p.chi_title LIKE '%{$tv['keyword']}%'  
                    OR p.description LIKE '%{$tv['keyword']}%'  
                    OR p.chi_description LIKE '%{$tv['keyword']}%'  
                )";
            }
        }
        
        $searchVar->sortOrder = "p.sort_order ASC, p.title ASC";
    }
    
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('producer_code', 'Please enter the producer code');
        $validate->validateData('title', 'Please enter the producer name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['sort_order']   = $fn->getNextSortOrder('producer');
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('producer_code', 'Please enter the producer code');
        $validate->validateData('title', 'Please enter the producer name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);
        $fa = $fn->addToFieldsArray($fa, 'producer_code');
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }

    /**
     *
     */
    function getImportData(){
        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper');

        $fa = array(
              'producer_code'    => $phpExcel->getImportFldObj('Producer Code')
             ,'title'            => $phpExcel->getImportFldObj('Producer E')
             ,'chi_title'        => $phpExcel->getImportFldObj('Producer C')
             ,'description'      => $phpExcel->getImportFldObj('Description E')
             ,'chi_description'  => $phpExcel->getImportFldObj('Description C')
             ,'picture1'         => $phpExcel->getImportFldObj('Picture Ref 1')
             ,'picture2'         => $phpExcel->getImportFldObj('Picture Ref 2')
             ,'picture3'         => $phpExcel->getImportFldObj('Picture Ref 3')                          
             ,'published'        => $phpExcel->getImportFldObj('Published')

        );

        $fa['published']['defaultValue'] = 1;
        $fa['picture1']['refOnly']       = true;
        $fa['picture2']['refOnly']       = true;
        $fa['picture3']['refOnly']       = true;

        /****************************************/
        $config = array(
             'module'              => 'wine_producer'
            ,'matchFieldArr'       => array('producer_code')
            ,'mandatoryFldsArr'    => array('producer_code')
            ,'fldsArr'             => $fa
            ,'callbackAfterInsert' => 'callbackAfterImportInsert'
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function callbackAfterImportInsert($producer_id, $fa) {
        $fn = Zend_Registry::get('fn');
        $media = Zend_Registry::get('media');
        $db = Zend_Registry::get('db');

        for($i = 1; $i <= 3; $i++){
            $fldName = "picture{$i}";
            if ($fa[$fldName] != ''){
                $picture = $fa[$fldName];
                $sourceFilePath = realpath('../media_import/producer') . "/{$picture}";
                $exp = array(
                     'srcFile' => $sourceFilePath
                    ,'actualFileName' => $picture
                );

                $condn = "
                record_id = {$producer_id}
                AND actual_file_name = '{$fa['picture1']}' 
                AND room_name = 'wine_producer' 
                AND record_type = 'picture'";
                $mediaRow = $fn->getRecordByCondition('media', $condn);
                if(!is_array($mediaRow)){
                    $media->model->createMedia('wine_producer', 'picture', $producer_id, $exp);
                }
            }
        }
    }
}
