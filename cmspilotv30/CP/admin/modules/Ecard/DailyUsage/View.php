<?
class CP_Admin_Modules_Ecard_DailyUsage_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList(){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');

        $rows = '';

        $total1 = 0;
        $total2 = 0;
        $total3 = 0;
        $total4 = 0;

        /******************************************/
        $SQL = "
        SELECT DATE(eh.creation_date) creation_date
              ,count(*) AS count
              ,(SELECT count(DISTINCT eh1.ecard_id)
                FROM ecard_history eh1
                JOIN ecard e1 ON (eh1.ecard_id = e1.ecard_id)
                WHERE DATE(eh1.creation_date) = DATE(eh.creation_date)
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh1.sent = 1
              ) AS ecard_count
              ,(SELECT count(*)
                FROM ecard_history eh1
                JOIN ecard e1 ON (eh1.ecard_id = e1.ecard_id)
                WHERE DATE(eh1.creation_date) = DATE(eh.creation_date)
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh1.viewed = 1
                  AND eh1.sent = 1
              ) AS viewed_count
              ,(SELECT count(*)
                FROM ecard_history eh1
                JOIN ecard e1 ON (eh1.ecard_id = e1.ecard_id)
                WHERE DATE(eh1.creation_date) = DATE(eh.creation_date)
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh1.opened = 1
                  AND eh1.sent = 1
              ) AS opened_count
        FROM ecard_history eh
        JOIN ecard e ON (eh.ecard_id = e.ecard_id)
        WHERE eh.sent = 1
          AND e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY DATE(eh.creation_date)
        ORDER BY eh.creation_date DESC
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['creation_date']}</td>
                <td>{$row['ecard_count']}</td>
                <td>{$row['count']}</td>
                <td>{$row['viewed_count']}</td>
                <td>{$row['opened_count']}</td>
            </tr>
            ";

            $total1 += $row['ecard_count'];
            $total2 += $row['count'];
            $total3 += $row['viewed_count'];
            $total4 += $row['opened_count'];
        }
        /******************************************/
        $exportUrl = "index.php?_topRm=ecard&module=ecard_dailyUsage&showHTML=0&_spAction=";

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                <h2>Daily Usage</h2>
            </div>
            <div class='float_right'>
                <a class='export' href='{$exportUrl}exportDailyUsage'>Export to Excel</a>
            </div>
        </div>
        <table class='list txtCenter'>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>No. of E-card Sent</th>
                    <th>Total Recipient</th>
                    <th>Email Count</th>
                    <th>E-card Count</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th>{$total1}</th>
                    <th>{$total2}</th>
                    <th>{$total3}</th>
                    <th>{$total4}</th>
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
    function getExportDailyUsage(){
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

        $file_name = "Daily-Usage.xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'No. Of E-card Sent');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Recipient');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email Count');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'E-card Count');
        
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
        SELECT DATE(eh.creation_date) creation_date
              ,count(*) AS count
              ,(SELECT count(DISTINCT eh1.ecard_id)
                FROM ecard_history eh1
                JOIN ecard e1 ON (eh1.ecard_id = e1.ecard_id)
                WHERE DATE(eh1.creation_date) = DATE(eh.creation_date)
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh1.sent = 1
              ) AS ecard_count
              ,(SELECT count(*)
                FROM ecard_history eh1
                JOIN ecard e1 ON (eh1.ecard_id = e1.ecard_id)
                WHERE DATE(eh1.creation_date) = DATE(eh.creation_date)
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh1.viewed = 1
                  AND eh1.sent = 1
              ) AS viewed_count
              ,(SELECT count(*)
                FROM ecard_history eh1
                JOIN ecard e1 ON (eh1.ecard_id = e1.ecard_id)
                WHERE DATE(eh1.creation_date) = DATE(eh.creation_date)
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh1.opened = 1
                  AND eh1.sent = 1
              ) AS opened_count
        FROM ecard_history eh
        JOIN ecard e ON (eh.ecard_id = e.ecard_id)
        WHERE eh.sent = 1
          AND e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
        GROUP BY DATE(eh.creation_date)
        ORDER BY eh.creation_date DESC
        ";

        $result = $db->sql_query($SQL);
        $total1 = 0;
        $total2 = 0;
        $total3 = 0;
        $total4 = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['creation_date']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['ecard_count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['viewed_count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['opened_count']);

            $total1 += $row['ecard_count'];
            $total2 += $row['count'];
            $total3 += $row['viewed_count'];
            $total4 += $row['opened_count'];
        }

        $rowc++;
        $actSheet->setCellValueByColumnAndRow(0, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow(1, $rowc, $total1);
        $actSheet->setCellValueByColumnAndRow(2, $rowc, $total2);
        $actSheet->setCellValueByColumnAndRow(3, $rowc, $total3);
        $actSheet->setCellValueByColumnAndRow(4, $rowc, $total4);

        $actSheet->getStyle("A{$rowc}:E{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   
    //==================================================================//
}
