<?
abstract class CP_Common_Lib_ModuleModelAbstract
{
    var $controller = null;
    var $view = null;
    var $fns = null;
    var $dataArray = array();
    var $expForGetSql = array();
    var $expForSearchVar = array();

    /**
     *
     */
    function getEditValidate(){
        return $this->getNewValidate();
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
        $id = $fn->addRecord($fa);
        return $fn->returnAfterNewSave($id);
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
        return $fn->returnAfterNewSave($id);
    }

    function getSaveSingleField(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $value = $fn->getReqParam('value');
        $id = $fn->getReqParam('id'); //ex: exch_rate_sell__row-2
        list($fieldName, $rowId) = explode('__', $id);
        list(,$rowId) = explode('-', $rowId);

        $moduleArr = $modulesArr[$tv['module']];
        $tableName = $moduleArr['tableName'];
        $keyField = $moduleArr['keyField'];

        $fa = array();
        $fa[$fieldName] = $value;
        $fn->saveRecord($fa, $tableName, $keyField, $rowId);

        return $value;
    }

    function getRecord(){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $moduleArr = $modulesArr[$tv['module']];
        $tableName = $moduleArr['tableName'];
        $keyField = $moduleArr['keyField'];

        $record_id = $fn->getReqParam('record_id');
        $exp = array('fetchType' => MYSQL_ASSOC);
        $row = $fn->getRecordRowByID($tableName, $keyField, $record_id, $exp);

        return $row;
    }

    /**
     *
     */
    function getSaveList(){
        $fn = Zend_Registry::get('fn');
        $task = $fn->getPostParam('task');

        if($task == 'delete'){
            $this->getDeleteFromList();
        }
    }

    /**
     *
     */
    function getDeleteFromList(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $spActionObj = includeCPClass('Lib', 'SpecialAction');
        $cid  = $fn->getPostParam('cid', '', false, true);
        foreach($cid as $key => $value ) {
            $spActionObj->getDeleteRecordByID($tv['module'], $value);
        }
    }

    /**
     *
     */
    function getModuleDataArray(){
        $media = Zend_Registry::get('media');
        $modelHelper = Zend_Registry::get('modelHelper');
        $modulesArr = Zend_Registry::get('modulesArr');
        $modelHelper->setModuleDataArray();
        $module = $this->controller->name;
        $table = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if ($table == 'content'){
            $module = 'webBasic_content';
        }

        $media->model->setMediaArray($module);
        $mediaArray = Zend_Registry::get('mediaArray');
        $moduleMediaArr = isset($mediaArray[$module]) ? $mediaArray[$module] : array();

        $dataArray = $this->dataArray;

        $arr = array();
        foreach($dataArray AS $row){
            $recMediaArr = array();
            foreach($moduleMediaArr AS $recType => $mediaArr){
                $recMediaArr[$recType] = $media->getMediaFilesArray($module, $recType, $row[$keyFieldName]);
            }

            $row['media'] = $recMediaArr;
            $arr[] = $row;
        }
        return $arr;
    }
}
