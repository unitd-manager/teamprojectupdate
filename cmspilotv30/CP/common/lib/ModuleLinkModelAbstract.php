<?

abstract class CP_Common_Lib_ModuleLinkModelAbstract {

    var $view = null;
    var $fns = null;
    var $dataArray = array();
    var $expForSearchVar = array();

    /**
     * $linkRecType = linked / notLinked
     */
    function getSQL($linkRecType='', $lnkRoom='') {
        $tv = Zend_Registry::get('tv');

        if (is_object($lnkRoom)){
            $lnkRoom = $lnkRoom->name;
        } else if($lnkRoom == ''){
            $lnkRoom = $tv['lnkRoom'];
        }
        $mainModule = substr($lnkRoom, 0, strpos($lnkRoom, 'Link'));
        $modObj = getCPModuleObj($mainModule);
        return $modObj->model->getSQL($linkRecType);
    }

    /**
     *
     */
    function getSQLForPager($clsInst) {
        $tv = Zend_Registry::get('tv');
        $lnkRoom = $clsInst->name;
        /** if linkRoom = contactLink, then main module - contact **/
        $mainModule = substr($lnkRoom, 0, strpos($lnkRoom, 'Link'));
        $modObj = getCPModuleObj($mainModule);
        if (method_exists($modObj->model, "getSQLForPager")) {
            return $modObj->model->getSQLForPager();
        }
    }
    
    /**
     *
     */
    function setSearchVar($linkRecType, $lnkRoom='', $exp) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $expForSearchVar = $fn->getIssetParam($exp, 'expForSearchVar', array());

        if (is_object($lnkRoom)){
            $lnkRoom = $lnkRoom->name;
        } else if($lnkRoom == ''){
            $lnkRoom = $tv['lnkRoom'];
        }

        $mainModule = substr($lnkRoom, 0, strpos($lnkRoom, 'Link'));
        $modObj = getCPModuleObj($mainModule);
        $modObj->model->expForSearchVar = $expForSearchVar;
        $modObj->model->setSearchVar($linkRecType);
    }

    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()) {
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getSave() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()) {
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

}
