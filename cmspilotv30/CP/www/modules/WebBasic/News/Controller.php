<?
class CP_Www_Modules_WebBasic_News_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text  = '';
        if ($tv['secType'] == 'News Fat'){
            if ($tv['action'] == 'detail'){
                $this->setModuleDataArray();
                $row = $this->model->dataArray[0];
                $text = $this->view->getFatDetail($row);
            }   else {
                $text = $this->getList('fatList');
            }
        } else if ($tv['secType'] == 'News List in Detail' || $tv['catType'] == 'News List in Detail') {
            $text = $this->getList('listInDetail');
        } else {
            $fnName = $fn->getFnNameByAction();
            $text = $this->$fnName();
        }
        
        return $text;
    }

    /**
     *
     */
    function getList($alternateListFn = ''){
        return parent::getList($alternateListFn);
    }
}