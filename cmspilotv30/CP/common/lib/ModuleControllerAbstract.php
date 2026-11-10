<?
abstract class CP_Common_Lib_ModuleControllerAbstract
{
    var $view = null;
    var $model  = null;
    var $fns    = null;
    var $name   = '';

    /**
     *
     */
    function getController() {
        $fn = Zend_Registry::get('fn');
        $fnName = $fn->getFnNameByAction();
        return $this->$fnName();
    }

    /**
     *
     */
    function getNew(){
        $viewHelper = Zend_Registry::get('viewHelper');
        $tv = Zend_Registry::get('tv');

        $text = $this->view->getNew();
        if($tv['spAction'] == 'new'){
            return $text;
        } else {
            return $viewHelper->getNewViewWrapper($text);
        }

        return $text;
    }

    /**
     *
     */
    function getList($alternateListFn = '', $exp = array()){
        $viewHelper = Zend_Registry::get('viewHelper');
        $fn = Zend_Registry::get('fn');
        $this->setModuleDataArray();
        $returnDataOnly = $fn->getIssetParam($exp, 'returnDataOnly');

        if ($returnDataOnly){
            return $this->model->dataArray;
        }

        $fnName = ($alternateListFn != '') ? 'get' . ucfirst($alternateListFn) : 'getList';

        if (method_exists($this->view, $fnName)) {
            $text = $this->view->$fnName($this->model->dataArray);
            $text = $viewHelper->getListViewWrapper($text);
            return $text;
        }
    }

    /**
     *
     */
    function getExtendedPanel(){
        if (method_exists($this->view, 'getExtendedPanel')) {
            return $this->getList('extendedPanel');
        }
    }

    /**
     *
     */
    function setModuleDataArray(){
        $modelHelper = Zend_Registry::get('modelHelper');
        $modelHelper->setModuleDataArray();
    }

    /**
     *
     */
    function getDetail($alternateDetailFn = ''){
        $viewHelper = Zend_Registry::get('viewHelper');
        $tv = Zend_Registry::get('tv');

        $text = $this->getDetailTextCommon($alternateDetailFn);


        if($tv['spAction'] == 'detail'){
            $text = $this->view->getDetail();
            return $text;
        } else {
            if (CP_SCOPE == 'www'){
                $text = $viewHelper->getDetailViewWrapperWww($text);
            } else {
                $text = $viewHelper->getDetailViewWrapper($text);
            }
        }

        return $text;
    }

    /**
     *
     */
    function getDetailTextCommon($alternateDetailFn = ''){
        $formObj = Zend_Registry::get('formObj');
        $viewHelper = Zend_Registry::get('viewHelper');
        $this->setModuleDataArray();
        $formObj->mode = 'detail';

        if (count($this->model->dataArray) == 0){
            exit("Record does not exist for the query executed in Mod: {$this->name}. Check SQL");
        }

        $row = $this->model->dataArray[0];
        $fnName = ($alternateDetailFn != '') ? 'get' . ucfirst($alternateDetailFn) : 'getDetail';

        if (method_exists($this->view, $fnName)) {
            $text = $this->view->$fnName($row);
        } else {
            $text = $this->view->getDetail($row);
        }

        return $text;
    }

    function getDetailWithForm($alternateDetailFn = ''){
        $viewHelper = Zend_Registry::get('viewHelper');
        $text = $this->getDetailTextCommon($alternateDetailFn);
        return $viewHelper->getDetailViewWrapper($text);
    }

    function getEdit($exp = array()){
        $viewHelper = Zend_Registry::get('viewHelper');
        $tv = Zend_Registry::get('tv');

        if($tv['spAction'] == 'edit'){
            $text = $this->view->getEdit();
            return $text;
        } else {
            $this->setModuleDataArray();
            $row = count($this->model->dataArray) > 0 ? $this->model->dataArray[0] : '';
            $text = $this->view->getEdit($row);

            if(CP_SCOPE == 'www'){
                $text = $viewHelper->getEditViewWrapperWWW($text, $exp);
            } else {
                $text = $viewHelper->getEditViewWrapper($text, $exp);
            }
            return $text;
        }
    }

    /**
     *
     */
    function getAdd(){
        return $this->model->getAdd();
    }

    /**
     *
     */
    function getExportData(){
        $viewHelper = Zend_Registry::get('viewHelper');
        $modelHelper = Zend_Registry::get('modelHelper');
        $modelHelper->setModuleDataArray();
        return $this->model->getExportData($this->model->dataArray);
    }
    
    function getSaveSingleField(){
        return $this->model->getSaveSingleField();
    }

    /**
     *
     */
    function getPrintData(){
        $viewHelper = Zend_Registry::get('viewHelper');
        $modelHelper = Zend_Registry::get('modelHelper');
        $modelHelper->setModuleDataArray();
        return $this->model->getPrintData($this->model->dataArray);
    }

    /**
     * 
     * @return type
     */
    function getSaveList(){
        return $this->model->getSaveList();
    }
    
    /**
     *
     */
    function getImportData(){
        return $this->model->getImportData();
    }

    /**
     *
     */
    function getSave(){
        return $this->model->getSave();
    }

    /**
     *
     */
    function getDelete(){
        return $this->model->getDelete();
    }

    /**
     *
     */
    function getReportsMenu(){
        return $this->view->getReportsMenu();
    }

    /**
     *
     */
    function getEditFromList(){
        return $this->view->getEditFromList();
    }

    /**
     *
     */
    function getSaveFromList(){
        return $this->model->getSaveFromList();
    }

    /**
     *
     */
    function getSearch(){
        $viewHelper = Zend_Registry::get('viewHelper');
        $tv = Zend_Registry::get('tv');

        $text = $this->view->getSearch();
        if($tv['spAction'] == 'search'){
            return $text;
        } else {
            return $viewHelper->getSearchViewWrapper($text);
        }

        return $text;
    }
    
    /**
     *
     */
    function getRecord(){
        $cpUtil = Zend_Registry::get('cpUtil');

        $row = $this->model->getRecord();
        $text = $cpUtil->getJsonFromArray($row);
        return $text;
    }
}