<?
abstract class CP_Common_Lib_ModuleLinkControllerAbstract
{
    var $view = null;
    var $model = null;
    var $fns   = null;
    
    /**
    */
    function getNew(){
        $viewHelper = Zend_Registry::get('viewHelper');
        $text = $this->view->getNew();
        $text = $viewHelper->getLinkNewViewWrapper($text);
        return $text;
    }
    
    /**
     *
     */
    function getList($linkRecType){
        $viewHelper = Zend_Registry::get('viewHelper');
        $modelHelper = Zend_Registry::get('modelHelper');
        $modelHelper->setModuleLinkDataArray($this, $linkRecType);
        $text = $this->view->getList($this->model->dataArray, $linkRecType);
        $text = $viewHelper->getLinkListViewWrapper($text);
        return $text;
    }
    
    /**
     *
     */
    function getDetail(){
        $viewHelper = Zend_Registry::get('viewHelper');
        $modelHelper = Zend_Registry::get('modelHelper');
        $modelHelper->setModuleDataArray();
        
        $row = $this->model->dataArray[0];
        $text = $this->view->getDetail($row);
        $text = $viewHelper->getLinkDetailViewWrapper($text);
        return $text;
    }

    /**
     *
     */
    function getEdit(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $modulesArr = Zend_Registry::get('modulesArr');
        
        $formObj->mode = 'edit';
		$id = $fn->getReqParam('id');
		if ($tv['lnkRoom'] != '' && $id != ''){
        	$tblName = $modulesArr[$tv['lnkRoom']]["tableName"];
        	$keyFld = $modulesArr[$tv['lnkRoom']]["keyField"];
        	$row = $fn->getRecordRowByID($tblName, $keyFld, $id);
	        $text = $this->view->getEdit($row);
		} else {
	        $viewHelper = Zend_Registry::get('viewHelper');
	        $modelHelper = Zend_Registry::get('modelHelper');
	        $modelHelper->setModuleDataArray();
	        
	        $row = $this->model->dataArray[0];
	        $text = $this->view->getEdit($row);
	        $text = $viewHelper->getLinkEditViewWrapper($text);
	    }
        return $text;
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
    function getSave(){
        return $this->model->getSave();
    }

    /**
     *
     */
    function getAddNewGridItem(){
        return $this->model->getAddNewGridItem();
    }

    /**
     *
     */
    function getSaveGridItem(){
        return $this->model->getSaveGridItem();
    }

    /**
     *
     */
    function getAddRecordFromList(){
        return $this->model->getAddRecordFromList();
    }

    /**
     *
     */
    function getNewRecordFromList(){
        return $this->view->getNewRecordFromList();
    }
}
