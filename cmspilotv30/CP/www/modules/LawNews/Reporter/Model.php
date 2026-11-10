<?
class CP_Www_Modules_LawNews_Reporter_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
    }

    /**
     *
     */
    function setSearchVar() {
    }

    /**
     *
     * @return type 
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->setModuleDataArray($this->controller);
        $this->dataArray = $dataArray;
        return $this->dataArray;
    }    
 
}