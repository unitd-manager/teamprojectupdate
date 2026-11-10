<?
class CPL_Admin_Widgets_EnggCrm_DashboardTopPanel_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $currentMonth = date('M');
        $last_month   = date('M', strtotime('last month'));

        $SQLBest = "
        SELECT DATE_FORMAT(o.creation_date, '%b') AS month
              ,COUNT(o.opportunity_id) as total
        FROM `opportunity` o
        WHERE DATE_FORMAT(o.creation_date, '%Y-%m-%d') > Date_add(Now(), interval - 12 month)
          AND DATE_FORMAT(o.creation_date, '%Y-%m-%d') < Now()
        GROUP BY DATE_FORMAT(o.creation_date, '%m-%Y')
        ORDER BY total DESC, DATE_FORMAT(o.creation_date, '%m-%Y') DESC
        LIMIT 1
        ";
        $resultBest = $db->sql_query($SQLBest);
        $rowBest    = $db->sql_fetchrow($resultBest);

        $SQLBestInvoice = "
        SELECT DATE_FORMAT(i.creation_date, '%b') AS month
              ,COUNT(i.invoice_id) as total
        FROM `invoice` i
        WHERE DATE_FORMAT(i.creation_date, '%Y-%m-%d') > Date_add(Now(), interval - 12 month)
          AND DATE_FORMAT(i.creation_date, '%Y-%m-%d') < Date_add(Now(), interval - 1 month)
        GROUP BY DATE_FORMAT(i.creation_date, '%m-%Y')
        ORDER BY total DESC, DATE_FORMAT(i.creation_date, '%m-%Y') DESC
        LIMIT 1
        ";
        $resultBestInvoice = $db->sql_query($SQLBestInvoice);
        $rowBestInvoice    = $db->sql_fetchrow($resultBestInvoice);

        $text = "
        <div class='content'>
            <div class='col-md-4'>
                <div class='card card-stats'>
                    <div class='card-header card-header-warning card-header-icon'>
                        <div class='card-icon'>
                            <i class='glyphicon glyphicon-user'></i>
                        </div>
                        <div class='card-title txtUline'>Tender Summary <span>(Total / Awarded)</span></div>             
                        <div class='floatbox'>
                            <h3 class='card-title'>Current Month($currentMonth)</h3>
                            <span class='txtUlineTop'>{$this->getTenderSummary('Current Month')}</span>
                        </div>
                    </div>
                    <div class='card-footer'>
                        <div class='floatbox'>
                            <div class='float_left'>
                                <h3 class='card-title'>Last Month($last_month)</h3>
                                <div class='mt45'>
                                    {$this->getTenderSummary('Last Month')}
                                </div>
                            </div>
                            <div class='float_right'>
                                <h3 class='card-title'>Best Month({$rowBest['month']})</h3>
                                <div class='mt45 txtRight'>
                                    {$this->getTenderSummary('Best Month')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card card-stats'>
                    <div class='card-header card-header-danger card-header-icon'>
                      <div class='card-icon'>
                        <i class='glyphicon glyphicon-list-alt'></i>
                      </div>
                      <div class='card-title txtUline'>Invoice Summary</div>
                      <div class='floatbox'>
                            <h3 class='card-title'>Current Month($currentMonth)</h3>
                            <span class='txtUlineTop'>{$this->getInvoiceSummary('Current Month')}</span>
                        </div>
                    </div>
                    <div class='card-footer'>
                        <div class='floatbox'>
                            <div class='float_left'>
                                <h3 class='card-title'>Last Month($last_month)</h3>
                                <div class='mt45'>
                                    {$this->getInvoiceSummary('Last Month')}
                                </div>
                            </div>
                            <div class='float_right'>
                                <h3 class='card-title'>Best Month({$rowBestInvoice['month']})</h3>
                                <div class='mt45 txtRight'> 
                                    {$this->getInvoiceSummary('Best Month')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getTenderSummary($month) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');

        $rows = 0;
        if($month == "Best Month") {
            $SQLBest = "
            SELECT DATE_FORMAT(o.creation_date, '%Y-%m') AS monthYear
                  ,COUNT(o.opportunity_id) as total
            FROM `opportunity` o
            WHERE DATE_FORMAT(o.creation_date, '%Y-%m-%d') > Date_add(Now(), interval - 12 month)
              AND DATE_FORMAT(o.creation_date, '%Y-%m-%d') < Now()
            GROUP BY DATE_FORMAT(o.creation_date, '%m-%Y')
            ORDER BY total DESC, DATE_FORMAT(o.creation_date, '%m-%Y') DESC
            LIMIT 1
            ";
            $resultBest = $db->sql_query($SQLBest);
            $totalQuoteAmount = 0;
            $AwardedCount     = 0;
            while($rowBest = $db->sql_fetchrow($resultBest)) {
                $SQLAmount = "
                SELECT SUM(q.total_amount) AS total_amount
                FROM opportunity o
                LEFT JOIN (quote q) ON (q.opportunity_id = o.opportunity_id)
                WHERE DATE_FORMAT(o.creation_date, '%Y-%m') = '{$rowBest['monthYear']}'
                ";
                $resultAmount = $db->sql_query($SQLAmount);
                $rowAmount    = $db->sql_fetchrow($resultAmount);

                $SQLAwarded = "
                SELECT COUNT(o.opportunity_id) AS Awarded
                FROM opportunity o
                LEFT JOIN (quote q) ON (q.opportunity_id = o.opportunity_id)
                WHERE DATE_FORMAT(o.creation_date, '%Y-%m') = '{$rowBest['monthYear']}'
                  AND (o.status = 'Awarded' 
                  OR o.status = 'Converted to Project')
                ";
                $resultAwarded = $db->sql_query($SQLAwarded);
                $rowAwarded    = $db->sql_fetchrow($resultAwarded);

                if($rowAmount['total_amount'] == "") {
                    $rowAmount['total_amount'] = 0;
                }

                $totalQuoteAmount = $rowAmount['total_amount'];
                
                $totalAmount = 0;
                if($totalQuoteAmount > 0) {
                    $totalAmount = " | $".number_format($totalQuoteAmount);
                }

                $rows = '('.$rowBest['total'].'/'.$rowAwarded['Awarded'].$totalAmount.')';
            }

        } else {
            if ($month == 'Current Month'){
                $start_date = date('Y-m-01');
                $end_date   = date('Y-m-t');
                $date = "(DATE_FORMAT(o.creation_date, '%Y-%m-%d') >= '{$start_date}' AND DATE_FORMAT(o.creation_date, '%Y-%m-%d') <= '{$end_date}')";
            } else if ($month == 'Last Month'){
                $start_date = date('Y-m-d', strtotime('first day of last month'));
                $end_date   = date('Y-m-d', strtotime('last day of last month'));
                $date = "(DATE_FORMAT(o.creation_date, '%Y-%m-%d') >= '{$start_date}' AND DATE_FORMAT(o.creation_date, '%Y-%m-%d') <= '{$end_date}')";
            }

             $SQLTender1 = "
            SELECT COUNT(o.opportunity_id) AS total
            FROM `opportunity` o
            WHERE {$date}
            ";
            $resultTender1 = $db->sql_query($SQLTender1);
            $rowTender1    = $db->sql_fetchrow($resultTender1);

            $SQLTender2 = "
            SELECT o.*
            FROM `opportunity` o
            WHERE {$date}
            AND (o.status = 'Awarded' 
              OR o.status = 'Converted to Project')
            ";
            $resultTender2 = $db->sql_query($SQLTender2);
            $totalQuoteAmount = 0;
            $AwardedCount     = 0;
            while ($rowTender2 = $db->sql_fetchrow($resultTender2)) {
                $quoteRec = $fn->getRecordByCondition('quote', "opportunity_id = {$rowTender2['opportunity_id']}");
                if($quoteRec['drawing_nos'] == 1) {
                    $totalQuoteAmount += $quoteRec['total_amount'] - $quoteRec['discount'];
                } else {
                  $sqlQuoteItems ="
                  SELECT *
                  FROM quote_items qi
                  WHERE qi.quote_id = {$quoteRec['quote_id']}
                  ";

                  $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
                  $subtotalValue = 0;
                  while ($rowQuoteItems = $db->sql_fetchrow($resultForQuoteItems)) {
                      $subtotal_amount = 0; 
                      if($rowQuoteItems['unit_price'] > 0 && $rowQuoteItems['quantity'] > 0) {
                          $subtotal_amount = round($rowQuoteItems['quantity'] * $rowQuoteItems['unit_price'], 2);
                      } elseif ($rowQuoteItems['unit_price'] > 0 && $rowQuoteItems['quantity'] == 0) {
                          $subtotal_amount = round($rowQuoteItems['unit_price'], 2);
                      } elseif ($rowQuoteItems['amount'] > 0) {
                          $subtotal_amount = round($rowQuoteItems['amount'], 2);
                      }

                      $subtotalValue += $subtotal_amount;
                      
                      if($quoteRec['gst'] == 1) {
                        $gsttaxvalue    = $cpCfg['cp.gstPercentage'] ;
                        $gstvalue       = $subtotalValue * $gsttaxvalue / 100;
                        /* Taking two decimal values for gst amount */
                        $fraction_length = strlen(substr(strrchr($gstvalue, "."), 1)); // Checking the lingth of the fraction value
                        if ($fraction_length > 2) {
                            list($integer, $fraction) = explode(".", (string) $gstvalue);

                            /* Checking whether 3rd decimal point is more than or equal to 5
                               If Yes, add 1 to 2nd decimal point
                             */
                            $gstDecimalMore = substr($fraction, 2, 1);
                            $fraction = substr($fraction, 0, 2);
                            if ($gstDecimalMore >= 5) {
                                $fraction = $fraction + 1;
                            }

                            $gstvalue = $integer . "." . $fraction;
                        }

                        $totalQuoteAmount += $gstvalue + $subtotalValue;
                      } else {
                        $totalQuoteAmount += $subtotalValue;
                      }
                  }
                }
                $AwardedCount++;
            }

            $totalAmount = "";
            if($totalQuoteAmount > 0) {
                $totalAmount = " | $".number_format($totalQuoteAmount);
            }

            $rows = '('.$rowTender1['total'].'/'.$AwardedCount.$totalAmount.')';
        }

        return $rows;
    }

    /**
     *
     */
    function getInvoiceSummary($month) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');

        $rows = 0;
        if($month == "Best Month") {
            $SQLBestInvoice = "
            SELECT DATE_FORMAT(i.creation_date, '%Y-%m') AS monthYear
                  ,COUNT(i.invoice_id) AS total
                  ,SUM(i.invoice_amount) AS totalAmount 
            FROM `invoice` i
            WHERE DATE_FORMAT(i.creation_date, '%Y-%m-%d') > Date_add(Now(), interval - 12 month)
              AND DATE_FORMAT(i.creation_date, '%Y-%m-%d') < Date_add(Now(), interval - 1 month)
            GROUP BY DATE_FORMAT(i.creation_date, '%m-%Y')
            ORDER BY total DESC, DATE_FORMAT(i.creation_date, '%m-%Y') DESC
            LIMIT 1
            ";
            $resultBestInvoice = $db->sql_query($SQLBestInvoice);
            $rowBestInvoice    = $db->sql_fetchrow($resultBestInvoice);
            $totalAmount = 0;
            if($rowBestInvoice['totalAmount'] != "" && $rowBestInvoice['totalAmount'] > 0) {
                $totalAmount = $rowBestInvoice['totalAmount'];
            }

            $rows = '($'.number_format($totalAmount).')';
        } else {
            if ($month == 'Current Month'){
                $start_date = date('Y-m-01');
                $end_date   = date('Y-m-t');
                $date = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
            } else if ($month == 'Last Month'){
                $start_date = date('Y-m-d', strtotime('first day of last month'));
                $end_date   = date('Y-m-d', strtotime('last day of last month'));
                $date = "i.invoice_date >= '{$start_date}' AND i.invoice_date <= '{$end_date}'";
            }

            $SQLInvoice1 = "
            SELECT SUM(i.invoice_amount) AS Amount
            FROM `invoice` i
            WHERE {$date}
              AND status != 'Cancelled'
            ";
            $resultInvoice1 = $db->sql_query($SQLInvoice1);
            $rowInvoice1    = $db->sql_fetchrow($resultInvoice1);

            $totalAmount = 0;
            if($rowInvoice1['Amount'] != "" && $rowInvoice1['Amount'] > 0) {
                $totalAmount = $rowInvoice1['Amount'];
            }

            $rows = '($'.number_format($totalAmount).')';
        }

        return $rows;
    }
}