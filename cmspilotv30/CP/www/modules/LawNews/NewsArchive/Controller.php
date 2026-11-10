<?
class CP_Www_Modules_LawNews_NewsArchive_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    //==================================================================//
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        if ($tv['catType'] == 'Advanced Search') {
            $text = $this->getAdvancedSearchList();
        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }

        return $text;
    } 

    /**
     *
     */
    function getAdvancedSearchList(){
        $viewHelper = Zend_Registry::get('viewHelper');
        $this->setModuleDataArray();
        
        $text = $this->view->getAdvancedSearchList($this->model->dataArray);
        $text = $viewHelper->getListViewWrapper($text);
        return $text;
    }
}