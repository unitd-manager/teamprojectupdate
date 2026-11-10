<?
class CP_Common_Lib_SpecialAction {

    //==================================================================//
    function getDeletePortalRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');

        $linkName = $tv['srcRoom'] . '#' . $tv['lnkRoom'];
        $record_id = $fn->getReqParam('id'); //history table id

        $clsInstSrc = getCPModuleObj($tv['srcRoom']);
        $clsInstSrc->fns->setLinksArray($arrayMasterLink);

        $clsInst = getCPModuleObj($tv['lnkRoom']);
        $linksArray = $arrayMasterLink->linksArray;

        $lra = $linksArray[$linkName];
        $tableName    = $lra['historyTableName']     != '' ? $lra['historyTableName']     : $modulesArr[$tv['lnkRoom']]['tableName'];
        $keyFieldName = $lra['historyTableKeyField'] != '' ? $lra['historyTableKeyField'] : $modulesArr[$tv['lnkRoom']]['keyField'] ;

        $funcName = "beforeDeletePortalHandler";
        if (method_exists($clsInst->fns, $funcName)) {
            $returnedVal = $clsInst->fns->$funcName($record_id, $linkName);
            if (is_array($returnedVal)){
                if ($returnedVal['status'] == 'error'){
                    return json_encode($returnedVal);
                }
            }
        }

        //-----------------------------------------------------//
        $SQL = "DELETE FROM `{$tableName}` WHERE {$keyFieldName}= {$record_id}";
        $result = $db->sql_query($SQL);

