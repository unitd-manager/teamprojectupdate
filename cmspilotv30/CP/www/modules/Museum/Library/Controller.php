<?
class CP_Www_Modules_Museum_Library_Controller extends CP_Common_Modules_Museum_Library_Controller
{
    //==================================================================//
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        if ($tv['action'] == 'list') {
            if($tv['subCatType'] == 'Library - Search Result'){
                $text = $this->getList();
            } else {
                $text = $this->getSearch();
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
    function getSearch() {
        $text = $this->view->getSearch();
        return $text;
    }
}