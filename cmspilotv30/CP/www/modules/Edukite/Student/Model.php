<?
class CP_Www_Modules_Edukite_Student_Model extends CP_Common_Modules_Edukite_Student_Model
{
    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('first_name', 'Please enter the firstname');
        $validate->validateData('last_name', 'Please enter the lastname');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'student_code');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'known_as_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_postal_code');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'joined_date');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'date_joined');
        $fa = $fn->addToFieldsArray($fa, 'username');
        $fa = $fn->addToFieldsArray($fa, 'comments');


        return $fa;
    }

    /**
     *
     */
    function getClassList() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = "";

        $sqlClass = getCPModuleObj('edukite_class')->model->getSQL();
        $result = $db->sql_query($sqlClass);

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td>{$row['title']}</td>
                <td>
                <a href='#' class='classLinkArrow'>
                <img src='/cmspilotv30/CP/www/themes/{$cpCfg['cp.theme']}/images/arrow.jpg'>
                </a>
                </td>
            </tr>
            ";
        }

        $text = "
        <div class='row'>
            <table class='list'>{$rows}</table>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['status'] = 'Active';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getDeleteLinkedClasses() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $student_id = $fn->getReqParam('student_id');
        $class_id  = $fn->getReqParam('class_id');

        $deleteSQL     = "
        DELETE FROM class_student
        WHERE class_id = {$class_id}
            AND student_id = {$student_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_student');
        $text    = $viewObj->getLinkedClassList($student_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteLinkedCohort() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $student_id     = $fn->getReqParam('student_id');
        $year_group_id  = $fn->getReqParam('year_group_id');

        $deleteSQL     = "
        DELETE FROM student_year_group
        WHERE year_group_id = {$year_group_id}
            AND student_id = {$student_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_student');
        $text    = $viewObj->getLinkedCohortList($student_id);

        return $text;
    }

    /**
     *
     */
    function getDeleteLinkedParents() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        $parent_id   = $fn->getReqParam('parent_id');
        $student_id  = $fn->getReqParam('student_id');

        $deleteSQL     = "
        DELETE FROM student_parent
        WHERE student_id  = {$student_id}
            AND parent_id = {$parent_id}
        ";
        $deleteResult  = $db->sql_query($deleteSQL);

        $viewObj = getCPViewObj('edukite_student');
        $text    = $viewObj->getLinkedParentList($student_id);

        return $text;
    }
    /**
     *
     */
    function getImportStudentImages() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $media = Zend_Registry::get('media');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $mediaArray = Zend_Registry::get('mediaArray');

        if (!isset($mediaArray['edukite_student']['picture'])){
            $mediaArrayObj->setMediaArray('edukite_student');
        }

        $mediaArray = Zend_Registry::get('mediaArray');

        $mediaArr  = $mediaArray['edukite_student']['picture'];
        $rows = "";
        require_once("imageResize.php");
        $imageResize = new ImageResize();

        //http://edukitedev.localhost/index.php?module=edukite_student&_spAction=importStudentImages&showHTML=0
        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];
        print 'comment the exit in the start and run this script' . '<br>';
        exit();


        // Define the full path to your folder from root
        $sourceTempFolder = $mediaArray["tempFolder"].'/Photo';
        $destFolder       = $mediaArr["thumbFolder"] ;
        $destFolderNormal = $mediaArr["normalFolder"] ;
        $destFolderLarge  = $mediaArr["largeFolder"] ;

        /*
        $imagearr = scandir($sourceTempFolder, 1);
        print '<pre>';
        print_r($imagearr);
        print '</pre>';
        exit();
        */


        // Open the folder
        $dir_handle = @opendir(realpath($sourceTempFolder)) or die("Unable to open $sourceTempFolder");

        // Loop through the files
        $SQL = "
        SELECT s.*
        FROM student s
        WHERE s.status = 'Active'
        LEFT JOIN (media m) ON (s.student_id = m.record_id)
        ORDER BY s.student_id
        ";
        $SQL = "
        SELECT s.student_id, s.student_code, s.first_name, s.last_name
        FROM student s
        WHERE s.status = 'Active'
        AND student_id not in(select record_id from media where room_name = 'edukite_student')
        ORDER BY s.student_id";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        //*** if student code exist in the table
        $count = 0;
        while ($row = $db->sql_fetchrow($result)){
            $student_code = $row['student_code'];
            if($student_code){
                //Arkouzis, Christos
                //$student_code = $row['last_name'] . ', '. $row['first_name'];
                if(strlen($student_code) == 4){
                    $student_code = '0' . $student_code;
                }
                $image = $student_code . '.jpg';
                //$sourceFilePath = "../media/temp/{$image}";
                $sourceFilePath = $sourceTempFolder. "/{$image}";
                print 'Student Code: ' . $student_code .  $row['last_name'] .' '.  $row['first_name'] . "<br>";
                if(file_exists($sourceFilePath)){
                    $destFileName   =  $image;
                    $actualFileName = $destFileName;
                    ///*
                    $fieldsArray = array();
                    $fa = &$fieldsArray;
                    $fa['record_id']              = $row['student_id'];
                    $fa['record_type']            = "picture";
                    $fa['room_name']              = 'edukite_student';
                    $fa['lang']                   = 'eng';
                    $fa['actual_file_name']       = $actualFileName;
                    $fa['content_type']           = "image/jpeg";
                    $fa['content_type']           = "image";
                    $fa['media_type']             = "image";
                    $fa['media_size']             = 32777;
                    $fa['creation_date']          =  date("Y-m-d H:i:s");
                    $InsertSQL         = $dbUtil->getInsertSQLStringFromArray($fa, "media");
                    $resultUpdate      = $db->sql_query($InsertSQL);
                    $media_id    = $db->sql_nextid();
                    //print $SQL ."<br>";
                    //*/
                    $destFileName = $media_id . "_" . $cpUtil->fixFileName($actualFileName);
                    $destFilePathThumb  = $destFolder .  $destFileName;
                    $destFilePathNormal = $destFolderNormal .  $destFileName;
                    $destFilePathLarge  = $destFolderLarge .  $destFileName;

                    $fa = array();
                    $fa['file_name'] = $destFileName;

                    $whereCondition = "WHERE media_id = {$media_id}";
                    $InsertSQL            = $dbUtil->getUpdateSQLStringFromArray($fa, "media", $whereCondition);
                    $resultUpdate         = $db->sql_query($InsertSQL);

                    print 'Source : ' . $sourceFilePath . "<br>";
                    //print $destFilePath . "<br>";
                    print 'Dest : ' . $destFileName . "<br>" . "Count : " . $count . "<br>" ;

                    $imageResize->imageCreateThumb($sourceFilePath, $destFilePathThumb, 142, 200);
                    $imageResize->imageCreateThumb($sourceFilePath, $destFilePathNormal, 142, 200);
                    $imageResize->imageCreateThumb($sourceFilePath, $destFilePathLarge, 142, 200);
                    $count++;
                }
                if($count ==1){
                    //return;
                }
            }
        }


        // Close
        closedir($dir_handle);

        print "the import was successful";
    }

    /**
     *FPDF FORMAT
     */
    function getPrintAchievementAsPdfOLD() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html2pdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/mc_table.php');

        $pdf = new PDF_MC_Table();
		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $student_id = $fn->getReqParam('student_id');

        $SQL = "
        SELECT sa.*
        	  ,a.title AS achievement_title
        	  ,a.achievement_code
        	  ,n.notice_id
        FROM achievement_student sa
        LEFT JOIN (achievement a) ON (a.achievement_id = sa.achievement_id)
        LEFT JOIN (notice n) ON (sa.notice_id = n.notice_id)
        WHERE sa.student_id = {$student_id}
        ";
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");
        $count = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
        //============================================================================= //
        $pdf->SetFont('Arial','',9);
        //syed:multi text code to set width of each column and alignment
        $pdf->SetWidths(array(20, 130, 40));
        $pdf->SetAligns(array('L', 'L', 'L'));

        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                 /*Logo of the institution */
                /*$pdf->Image('images/logo.jpg',10,5,45);
                $pdf->SetXY(10,8);
                $pdf->Cell(30, 20, $cpCfg['cp.addressPdf1'] . ' ' . $cpCfg['cp.addressPdf2'] . ' ' . $cpCfg['cp.addressPdf3'] . ' ' .
                                    $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                //$pdf->SetFont('Arial','',9);
                $pdf->Cell(50, 24, 'Authorized Distributor of:');
                $pdf->SetXY(10,25);
                $pdf->Image('images/parker.jpg',10,28, 25);
                //$pdf->Image('images/gse.png',42,25, 25);

                $pdf->Ln(5);
                $pdf->SetXY(152,16);
                $pdf->Cell(50, 20, $cpCfg['printEmailAddress']);
                $pdf->Ln(5);
                $pdf->SetXY(152,21);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf7']);
                $pdf->Ln(5);
                $pdf->SetXY(152,26);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf6']);
                */

                /* Header */
                $pdf->SetFont('Arial','BU',9);
                $pdf->SetXY(90, 35);
                $pdf->Cell(21, 20, "ACHIEVEMENT LIST", 0, 0, 'C');
                $pdf->Ln(15);

                /*$pdf->SetFont('Arial','B',9);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(65,8,"TO",1,0, '0', 1);
                $pdf->Cell(60, 8, "QUOTE CODE : {$quoteCode}", '1', 0, 'L', 1);
	            $pdf->Cell(65, 8, "DATE : {$fn->getCPDate($row['quote_date'], 'd-m-Y')}", '1', 0, 'L', 1);
                //$pdf->Cell(95,8,"",1,0, '0', 1);
                $pdf->Ln();
                //$pdf->SetFillColor(254,203,156);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Arial','',9);
                $pdf->Cell(190, 5, $row['company_name'], 'TLR', 0, 'L', 1);
                //$pdf->SetFont('Arial','B',10);
                //$pdf->Cell(95, 8, "QUOTE CODE : {$quoteCode}", 'LR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(190, 5, "$contact_name", 'LR', 0, 'L', 1);
	            //$pdf->Cell(95, 12, "DATE : {$fn->getCPDate($row['quote_date'], 'd-m-Y')}", 'R', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(190, 5, $billingAddressFlat . ' ' . $billingAddressStreet . ' ' . $billingAddressTown . ' ' .
	                        $billingAddressCountry . ' - ' . $billingAddressState, 'LR', 0, 'L', 1);

                $pdf->Ln();
	            $pdf->Cell(190, 2, '', 'BLR', 0, 'L', 1);
                $pdf->Ln();
                $pdf->Ln(4);
                */

                /* List of order items header */
                $pdf->SetFont('Arial','B',9);
                //$pdf->SetFillColor(254,203,156);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(20,8,"S.NO",1,0, 'C', 1);
                $pdf->Cell(130,8,"ACHIEVEMENT",1,0, 'C', 1);
                $pdf->Cell(40,8,"CODE",1,0, 'C', 1);
                $pdf->Ln();
                $x=$pdf->GetX()+ 10;
                $y=$pdf->GetY();

                $height=10;
                $leftmargin=92;
            }

            //===================================MAIN TABLE============================= //

            $pdf->SetFont('Arial','',9);
            $pdf->SetFillColor(255,255,255);
            //code to match values in the table for each column
            $pdf->Row(array($lineItemNumber, $row['achievement_title'] , $row['achievement_code']));

            $count++;
            $lineItemNumber++;

        }

			$pdf->Output();

    }
    /**
     *
     This function will add password to the students if they are less than 6 characters
     */
    function getStudentPassword() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows = "";

        set_time_limit(1000000);
        $hostName   = $_SERVER['HTTP_HOST'];

        //http://edukitedev.localhost/index.php?module=edukite_student&_spAction=studentPassword&showHTML=0

        $SQLParent   = "SELECT c.*
                     ,c.pass_word as student_password
                     FROM student c
                     ORDER BY c.student_id";
        $resultParent = $db->sql_query($SQLParent);
        $numParent    = $db->sql_numrows($resultParent);

        while ($rowStudent = $db->sql_fetchrow($resultParent)) {
            $password_add = "";
            $len = strlen($rowStudent['student_password']);
            if($len == 0){
                $password_add = $rowStudent['first_name'];
            }
            else if($len == 1){
                $password_add = $rowStudent['first_name'];
            }
            else if($len == 2){
                $password_add = $rowStudent['first_name'];
            }
            else if($len == 3){
                $password_add = substr(rand(),0,3);
            }
            else if($len == 4){
                $password_add = substr(rand(),0,2);
            }
            else if($len == 5){
                $password_add = substr(rand(),0,1);;
            }
            else if($len > 6){
                //$password_add = substr(rand(),0,2);;
            }
            if($len < 6){
                $fa = array();
                $fa['pass_word']  = $rowStudent['student_password'] . $password_add;
                $whereCondition = "WHERE student_id = {$rowStudent['student_id']}";
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'student', $whereCondition);
                $db->sql_query($SQL);
                print 'Password :' . $rowStudent['student_password'] . ' - ' . $len . ' - ' . $password_add .  "<br>";
                print  $SQL .  "<br>";
            }
                //return;
        }
    }

    /**
     *TCPDF FORMAT
     */

    function getPrintAchievementAsPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

       // include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');
       include_once(CP_CORE_PATH.'CP/www/modules/Edukite/Student/headfoot.php');

        //$pdf = new MYPDF2();
        // create new PDF document
        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Edukite Outcomes');
        $pdf->SetTitle('Edukite Outcomes');
        $pdf->SetSubject('Edukite Outcomes');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------QUOTE QUERY START

        $pdf->SetFont('Arial','B',10);
        $pdf->AddPage();

        $student_id       = $fn->getReqParam('student_id');

        $SQL = "
        SELECT DISTINCT sa.achievement_id
              ,sa.student_id
              ,a.title AS achievement_title
              ,a.achievement_code
              ,a.number
              ,n.notice_id
              ,CONCAT_WS(' ', st.first_name, st.last_name) AS student_name
        FROM achievement_student sa
        LEFT JOIN (student st) ON (st.student_id = sa.student_id)
        LEFT JOIN (achievement a) ON (a.achievement_id = sa.achievement_id)
        LEFT JOIN (notice n) ON (sa.notice_id = n.notice_id)
        WHERE sa.student_id = {$student_id}
        order by a.number
        ";
        $result         = $db->sql_query($SQL);
        $numRows        = $db->sql_numrows($result);
        $resultStudent  = $db->sql_query($SQL);
        $rowStudent     = $db->sql_fetchrow($resultStudent);

        $today = date("Y-m-d");
        $count = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt

        //============================================================================= //

        $pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT *
        FROM media
        WHERE record_id = '{$rowStudent['student_id']}'
        AND room_name = 'edukite_student'
        AND record_type =  'picture'
        ";

        $resultStudentImage  = $db->sql_query($SQL);
        $rowStudentImage     = $db->sql_fetchrow($resultStudentImage);


        $studentImage = '<img border="0" src="/media/thumb/'.$rowStudentImage['file_name'].'" width="60" height="50" />';


        $tb1studentImage ='
        <table border="1"  style="font-weight: bold;" cellpadding="4" width="100%">
          <tr>
             <td bgcolor="#6594EE" style="color: #FFFFFF;" width="18%"><b>Student Name :</b></td>
             <td style="color: #000000;" width="67%">'.$rowStudent['student_name'].'</td>
             <td width="15%" align ="right">'.$studentImage.'</td>
          </tr>
        </table>
        ';

        $tb1edukiteText ='
        <table border="0" cellpadding="4" width="100%">
          <tr>
             <td><b>Please find the outcomes as below</b></td>

          </tr>
        </table>
        ';

        $tblAchievement ='<table border="1" style="font-weight: bold;" cellpadding="4" width="100%">';
        $tblAchievement = $tblAchievement.'
        <tr bgcolor="#6594EE">
            <th style="color: #FFFFFF;" width="7%"><b>NO.</b></th>
            <th style="color: #FFFFFF;" width="66%" align="center"><b>ACHIEVEMENT LIST</b></th>
            <th style="color: #FFFFFF;" width="27%" align="center"><b>ACTIVITY TITLE</b></th>
        </tr>
        ';

        while ($row = $db->sql_fetchrow($result)) {

            $noticeTitle = '';

            $noticeRec = $fn->getRecordRowByID('notice', 'notice_id', $row['notice_id']);
            $date = $dateUtil->formatDate($noticeRec['launch_date'], 'DD-MM-YYYY');

            if($noticeRec['title'] != '')  {
                $noticeTitle = $noticeRec['title'] . '('.$date. ')';
            }

             $tblAchievement = $tblAchievement.'
                    <tr nobr="true">
                        <td width="7%">'.$row['number'].'</td>
                        <td width="66%">'.$row['achievement_title'].'</td>
                        <td width="27%">'.$noticeTitle.'</td>
                    </tr>';


               $lineItemNumber++;

        }

        $tblAchievement = $tblAchievement.'</table>';

        $pdf->Ln(12);
        $pdf->writeHTML($tb1studentImage, true, false, false, false, '');
        $pdf->writeHTML($tb1edukiteText, true, false, false, false, '');
        $pdf->writeHTML($tblAchievement, true, false, false, false, '');
        $pdf->Output('edukite_outcomes.pdf', 'I');

    }

    /**
     *TCPDF FORMAT
     */

    function getPrintAchievementAsPdfForAllStudent() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');


        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        $student_id       = $fn->getReqParam('student_id');

        $SQLMain = "
        SELECT DISTINCT sa.student_id
        FROM achievement_student sa
        LEFT JOIN (student st) ON (st.student_id = sa.student_id)
        WHERE st.status = 'Active'
        GROUP BY sa.student_id
        ";
        $resultMain         = $db->sql_query($SQLMain);

        while ($rowMain = $db->sql_fetchrow($resultMain)) {

           // include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
            //include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/tcpdf.php');
           include_once(CP_CORE_PATH.'CP/www/modules/Edukite/Student/headfoot.php');

            //$pdf = new MYPDF2();
            // create new PDF document
            $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Edukite Outcomes');
            $pdf->SetTitle('Edukite Outcomes');
            $pdf->SetSubject('Edukite Outcomes');
            //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

            // set default header data
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // set some language-dependent strings (optional)
            if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                require_once(dirname(__FILE__).'/lang/eng.php');
                $pdf->setLanguageArray($l);
            }

            // ---------------------------------------------------------QUOTE QUERY START

            $pdf->SetFont('Arial','B',10);
            $pdf->AddPage();

            $SQL = "
            SELECT DISTINCT sa.achievement_id
                  ,sa.student_id
                  ,a.title AS achievement_title
                  ,a.achievement_code
                  ,a.number
                  ,n.notice_id
                  ,CONCAT_WS(' ', st.first_name, st.last_name) AS student_name
            FROM achievement_student sa
            LEFT JOIN (student st) ON (st.student_id = sa.student_id)
            LEFT JOIN (achievement a) ON (a.achievement_id = sa.achievement_id)
            LEFT JOIN (notice n) ON (sa.notice_id = n.notice_id)
            WHERE sa.student_id = {$rowMain['student_id']}
            order by a.number
            ";
            $result         = $db->sql_query($SQL);
            $numRows        = $db->sql_numrows($result);
            $resultStudent  = $db->sql_query($SQL);
            $rowStudent     = $db->sql_fetchrow($resultStudent);

            $today = date("Y-m-d");
            $count = 0;
            $rows = "";
            $lineItemNumber = 1;  // To increment the line item in receipt

            //============================================================================= //

            $pdf->SetFont('Arial','',10);

            $SQL = "
            SELECT *
            FROM media
            WHERE record_id = '{$rowStudent['student_id']}'
            AND room_name = 'edukite_student'
            AND record_type =  'picture'
            ";

            $resultStudentImage  = $db->sql_query($SQL);
            $rowStudentImage     = $db->sql_fetchrow($resultStudentImage);


            $studentImage = '<img border="0" src="/media/thumb/'.$rowStudentImage['file_name'].'" width="60" height="50" />';


            $tb1studentImage ='
            <table border="1"  style="font-weight: bold;" cellpadding="4" width="100%">
              <tr>
                 <td bgcolor="#6594EE" style="color: #FFFFFF;" width="18%"><b>Student Name :</b></td>
                 <td style="color: #000000;" width="67%">'.$rowStudent['student_name'].'</td>
                 <td width="15%" align ="right">'.$studentImage.'</td>
              </tr>
            </table>
            ';

            $tb1edukiteText ='
            <table border="0" cellpadding="4" width="100%">
              <tr>
                 <td><b>Please find the outcomes as below</b></td>

              </tr>
            </table>
            ';

            $tblAchievement ='<table border="1" style="font-weight: bold;" cellpadding="4" width="100%">';
            $tblAchievement = $tblAchievement.'
            <tr bgcolor="#6594EE">
                <th style="color: #FFFFFF;" width="7%"><b>NO.</b></th>
                <th style="color: #FFFFFF;" width="66%" align="center"><b>ACHIEVEMENT LIST</b></th>
                <th style="color: #FFFFFF;" width="27%" align="center"><b>ACTIVITY TITLE</b></th>
            </tr>
            ';

            while ($row = $db->sql_fetchrow($result)) {

                $noticeTitle = '';

                $noticeRec = $fn->getRecordRowByID('notice', 'notice_id', $row['notice_id']);
                $date = $dateUtil->formatDate($noticeRec['launch_date'], 'DD-MM-YYYY');

                if($noticeRec['title'] != '')  {
                    $noticeTitle = $noticeRec['title'] . '('.$date. ')';
                }

                 $tblAchievement = $tblAchievement.'
                        <tr nobr="true">
                            <td width="7%">'.$row['number'].'</td>
                            <td width="66%">'.$row['achievement_title'].'</td>
                            <td width="27%">'.$noticeTitle.'</td>
                        </tr>';


                   $lineItemNumber++;

            }

            $tblAchievement = $tblAchievement.'</table>';

            $pdf->Ln(12);
            $pdf->writeHTML($tb1studentImage, true, false, false, false, '');
            $pdf->writeHTML($tb1edukiteText, true, false, false, false, '');
            $pdf->writeHTML($tblAchievement, true, false, false, false, '');
            $pdf->Output('downloads/'.$rowStudent['student_name'].'.pdf', 'F');
            /*$path = "/edukite_outcomes.pdf";
            $pdf->Output($path,'F');
            header('location:'.$path);*/
        }

        $this->getDownloadAllOutcomes();
    }


    /**
     *
     */

    function getDownloadAllOutcomes() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        // Get real path for our folder
        $rootPath = realpath('downloads');
        $zip_file = 'edukite_outcomes.zip';
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($zip_file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zip_file));
        readfile($zip_file);
    }
}
