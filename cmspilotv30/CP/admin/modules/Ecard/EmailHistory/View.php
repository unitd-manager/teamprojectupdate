<?
class CP_Admin_Modules_Ecard_EmailHistory_View extends CP_Common_Lib_ModuleViewAbstract
{
    //==================================================================//
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        
        $rows  = '';
        $totalViewed = 0;
        $totalOpened = 0;
        
        /******************************************/
        foreach ($dataArray as $row){
            $opened = ($row['opened'] == 1) ? 'Opened' : '';
            $viewed = ($row['viewed'] == 1) ? 'Viewed' : '';
            $sent_date = ($row['sent_date'] != '') ? $row['sent_date'] : $row['creation_date'];
            $rows .= "
            <tr>
                <td>
                    Design: {$row['design']}<br>
                    Music: {$row['music']}<br>
                    Language: {$row['language']}<br>
                    Sender: {$row['sender_email']}<br>
                </td>
                <td>
                    Name: {$row['recp_name']}<br>
                    Email: {$row['recp_email']}<br>
                </td>
                <td>{$sent_date}</td>
                <td>{$viewed}</td>
                <td>{$opened}</td>
            </tr>
            ";

            $totalViewed += $row['viewed'];
            $totalOpened += $row['opened'];
        }

        /******************************************/
        $exportUrl = "index.php?_topRm=ecard&module=ecard_emailHistory&showHTML=0&_spAction=";

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                <h2>Email History</h2>
            </div>
            <div class='float_right'>
                <a class='export' href='{$exportUrl}exportEmailHistory'>Export to Excel</a>
            </div>
        </div>
        <table class='list txtCenter'>
            <thead>
                <tr>
                    <th>Email Details</th>
                    <th>Recipient</th>
                    <th>Sent Time</th>
                    <th>Email</th>
                    <th>E-card</th>
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
                    <th>{$totalViewed}</th>
                    <th>{$totalOpened}</th>
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
    function getExportEmailHistory(){
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

        $file_name = "Email-History.xls";

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

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email Details');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Recipient');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Sent Time');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Email');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'E-card');
        
        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );
        
        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);
        
        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            //$actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        $actSheet->getColumnDimension('A')->setWidth(50);
        $actSheet->getColumnDimension('B')->setWidth(40);
        $actSheet->getColumnDimension('C')->setWidth(20);
        $actSheet->getColumnDimension('D')->setWidth(15);
        $actSheet->getColumnDimension('E')->setWidth(15);

        $SQL = "
        SELECT ap.title AS design
              ,am.title AS music
              ,e.language
              ,c.email AS sender_email
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
              ,eh.name AS recp_name
              ,eh.email AS recp_email
              ,eh.sent_date
              ,eh.creation_date
              ,eh.opened
              ,eh.viewed
              ,eh.sent
        FROM ecard_history eh
        JOIN ecard e   ON (e.ecard_id   = eh.ecard_id)
        JOIN assets ap ON (ap.assets_id = e.picture_id)
        JOIN assets am ON (am.assets_id = e.music_id)
        JOIN contact c ON (c.contact_id = e.contact_id)
        WHERE eh.sent = 1
          AND e.music_id IS NOT NULL
          AND e.picture_id IS NOT NULL
          ORDER BY eh.sent_date DESC
        ";
        
        $result = $db->sql_query($SQL);

        $totalViewed = 0;
        $totalOpened = 0;

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;
            
            $col1Text = "Design: {$row['design']}\nMusic: {$row['music']}\nLanguage: {$row['language']}\nSender: {$row['sender_email']}\n";
            $col2Text = "Name: {$row['recp_name']}\nEmail: {$row['recp_email']}\n";

            $viewed = ($row['viewed'] == 1) ? 'Viewed' : '';
            $opened = ($row['opened'] == 1) ? 'Opened' : '';
            $sent_date = ($row['sent_date'] != '') ? $row['sent_date'] : $row['creation_date'];
        
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $col1Text);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $col2Text);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $sent_date);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $viewed);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $opened);

            $totalViewed += $row['viewed'];
            $totalOpened += $row['opened'];
        }

        $rowc++;
        $actSheet->setCellValueByColumnAndRow(0, $rowc, 'Total');
        $actSheet->setCellValueByColumnAndRow(3, $rowc, $totalViewed);
        $actSheet->setCellValueByColumnAndRow(4, $rowc, $totalOpened);

        $actSheet->getStyle("A{$rowc}:F{$rowc}")->applyFromArray($headStyle);

        $colStr = PHPExcel_Cell::stringFromColumnIndex($colc);
        $actSheet->getStyle("A1:{$colStr}{$rowc}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $actSheet->getStyle("A1:{$colStr}{$rowc}")->getAlignment()->setWrapText(true);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }   

    //==================================================================//
}
