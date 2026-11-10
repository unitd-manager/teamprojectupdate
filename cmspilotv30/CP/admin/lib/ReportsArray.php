<?
class CP_Admin_Lib_ReportsArray {
    var $reportsArray  = array();

    //==================================================================//
    function __construct() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $moduleName = $fn->getReqParam('roomName');
        $clsInst  = includeCPClass('ModuleFns', $moduleName);
        $funcName = "setReportsArray";
        if (method_exists($clsInst, $funcName)) {
             $clsInst->$funcName($this);
        }
    }

    //==================================================================//
    function setReportArrayObj($moduleName, $reportName) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $this->reportsArray[$moduleName][$reportName] = array();

        $arr = &$this->reportsArray[$moduleName][$reportName];
        $arr['reportType']        = 'jasper';
        $arr['jasperFileName']    = '';
        $arr['sendRecIds']        = false;
        $arr['sendSortOrder']     = false;
        $arr['printInLetterhead'] = false;
        $arr['includeSignature']  = false;
        $arr['reportsPath']       = '';
        $arr['depModules']        = array();
        $arr['extraParams']       = array();
        $arr['outputFileName']    = '';
        
    }
}
