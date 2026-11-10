<?
class CP_Admin_Modules_Ecard_EcardUsage_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList(){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $design = '';
        $music  = '';
        $language = '';
        
        $designTot   = 0;
        $musicTot    = 0;
        $languageTot = 0;
        
        /******************************************/
        $SQL = "
        SELECT a.title
              ,count(*) AS count
        FROM ecard e
        JOIN assets a ON (a.assets_id = e.picture_id)
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id AND eh.sent = 1)
        WHERE e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY a.title
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $design .= "
            <tr>
                <td>{$row['title']}</td>
                <td>{$row['count']}</td>
            </tr>
            ";
            
            $designTot += $row['count'];
        }
        
        /******************************************/
        $SQL = "
        SELECT a.title
              ,count(*) AS count
        FROM ecard e
        JOIN assets a ON (a.assets_id = e.music_id)
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id AND eh.sent = 1)
        WHERE e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY a.title
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $music .= "
            <tr>
                <td>{$row['title']}</td>
                <td>{$row['count']}</td>
            </tr>
            ";
            
            $musicTot += $row['count'];
        }

        /******************************************/
        $SQL = "
        SELECT e.language
              ,count(*) AS count
        FROM ecard e
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id AND eh.sent = 1)
        WHERE e.language IS NOT NULL
          AND e.language != ''
          AND e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY e.language
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $language .= "
            <tr>
                <td>{$row['language']}</td>
                <td>{$row['count']}</td>
            </tr>
            ";
            
            $languageTot += $row['count'];
        }

        /******************************************/
        
        $exportUrl = "index.php?_topRm=ecard&module=ecard_ecardUsage&showHTML=0&_spAction=";

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                <h2>E-card Design Usage</h2>
            </div>
            <div class='float_right'>
                <a class='export' href='{$exportUrl}exportEcardDesignUsage'>Export to Excel</a>
            </div>
        </div>

        <table class='list txtCenter'>
            <thead>
                <tr>
                    <th class='w50p'>E-card Design</th>
                    <th>Recipient</th>
                </tr>
            </thead>
            <tbody>
                {$design}
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th>{$designTot}</th>
                </tr>
            </tfoot>
        </table>        

        <div class='floatbox mt20'>
            <div class='float_left'>
                <h2>Music Usage</h2>
            </div>
            <div class='float_right'>
                <a class='export' href='{$exportUrl}exportEcardMusicUsage'>Export to Excel</a>
            </div>
        </div>
        <table class='list txtCenter'>
            <thead>
                <tr>
                    <th class='w50p'>Music</th>
                    <th>Recipient</th>
                </tr>
            </thead>
            <tbody>
                {$music}
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th>{$musicTot}</th>
                </tr>
            </tfoot>
        </table>        

        <div class='floatbox mt20'>
            <div class='float_left'>
                <h2>Language Usage</h2>
            </div>
            <div class='float_right'>
                <a class='export' href='{$exportUrl}exportEcardLanguageUsage'>Export to Excel</a>
            </div>
        </div>
        <table class='list txtCenter'>
            <thead>
                <tr>
                    <th class='w50p'>Language</th>
                    <th>Recipient</th>
                </tr>
            </thead>
            <tbody>
                {$language}
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th>{$languageTot}</th>
                </tr>
            </tfoot>
        </table>        
        ";
        
        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            $('table.list tbody tr:odd').addClass('odd');
            $('table.list tbody tr:even').addClass('even');
        "));

        return $text;

    }

    //==================================================================//
    function getExportEcardDesignUsage(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Ecard-Design-Usage.xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'E-card Design');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Recipient');
        
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );
        
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);
        
        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $SQL = "
        SELECT a.title
              ,count(*) AS count
        FROM ecard e
        JOIN assets a ON (a.assets_id = e.picture_id)
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id AND eh.sent = 1)
        WHERE e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY a.title
        ";

        $result = $db->sql_query($SQL);
        $total = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['count']);

            $total += $row['count'];
        }

        $rowc++;
        $actSheet->setCellValueByColumnAndRow(0, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow(1, $rowc, $total);

        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    //==================================================================//
    function getExportEcardMusicUsage(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Ecard-Music-Usage.xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Music');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Recipient');
        
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );
        
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);
        
        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $SQL = "
        SELECT a.title
              ,count(*) AS count
        FROM ecard e
        JOIN assets a ON (a.assets_id = e.music_id)
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id AND eh.sent = 1)
        WHERE e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY a.title
        ";

        $result = $db->sql_query($SQL);
        $total = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['title']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['count']);

            $total += $row['count'];
        }

        $rowc++;
        $actSheet->setCellValueByColumnAndRow(0, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow(1, $rowc, $total);

        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    //==================================================================//
    function getExportEcardLanguageUsage(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "Ecard-Language-Usage.xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Language');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Recipient');
        
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );
        
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);
        
        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $SQL = "
        SELECT e.language
              ,count(*) AS count
        FROM ecard e
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id AND eh.sent = 1)
        WHERE e.language IS NOT NULL
          AND e.language != ''
          AND e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY e.language
        ";

        $result = $db->sql_query($SQL);
        $total = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['language']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['count']);

            $total += $row['count'];
        }

        $rowc++;
        $actSheet->setCellValueByColumnAndRow(0, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow(1, $rowc, $total);

        $actSheet->getStyle("A{$rowc}:B{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
    //==================================================================//

}
