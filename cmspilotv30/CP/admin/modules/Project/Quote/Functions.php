<?
class CP_Admin_Modules_Project_Quote_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_quote');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function getTotalQuoteAmount($quote_id) {
        $db = Zend_Registry::get('db');

        $totalQuoteAmount = 0;

        if ($quote_id > 0) {
            $SQL = "SELECT (
            (
              SELECT SUM(qi.amount)
              FROM quote_items qi
              WHERE quote_id = {$quote_id}
              ) 
            ) AS total_amount
            ";
            $result  = $db->sql_query($SQL);
            $row     = $db->sql_fetchrow($result, MYSQL_ASSOC);
            $totalQuoteAmount = ($row['total_amount'] > 0) ? $row['total_amount'] : 0;
        }

        return $totalQuoteAmount;
    }

    /**
     *
     */
    function getTotalQuoteAmountByItemType($quote_id, $itemType, $amtFld = 'amount') {
        $db = Zend_Registry::get('db');

        $totalQuoteAmount = 0;

        if ($quote_id > 0) {
            $SQL = "
            SELECT (
                (
                    SELECT SUM(qi.{$amtFld})
                    FROM quote_items qi
                    WHERE quote_id  = {$quote_id}
                      AND item_type = '{$itemType}'
                )
            ) AS total_amount
            ";
            $result  = $db->sql_query($SQL);
            $row     = $db->sql_fetchrow($result, MYSQL_ASSOC);
            $totalQuoteAmount = ($row['total_amount'] > 0) ? $row['total_amount'] : 0;
        }
        return $totalQuoteAmount;
    }

    /**
     *
     */
    function refreshValuesBasedOnConfirmedQuote($quote_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $quoteRec         = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $opportunity_id   = $quoteRec['opportunity_id'];
        $project_id       = $quoteRec['project_id'];

        if ($opportunity_id == '' && $project_id == '' ) {
            return;
        }

        if ($quoteRec['status'] == 'Agreed') {
            $totalQuoteAmount    = $this->getTotalQuoteAmount($quote_id);
            $totalInhouse        = $this->getTotalQuoteAmountByItemType($quote_id, "in-house",  "amount");
            $total3rdParty       = $this->getTotalQuoteAmountByItemType($quote_id, "3rd party", "amount");
            $total3rdPartyActual = $this->getTotalQuoteAmountByItemType($quote_id, "3rd party", "actual_amount");

            if ($opportunity_id > 0) {
                $SQL = "
                UPDATE opportunity
                SET confirmed_quote_id = {$quote_id}
                WHERE opportunity_id = {$opportunity_id}
                ";
                $db->sql_query($SQL);
            }

            if ($project_id > 0) {
                $projRec = $fn->getRecordByCondition('project', "project_id = {$project_id}");
            } else {
                $projRec = $fn->getRecordByCondition('project', "opportunity_id = {$opportunity_id}");
            }

            if (isset($projRec['project_id']) && $projRec['project_id'] > 0) {
                $SQL = "
                UPDATE project
                SET confirmed_quote_id = {$quote_id}
                   ,project_value      = {$totalQuoteAmount}
                   ,budget_inhouse     = {$totalInhouse}
                   ,budget_third_party = {$total3rdParty}
                   ,used_third_party   = {$total3rdPartyActual}
                WHERE project_id = {$projRec['project_id']}
                ";
                $db->sql_query($SQL);
            }
        } else {
            $SQL = "
            SELECT count(*) AS count
            FROM quote
            WHERE (opportunity_id = '{$opportunity_id}' OR project_id = '{$project_id}')
              AND status = 'Agreed'
            ";
            $result = $db->sql_query($SQL);
            $row    = $db->sql_fetchrow($result);

            if ($row['count'] == 0) {

                if ($opportunity_id > 0) {
                    $SQL = "
                    UPDATE opportunity
                    SET confirmed_quote_id = ''
                    WHERE opportunity_id = {$opportunity_id}
                    ";
                    $db->sql_query($SQL);
                }

                if ($project_id > 0) {
                    $projRec = $fn->getRecordByCondition('project', "project_id = {$project_id}");
                } else {
                    $projRec = $fn->getRecordByCondition('project', "opportunity_id = {$opportunity_id}");
                }

                if (isset($projRec['project_id']) && $projRec['project_id'] > 0) {
                    $SQL = "
                    UPDATE project
                    SET confirmed_quote_id = ''
                       ,project_value      = 0
                       ,budget_inhouse     = 0
                       ,budget_third_party = 0
                    WHERE project_id = {$projRec['project_id']}
                    ";
                    $db->sql_query($SQL);
                }
            }
        }
    }

    /**
     *
     */
    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $report = $fn->getReqParam('report');
    	$rec_id = $fn->getReqParam('record_id');

        $repInst->setReportArrayObj('project_opportunity', "opportunityList");
        $arr = &$repInst->reportsArray['project_opportunity']['opportunityList'];
        $arr['jasperFileName'] = 'opportunity_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;

        /**********************************************************************/
        $repInst->setReportArrayObj('project_quote', "quote");
        $arr = &$repInst->reportsArray['project_quote']['quote'];
        $arr['jasperFileName'] = 'quote.jasper';
        $arr['includeSignature']  = true;
        $arr['depModules']        = array('project_contact', 'project_opportunity', 'project_project');
        $arr['extraParams']['print_page_in_footer'] = true;
        $arr['extraParams']['print_code_in_footer'] = true;

        $repInst->setReportArrayObj('project_quote', "quoteOther");
        $arr = &$repInst->reportsArray['project_quote']['quoteOther'];
        $arr['jasperFileName'] = 'quote_other.jasper';
        $arr['includeSignature']  = true;
        $arr['depModules']        = array('project_contact', 'project_opportunity', 'project_project');

        $repInst->setReportArrayObj('project_quote', "quoteNoCategory");
        $arr = &$repInst->reportsArray['project_quote']['quoteNoCategory'];
        $arr['jasperFileName'] = 'quoteNoCategory.jasper';
        $arr['includeSignature']  = true;
        $arr['depModules']        = array('project_contact', 'project_opportunity', 'project_project');

        $repInst->setReportArrayObj('project_quote', "quoteNoItems");
        $arr = &$repInst->reportsArray['project_quote']['quoteNoItems'];
        $arr['jasperFileName'] = 'quote_no_line_item.jasper';
        $arr['includeSignature']  = true;
        $arr['depModules']        = array('project_contact', 'project_opportunity', 'project_project');

        $repInst->setReportArrayObj('project_quote', "quoteWOLogo");
        $arr = &$repInst->reportsArray['project_quote']['quoteWOLogo'];
        $arr['jasperFileName'] = 'quote.jasper';
        $arr['printInLetterhead']  = true;
        $arr['includeSignature']  = true;
        $arr['depModules']        = array('project_contact', 'project_opportunity', 'project_project');

        $repInst->setReportArrayObj('project_quote', "quoteOtherWOLogo");
        $arr = &$repInst->reportsArray['project_quote']['quoteOtherWOLogo'];
        $arr['jasperFileName'] = 'quote_other.jasper';
        $arr['printInLetterhead']  = true;
        $arr['includeSignature']  = true;
        $arr['depModules']        = array('project_contact', 'project_opportunity', 'project_project');

        $repInst->setReportArrayObj('project_quote', "quoteNoItemsWOLogo");
        $arr = &$repInst->reportsArray['project_quote']['quoteNoItemsWOLogo'];
        $arr['jasperFileName'] = 'quote_no_line_item.jasper';
        $arr['printInLetterhead']  = true;
        $arr['includeSignature']  = true;
        $arr['depModules']        = array('project_contact', 'project_opportunity', 'project_project');
        
        if ($rec_id > 0){
            $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $rec_id);
            $arr = &$repInst->reportsArray['project_quote'][$report];
            $arr['extraParams']['sign_staff_id'] = $quoteRec['sign_staff_id'];
            
            if ($quoteRec['project_id'] != ''){
                $projRec = $fn->getRecordRowByID('project', 'project_id', $quoteRec['project_id']);
                $code = $projRec['project_code'];
            } else {
                $projRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $quoteRec['opportunity_id']);
                $code = $projRec['opportunity_code'];
            }
            
            $compRec = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);
            
            $arr['outputFileName'] = $compRec['company_name'] . '-' . $code;
        }
        
        $arr['extraParams']['COMPANY_NAME']    = $cpCfg['cp.companyName'];
    }
}
