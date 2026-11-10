<?
class CP_Common_Lib_TbsExcelExportWrapper
{
    var $tbs;

    /**
     *
     */
    function __construct() {
        $tv = Zend_Registry::get('tv');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        include_once('lib_php/tbs_us/tbs_class.php');
        include_once('lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');

        $this->tbs = new clsTinyButStrong; // new instance of TBS
        $this->tbs->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

    }

    /**
     *
     */
    function exportData($cfg) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tbs = $this->tbs;

        $dataArray = $cfg['dataArray'];
        $output_file_name = $cfg['output_file_name'];
        $template = $cfg['template'];
        $globalArray = $fn->getIssetParam($cfg, 'globalArray', array());
        $formatArray = $fn->getIssetParam($cfg, 'formatArray', array());
        $print = $fn->getIssetParam($cfg, 'print');

        $refObjs = $fn->getIssetParam($cfg, 'refObjs', array());

        $globalArray[] = $globalArray;

        $formatArray['date']     = isset($formatArray['date']) ? $formatArray['date'] : $cpCfg['cp.dateDisplayFormatTBS'];
        $formatArray['dateTime'] = isset($formatArray['dateTime']) ? $formatArray['dateTime'] : $cpCfg['cp.dateTimeDisplayFormatTBS'];
        $formatArray['number']   = isset($formatArray['number']) ? $formatArray['number'] : $cpCfg['cp.numberFormatTBS'];
        $formatArray['number2']  = isset($formatArray['number2']) ? $formatArray['number2'] : $cpCfg['cp.numberFormatTBS2'];

        //work around when [onshow..now;frm=[f.dateTime]] does not work
        $formatArray['dateTime2'] = $formatArray['dateTime'];

        if ($print) {
            $filename = str_replace('.xls', '.pdf', $filename);
        }
        //array(instance, method). ex: array($this, writeExtraInfo)
        $callbackBeforeSave = $fn->getIssetParam($cfg, 'callbackBeforeSave', null);

        $tbs->LoadTemplate($template);
        $tbs->ObjectRef =& $refObjs;

        // Merge format field and data
        $tbs->MergeField('f', $formatArray);
        $tbs->MergeField('g', $globalArray);
        $tbs->MergeBlock('data', $dataArray);
        $tbs->MergeBlock('data2', $dataArray); //just for duplicated use of data array


        // download
        $tbs->Show(OPENTBS_DOWNLOAD, $output_file_name);

        // save file to server disk
        //$tbs->Show(OPENTBS_FILE, $output_file_name);

    }

}