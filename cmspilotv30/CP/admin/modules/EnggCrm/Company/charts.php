<?
    //==================================================================//
    function getPrintReport() {
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $reportName = $fn->getReqParam('reportName');
        $searchQueryString = $pager->removeQueryString(array('_spAction'));

        $module = $fn->getReqParam('module');
        if ($module == "") {
            $searchQueryString .= '&module=' . $tv['module'];
        }

        $text = "";

        if ($reportName == "gantt") {
            $imgUrl = "{$searchQueryString}&_spAction=ganttChart&_sortOrder=a.start_date&showHTML=0&hasDB=1";
        }
        else if ($reportName == "barChartSales") {
            $imgUrl = "{$searchQueryString}&_spAction=barChartSales&_sortOrder=a.start_date&showHTML=0&hasDB=1";
        }
        else if ($reportName == "barchart3rdParty") {
            $imgUrl = "{$searchQueryString}&_spAction=barChartThirdParty&showHTML=0&hasDB=1";
        }
        else if ($reportName == "barChartInHouse") {
            $imgUrl = "{$searchQueryString}&_spAction=barChartInHouse&showHTML=0&hasDB=1";
        }
        else if ($reportName == "pie") {
            $imgUrl = "{$searchQueryString}&_spAction=pieChart&showHTML=0&hasDB=1";
        }
        else if ($reportName == "icon") {
            $imgUrl = "{$searchQueryString}&_spAction=iconChart&showHTML=0&hasDB=1";
        }
        else if ($reportName == "scheduleGanttChart") {
            $imgUrl = "{$searchQueryString}&_spAction=scheduleGanttChart&showHTML=0&hasDB=1";
        }

        $text = "
        {$dh->getReportHeader()}
        <img src='{$imgUrl}' />
        {$dh->getListFooterReport()}
        ";

        return $text;
    }

    //==================================================================//
    function getGanttChart($result) {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        require_once("include/ChartDirector/lib/phpchartdir.php");

        $numRows = $db->sql_numrows($result);

        $startDateArray = array();
        $endDateArray = array();
        $labelArray = array();
        $rangeEndMonthTemp = 0;

        $rowCounter = 1;

        while ($row = $db->sql_fetchrow($result)) {

            $year1  = $dateUtil->formatDate($row['start_date'], 'YYYY');
            $month1 = $dateUtil->formatDate($row['start_date'], 'MM');
            $day1   = $dateUtil->formatDate($row['start_date'], 'DD');

            $year2  = $dateUtil->formatDate($row['estimated_finish_date'], 'YYYY');
            $month2 = $dateUtil->formatDate($row['estimated_finish_date'], 'MM');
            $day2   = $dateUtil->formatDate($row['estimated_finish_date'], 'DD');

            $month2 = $month2 > 0 ? $month2: date("m", mktime(0, 0, 0, $month1 + 1, 1, date('Y')));

            $startDate = chartTime($year1, $month1, $day1);
            $endDate = $year2 > 0 ? chartTime($year2, $month2, $day2) : chartTime(date('Y'), $month2, 31);

            $startDateArray[] = $startDate;
            $endDateArray[] = $endDate;
            $labelsArray[] = $row['title'];

            if ($rowCounter == 1) {
                $rangeStartYear = $year1 > 0 ? $year1  : date('Y');
                $rangeStartMonth = $month1 > 0 ? $month1 : 01;
                $rangeStartMonthName = $dateUtil->getShortMonthName($rangeStartMonth);
            }
            //print $rangeStartYear . "start year";
            $rangeEndMonthTemp = ($month2 > $rangeEndMonthTemp) ? $month2 : $rangeEndMonthTemp;
            if ($rowCounter == $numRows) {
                $rangeEndYear = $year2 > 0 ? $year2  : date('Y');
                $rangeEndMonth = $rangeEndMonthTemp > 0 ? $rangeEndMonthTemp : date("m", mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
                $rangeEndMonthName = $dateUtil->getShortMonthName($rangeEndMonth);
            }

            $rowCounter++;
        }

        # Create a XYChart object of size 620 x 280 pixels. Set background color to light
        # blue (ccccff), with 1 pixel 3D border effect.

        $noOfYears = $rangeEndYear - $rangeStartYear;

        $chartWidth = $noOfYears > 0 ? $noOfYears * 600 : 900;
        $chartheight = $numRows * 25;

        $plotAreaStartX = 300;
        $plotAreaStartY = 55;

        $plotAreaWidth = $chartWidth - $plotAreaStartX;
        $plotAreaHeight = $chartheight - $plotAreaStartY - 20;

        $c = new XYChart($chartWidth, $chartheight, 0xE3E4E8, 0x000000, 1);

        # Add a title to the chart using 15 points Times Bold Itatic font, with white
        # (ffffff) text on a deep blue (000080) background
        $textBoxObj = $c->addTitle("Projects:  {$rangeStartMonthName} {$rangeStartYear} - {$rangeEndMonthName} {$rangeEndYear}", "verdana.ttf", 13, 0xffffff);
        $textBoxObj->setBackground(0xEE2C2A);

        # Set the plotarea at (140, 55) and of size 460 x 200 pixels. Use alternative
        # white/grey background. Enable both horizontal and vertical grids by setting their
        # colors to grey (c0c0c0). Set vertical major grid (represents month boundaries) 2
        # pixels in width
        $plotAreaObj = $c->setPlotArea($plotAreaStartX, $plotAreaStartY, $plotAreaWidth, $plotAreaHeight, 0xffffff, 0xeeeeee, LineColor,
                        0xc0c0c0, 0xc0c0c0);
        $plotAreaObj->setGridWidth(2, 1, 1, 1);

        # swap the x and y axes to create a horziontal box-whisker chart
        $c->swapXY();

        # Set the y-axis scale to be date scale from Aug 16, 2004 to Nov 22, 2004, with ticks
        # every 7 days (1 week)
        $c->yAxis->setDateScale(chartTime($rangeStartYear, $rangeStartMonth, 01), chartTime($rangeEndYear, $rangeEndMonth, 31), 86400 * 30);

        # Set multi-style axis label formatting. Month labels are in Arial Bold font in "mmm
        # d" format. Weekly labels just show the day of month and use minor tick (by using
        # '-' as first character of format string).
        /* This is for the month label in the top */
        if ($rangeStartYear == $rangeEndYear) {
            $c->yAxis->setMultiFormat(StartOfMonthFilter(), "<*font=arialbd.ttf*>{value|mmm d}",
                            StartOfDayFilter(), "-{value|d}");
        }
        else {
            $c->yAxis->setMultiFormat(StartOfMonthFilter(), "<*font=arialbd.ttf*>{value|yyyy-mmm d}",
                            StartOfDayFilter(), "-{value|d}");
        }
        # Set the y-axis to shown on the top (right + swapXY = top)
        $c->setYAxisOnRight();

        # Set the labels on the x axis
        $c->xAxis->setLabels($labelsArray);

        # Reverse the x-axis scale so that it points downwards.
        $c->xAxis->setReverse();

        # Set the horizontal ticks and grid lines to be between the bars
        $c->xAxis->setTickOffset(0.5);

        # Add a green (33ff33) box-whisker layer showing the box only.
        /* This is for the result bar in green */
        $c->addBoxWhiskerLayer($startDateArray, $endDateArray, null, null, null, 0x888F97,
                        SameAsMainColor, SameAsMainColor);

        # output the chart
        header("Content-type: image/png");
        print($c->makeChart2(PNG));
    }

    //==================================================================//
    function getBarChartSales($result) {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        $sqlMaster = Zend_Registry::get('sqlMaster');
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        require_once("include/ChartDirector/lib/phpchartdir.php");

        $SQL = $sqlMaster->getSQL($tv['module']);
        $SQL .= $searchVar->getSearchVar($tv['module']);

        //$pager->setPagerData($pager->getTotalRecords($SQL), $pager->getNumRecordsPerPage());

        //$SQL        = $pager->addLimitToSql($SQL);

        $result = $db->sql_query($SQL);
        //$numRows = $db->sql_numrows($result);
        $numRows = $db->sql_numrows($result);

        $startDateArray = array();
        $endDateArray = array();
        $labelArray = array();
        $data0 = array();
        $data1 = array();
        $labels = array();
        $start_date = "";
        $end_date = "";
        $rangeEndMonthTemp = 0;

        $rowCounter = 1;

        while ($row = $db->sql_fetchrow($result)) {
            if ($rowCounter == 1) {
                $start_date = $row['start_date'];
            }
            $rowCounter = $rowCounter + 1;
            /* seek to last row
            mysql_data_seek($result, $numRows)
            $row = $db->sql_fetchrow($result)
            $end_date = $row['estimated_finish_date']; */

            if ($rowCounter == $numRows) {
                $end_date = $row['estimated_finish_date'];
            }
        }
        if ($start_date == '') {
            $start_date = '2007-01-01';
        }

        if ($end_date == ''|| $end_date == '0000-00-00') {
            $end_date = date("Y-m-d");
        }

        $used_third_party_sql = "
        (SELECT sum(actual_amount) AS total_cost
         FROM third_party_cost
         WHERE project_id = a.project_id
        )
        ";

        $used_inhouse_sql = "
        (SELECT sum(total_cost) AS total_cost
         FROM timesheet ts
         WHERE ts.project_id = a.project_id
        )
        ";

        $SQL = "
        SELECT a.start_date
              ,a.estimated_finish_date
              ,CONCAT_WS('', DATE_FORMAT(a.start_date, '%b'), '-', YEAR(a.start_date)) AS start_month
              ,{$used_third_party_sql} AS used_third_party
              ,{$used_inhouse_sql} AS used_inhouse
        FROM project a
        GROUP BY start_month
        HAVING a.start_date >= '{$start_date}'
          AND a.estimated_finish_date <= '{$end_date}'
        ORDER BY a.start_date
        ";

        $SQL = "
        SELECT a.start_date
              ,a.estimated_finish_date
              ,CONCAT_WS( '', DATE_FORMAT( a.start_date, '%b' ) , '-', YEAR( a.start_date ) ) AS start_month
              ,(SELECT sum( actual_amount ) AS total_cost
                FROM third_party_cost tp
                    ,project p
                WHERE tp.project_id = p.project_id
                  AND p.start_date BETWEEN a.start_date AND a.estimated_finish_date
               ) AS used_third_party
              ,(SELECT sum( total_cost ) AS total_cost
                FROM timesheet ts
                    ,project p
                WHERE ts.project_id = p.project_id
                AND p.start_date BETWEEN a.start_date AND a.estimated_finish_date
               ) AS used_inhouse
        FROM project a
        GROUP BY start_month
        HAVING a.start_date >= '{$start_date}'
        AND a.estimated_finish_date <= '{$end_date}'
        ORDER BY a.start_date";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            # The data for the bar chart
            $data0[] = $row['used_third_party'];
            $data1[] = $row['used_inhouse'];
            # The labels for the bar chart
            $labels[] = $row['start_month'];
        }

        # Create a XYChart object of size 400 x 240 pixels
        $c = new XYChart(800, 440);

        # Add a title to the chart using 10 pt Arial font
        //$c->addTitle(" Total Sales by Month", "", 10);
        $textBoxObj = $c->addTitle(" Total Sales by Month", "verdana.ttf", 13, 0xffffff);
        $textBoxObj->setBackground(0xEA2E2D);

        # Set the plot area at (50, 25) and of size 320 x 180. Use two alternative background # colors (0xffffc0 and 0xffffe0)
        $c->setPlotArea(50, 25, 720, 380, 0xffffff, 0xffffff);

        # Add a legend box at (55, 18) using horizontal layout. Use 8 pt Arial font, with
        # transparent background
        $legendObj = $c->addLegend(55, 18, false, "", 8);
        $legendObj->setBackground(Transparent);

        # Add a title to the y-axis
        $c->yAxis->setTitle("Amount in HK$");

        # Reserve 20 pixels at the top of the y-axis for the legend box
        $c->yAxis->setTopMargin(20);

        # Set the x axis labels
        $c->xAxis->setLabels($labels);

        # Add a multi-bar layer with 3 data sets and 3 pixels 3D depth
        $layer = $c->addBarLayer2(Side, 3);
        $layer->addDataSet($data0, 0xff0000, "Third party");
        $layer->addDataSet($data1, 0x666666, "In House");

        # output the chart
        header("Content-type: image/png");
        print($c->makeChart2(PNG));
    }

    //==================================================================//
    function getScheduleGanttChart($result) {

        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        require_once("include/ChartDirector/lib/phpchartdir.php");

        $row = $db->sql_fetchrow($result);
        $project_id = $row['project_id'];
        $project_title = $row['title'];

        //---------------------------------//
        $SQL = "
        SELECT *
        FROM schedule
        WHERE project_id = {$project_id}
        ";

        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows) {
            $startDateArray = array();
            $endDateArray = array();
            $labelArray = array();
            $rangeEndMonthTemp = 0;

            $rowCounter = 1;

            while ($row = $db->sql_fetchrow($result)) {

                $year1  = $dateUtil->formatDate($row['start_date'], 'YYYY');
                $month1 = $dateUtil->formatDate($row['start_date'], 'MM');
                $day1   = $dateUtil->formatDate($row['start_date'], 'DD');

                $year2  = $dateUtil->formatDate($row['end_date'], 'YYYY');
                $month2 = $dateUtil->formatDate($row['end_date'], 'MM');
                $day2   = $dateUtil->formatDate($row['end_date'], 'DD');

                $month2 = $month2 > 0 ? $month2: date("m", mktime(0, 0, 0, $month1 + 1, 1, date('Y')));

                $startDate = chartTime($year1, $month1, $day1);
                $endDate = $year2 > 0 ? chartTime($year2, $month2, $day2) : chartTime(date('Y'), $month2, 31);

                $startDateArray[] = $startDate;
                $endDateArray[] = $endDate;
                $labelsArray[] = $row['title'];

                if ($rowCounter == 1) {
                    $rangeStartYear = $year1 > 0 ? $year1  : date('Y');
                    $rangeStartMonth = $month1 > 0 ? $month1 : 01;
                    $rangeStartMonthName = $dateUtil->getShortMonthName($rangeStartMonth);
                }
                $rangeEndMonthTemp = ($month2 > $rangeEndMonthTemp) ? $month2 : $rangeEndMonthTemp;
                if ($rowCounter == $numRows) {
                    $rangeEndYear = $year2 > 0 ? $year2  : date('Y');
                    $rangeEndMonth = $rangeEndMonthTemp > 0 ? $rangeEndMonthTemp : date("m", mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
                    $rangeEndMonthName = $dateUtil->getShortMonthName($rangeEndMonth);
                }

                $rowCounter++;
            }

            # Create a XYChart object of size 620 x 280 pixels. Set background color to light
            # white (ffffff), with 1 pixel 3D border effect.

            $noOfYears = $rangeEndYear - $rangeStartYear;

            $chartWidth = $noOfYears > 0 ? $noOfYears * 600 : 900;
            $chartHeight = $numRows * 100;
            $plotAreaStartX = 120;
            $plotAreaStartY = 55;

            $plotAreaWidth = $chartWidth - $plotAreaStartX;
            $plotAreaHeight = $chartHeight - $plotAreaStartY - 20;

            $c = new XYChart($chartWidth, $chartHeight, 0xE3E4E8, 0x000000, 1);

            # Add a title to the chart using 15 points Times Bold font, with black
            # (000000) text on a grey (808080) background
            $textBoxObj = $c->addTitle("Schedule for the project: {$project_title}", "verdana.ttf", 13, 0x000000);
            $textBoxObj->setBackground(0xEE2C2A);

            # Set the plotarea at (140, 55) and of size 460 x 200 pixels. Use alternative
            # white/grey background. Enable both horizontal and vertical grids by setting their
            # colors to grey (c0c0c0). Set vertical major grid (represents month boundaries) 2
            # pixels in width
            $plotAreaObj = $c->setPlotArea($plotAreaStartX, $plotAreaStartY, $plotAreaWidth, $plotAreaHeight, 0xffffff, 0xeeeeee, LineColor,
                            0xc0c0c0, 0xc0c0c0);
            $plotAreaObj->setGridWidth(2, 1, 1, 1);

            # swap the x and y axes to create a horziontal box-whisker chart
            $c->swapXY();

            # Set the y-axis scale to be date scale from Aug 16, 2004 to Nov 22, 2004, with ticks
            # every 7 days (1 week)
            $c->yAxis->setDateScale(chartTime($rangeStartYear, $rangeStartMonth, 01), chartTime($rangeEndYear, $rangeEndMonth, 31), 86400 * 1);

            # Set multi-style axis label formatting. Month labels are in Arial Bold font in "mmm
            # d" format. Weekly labels just show the day of month and use minor tick (by using
            # '-' as first character of format string).
            /* This is for the month label in the top */
            $c->yAxis->setMultiFormat(StartOfMonthFilter(), "<*font=arialbd.ttf*>{value|yyyy-mmm d}",
                            StartOfDayFilter(), "-{value|d}");

            # Set the y-axis to shown on the top (right + swapXY = top)
            $c->setYAxisOnRight();

            # Set the labels on the x axis
            $c->xAxis->setLabels($labelsArray);

            # Reverse the x-axis scale so that it points downwards.
            $c->xAxis->setReverse();

            # Set the horizontal ticks and grid lines to be between the bars
            $c->xAxis->setTickOffset(0.5);

            # If condition is used to set the cell color according to percentage used value.
            # If percentage used is >= 100, dark pink (FFA0A0) is the color
            # If percentage used is > 100, light pink (FFCFCF) is the color
            # If the conditions are not satisfied, then the color is grey (D7D0D0)
            /* This is for the result bar */

            $percentage_used = $row['percentage_used'];

            if ($percentage_used >= 100) {
                $color = 0xFFA0A0;
            } else if ($percentage_used > 80) {
                $color = 0xFFCFCF;
            } else {
                $color = 0x888F97;
            }

            $c->addBoxWhiskerLayer($startDateArray, $endDateArray, null, null, null, $color,
                            SameAsMainColor, SameAsMainColor);

            # output the chart
            //header("Content-type: image/png");
            print($c->makeChart2(PNG));
        }
    }

    //==================================================================//
    function getBarChartInHouse($result) {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        require_once("include/ChartDirector/lib/phpchartdir.php");

        $row = $db->sql_fetchrow($result);

        # The data for the bar chart
        $data = array(0, $row['budget_inhouse'], $row['used_inhouse'], 0);
        # The labels for the bar chart
        $labels = array("", "budgeted", "used", "");
        # The colors for the bar chart
        $colors = array(0x888F95, 0x888F95, 0x888F95, 0x888F95, 0x888F95);
        # Create a XYChart object of size 250 x 250 pixels
        $c = new XYChart(250, 350);
        //$c = new XYChart(170, 250, goldColor(), -1, 2);
        # Set the plotarea at (30, 20) and of size 200 x 200 pixels
        $c->setPlotArea(50, 35, 200, 250);
        # Add a bar chart layer using the given data
        $barLayerObj = $c->addBarLayer3($data, $colors);
        //$c->setBackground(0xE3E4E8);
        //$barLayerObj->setBackground(transparent);
        # Set the labels on the x axis.
        $c->xAxis->setLabels($labels);
        # output the chart
        header("Content-type: image/png");
        print($c->makeChart2(PNG));
    }

    //==================================================================//
    function getBarChartThirdParty($result) {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        require_once("include/ChartDirector/lib/phpchartdir.php");

        $numRows = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        # The data for the bar chart
        $data = array(0, $row['budget_third_party'], $row['used_third_party'], 0);
        # The labels for the bar chart
        $labels = array("", "budgeted", "used", "");
        # The colors for the bar chart
        $colors = array(0x888F95, 0x888F95, 0x888F95, 0x888F95, 0x888F95);
        # Create a XYChart object of size 250 x 250 pixels
        $c = new XYChart(250, 350);
        //$c = new XYChart(170, 250, goldColor(), -1, 2);
        # Set the plotarea at (30, 20) and of size 200 x 200 pixels
        $c->setPlotArea(50, 35, 200, 250);
        # Add a bar chart layer using the given data
        $barLayerObj = $c->addBarLayer3($data, $colors);
        //$c->setBackground(0xE3E4E8);
        //$c->addBarLayer($data);
        # Set the labels on the x axis.
        $c->xAxis->setLabels($labels);
        # output the chart
        header("Content-type: image/png");
        print($c->makeChart2(PNG));
    }

    //==================================================================//
    function getPieChart($result) {
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        $pager = Zend_Registry::get('pager');

        require_once("include/ChartDirector/lib/phpchartdir.php");

        $labelArray = array();
        $dataArray = array();
        $category1 = 0;
        $category2 = 0;
        $category3 = 0;
        $category4 = 0;
        $category5 = 0;
        $category6 = 0;
        $category7 = 0;

        $numRows = $db->sql_numrows($result);

        $sqlCombo = "
        SELECT count(*) AS total_count
        FROM valuelist
        WHERE key_text = 'projectCategory'
        ORDER BY sort_order
        ";
        $resultCount = $db->sql_query($sqlCombo);
        $rowCount = $db->sql_fetchrow($resultCount);
        $numRowsCount = $rowCount['total_count'];

        $sqlCombo = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'projectCategory'
        ORDER BY sort_order
        ";
        $result1 = $db->sql_query($sqlCombo);

        while ($rowCategory = $db->sql_fetchrow($result1)) {
            $labelArray[] = $rowCategory['value'];

        }

        $rowCounter = 1;

        while ($row = $db->sql_fetchrow($result)) {
            if ($labelArray[0] == $row['category']) {
                $category1 = $category1 + 1;
            }
            else if ($labelArray[1] == $row['category']) {
                $category2 = $category2 + 1;
            }
            else if ($labelArray[2] == $row['category']) {
                $category3 = $category3 + 1;
            }
            else if ($labelArray[3] == $row['category']) {
                $category4 = $category4 + 1;
            }
            else if ($labelArray[4] == $row['category']) {
                $category5 = $category5 + 1;
            }
            else if ($labelArray[5] == $row['category']) {
                $category6 = $category6 + 1;
            }
            else if ($labelArray[6] == $row['category']) {
                $category7 = $category7 + 1;
            }

            /* making codes generalised
            for ($i=0; $i<=$numRowsCount; $i++){
            if ($labelArray[$i]  == $row['category']){
               $dataArray[$i]    = $dataArray[$i] + 1;
               print $labelArray[$i] ." / ". $dataArray[$i] . "<hr>";
            }
         } */

        }
        if ($category1 != "") {
            $dataArray[] = $category1;
        }
        if ($category2 != "") {
            $dataArray[] = $category2;
        }
        if ($category3 != "") {
            $dataArray[] = $category3;
        }
        if ($category4 != "") {
            $dataArray[] = $category4;
        }
        if ($category5 != "") {
            $dataArray[] = $category5;
        }
        if ($category6 != "") {
            $dataArray[] = $category6;
        }
        if ($category7 != "") {
            $dataArray[] = $category7;
        }

        # Colors of the sectors if custom coloring is used
        $colors = array(0xff6666, 0x999999, 0xff0000, 0xcccccc, 0x666666, 0x594330, 0xa0bdc4) ;

        //$dataArray = array($category1, $category2, $category3, $category4, $category5, $category6,$category7);
        # The data for the pie chart
        $data = $dataArray;
        # The labels for the pie chart
        $labels = $labelArray;
        # Create a PieChart object of size 360 x 300 pixels
        $c = new PieChart(600, 600);
        //$c->addTitle("Project Cost Breakdown");
        $textBoxObj = $c->addTitle("Project by Categories", "verdana.ttf", 12, 0xffffff);
        $textBoxObj->setBackground(0xEA2E2D);
        $c->setBackground(0xE3E4E8);
        # Set the center of the pie at (180, 140) and the radius to 100 pixels
        $c->setPieSize(300, 300, 200);
        # Draw the pie in 3D
        $c->set3D();
        # set the LineColor to light gray
        $c->setColor(LineColor, 0xc0c0c0);
        # use given color array as the data colors (sector colors)
        $c->setColors2(DataColor, $colors);
        # Set the pie data and the pie labels
        $c->setData($data, $labels);
        # Explode the 1st sector (index = 0)
        $c->setExplode(0);
        # output the chart
        header("Content-type: image/png");
        print($c->makeChart2(GIF));
    }

    //==================================================================//
    function getIconChart($result) {
        // FUEL GAUGE CHART
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        require_once("include/ChartDirector/lib/phpchartdir.php");

        $numRows = $db->sql_numrows($result);
        $row = $db->sql_fetchrow($result);

        $value = $row['percentage_used'];
        //$budget_inhouse  = $row['budget_inhouse'];

        if ($value == 0) {
            $value = 100;
        }
        else if ($value == 100) {
            $value = 0;
        }
        else if ($value > 100) {
            $value = 0;
        }
        else if ($value > 0) {
            $value = 100 - $value;
        }

        # The value to display on the meter is in $value

        # Create an AugularMeter object of size 70 x 90 pixels, using black background with a
        # 2 pixel 3D depressed border.
        $m = new AngularMeter(200, 200, 0, 0, - 2);

        #Set directory for loading images to current script directory
        #Need when running under Microsoft IIS
        $m->setSearchPath(dirname(__FILE__));

        # Use white on black color palette for default text and line colors
        $m->setColors($whiteOnBlackPalette);

        # Set the meter center at (10, 45), with radius 50 pixels, and span from 135 to 45
        # degress
        $m->setMeter(30, 100, 90, 135, 30);

        # Set meter scale from 0 - 100, with the specified labels
        $m->setScale2(0, 100, array("E", " ", " ", " ", "F"));

        # Set the angular arc and major tick width to 2 pixels
        $m->setLineWidth(2, 2);

        # Add a red zone at 0 - 15
        $m->addZone(0, 15, 0xff3333);

        # Add an icon at (25, 35)
        $m->addText(25, 35, "<*img=gas.gif*>");

        # Add a yellow (ffff00) pointer at the specified value
        $m->addPointer($value, 0xffff00);

        # output the chart header
        ("Content-type: image/png");
        print($m->makeChart2(PNG));
    }
