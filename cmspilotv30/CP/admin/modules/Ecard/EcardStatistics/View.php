<?
class CP_Admin_Modules_Ecard_EcardStatistics_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList(){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $rows  = '';
        $total = 0;
        
        /******************************************/
        $SQL = "
        SELECT ap.title AS design
              ,am.title AS music
              ,e.language
              ,count(*) AS count
        FROM ecard e
        JOIN assets ap ON (ap.assets_id = e.picture_id)
        JOIN assets am ON (am.assets_id = e.music_id)
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id AND eh.sent = 1)
        WHERE e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY ap.title, am.title, e.language
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['design']}</td>
                <td>{$row['music']}</td>
                <td>{$row['language']}</td>
                <td>{$row['count']}</td>
            </tr>
            ";
            
            $total += $row['count'];
        }

        /******************************************/
        $exportUrl = "index.php?_topRm=ecard&module=ecard_ecardStatistics&showHTML=0&_spAction=";

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                <h2>E-card Statistics</h2>
            </div>
            <div class='float_right'>
                <a class='export' href='{$exportUrl}exportEcardStatistics'>Export to Excel</a>
            </div>
        </div>

        <table class='list txtCenter'>
            <thead>
                <tr>
                    <th>Design</th>
                    <th>Music</th>
                    <th>Language</th>
                    <th>Recipient</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th>{$total}</th>
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
    function getExportEcardStatistics(){
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

        $file_name = "Ecard-Statistics.xls";

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
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Music');
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
        SELECT ap.title AS design
              ,am.title AS music
              ,e.language
              ,count(*) AS count
        FROM ecard e
        JOIN assets ap ON (ap.assets_id = e.picture_id)
        JOIN assets am ON (am.assets_id = e.music_id)
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id AND eh.sent = 1)
        WHERE e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY ap.title, am.title, e.language
        ";

        $result = $db->sql_query($SQL);
        $total = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['design']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['music']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['language']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['count']);

            $total += $row['count'];
        }

        $rowc++;
        $actSheet->setCellValueByColumnAndRow(0, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow(3, $rowc, $total);

        $actSheet->getStyle("A{$rowc}:D{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
    //==================================================================//
}
