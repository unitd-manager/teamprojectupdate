<?
class CP_Admin_Modules_Ecard_UserStatistics_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList(){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $rows  = '';
        $sentTot = 0;
        $recpTot = 0;
        $openedTot = 0;
        $viewedTot = 0;
        /******************************************/
        $SQL = "
        SELECT DISTINCT c.contact_id
              ,c.email
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
              ,(SELECT count(DISTINCT ec.ecard_id)
                FROM ecard_history ec
                JOIN ecard e1 ON (ec.ecard_id = e1.ecard_id)
                WHERE e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND ec.contact_id = c.contact_id
                  AND ec.sent = 1
              ) AS ecard_sent_count
              ,(SELECT count(*) 
                FROM ecard_history eh
                JOIN ecard e1 ON (eh.ecard_id = e1.ecard_id)
                WHERE eh.contact_id = c.contact_id
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh.sent = 1
              ) AS ecard_recipient_count
              ,(SELECT count(*) 
                FROM ecard_history eh
                JOIN ecard e1 ON (eh.ecard_id = e1.ecard_id)
                WHERE eh.contact_id = c.contact_id
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh.viewed = 1
                  AND eh.sent = 1
              ) AS ecard_viewed_count
              ,(SELECT count(*) 
                FROM ecard_history eh
                JOIN ecard e1 ON (eh.ecard_id = e1.ecard_id)
                WHERE eh.contact_id = c.contact_id
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh.opened = 1
                  AND eh.sent = 1
              ) AS ecard_opened_count
        FROM contact c
        JOIN ecard e ON (c.contact_id = e.contact_id)
        JOIN ecard_history eh ON (eh.ecard_id = e.ecard_id)
        WHERE eh.sent = 1
        AND e.music_id IS NOT NULL 
        AND e.picture_id IS NOT NULL
        ORDER BY contact_name
        ";

        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['email']}</td>
                <td>{$row['ecard_sent_count']}</td>
                <td>{$row['ecard_recipient_count']}</td>
                <td>{$row['ecard_viewed_count']}</td>
                <td>{$row['ecard_opened_count']}</td>
            </tr>
            ";

            $sentTot   += $row['ecard_sent_count'];
            $recpTot   += $row['ecard_recipient_count'];
            $viewedTot += $row['ecard_viewed_count'];
            $openedTot += $row['ecard_opened_count'];
        }

        /******************************************/
        $exportUrl = "index.php?_topRm=ecard&module=ecard_userStatistics&showHTML=0&_spAction=";

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                <h2>User Statistics</h2>
            </div>
            <div class='float_right'>
                <a class='export' href='{$exportUrl}exportUserStatistics'>Export to Excel</a>
            </div>
        </div>
        <table class='list txtCenter'>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>E-card Sent</th>
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
                    <th>{$sentTot}</th>
                    <th>{$recpTot}</th>
                    <th>{$viewedTot}</th>
                    <th>{$openedTot}</th>
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
    function getExportUserStatistics(){
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

        $file_name = "User-Statistics.xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'E-card Sent');
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
        SELECT DISTINCT c.contact_id
              ,c.email
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
              ,(SELECT count(DISTINCT ec.ecard_id)
                FROM ecard_history ec
                JOIN ecard e1 ON (ec.ecard_id = e1.ecard_id)
                WHERE e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND ec.contact_id = c.contact_id
                  AND ec.sent = 1
              ) AS ecard_sent_count
              ,(SELECT count(*) 
                FROM ecard_history eh
                JOIN ecard e1 ON (eh.ecard_id = e1.ecard_id)
                WHERE eh.contact_id = c.contact_id
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh.sent = 1
              ) AS ecard_recipient_count
              ,(SELECT count(*) 
                FROM ecard_history eh
                JOIN ecard e1 ON (eh.ecard_id = e1.ecard_id)
                WHERE eh.contact_id = c.contact_id
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh.opened = 1
                  AND eh.sent = 1
              ) AS ecard_opened_count
              ,(SELECT count(*) 
                FROM ecard_history eh
                JOIN ecard e1 ON (eh.ecard_id = e1.ecard_id)
                WHERE eh.contact_id = c.contact_id
                  AND e1.music_id IS NOT NULL
                  AND e1.picture_id IS NOT NULL
                  AND eh.viewed = 1
                  AND eh.sent = 1
              ) AS ecard_viewed_count
        FROM contact c
        JOIN ecard e ON (c.contact_id = e.contact_id)
        ORDER BY contact_name
        ";

        $result = $db->sql_query($SQL);

        $sentTot   = 0;
        $recpTot   = 0;
        $viewedTot = 0;
        $openedTot = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['email']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['ecard_sent_count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['ecard_recipient_count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['ecard_viewed_count']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['ecard_opened_count']);

            $sentTot   += $row['ecard_sent_count'];
            $recpTot   += $row['ecard_recipient_count'];
            $viewedTot += $row['ecard_viewed_count'];
            $openedTot += $row['ecard_opened_count'];
        }

        $rowc++;
        $actSheet->setCellValueByColumnAndRow(0, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow(1, $rowc, $sentTot);
        $actSheet->setCellValueByColumnAndRow(2, $rowc, $recpTot);
        $actSheet->setCellValueByColumnAndRow(3, $rowc, $viewedTot);
        $actSheet->setCellValueByColumnAndRow(4, $rowc, $openedTot);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    //==================================================================//
}
