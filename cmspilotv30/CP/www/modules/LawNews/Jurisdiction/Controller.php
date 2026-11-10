<?
class CP_Www_Modules_LawNews_Jurisdiction_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    //==================================================================//
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $sp = $fn->getReqParam('sp');

        if ($sp == 'archive') {
            $text = $this->getArchiveList();
        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }        
        
        return $text;
    }
    
    /**
     *
     */
    function getArchiveList(){
        $viewHelper = Zend_Registry::get('viewHelper');
        $this->setModuleDataArray();
        
        $text = $this->view->getArchiveList($this->model->dataArray);
        $text = $viewHelper->getListViewWrapper($text);
        return $text;
    }    
}