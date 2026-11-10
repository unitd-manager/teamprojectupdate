<?
class CP_Common_Lib_SqlMaster
{
    var $SQL = '';
    var $SQLNoLimit = '';

    /** Set this before call getSQL function
     This is used in specialAction->getRecordsIds()
     for passing only ids to jasper reports
     **/
    var $generateSQLWithOnlyKeyFldGC = 0;

    //========================================================//
    function getSQL($objName, $linkRecType = '', $exp=array()) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $fileType = $fn->getIssetParam($exp, 'fileType');
        
        if ($fileType == 'Widget' || $fileType == 'Plugin'){
            $obj = $objName;
        } else if (is_object($objName)){
            $obj = $objName;
        } else if (Zend_Registry::isRegistered('currentModule')){
            $obj = Zend_Registry::get('currentModule');
        } else {
            $obj = getCPModuleObj($objName);
        }

        $model = $obj->model;
        $funcName = "getSQL";

        if (method_exists($model, $funcName)) {
            return $model->$funcName($linkRecType, $obj->name);
        }
    }
}
