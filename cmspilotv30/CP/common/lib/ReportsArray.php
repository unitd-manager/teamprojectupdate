<?
class ReportsArray {
    var $reportsArray  = array();

    //==================================================================//
    function __construct() {
        global $tv, $cfg, $fn;
        $roomName = $fn->getReqParam('roomName');
        $clsInst  = includeCPClass('fnsMod', $roomName, 'FunctionsMod');
        $funcName = "setReportsArray";

        if (method_exists($clsInst, $funcName)) {
             $clsInst->$funcName($this);
        }
    }

    //==================================================================//
    function setReportArrayObj($roomName, $reportName) {
        global $cfg;

        $this->reportsArray[$roomName][$reportName] = array();

        $arr = &$this->reportsArray[$roomName][$reportName];
        $arr['reportType']        = 'jasper'; // jasper / fpdf
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