        $arr = array('status' => 'success');
        return json_encode($arr);
    }

    //==================================================================//
    function getDeleteRecordByID($module = '', $record_id = '') {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $linksArray = Zend_Registry::get('linksArray');

        if ($module == ''){ // if called from url
            if(!$fn->isValidCSRFToken()){ //check for valid CSRF token
                $fn->setCSRFToken();
                $arr = array(
                         'message' => 'Invalid Access!'
                        ,'refresh' => true
                    );
                return json_encode($arr);
            }

            $record_id = $fn->getPostParam('record_id');
            $module    = $fn->getPostParam('room');
        }

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }
        $fn->setCSRFToken();
        $fnMod = includeCPClass('ModuleFns', $module);
        $funcName = "beforeDeleteHandler";
        if (method_exists($fnMod, $funcName)) {
            $returnedVal = $fnMod->$funcName($record_id);
            if (is_array($returnedVal)){
                if ($returnedVal['status'] == 'error'){
                    return json_encode($returnedVal);
                }
            }
        }

        $tableName          = $modulesArr[$module]['tableName'];
        $keyFieldName       = $modulesArr[$module]['keyField'];
        $relatedTablesArray = $modulesArr[$module]['relatedTables'];

        foreach ($relatedTablesArray as $key => $value) {
            if ($value == 'media') {
                $media = getCPPluginObj('common_media');
                $media->deleteMediaRecord($module, $record_id);
            } else {
                $SQL = "DELETE FROM `{$value}` WHERE {$keyFieldName}= {$record_id}";
                $result = $db->sql_query($SQL);
            }
        }

        //-----------------------------------------------------//
        $SQL = "DELETE FROM `{$tableName}` WHERE {$keyFieldName}= {$record_id}";
        $result = $db->sql_query($SQL);

        $funcName   = "afterDeleteHandler";
        if (method_exists($fnMod, $funcName)) {
            $fnMod->$funcName($record_id);
        }

        $arr = array('status' => 'success');
        return json_encode($arr);
    }

    //==================================================================//
    function getDuplicateRecordByID($exp = array()) {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $dbUtil = Zend_Registry::get('dbUtil');
        $pager = Zend_Registry::get('pager');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $linksArray = Zend_Registry::get('linksArray');

        $afterDuplicateHandler = $fn->getIssetParam($exp, 'afterDuplicateHandler');

        $record_id = $fn->getPostParam('record_id');
        $module    = $fn->getPostParam('room');
        $topRm     = $fn->getPostParam('topRoom');

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        $fnMod = includeCPClass('ModuleFns', $module);
        $funcName = "beforeDuplicateHandler";
        if (method_exists($fnMod, $funcName)) {
            $returnedVal = $fnMod->$funcName($record_id);
            if (is_array($returnedVal)){
                if ($returnedVal['status'] == 'error'){
                    return json_encode($returnedVal);
                }
            }
        }

        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        $modelObj = getCPModelObj($module);
        $fieldValuesArr = array();
        $funcName = "getDefaultValuesForAdd";
        if (method_exists($modelObj, $funcName)) {
            $fieldValuesArr = $modelObj->$funcName();
        }
        $fieldNamesArr = $dbUtil->getTableColumns($tableName, 'field');
        if (array_key_exists('creation_date', $fieldNamesArr)) {
            $fieldValuesArr['creation_date'] = date('Y-m-d H:i:s');
        }
        if (array_key_exists('modification_date', $fieldNamesArr)) {
            $fieldValuesArr['modification_date'] = date('Y-m-d H:i:s');
        }


        $omitFieldsArr = array($keyFieldName);
        $exp = array(
             'sourceTableName' => $tableName
            ,'destTableName' => $tableName
            ,'sourceTableIdName' => $keyFieldName
            ,'sourceTableIdValue' => $record_id
            ,'omitFieldsArr' => $omitFieldsArr
            ,'fieldValuesArr' => $fieldValuesArr
        );
        $record_id_new = $dbUtil->copyRecordByInsert($exp);

        $media->getDuplicateMedia($module, $record_id, $record_id_new);

        $funcName = 'afterDuplicateHandler';

        if ($afterDuplicateHandler) {
            $params = array(
                'record_id' => $record_id
               ,'record_id_new' => $record_id_new
            );
            call_user_func($afterDuplicateHandler, $params);
        } else if (method_exists($fnMod, $funcName)) {
            $fnMod->$funcName($record_id, $record_id_new);
        } else {
            if (method_exists($fnMod, $afterDuplicateHandler)) {
                $fnMod->$funcName($record_id_new);
            }
        }

        $pager->setSearchQueryString('GET', array("_page", "_action", "_spAction", 'logged_in', 'showHTML', 'record_id'));
        $searchQueryString = $pager->searchQueryString;
        $returnUrl = "{$searchQueryString}&_topRm={$topRm}&module={$module}&_action=edit&record_id={$record_id_new}&newRecord=1";

        $arr = array('status' => 'success', 'returnUrl' => $returnUrl);

        return json_encode($arr);
    }

    //==================================================================//
    function getLinkPortalRecordsByFilter() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $arrayMaster = Zend_Registry::get('arrayMaster');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');
        $cpPaths = Zend_Registry::get('cpPaths');

        $linkName      = $fn->getReqParam('linkName');
        $srcRoom       = $fn->getReqParam('srcRoom');
        $lnkRoom       = $fn->getReqParam('lnkRoom');
        $record_id     = $fn->getReqParam('record_id');

        $getLinkTemp = "get{$srcRoom}{$lnkRoom}";

        $arr = array_merge(array($srcRoom), $modulesArr[$srcRoom]['depModulesForJSS']);

        foreach ($arr as $module){
            $clsInst  = getCPModuleObj($module)->fns;
            $funcName = "setLinksArray";

            if (method_exists($clsInst, $funcName)) {
                $clsInst->$funcName($arrayMasterLink);
            }
        }

        CP_Common_Lib_Registry::arrayMerge('linksArray', $arrayMasterLink->linksArray);
        $linksArray = Zend_Registry::get('linksArray');
        $lra = &$linksArray;
        $linkName  = $srcRoom . '#' . $lnkRoom;
        $linkName2 = $srcRoom . '__' . $lnkRoom;
        $portalListLimit = $lra[$linkName]['portalListLimit'];

        $pager = includeCPClass('Lib', 'Pager', 'Pager');

        $SQL = $displayLinkData->getSQL($srcRoom, $lnkRoom, $record_id);
        $pager->setPagerData($pager->getTotalRecords($SQL), $portalListLimit, 0);
        $SQL = $pager->addLimitToSql($SQL, '', $portalListLimit);
        $result       = $db->sql_query($SQL);
        $numRows      = $db->sql_numrows($result);

        $pagerObjTitle = 'pager_' . $linkName2; //
        Zend_Registry::set($pagerObjTitle, $pager);

        $text = $displayLinkData->getLinkPortal($result, $numRows, $srcRoom, $lnkRoom, $record_id);

        return $text;
    }

    //==================================================================//
    function getCPTestEmail() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        checkLoggedIn();

        $SERVER = $_SERVER['HTTP_HOST'];
        $email = $fn->getPostParam('email');
        $pass  = $fn->getPostParam('pass');

        $text = "";
        if (trim($email) == "" || sha1($pass) != "f8ec7c7a24f3d6401474fc726513184db1a822fe") {
            $text = "
            <style>
                table {width:250px; border:1px dotted #000; }
                table td {padding:5px;}
            </style>
            <div style='padding:100px;'>
            <form method='post' action='index.php?_spAction=CPTestEmail'>
                <table>
                <tr>
                    <td>Email</td>
                    <td><input type='text' name='email' value='' /> </td>
                </tr>

                <tr>
                    <td>Pass</td>
                    <td><input type='password' name='pass' value='' /></td>
                </tr>

                <tr>
                    <td colspan='2'>
                    <input type='submit' value='Submit' />
                    </td>
                </tr>
                </table>
            </form>
            </div>
            ";
        } else {
            //-----------------------------------------------------------------//
            $currentDate  = date("d-M-Y l h:i:s A");
            //-----------------------------------------------------------------//
            $mailSLocal  = isset($cpCfg['SMTPAddress']) ? $cpCfg['SMTPAddress'] : "";
            $mailSCommon = isset($cpCfg['SMTPAddress']) ? $cpCfg['SMTPAddress'] : "";
            $mailServer  = ($mailSLocal != "") ? $mailSLocal : $mailSCommon;

            $message = "
            <html>
            <body>
               Test email generated from {$SERVER} at {$currentDate}
               <br /><br />
               Mail sent through {$mailServer}
            </body>
            </html>
            ";

            $subject     = "Test smtp from {$cpCfg['cp.siteUrl']}";
            $fromName    = "Pilot Techincal";
            $fromEmail   = "no-reply@pilot.com.hk";
            $toName      = "";

            $toEmail     = $email;
            $smtpLocal   = new CPSMTP();
            $error       = $smtpLocal->sendEmail($toName, $toEmail, $fromName, $fromEmail, $subject, $message);

            if (!$error) {
                print "mail sent sucessfully to {$toEmail}";
            } else {
                print "error occurred";
            }
        }
        print $text;
    }
    //========================================================//
    function getPrintReport() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        $SERVER = $_SERVER['HTTP_HOST'];


        $report         = $fn->getReqParam('report');
        $module         = $fn->getReqParam('roomName');
        $report_heading = $fn->getReqParam('report_heading');
        $record_id      = (int) $fn->getReqParam('record_id', 0);
        $showPrice      = (int) $fn->getReqParam('showPrice', 0);
        $showPrice      = $showPrice == 1 ? true : false;

        $reportsArrayObj = includeCPClass('Lib', 'reportsArray', 'ReportsArray');
        $reportsArray = $reportsArrayObj->reportsArray;

        if (isset($reportsArray[$module][$report])) {
            $repObj = $reportsArray[$module][$report];

            if ($repObj['reportType'] == 'jasper') {
                $dbConf = $cpCfg[CP_ENV]['db'];
                require_once("http://{$dbConf['tomcatServer']}/{$dbConf['jasperFolder']}/java/Java.inc");

                $paramMap = new Java('java.util.HashMap');
                $this->outputFileName = "{$report}_{$record_id}";

                $this->reportFileName = $repObj['jasperFileName'];
                $paramMap->put('print_logo_left', $cpCfg['cp.repPrintLogoInLeft']);
                $paramMap->put('print_logo_right', $cpCfg['cp.repPrintLogoInRight']);
                $paramMap->put('print_address_right', $cpCfg['cp.repPrintAddressInRight']);
                $paramMap->put('print_address_footer', $cpCfg['cp.repPrintAddressInFooter']);

                if ($record_id != 0) {
                    $paramMap->put('record_id', $record_id);
                }

                if ($report_heading != '') {
                    $paramMap->put('report_heading', $report_heading);
                }

                if ($repObj['sendRecIds']) {
                    $record_ids = $this->getRecordsIds($module);
                    $paramMap->put('record_ids', $record_ids);
                }

                if ($repObj['sendSortOrder'] && $searchVar->sortOrder != '') {
                    $paramMap->put('sort_order', $searchVar->sortOrder);
                }

                if ($repObj['printInLetterhead']) {
                    $paramMap->put('print_in_letterhead', true);
                }

                if ($repObj['outputFileName'] != '') {
                    $this->outputFileName = $repObj['outputFileName'];
                }

                $signPic = '';
                $signStaffName  = '';
                $signStaffTitle = '';

                if (isset($repObj['extraParams']['sign_staff_id'])) {
                    $staff_id = $repObj['extraParams']['sign_staff_id'];

			        $signArr = $media->getMediaFilesArray('core_staff', 'signature', $staff_id);
			        if (count($signArr) > 0){
			            $signPic = realpath($cpCfg['cp.mediaFolder']) . "/normal/{$signArr[0]['file_name']}";

			            if (!file_exists($signPic)){
			                $signPic = '';
			            }
			        }

			        if ($signPic == ''){
			        	exit('Signature missing, please attach the signature of the staff and proceed');
			        }

                    $staffRec = $fn->getRecordRowByID('staff', 'staff_id', $staff_id);
                    $signStaffName  = $staffRec['first_name'] . ' ' . $staffRec['last_name'];
                    $signStaffTitle = $staffRec['position'];
                }

                foreach($repObj['depModules'] as $key => $value) {
                    $modFolder = $cpPaths->getModuleFolder($value) . 'jasper/';
                    $valueArr = explode('_', $value);
                    $modNameOnly = strtoupper($valueArr[1]);
                    $paramName = "DIR_MOD_{$modNameOnly}";
                    $paramMap->put($paramName, $modFolder);
                }

                //mod group folder
                $modGrpFolder = realpath( $cpPaths->getModuleFolder($module) . '../Lib/jasper/') . '/';
                $paramMap->put("DIR_MG_LIB", $modGrpFolder);

                //-------------------------------------//
                $this->outputFileName = $this->outputFileName . '.pdf';

                if ($repObj['reportsPath'] != ''){
                    $reportsPath = $repObj['reportsPath'];
                } else {
                    $reportsPath    = $cpPaths->getModuleFolder($module) . 'jasper/';
                    $reportsPathLoc = $cpPaths->getModuleFolder($module, 'local') . 'jasper/';

                    if (file_exists($reportsPathLoc . $this->reportFileName)){
                        $reportsPath = realpath($reportsPathLoc) . "/";
                    }
                }

                $this->reportsPath    = $reportsPath;
                $this->reportFilePath = $this->reportsPath . $this->reportFileName;
                $this->outputFilePath = $cpCfg['cp.masterPath'] . 'jasper/' . $this->outputFileName;
                $paramMap->put('DIR_SUBREPORT', $this->reportsPath);
                $paramMap->put('DIR_IMAGES', realpath($cpCfg['cp.localPath']) . '/images/');
                $paramMap->put('DIR_MEDIA', realpath($cpCfg['cp.mediaFolder']) . '/');
                $paramMap->put('SIGNATURE', $signPic);
                $paramMap->put('SIGNATURE_STAFF_NAME', $signStaffName);
                $paramMap->put('SIGNATURE_STAFF_TITLE', $signStaffTitle);

                foreach($repObj['extraParams'] as $key => $value) {
                    $paramMap->put($key, $value);
                }

                $this->getExportReport($paramMap);
            } else if ($repObj['reportType'] == 'fpdf') {
                //$funcName is generalised name of a function.
                $funcName = "get" . ucfirst($report) . 'PrintToFpdf';
				 //return;
                $modObj = getCPModuleObj($module);
                if (method_exists($modObj->model, $funcName)) {
                     $modObj->model->$funcName();
                } else {
                    exit($funcName . ' does not exist in ' . $module);
                }
            }
        }
    }

    //========================================================//
    function  getPrintUrl() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        //global $db, $cfg, $tv, $result, $utilCommon, $dateCommon, $fn, $ln;


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        //require('lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',14);
        //$pdf->Cell(width,height,'text', border, lnbreak, align, fill);
        //$pdf->Cell(40,10,'Hello World!');
        //$pdf->Cell(40,10,'Hello World !',1, 2);
        //$pdf->Cell(60,10,'Powered by FPDF.', 1, 2,'C');

        //$header = array('Term', 'Subject', 'Grade', 'Marks');

        $record_id     = $fn->getReqParam('record_id');

        $SQL = "
        SELECT i.*
            ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
            ,cont.position as position
            ,cont.company_address_flat
            ,cont.company_address_street
            ,cont.company_address_town
            ,cont.company_address_state
            ,cont.company_address_country
            ,c.company_name
            ,c.phone
            ,p.title AS project_title
            ,p.project_value AS project_value
            ,p.currency AS project_currency
            ,p.description AS project_description
            ,p.project_code as project_code
            ,ca.address_flat    AS comp_mul_address_flat
            ,ca.address_street  AS comp_mul_address_street
            ,ca.address_town    AS comp_mul_address_town
            ,ca.address_state   AS comp_mul_address_state
            ,ca.address_country AS comp_mul_address_country
            ,DATEDIFF(Now() ,i.invoice_due_date) AS age
        FROM invoice i
        LEFT JOIN (project p)         ON (i.project_id = p.project_id)
        LEFT JOIN (contact cont)      ON (p.contact_id = cont.contact_id)
        LEFT JOIN (company c)         ON (p.company_id = c.company_id)
        LEFT JOIN (company_address ca)ON (cont.company_id = ca.company_id)
        WHERE invoice_id = $record_id
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
        $count = 0;
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                // Framed
                $date = $fn->getCPDate($row['invoice_date'], 'M d, Y');
                $code = 'Invoice # : '. $row['invoice_code'];

                $pdf->SetX(90);
                $pdf->Cell(40, 20, "Invoice");
                $pdf->Ln(10);

                $pdf->SetFont('Arial','B',9);
                $pdf->SetX(170);
                $pdf->Cell(50, 20, "$code" );
                $pdf->Ln(5);
                $pdf->SetX(170);
                $pdf->Cell(50, 20, "$date");
                $pdf->Ln(10);

                $pdf->SetX(10);
                $pdf->SetFont('Arial','B',10);
                $pdf->Image('images/logo.jpg',10,25,45);
                $pdf->Ln(10);

               /* $pdf->Cell(50, 20, '10 Jalan Besar #17-02');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Sim Lim Tower');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Singapore 208787');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Tel : +65 6396 7554');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Fax : +65 6337 2134');
                $pdf->Ln(5);
                $pdf->Cell(50, 20, 'Web : www.usoftsolutions.com');
                $pdf->Ln(15); */

                $pdf->SetFont('Arial','B',10);
                $pdf->SetX(10);
                $pdf->Cell(50, 20, "Bill To :");
                $pdf->Ln(7);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(50, 20, $row['company_name']);
                $pdf->Ln(8);
                $pdf->Cell(50, 20, $row['comp_mul_address_flat']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['comp_mul_address_street']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['comp_mul_address_state']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['comp_mul_address_country']);
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['phone']);
                $pdf->Ln(5);

                $pdf->SetFont('Arial','B',14);
                $pdf->Ln(20);
                $pdf->Cell(40,10,"Client Name",1);
                $pdf->Cell(40,10,"Project Name",1);
                $pdf->Cell(40,10,"Invoice Type",1);
                $pdf->Cell(40,10,"Amount",1);
                $pdf->Ln();
            }
            $pdf->SetFont('Arial','',13);
            $pdf->Cell(40,10,$row['contact_name'],1);
            $pdf->Cell(40,10,$row['project_title'],1);
            $pdf->Cell(40,10,$row['invoice_type'],1);
            $pdf->Cell(40,10,$row['invoice_amount'],1);
            $pdf->Ln(140);
            $count++;
        }
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(50, 20, '10 Jalan Besar #17-02, Sim Lim Tower, Singapore 208787, Tel : +65 6396 7554, Fax : +65 6337 2134, Web : www.usoftsolutions.com');
                /*$pdf->Cell(50, 20, 'Sim Lim Tower');
                $pdf->Cell(50, 20, 'Singapore 208787');
                $pdf->Cell(50, 20, 'Tel : +65 6396 7554');
                $pdf->Cell(50, 20, 'Fax : +65 6337 2134');
                $pdf->Cell(50, 20, 'Web : www.usoftsolutions.com');*/


        $pdf->Output();
    }
    //============================================================//
    private function getExportReport($paramMap) {
        $cpCfg = Zend_Registry::get('cpCfg');

        try {

            $dbConf = $cpCfg[CP_ENV]['db'];

            //java_require($this->javaLibraryPath);
            $Conn = new Java("com.pilot.jasperReports.JdbcConnection");
            $Conn->setDriver("com.mysql.jdbc.Driver");
            $Conn->setConnectString("jdbc:mysql://{$dbConf['host']}/{$dbConf['dbname']}?zeroDateTimeBehavior=convertToNull");
            $Conn->setUser($dbConf['username']);
            $Conn->setPassword($dbConf['password']);

            $sJfm = new Java('net.sf.jasperreports.engine.JasperFillManager');
            //$print = $sJfm->fillReport($this->reportFilePath, $paramMap);
            $print = $sJfm->fillReport($this->reportFilePath, $paramMap, $Conn->getConnection());
            // Export du fichier au format pdf
            $sJem = new Java('net.sf.jasperreports.engine.JasperExportManager');

            $sJem->exportReportToPdfFile($print, $this->outputFilePath);

            if (file_exists($this->reportFilePath)) {
                header('Content-disposition: attachment; filename="' . $this->outputFileName);
                header('Content-Type: application/pdf');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: '. @filesize($this->outputFilePath));
                header('Pragma: no-cache');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Expires: 0');
                set_time_limit(0);
                @readfile($this->outputFilePath) or die('problem occurs.');
                unlink($this->outputFilePath);
            }

        } catch (JavaException $ex) {
            $trace = new Java('java.io.ByteArrayOutputStream');
            $value = $ex->printStackTrace(new Java('java.io.PrintStream', $trace));
            print "java stack trace: $trace\n";
            print "<pre>";
            print java_values($trace);
            print "</pre>";
        }
    }

    //============================================================//
    function getCompileReports() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $cpPaths = Zend_Registry::get('cpPaths');

        require_once("http://{$cpCfg['tomcatServer']}/JavaBridge/java/Java.inc");
        try {
            $sJcm = new Java('net.sf.jasperreports.engine.JasperCompileManager');

            //$folder    = realpath($cpCfg['cp.masterPath']) . '/jasper/';
            $folder = $cpPaths->getModuleFolder('quote') . 'jasper/';

            if ($handle = opendir($folder)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." &&  substr($file, -5) == 'jrxml') {
                        $sourcePath = $folder . $file;
                        $sourcePath = $folder . $file;
                        $sourcePath = str_replace("\\", "/", $sourcePath);
                        print "compiling: {$sourcePath}<br/>";
                        $destPath   = str_replace(".jrxml", ".jasper", $sourcePath);

                        $sJcm->compileReportToFile($sourcePath, $destPath);
                    }
                }

                closedir($handle);
            }

        } catch (JavaException $ex) {
            $trace = new Java('java.io.ByteArrayOutputStream');
            $value = $ex->printStackTrace(new Java('java.io.PrintStream', $trace));
            print "java stack trace: $trace\n";
            print "<pre>";
            print java_values($trace);
            print "</pre>";
        }
    }

    //============================================================//
    private function getIncludeLibPath($libFolder) {
        if ($libFolder == '') {
            return '';
        }

        $java_library_path = '';
        $handle = @opendir($libFolder);
        while(($new_item = readdir($handle))!==false) {
            $java_library_path .= 'file:' . $libFolder . '/' . $new_item . ';';
        }

        return $java_library_path;
    }

    /**
     *
     */
    function getRecordsIds($module) {
        $modulesArr = Zend_Registry::get('modulesArr');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $sqlMaster = Zend_Registry::get('sqlMaster');

        $SQL = "
        SET SESSION group_concat_max_len = 1000000;
        ";
        $db->sql_query($SQL);

        $sqlMaster->generateSQLWithOnlyKeyFldGC = 1;
        $SQL = $sqlMaster->getSQL($module);
        $SQL .= $searchVar->getSearchVar($module, 0);
        $sqlMaster->generateSQLWithOnlyKeyFldGC = 0;
        $searchVar->setSortOrder();
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        //print $row['record_ids'];
        //exit();
        return $row['record_ids'];
    }

    //==================================================================//
    function getCreateDeleteLinkRecord() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');

        $srcRoom           = $tv['srcRoom'];
        $lnkRoom           = $tv['lnkRoom'];
        $record_id         = $fn->getReqParam('record_id');
        $linkMasterTableID = $fn->getReqParam('linkMasterTableID');
        $currentValue      = $fn->getReqParam('currentValue');

        $clsInst = getCPModuleObj($srcRoom);
        $clsInst->fns->setLinksArray($arrayMasterLink);
        $linksArray     = $arrayMasterLink->linksArray;

        $linkName             = $srcRoom . "#" . $lnkRoom;
        $mainRoomKeyField     = $modulesArr[$srcRoom]["keyField"];
        $linkRoomKeyField     = $modulesArr[$lnkRoom]["keyField"];
        $historyTableName     = $linksArray[$linkName]["historyTableName"];
        $historyTableKeyField = $linksArray[$linkName]["historyTableKeyField"];
        $recordTypeForHistory = $linksArray[$linkName]["recordTypeForHistory"];
        $keyFieldForLinking   = $linksArray[$linkName]["keyFieldForLinking"];
        $mainRoomKeyFldNameInHistTbl = $linksArray[$linkName]["mainRoomKeyFldNameInHistTbl"];
        $linkRoomKeyFldNameInHistTbl = $linksArray[$linkName]["linkRoomKeyFldNameInHistTbl"];

        if ($mainRoomKeyFldNameInHistTbl != ""){
            $mainRoomKeyField  = $mainRoomKeyFldNameInHistTbl;
        }

        if ($keyFieldForLinking != ""){
            $linkRoomKeyField  = $keyFieldForLinking;
        }

        if ($linkRoomKeyFldNameInHistTbl != ""){
            $linkRoomKeyField = $linkRoomKeyFldNameInHistTbl;
        }

        if (!is_numeric ($record_id) || !is_numeric ($linkMasterTableID)) {
            return;
        }

        $append = '';
        if ($recordTypeForHistory != ''){
            $append = "AND record_type = '{$recordTypeForHistory}'";
        }

        $srcRmArr = explode('_', $srcRoom);
        $lnkRmArr = explode('_', $lnkRoom);
        if ($currentValue == 1) {
            $SQL = "
            SELECT {$linkRoomKeyField}
            FROM `{$historyTableName}`
            WHERE {$mainRoomKeyField}= {$linkMasterTableID}
              AND {$linkRoomKeyField} = {$record_id}
              {$append}
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            if ($numRows == 0) {
                $fieldValuesArray = array();
                $fa = &$fieldValuesArray;

                $fa[$mainRoomKeyField] = $linkMasterTableID;
                $fa[$linkRoomKeyField] = $record_id;

                if ($srcRoom == "common_broadcast" && ($lnkRoom == "common_contactLink" ||  $lnkRoom == "common_testRecipientLink")) {
                    $fa['send_tag'] = '0';
                    $fa['status']  = 'To Be Sent';
                }

                if ($recordTypeForHistory != ''){
                    $fa['record_type'] = $recordTypeForHistory;
                }

                $fa['creation_date']     = date("Y-m-d H:i:s");
                $fa['modification_date'] = date("Y-m-d H:i:s");

                $SQL   = $dbUtil->getInsertSQLStringFromArray($fieldValuesArray, $historyTableName);
                $result = $db->sql_query($SQL);
                $history_table_id = $db->sql_nextid();


                //ex: getTradingEnquiryTradingProductAddLinkCallback
                $funcName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                                    ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) .
                                    "AddLinkCallback";
                $fnMod = getCPModuleObj($srcRoom)->fns;
                if (method_exists($fnMod, $funcName)) {
                    $historyRow = $fn->getRecordRowByID($historyTableName, $historyTableKeyField, $history_table_id);
                    $fnMod->$funcName($history_table_id, $historyRow);
                }
            }

        } else {
            $SQL = "
            DELETE FROM `{$historyTableName}`
            WHERE {$mainRoomKeyField} = {$linkMasterTableID}
              AND {$linkRoomKeyField} = {$record_id}
              {$append}
            ";

            //ex: getTradingEnquiryTradingProductDeleteLinkCallback
            $funcName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                                ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) .
                                "DeleteLinkCallback";
            $fnMod = getCPModuleObj($srcRoom)->fns;
            if (method_exists($fnMod, $funcName)) {
                $fnMod->$funcName($linkMasterTableID, $record_id);
            }
            //result is placed here to use any values in the above call back function before //deleting the record.
            $result = $db->sql_query($SQL);
        }
    }

    //==================================================================//
    function getCreateDeleteLinkAllRecords() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $linksArray = Zend_Registry::get('linksArray');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');
        $searchVar = Zend_Registry::get('searchVar');
        $sqlMaster = Zend_Registry::get('sqlMaster');

        $srcRoom           = $fn->getReqParam('srcRoom');
        $lnkRoom           = $fn->getReqParam('lnkRoom');
        $linkMasterTableID = $fn->getReqParam('linkMasterTableID');
        $currentValue      = $fn->getReqParam('currentValue');
        $linkRecType       = $fn->getReqParam('linkRecType');

        $clsInst = getCPModuleObj($srcRoom)->fns;
        $clsInst->setLinksArray($arrayMasterLink);
        $linksArray  = $arrayMasterLink->linksArray;
        $linkedIds = $fn->getLinkedIDs($tv['srcRoom'], $tv['lnkRoom']);

        Zend_Registry::set('linkedIds', "{$linkedIds}");

        $linkName                    = $srcRoom . "#" . $lnkRoom;
        $mainRoomKeyField            = $modulesArr[$srcRoom]["keyField"];
        $linkRoomKeyField            = $modulesArr[$lnkRoom]["keyField"];
        $linkRoomKeyField2           = $modulesArr[$lnkRoom]["keyField"];
        $linkRoomTable               = $modulesArr[$lnkRoom]["tableName"];
        $historyTableName            = $linksArray[$linkName]["historyTableName"];
        $recordTypeForHistory        = $linksArray[$linkName]["recordTypeForHistory"];
        $mainRoomKeyFldNameInHistTbl = $linksArray[$linkName]["mainRoomKeyFldNameInHistTbl"];
        $linkRoomKeyFldNameInHistTbl = $linksArray[$linkName]["linkRoomKeyFldNameInHistTbl"];

        if ($mainRoomKeyFldNameInHistTbl != ""){
            $mainRoomKeyField  = $mainRoomKeyFldNameInHistTbl;
        }

        $linkRoomKeyFldNameInHistTbl = ($linkRoomKeyFldNameInHistTbl != '') ? $linkRoomKeyFldNameInHistTbl : $linkRoomKeyField;

        $searchVar->sqlSearchVar = array();
        $SQL = $sqlMaster->getSQL($lnkRoom);
        $SQL .= $searchVar->getSearchVar($lnkRoom, 1, $linkRecType);
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $recordIds[] = $row[$linkRoomKeyField2];
        }

        if (!is_numeric ($linkMasterTableID)) {
            return;
        }

        $append = '';
        if ($recordTypeForHistory != ''){
            $append = "AND record_type = '{$recordTypeForHistory}'";
        }

        $srcRmArr = explode('_', $srcRoom);
        $lnkRmArr = explode('_', $lnkRoom);

        if ($currentValue == 1) {
            $recordIdsStr = join(",", $recordIds);
            $creation_date     = date("Y-m-d H:i:s");
            $modification_date = date("Y-m-d H:i:s");

            $extraFlds = '';
            $extraValues = '';

            if ($srcRoom == "common_broadcast" && ($lnkRoom == "common_contactLink" ||  $lnkRoom == "common_testRecipient")) {
                $extraFlds = ",send_tag,status";
                $extraValues = ",0,'To Be Sent'";
            }

            //ex: getTradingEnquiryTradingProductAddAllLinkCallback
            $cbFuncName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                                ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) .
                                "AddAllLinkCallback";
            $fnMod = getCPModuleObj($srcRoom)->fns;
            if (method_exists($fnMod, $cbFuncName)) {
                $selectSQL = "
                SELECT GROUP_CONCAT({$linkRoomKeyField} SEPARATOR ',') AS newly_created_records_ids
                FROM `{$linkRoomTable}`
                WHERE {$linkRoomKeyField} IN ({$recordIdsStr})
                  AND {$linkRoomKeyField} NOT IN (
                    SELECT {$linkRoomKeyField}
                    FROM {$historyTableName}
                    WHERE {$mainRoomKeyField} = '{$linkMasterTableID}'
                    AND {$linkRoomKeyField} IN ({$recordIdsStr})
                  )
                ";
                $resultCb = $db->sql_query($selectSQL);
                $rowCb = $db->sql_fetchrow($resultCb);
            }

            $SQL = "
            INSERT INTO `{$historyTableName}` (
                 `{$mainRoomKeyField}`
                ,`{$linkRoomKeyFldNameInHistTbl}`
                ,`creation_date`
                ,`modification_date`
                {$extraFlds}
                )
            SELECT '{$linkMasterTableID}'
                  ,{$linkRoomKeyField}
                  ,'{$creation_date}'
                  ,'{$modification_date}'
                  {$extraValues}
            FROM `{$linkRoomTable}`
            WHERE {$linkRoomKeyField} IN ({$recordIdsStr})
              AND {$linkRoomKeyField} NOT IN (
                SELECT {$linkRoomKeyFldNameInHistTbl}
                FROM {$historyTableName}
                WHERE {$mainRoomKeyField} = '{$linkMasterTableID}'
                AND {$linkRoomKeyFldNameInHistTbl} IN ({$recordIdsStr})
              )
            ";
            $result = $db->sql_query($SQL);
            if (method_exists($fnMod, $cbFuncName)) {
                $fnMod->$cbFuncName($linkMasterTableID, $rowCb['newly_created_records_ids']);
            }

            /* ex for the above query

            INSERT INTO `broadcast_contact` (
                 `broadcast_id`
                ,`contact_id`
                ,`creation_date`
                ,`modification_date`)
                SELECT '3'
                      ,contact_id
                      ,'2012-05-16 16:46:35'
                      ,'2012-05-16 16:46:35'
                FROM `contact`
                WHERE contact_id IN (6,3,8,1,5,4,2,7)
                  AND contact_id NOT IN (
                    SELECT contact_id
                    FROM broadcast_contact
                    WHERE broadcast_id = '3'
                    AND contact_id IN (6,3,8,1,5,4,2,7)
                  )
            */

            //foreach ($recordIds as $key => $record_id) {
            //    if ($record_id == "") {
            //        continue;
            //    }
            //
            //    $SQL = "
            //    SELECT {$linkRoomKeyField}
            //    FROM `{$historyTableName}`
            //    WHERE {$mainRoomKeyField} = {$linkMasterTableID}
            //      AND {$linkRoomKeyField} = {$record_id}
            //      {$append}
            //    ";
            //    $result  = $db->sql_query($SQL);
            //    $numRows = $db->sql_numrows($result);
            //
            //    if ($numRows == 0) {
            //        $fieldValuesArray = array();
            //        $fa = &$fieldValuesArray;
            //
            //        $fa[$mainRoomKeyField]   = $linkMasterTableID;
            //        $fa[$linkRoomKeyField]   = $record_id;
            //
            //        if ($srcRoom == "common_broadcast" && ($lnkRoom == "common_contact" ||  $lnkRoom == "common_testRecipient")) {
            //            $fa['send_tag'] = '0';
            //            $fa['status']  = 'To Be Sent';
            //        }
            //
            //        if ($recordTypeForHistory != ''){
            //            $fa['record_type'] = $recordTypeForHistory;
            //        }
            //
            //        $fa['creation_date']     = date("Y-m-d H:i:s");
            //        $fa['modification_date'] = date("Y-m-d H:i:s");
            //
            //        print $SQL    = $dbUtil->getInsertSQLStringFromArray($fieldValuesArray, $historyTableName);
            //        $result = $db->sql_query($SQL);
            //    }
            //}

        } else {
            $commaSepratedIDs = join(",", $recordIds);
            $SQL = "
            DELETE FROM `{$historyTableName}`
            WHERE {$mainRoomKeyField}= {$linkMasterTableID}
              AND {$linkRoomKeyField} IN ({$commaSepratedIDs})
            ";

            //ex: getTradingEnquiryTradingProductRemoveAllLinkCallback
            $cbFuncName = "get" . ucfirst($srcRmArr[0]) . ucfirst($srcRmArr[1]) .
                                ucfirst($lnkRmArr[0]) . ucfirst($lnkRmArr[1]) .
                                "RemoveAllLinkCallback";
            $fnMod = getCPModuleObj($srcRoom)->fns;
            if (method_exists($fnMod, $cbFuncName)) {
                $fnMod->$cbFuncName($linkMasterTableID, $commaSepratedIDs);
            }
            $result = $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function getStreamFile() {
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpPaths = Zend_Registry::get('cpPaths');
        $modname  = $fn->getReqParam('modname');
        $filename = $fn->getReqParam('filename');
        $filepath = $cpPaths->getTheLastFile('modules', $modname, $filename);
        $cpUtil->sendFile($filepath, $filename);
    }

    //==================================================================//
    function getJsonForDropdown() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn     = Zend_Registry::get('fn');
        $ln     = Zend_Registry::get('ln');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module   = $fn->getReqParam('room');
        $srcFld   = $fn->getReqParam('srcFld', '', true);
        $srcValue = $fn->getReqParam('srcValue', '', true);

        $json = array();

        $funcName = 'getJsonForDropdown';
        $modObj = getCPModuleObj($module);
        if (method_exists($modObj->model, $funcName)) {
             return $modObj->model->$funcName();
        }

        if ($srcValue == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        if ($srcFld == 'address_country_code'){
            $rec = $fn->getRecordByCondition('geo_country', "country_code = '{$srcValue}'");
            $srcFld   = 'country_id';
            $srcValue = $rec['geo_country_id'];
        }

        $exp = array('condn' => "{$srcFld} = '{$srcValue}'");
        $SQL = $fn->getDDSql($module, $exp);
        //print $SQL;
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row[0], "caption" => $row[1]);
        }

        return json_encode($json);
    }

    //==================================================================//
    function getJsonForAutoSuggest() {
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module   = $fn->getReqParam('room');
        $tableName  = $modulesArr[$module]['tableName'];
        $keyField   = $modulesArr[$module]['keyField'];
        $titleField = $modulesArr[$module]['titleField'];

        $srchFld  = $fn->getReqParam('srchFld', $titleField, true);
        $valueFld = $fn->getReqParam('valueFld', $srchFld, true);
        $labelFld = $fn->getReqParam('labelFld', $srchFld, true);

        $term = $fn->getReqParam('term', '', true);


        $termArr = explode(' ',$term);
        $where = '';
        if (count($termArr) == 1) {
            $where = "{$srchFld} LIKE '%{$term}%'";
        } else {
            $termArr = $cpUtil->getArrayPermutations($termArr);
            $arr2 = array();
            foreach ($termArr as $arr) {
                $arr2[] = "{$srchFld} LIKE '" . join("% %", $arr) . "";
            }
            $where = join("%' OR \n", $arr2) . "%'";
        }

        $SQL = "
        SELECT DISTINCT
               {$valueFld} AS value
        	  ,{$labelFld} AS label
        	  ,{$keyField} AS id
        FROM {$tableName}
        WHERE {$where}
        ORDER BY {$srchFld}
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    //==================================================================//
    function getGenerateMinFiles() {
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpPaths = Zend_Registry::get('cpPaths');

        $minDir = $cpPaths->getMinFilePath();

        /*** create min directory ***/
        if(!is_dir($minDir)){
            mkdir($minDir, 0700);
        }

        /*** create js & css file ***/
        $jsFileMin = $minDir . "/cp.js";
        $cssFileMin = $minDir . "/cp.css";

        if (!file_exists($jsFileMin)) {
            $fh = fopen($jsFileMin, "w");
            fclose ($fh);
        }

        if (!file_exists($cssFileMin)) {
            $fh = fopen($cssFileMin, "w");
            fclose ($fh);
        }

        foreach ($cpCfg['cp.availableModules'] as $module){
            getCPModuleObj($module, true);
        }

        foreach ($cpCfg['cp.availablePlugins'] as $plugin){
            getCPPluginObj($plugin);
        }

        foreach ($cpCfg['cp.availableWidgets'] as $widget){
            getCPWidgetObj($widget);
        }

        file_put_contents($cssFileMin, '');

        $cssFilesArr      = Zend_Registry::get('cssFilesArr');
        $cssFilesArrLocal = Zend_Registry::get('cssFilesArrLocal');
        $cssFilesArrLast  = Zend_Registry::get('cssFilesArrLast');
        $cssPatchFilesArr = Zend_Registry::get('cssPatchFilesArr');

        $cssFilesAll = array_merge($cssFilesArr, $cssFilesArrLocal, $cssFilesArrLast);

        $cssFilesAllFinal = array();
        /*** Loop through the CSS files array and form the include text ***/
        foreach($cssFilesAll as $key => $value){
            if (!in_array($value, $cssFilesAllFinal) ){
                $cssFilesAllFinal[] = $value;
            }
        }

        foreach ($cssFilesAllFinal as $cssFile){
            $cssFileAbs = $cpPaths->getAbsPathForRelativePath($cssFile);

            $fileContent = file_get_contents($cssFileAbs);
            $appendContent = "\n/** {$cssFile} **/\n";
            $appendContent = "";

            $basePath = dirname($cssFile);
            $basePath2 = substr($basePath , 0, strrpos($basePath, '/')); // used for common stylesheet where images are referenced with ../

            $fileContent = str_replace("('../images/", "('{$basePath2}/images/", $fileContent);
            $fileContent = str_replace("(\"../images/", "(\"{$basePath2}/images/", $fileContent);

            $fileContent = str_replace("('images/", "('{$basePath}/images/", $fileContent);
            $fileContent = str_replace("(\"images/", "(\"{$basePath}/images/", $fileContent);

            $fileContent = str_replace("(../images/", "({$basePath2}/images/", $fileContent);

            $appendContent .= $cpUtil->compressCSS($fileContent);
            file_put_contents($cssFileMin, $appendContent, FILE_APPEND);
        }

        file_put_contents($jsFileMin, '');

        $jsFilesArr      = Zend_Registry::get('jsFilesArr');
        $jsFilesArrLocal = Zend_Registry::get('jsFilesArrLocal');
        $jsFilesArrLast  = Zend_Registry::get('jsFilesArrLast');

        $jsFilesAll = array_merge($jsFilesArr, $jsFilesArrLocal, $jsFilesArrLast);

        $jsFilesAllFinal = array();
        /*** Loop through the CSS files array and form the include text ***/
        foreach($jsFilesAll as $key => $value){
            if (!in_array($value, $jsFilesAllFinal) ){
                $jsFilesAllFinal[] = $value;
            }
        }

        foreach ($jsFilesAllFinal as $jsFile){
            $jsFileAbs = $cpPaths->getAbsPathForRelativePath($jsFile);

            $appendContent = "\n/** {$jsFile} **/\n";
            $appendContent .= file_get_contents($jsFileAbs);

            file_put_contents($jsFileMin, $appendContent, FILE_APPEND);
        }
    }

    //*************************************************************************//
    function linkRecordsByFormField($srcRoom, $lnkRoom, $mainRoomID, $fldName) {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $linksArray = Zend_Registry::get('linksArray');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $arrayMasterLink = Zend_Registry::get('arrayMasterLink');

        $clsInst = getCPModuleObj($srcRoom);
        $clsInst->fns->setLinksArray($arrayMasterLink);
        $linksArray   = $arrayMasterLink->linksArray;
        $linkName = $srcRoom . "#" . $lnkRoom;
        $lra      = &$linksArray[$linkName];

        $mainRoomKeyField             = $modulesArr[$srcRoom]["keyField"];
        $linkRoomKeyField             = $modulesArr[$lnkRoom]["keyField"];
        $recordTypeForHistory         = $lra["recordTypeForHistory"];
        $linkRoomKeyFieldForHistory   = $lra["keyFieldForHistory"];
        $linkRoomRecordTypeForHistory = $lra["recordTypeForHistory"];
        $historyTableName             = $lra["historyTableName"];
        $historyTableKeyField         = $lra["historyTableKeyField"];

        if ($linkRoomKeyFieldForHistory != "") {
            $linkRoomKeyField  = $linkRoomKeyFieldForHistory;
        }
        $idsArray = isset($_REQUEST[$fldName]) ? $_REQUEST[$fldName] : array();

        if (!is_array($idsArray)) {
            if ($idsArray != "") {
                $idsArray = array($idsArray);
            }
        }

        $recordTypeField = $linkRoomRecordTypeForHistory != "" ? "record_type, " : "";
        $recordTypeValue = $linkRoomRecordTypeForHistory != "" ? "'{$linkRoomRecordTypeForHistory}', " : "";
        $recordTypeQuery = $linkRoomRecordTypeForHistory != "" ? " AND record_type = '{$linkRoomRecordTypeForHistory}'" : "";

        $SQL = "
        SELECT {$linkRoomKeyField}
        FROM {$historyTableName}
        WHERE {$mainRoomKeyField}   = {$mainRoomID}
        {$recordTypeQuery}
        ";
        $result = $db->sql_query($SQL);

        //********* delete un-selected ones
        while ($row = $db->sql_fetchrow($result)) {
            if (in_array($row[$linkRoomKeyField], $idsArray) == false) {
                $SQL = "
                DELETE FROM {$historyTableName}
                WHERE {$mainRoomKeyField} = {$mainRoomID}
                  AND {$linkRoomKeyField} = {$row[$linkRoomKeyField]}
                {$recordTypeQuery}
                ";
                $db->sql_query($SQL);
            }
        }
        //----------------------------------------------------------------//

        $needToRunHistorySQL = 0;

        $SQLHist   = "
        INSERT INTO {$historyTableName}
                    ({$mainRoomKeyField}
                   ,{$linkRoomKeyField}
                   ,{$recordTypeField}
                     creation_date
        ) VALUES
        ";

        foreach ($idsArray as $value) {
            $SQL2 = "
            SELECT 1
            FROM {$historyTableName}
            WHERE {$mainRoomKeyField}  = {$mainRoomID}
              AND {$linkRoomKeyField}  = {$value}
              {$recordTypeQuery}
            ";

            $result2 = $db->sql_query($SQL2);
            $numRows = $db->sql_numrows($result2);
            if ($numRows == 0) {
                $SQLHist .= "({$mainRoomID}, {$value}, {$recordTypeValue} NOW()),";
                $needToRunHistorySQL = 1;
            }
        }
        $SQLHist = substr($SQLHist, 0, -1); //*** remove the final comma

        if ($needToRunHistorySQL == 1) {
            $db->sql_query($SQLHist);
        }
        //----------------------------------------------------------------//
    }

    //==================================================================//
    function getMoveRowData() {

        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');

        $record_id   = $fn->getPostParam('record_id');
        $module      = $fn->getPostParam('room');
        $adjRecordID = $fn->getPostParam('adjRecordID');

        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        $sortValueAdj     = 0;
        $sortValueCurrent = 0;

        //-----------------------------------------------------//
        $SQL = "
        SELECT sort_order
        FROM {$tableName}
        WHERE {$keyFieldName} = {$adjRecordID}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row           = $db->sql_fetchrow($result);
            $sortValueAdj  = $row['sort_order'];

        } else {
            print "error";
            return;
        }

        //-----------------------------------------------------//
        $SQL = "
        SELECT sort_order
        FROM {$tableName}
        WHERE {$keyFieldName} = {$record_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row              = $db->sql_fetchrow($result);
            $sortValueCurrent = $row['sort_order'];

        } else {
            print "error";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET sort_order = {$sortValueAdj}
        WHERE {$keyFieldName}=  {$record_id}
        ";

        $result = $db->sql_query($updateSQL);

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET sort_order = {$sortValueCurrent}
        WHERE {$keyFieldName} = {$adjRecordID}
        ";

        $result = $db->sql_query($updateSQL);
    }

    //==================================================================//
    function getChangeSortValue() {

        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');

        $record_id = $fn->getPostParam('record_id');
        $module    = $fn->getPostParam('room');
        $sortValue = $fn->getPostParam('sortValue');

        if ($module == "media") {
            $tableName    = "media";
            $keyFieldName = "media_id";

        } else if ($module == "packing_list_history") {
            $tableName    = "packing_list_history";
            $keyFieldName = "packing_list_history_id";

        } else if ($module == "purchase_order_history") {
            $tableName    = "purchase_order_history";
            $keyFieldName = "purchase_order_history_id";

        } else {
            $tableName    = $modulesArr[$module]['tableName'];
            $keyFieldName = $modulesArr[$module]['keyField'];
        }


        if (!is_numeric ($sortValue) && $sortValue != '') {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET sort_order = '{$sortValue}'
        WHERE {$keyFieldName} = {$record_id}
        ";

        $result = $db->sql_query($updateSQL);
        //-----------------------------------------------------//
        print "success";
    }

    //==================================================================//
    function getFlagRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $record_id    = $fn->getReqParam('record_id');
        $module       = $fn->getReqParam('room');
        $currentValue = $fn->getReqParam('currentValue');

        $color = $fn->getReqParam('color', 'red');
        $flag_fld = 'flag';
        if ($color != 'red') {
            $flag_fld = 'flag_' . $color;
        }

        $newValue     = $currentValue == 0 ? 1 : 0;
        $imageIcon    = $currentValue == 0 ? "flag_on_{$color}.png" : "flag_off.png";

        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//

        $updateSQL = "
        UPDATE `{$tableName}`
        SET {$flag_fld} = {$newValue}
        WHERE {$keyFieldName}= {$record_id}
        ";
        $db->sql_query($updateSQL);

        //-----------------------------------------------------//
        $text = "
        <a href='#'
           class='list-flag'
           module='{$module}'
           record_id='{$record_id}'
           currentValue='{$newValue}'
           color='{$color}'>
            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/{$imageIcon}'>
        </a>
        ";
        return $text;

    }

    //==================================================================//
    function getFlagUnflagAllRecords() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $record_ids = $fn->getReqParam('record_ids');
        $module     = $fn->getReqParam('room');
        $action     = $fn->getReqParam('action');
        $color      = $fn->getReqParam('color', 'red');
        $flag_fld = 'flag';
        if ($color != 'red') {
            $flag_fld = 'flag_' . $color;
        }
        $flagValue = 1;
        if ($action == 'unflagAll') {
            $flagValue = 0;
        }

        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET {$flag_fld} = {$flagValue}
        WHERE {$keyFieldName} IN ({$record_ids})
        ";
        $db->sql_query($updateSQL);
    }

    //==================================================================//
    function getPublishRecordByID() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');

        $record_id    = $fn->getPostParam('record_id');
        $module       = $fn->getPostParam('room');
        $currentValue = $fn->getPostParam('currentValue');
        $uploadTo     = $fn->getPostParam('uploadTo', 'live');
        $reUpload     = $fn->getPostParam('reUpload', 0);

        if ($reUpload == 1) {
            $newValue  = 1;
        } else {
            $newValue  = ($currentValue == 0) ? 1 : 0;
        }

        /* if newValue = 0 it means the record has to be un-published
         if newValue = 1 it means the record has to be published
        */


        $tableName    = $modulesArr[$module]['tableName'];
        $keyFieldName = $modulesArr[$module]['keyField'];

        if (!is_numeric ($record_id)) {
            print "error:not a number";
            return;
        }

        //-----------------------------------------------------//
        $updateSQL = "
        UPDATE {$tableName}
        SET published = {$newValue}
        WHERE {$keyFieldName} = {$record_id}
        ";
        $result = $db->sql_query($updateSQL);

        $text = $listObj->getListPublishedImageIcon($module, $record_id, $newValue);

        return $text;
    }

    //==================================================================//
    function getFormValidate() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $value = $fn->getReqParam('value', '', true);
        $field = $fn->getReqParam('field', '', true);
        $valid = true;
        $message = '';

        //if ($field == 'captcha_code'){
        //    require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
        //    $img = new Securimage;
        //    if ($img->check($value) == false) {
        //        $valid = false;
        //        $message = $ln->gd("cp.form.fld.captchaCode.err");
        //    }
        //}

        $json = array(
            'value' => $value,
            'valid' => $valid,
            'message' => $message
        );

        return json_encode($json);
    }
}