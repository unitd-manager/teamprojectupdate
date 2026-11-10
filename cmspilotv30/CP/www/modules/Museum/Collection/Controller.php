<?
class CP_Www_Modules_Museum_Collection_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    //==================================================================//
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modelHelper = Zend_Registry::get('modelHelper');

        $text = '';
        if ($tv['action'] == 'list'
            && $cpCfg['m.museum.collection.list.showIntroContent']
            ){

            $contentObj = getCPModuleObj('webBasic_content');
            $contentArr = $modelHelper->setModuleDataArray($contentObj);

            if (count($contentArr) > 0){
                $text = $contentObj->view->getList($contentArr);
            } else {
                $fnName = $fn->getFnNameByAction();
                $text = $this->$fnName();
            }

        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }

        return $text;
    }

    /**
     *
     */
    function getSponsor(){
        $this->setModuleDataArray();
        return $this->view->getSponsor($this->model->dataArray);
    }

    /**
     *
     */
    function getContentList(){

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArrayContent = $modelHelper->setModuleDataArray(getCPModuleObj('webBasic_content'));
        return $this->view->getSponsor($dataArrayContent);
    }
}