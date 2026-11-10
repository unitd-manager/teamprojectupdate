<?
class CPL_Admin_Lib_Functions extends CP_Admin_Lib_Functions
{
    /**
     *
     */

    function getRecordRowByID($tableName, $key_field_name, $id, $exp = array()) {
        $db = Zend_Registry::get('db');

        $globalForAllSites = $this->getIssetParam($exp, 'globalForAllSites', false);
        $fetchType = $this->getIssetParam($exp, 'fetchType', MYSQL_BOTH);

        if ($id == ''){
            return;
        }

        $condn = $this->getIssetParam($exp, 'condn');
        if (is_array($condn)) {
            $condn = $this->addSiteIdToWhereCondn();
        }

        $SQL = "
        SELECT *
        FROM `{$tableName}`
        WHERE {$key_field_name} = {$id}
        {$condn}
        ";

        //print $SQL .'<hr>';

        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result, $fetchType);

        return $row;
    }


    /**
     *
     */
    function getRecordByCondition($tableName, $condn = '', $orderBy = '', $module = '') {
        $db = Zend_Registry::get('db');
        if ($condn != '') {
            $condn = " WHERE {$condn}";
        }

        $orderBy = ($orderBy != '') ? " ORDER BY {$orderBy}" : '';

        $SQL = "
        SELECT *
        FROM `{$tableName}`
        {$this->appendSiteIdToCondn($condn, $module)}
        {$orderBy}
        LIMIT 0, 1
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            return $row;
        }
    }

    /**
     *
     */
    function getSiteIdForWhereCondn($tblPrefix = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tblPrefix = ($tblPrefix != '') ? ".{$tblPrefix}" : '';

        if ($cpCfg['cp.hasMultiUniqueSites']){
            if (CP_SCOPE == 'admin'){
                $site_id = @$_SESSION['cp_site_id'];
            } else {
                $site_id = $cpCfg['cp.site_id'];
            }

            if ($site_id != ''){
                return "site_id = {$tblPrefix}{$site_id}";
            }
        }
    }

    /**
     *
     */
    function appendSiteIdToCondn($condn, $module= '') {
        $cpCfg = Zend_Registry::get('cpCfg');

        $siteCondn = '';

        if ($module != '' & in_array($module, $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
            $siteCondn = '';
        }

        $condn .= ($condn != '' && $siteCondn != '') ? " AND {$siteCondn}"  : ($siteCondn != '' ? " WHERE {$siteCondn}" : '');

        return $condn;
    }

    /**
     *
     */
    function addRecord($fieldsArray, $tableName = '', $excludeFldsArr = array()) {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $modulesArr = Zend_Registry::get('modulesArr');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($tableName == '') {
            if ($tv['module'] != ''){
                $tableName   = $modulesArr[$tv['module']]['tableName'];
            } else if ($tv['lnkRoom'] != ''){
                $tableName   = $modulesArr[$tv['lnkRoom']]['tableName'];
            } else {
                exit('Room Name missing in: $fn->addRecord');
            }
        }

        if ($cpCfg['cp.hasMultiUniqueSites'] && $tableName != 'site'){
            if($dbUtil->getColumnExists($tableName, 'site_id')){
                if($tv['module'] != 'hms_inventory'){
                    $fieldsArray['site_id'] = $this->getSessionParam('cp_site_id');
                }
            }
        }

        if ($cpCfg['cp.hasMultiYears']){
            if($dbUtil->getColumnExists($tableName, 'cp_year')){
                $fieldsArray['cp_year'] = $fn->getSessionParam('cp_year');
            }
        }

        $fieldsArray = $this->addCreationDetailsToFieldsArray($fieldsArray, $tableName);
        //-----------------------------------------------------------------------//
        $SQL    = $dbUtil->getInsertSQLStringFromArray($fieldsArray, $tableName, $excludeFldsArr);
        //fb::log($SQL);
        $result = $db->sql_query($SQL);
        $id     = $db->sql_nextid();

        return $id;
    }
    /**
     * 
     */
    function getConvertNumber($number) {

    $fn = Zend_Registry::get('fn');
    $site_id = $fn->getSessionParam('cp_site_id');

    // Currency settings based on site
    if ($site_id == 1) {
        // Kuwait
        $fractionName = 'Fills';
        $decimalPlaces = 3;
    } else if ($site_id == 2) {
        // Saudi Arabia
        $fractionName = 'Halalas';
        $decimalPlaces = 2;
    } else {
        // Default
        $fractionName = 'Fills';
        $decimalPlaces = 3;
    }

    /*
     * Round according to the country's currency
     *
     * Kuwait: 131.7894 -> 131.789
     * Saudi : 131.789  -> 131.79
     */
    $number = number_format(
        (float)$number,
        $decimalPlaces,
        '.',
        ''
    );

    list($integer, $fraction) = explode(".", (string)$number);

    $output = "";

    // Handle negative / positive numbers
    if ($integer[0] == "-") {

        $output = "negative ";
        $integer = ltrim($integer, "-");

    } else if ($integer[0] == "+") {

        $output = "positive ";
        $integer = ltrim($integer, "+");
    }

    // Convert integer part
    if ($integer[0] == "0") {

        $output .= "zero";

    } else {

        $integer = str_pad($integer, 36, "0", STR_PAD_LEFT);

        $group = rtrim(chunk_split($integer, 3, " "), " ");
        $groups = explode(" ", $group);

        $groups2 = array();

        foreach ($groups as $g) {

            $groups2[] = $this->getConvertThreeDigit(
                $g[0],
                $g[1],
                $g[2]
            );
        }

        for ($z = 0; $z < count($groups2); $z++) {

            if ($groups2[$z] != "") {

                $output .= $groups2[$z]
                    . $this->getConvertGroup(11 - $z)
                    . (
                        $z < 11
                        && !array_search('', array_slice($groups2, $z + 1, -1))
                        && $groups2[11] != ''
                        && $groups[11][0] == '0'
                            ? " and "
                            : " "
                    );
            }
        }

        $output = rtrim($output, ", ");
    }

    // Convert fraction
    if ((int)$fraction > 0) {

        if ($decimalPlaces == 3) {

            // Kuwait: 789 -> Seven Hundred Eighty Nine
            $output .= " " . $this->getConvertThreeDigit(
                $fraction[0],
                $fraction[1],
                $fraction[2]
            );

        } else {

            // Saudi: 79 -> Seventy Nine
            $output .= " " . $this->getConvertThreeDigit(
                "0",
                $fraction[0],
                $fraction[1]
            );
        }

        $output .= " " . $fractionName . " Only";

    } else {

        $output .= " Only";
    }

    return $output;
}
 /**
     * 
     */
    function getConvertNumberOLd1($number) {
        
        /* Check whether number has decimal value or not. If no, add decimal point and zeros */
        if (strpos($number,".") == false){
            $number = $number . '.00';
        }
        
        /*
        if (!is_float($number)) {
            $number = $number . '.00';
        }
        */

        list($integer, $fraction) = explode(".", (string) $number);

        $output = "";

        if ($integer[0] == "-") {
            $output = "negative ";
            $integer    = ltrim($integer, "-");
        } else if ($integer[0] == "+") {
            $output = "positive ";
            $integer    = ltrim($integer, "+");
        }

        if ($integer[0] == "0") {
            $output .= "zero";
        } else {
            $integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
            $group   = rtrim(chunk_split($integer, 3, " "), " ");
            $groups  = explode(" ", $group);

            $groups2 = array();
            foreach ($groups as $g) {
                $groups2[] = $this->getConvertThreeDigit($g[0], $g[1], $g[2]);
            }

            for ($z = 0; $z < count($groups2); $z++) {
                if ($groups2[$z] != "") {
                    $output .= $groups2[$z] . $this->getConvertGroup(11 - $z) . (
                            $z < 11
                            && !array_search('', array_slice($groups2, $z + 1, -1))
                            && $groups2[11] != ''
                            && $groups[11][0] == '0'
                                ? " and "
                                : " "
                        );
                }
            }

            $output = rtrim($output, ", ");
        }

        if ($fraction > 0) {
            /* If the decimal point has more than three numbers */
            if (strlen($fraction) > 2) {
                $fraction = substr($fraction, 0, 2);
            }

            $fraction1 = substr($fraction, 0, 1);
            $fraction2 = substr($fraction, 1, 2);
            $fraction3 = substr($fraction, 2, 3);
            $output .= "  ";
            if ($fraction1 > 0) {
                $output .= " " . $this->getConvertThreeDigit($fraction1, $fraction2,$fraction3);
            } else {
                $output .= " " . $this->getConvertDigit($fraction2);
            }
            /* Check whether decimal is 2 or 1 digit */
            /*
            if (strlen($fraction) == 2) {
                for ($i = 0; $i < strlen($fraction); $i++) {
                    $output .= " " . $this->getConvertDigit($fraction[$i]);
                }
            } else {
                $output .= " " . $this->getConvertTwoDigit($fraction, '');
            }
            */
        } else {
            $output .= "";
        }


        if ($fraction != "00") {
            // Appending "Fills Only" only if the fraction part is not '00'
            $output = $output . ' Fills Only';
        } else {
            $output = $output . ' Only';
        }

        return $output;
    }

    /**
     *
     */
    function getConvertNumberOld($number) {
        
        // Check whether number has decimal value or not. If no, add decimal point and zeros
        if (strpos($number,".") === false){
            $number = $number . '.00';
        }
    
        list($integer, $fraction) = explode(".", (string) $number);
    
        $output = "";
    
        if ($integer[0] == "-") {
            $output = "negative ";
            $integer = ltrim($integer, "-");
        } else if ($integer[0] == "+") {
            $output = "positive ";
            $integer = ltrim($integer, "+");
        }
    
        if ($integer[0] == "0") {
            $output .= "zero";
        } else {
            $integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
            $group   = rtrim(chunk_split($integer, 3, " "), " ");
            $groups  = explode(" ", $group);
    
            $groups2 = array();
            foreach ($groups as $g) {
                $groups2[] = $this->getConvertThreeDigit($g[0], $g[1], $g[2]);
            }
    
            for ($z = 0; $z < count($groups2); $z++) {
                if ($groups2[$z] != "") {
                    $output .= $groups2[$z] . $this->getConvertGroup(11 - $z) . (
                            $z < 11
                            && !array_search('', array_slice($groups2, $z + 1, -1))
                            && $groups2[11] != ''
                            && $groups[11][0] == '0'
                                ? " and "
                                : " "
                        );
                }
            }
    
            $output = rtrim($output, ", ");
        }
    
            // Convert fraction part to words
            $output .= " " . $this->getConvertThreeDigit($fraction[0], $fraction[1], $fraction[2]);
          
        
    
        if ($fraction != "00") {
            // Appending "Fills Only" only if the fraction part is not '00'
            $output .= " Fills Only";
        } else {
            $output .= "Only";
        }
    
        return $output;
    }
    

    /**
     *
     */
    function getConvertGroup($index) {
        switch ($index) {
            case 11:
                return " Decillion";
            case 10:
                return " Nonillion";
            case 9:
                return " Octillion";
            case 8:
                return " Septillion";
            case 7:
                return " Sextillion";
            case 6:
                return " Quintrillion";
            case 5:
                return " Quadrillion";
            case 4:
                return " Trillion";
            case 3:
                return " Billion";
            case 2:
                return " Million";
            case 1:
                return " Thousand";
            case 0:
                return "";
        }
    }

    /**
     *
     */
    function getConvertThreeDigit($digit1, $digit2, $digit3) {
        $buffer = "";

        if ($digit1 == "0" && $digit2 == "0" && $digit3 == "0") {
            return "";
        }

        if ($digit1 != "0") {
            $buffer .= $this->getConvertDigit($digit1) . " Hundred";
            if ($digit2 != "0" || $digit3 != "0") {
                $buffer .= " ";
            }
        }

        if ($digit2 != "0") {
            $buffer .= $this->getConvertTwoDigit($digit2, $digit3);
        } else if ($digit3 != "0") {
            $buffer .= $this->getConvertDigit($digit3);
        }

        return $buffer;
    }

    /**
     *
     */
    function getConvertTwoDigit($digit1, $digit2) {
        if ($digit2 == "0") {
            switch ($digit1) {
                case "1":
                    return "Ten";
                case "2":
                    return "Twenty";
                case "3":
                    return "Thirty";
                case "4":
                    return "Forty";
                case "5":
                    return "Fifty";
                case "6":
                    return "Sixty";
                case "7":
                    return "Seventy";
                case "8":
                    return "Eighty";
                case "9":
                    return "Ninety";
            }
        } else if ($digit1 == "1") {
            switch ($digit2) {
                case "1":
                    return "Eleven";
                case "2":
                    return "Twelve";
                case "3":
                    return "Thirteen";
                case "4":
                    return "Fourteen";
                case "5":
                    return "Fifteen";
                case "6":
                    return "Sixteen";
                case "7":
                    return "Seventeen";
                case "8":
                    return "Eighteen";
                case "9":
                    return "Nineteen";
            }
        } else {
            $temp = $this->getConvertDigit($digit2);
            switch ($digit1) {
                case "1":
                    return "Ten $temp";
                case "2":
                    return "Twenty $temp";
                case "3":
                    return "Thirty $temp";
                case "4":
                    return "Forty $temp";
                case "5":
                    return "Fifty $temp";
                case "6":
                    return "Sixty $temp";
                case "7":
                    return "Seventy $temp";
                case "8":
                    return "Eighty $temp";
                case "9":
                    return "Ninety $temp";
            }
        }
    }

    /**
     *
     */
    function getConvertDigit($digit) {
        switch ($digit) {
            case "0":
                return "";
            case "1":
                return "One";
            case "2":
                return "Two";
            case "3":
                return "Three";
            case "4":
                return "Four";
            case "5":
                return "Five";
            case "6":
                return "Six";
            case "7":
                return "Seven";
            case "8":
                return "Eight";
            case "9":
                return "Nine";
        }
    }

    /**
     *
     */
    function getAmountFractionFormattedForGst($invoice_amount, $gst_percentage) {
        $invoice_amount = (float) $invoice_amount;
        $gst_percentage = (float) $gst_percentage;
        $gst_amount = round(($invoice_amount * $gst_percentage) / 100, 2);

        return $invoice_amount + $gst_amount;
    }

    /**
     *
     */
    function getStockForProduct($product_id) {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $tv       = Zend_Registry::get('tv');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');

        $SQLOthersite = "
        SELECT
            (SELECT SUM(pop.qty) FROM po_product pop
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pop.purchase_order_id)
             WHERE pop.product_id = {$product_id}) as product_qty_purchased

           ,(SELECT SUM(damage_qty) FROM po_product pp
             LEFT JOIN purchase_order po ON (po.purchase_order_id=pp.purchase_order_id)
             WHERE pp.product_id = {$product_id}) as damage_qty

           ,(SELECT SUM(pm.quantity) FROM project_materials pm
            WHERE pm.product_id = {$product_id}
            AND pm.status = 'Used'
            ) as product_qty_sold

            ,(SELECT SUM(sth.qty) FROM stock_transfer_history sth
            WHERE sth.product_id = {$product_id}
            ) as product_qty_sold_from_stock

            ,(SELECT SUM(srh.qty_return) FROM sales_return_history srh
            LEFT JOIN (invoice_item ini) ON (ini.invoice_item_id = srh.invoice_item_id)
            LEFT JOIN (invoice inv) ON (inv.invoice_id = srh.invoice_id AND inv.status != 'Cancelled' )
            WHERE ini.record_id = {$product_id}
              AND srh.status = 'Approved'
            ) as sales_return_qty
        ";
        $resultothersite = $db->sql_query($SQLOthersite);
        $rowothersite = $db->sql_fetchrow($resultothersite);

        $stock = $rowothersite['product_qty_purchased'] - $rowothersite['product_qty_sold_from_stock'] - $rowothersite['product_qty_sold'] + $rowothersite['sales_return_qty'] - $rowothersite['damage_qty'];

        $Values = array('OverallStock'    => $stock
                        ,'PurchasedQty'   => $rowothersite['product_qty_purchased']
                        ,'SoldQty'        => $rowothersite['product_qty_sold'] + $rowothersite['product_qty_sold_from_stock']
                        ,'SalesReturnQty' => $rowothersite['sales_return_qty']
                        ,'DamagedQty'     => $rowothersite['damage_qty']);

        return $Values;
    }
}