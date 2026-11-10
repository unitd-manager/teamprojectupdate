<?
class CP_Admin_Modules_Project_Quote_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $rows = '';

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['quote_code'])}
            {$listObj->getListDataCell($row['opportunity_title'])}
            {$listObj->getListDataCell($row['project_title'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDateCell($$row['quote_date'])}
            {$listObj->getListDataCell($row['quote_id'], 'center')}
            {$listObj->getListRowEnd($row['quote_id'])}
            ";

            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Quote Code', 'a.quote_code')}
        {$listObj->getListHeaderCell('Opportunity', 'b.title')}
        {$listObj->getListHeaderCell('Project', 'c.title')}
        {$listObj->getListHeaderCell('Status', 'a.status')}
        {$listObj->getListHeaderCell('Quote Date', 'a.quote_date')}
        {$listObj->getListHeaderCell('ID', 'a.quote_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $formObj = Zend_Registry::get('formObj');

        $text = "
        {$formObj->getHeaderRow('Quote Details')}
        {$formObj->getTBRow('Opportunity', 'opportunity_id', $row['opportunity_title'])}
        {$formObj->getTBRow('Project', 'project_id', $row['project_title'])}
        {$formObj->getTBRow('Quote Code', 'quote_code', $row['quote_code'])}
        {$formObj->getDateRow('Quote Date', 'quote_date', $row['quote_date'])}
        {$formObj->getTBRow('Status', 'status', $row['status'])}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuotesPortal($recId ='', $recType='') {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        if ($recId == '') {
            $recId   = $fn->getGetParam('recId');
            $recType = $fn->getGetParam('recType');
        }
        
        if ($recType == 'proj') {
            $projRec = $fn->getRecordRowByID('project', 'project_id', $recId);
            $mainCurDisplay = isset($projRec['currency']) && $projRec['currency'] != '' ? $projRec['currency'] : $cpCfg['m.project.baseCurrency'];
        } else {
            $oppRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $recId);
            $mainCurDisplay = isset($oppRec['currency']) && $oppRec['currency'] != '' ? $oppRec['currency'] : $cpCfg['m.project.baseCurrency'];
        }

        if (trim($recId) == '') {
            return;
        }

        $quote_id            = '';
        $quote_category_id   = '';

        $quoteCategory = includeCPClass('Module', 'project_quoteCategory', 'QuoteCategory');
        $quoteItem     = includeCPClass('Module', 'project_quoteItem'    , 'QuoteItem');

        $quoteDiscCatSQL = "(
        SELECT SUM(qi.amount)
        FROM quote_items qi
        WHERE qi.quote_category_id = c.quote_category_id
          AND (qi.item_type = 'discount IH' OR qi.item_type = 'discount 3P')
        )
        ";

        $quoteDiscSQL = "(
        SELECT SUM(qi.amount)
        FROM quote_items qi
        WHERE a.quote_id = qi.quote_id
          AND (qi.item_type = 'discount IH' OR qi.item_type = 'discount 3P')
        )
        ";

        if ($recType == 'quoteTemplate') {
            $appendSQL = "
            a.quote_id = {$recId}
            ";
        } else if ($recType == 'proj') {
            $appendSQL = "
            a.project_id = {$recId}
            ";
        } else {
            $appendSQL = "
            a.opportunity_id = {$recId}
            ";
        }

        $SQL = "
        SELECT a.*
              ,DATE_FORMAT(a.quote_date, '%d %b %Y') AS quote_date
              ,b.quote_category_id
              ,b.valuelist_id
              ,b.category_type
              ,c.quote_items_id
              ,c.title AS item_title
              ,c.description AS item_description
              ,FORMAT(c.amount, 0) AS amount
              ,FORMAT(c.amount_other, 0) AS amount_other
              ,FORMAT(
                      (
                       SELECT SUM(qi.amount)
                       FROM quote_items qi
                       WHERE qi.quote_category_id = c.quote_category_id
                      )
                      ,0
              ) AS amount_sum
              ,FORMAT(
                      (
                       SELECT SUM(qi.amount_other)
                       FROM quote_items qi
                       WHERE qi.quote_category_id = c.quote_category_id
                      )
                      ,0
              ) AS amount_other_sum
              ,c.item_type
              ,d.value AS quote_cat_title
              ,FORMAT(
                      (
                       SELECT SUM(qi.amount)
                       FROM quote_items qi
                       WHERE a.quote_id = qi.quote_id
                      )
                      ,0
                     ) AS total
              ,FORMAT(
                      (
                       SELECT SUM(qi.amount_other)
                       FROM quote_items qi
                       WHERE a.quote_id = qi.quote_id
                      )
                      ,0
                     ) AS total_other
        FROM quote a
        LEFT JOIN quote_category b ON (a.quote_id          = b.quote_id)
        LEFT JOIN quote_items c    ON (b.quote_category_id = c.quote_category_id)
        LEFT JOIN (valuelist d)    ON (b.valuelist_id      = d.valuelist_id)
        WHERE {$appendSQL}
        ORDER BY a.quote_id, b.sort_order, c.sort_order
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows         = '';
        $gQuoteID     = '';
        $gQuoteCatID  = '';

        $data = array();
        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {

            $quote_id = $row['quote_id'];

            if ($row['quote_id'] != $gQuoteID) {
                $gQuoteID        = $row['quote_id'];

                $data[$quote_id] = array( 'quote_id'    => $row['quote_id']
                                         ,'quote_code'  => $row['quote_code']
                                         ,'quote_date'  => $row['quote_date']
                                         ,'status'      => $row['status']
                                         ,'currency'    => $row['currency_item']
                                         ,'total'       => $row['total']
                                         ,'total_other' => $row['total_other']
                                         ,'quote_type'  => $row['quote_type']
                                        );

                $quoteArr = &$data[$quote_id];
            }

            if ($row['quote_category_id'] != $gQuoteCatID && $row['quote_category_id'] > 0) {
                $gQuoteCatID = $row['quote_category_id'];

                $quoteArr['categories'][$row['quote_category_id']] =
                        array( 'quote_cat_title'  => $row['quote_cat_title']
                              ,'category_type'    => $row['category_type']
                              ,'amount_sum'       => $row['amount_sum']
                              ,'amount_other_sum' => $row['amount_other_sum']
                             );

                $quoteCatArr = &$quoteArr['categories'][$row['quote_category_id']]['items'];
            }

            if ($row['quote_items_id'] > 0) {
                $quoteCatArr[$row['quote_items_id']] =
                        array( 'title'        => $row['item_title']
                              ,'amount'       => $row['amount']
                              ,'amount_other' => $row['amount_other']
                              ,'item_type'    => $row['item_type']
                              ,'description'  => $row['item_description']
                             );
            }
        }

        /*
        print "<pre>";
        print_r ($data);
        print "</pre>";
        */

        $quotes = '';

        /************ step1: loop through quotes records **********/
        foreach ($data as $quote_id => $quote) {
            if ($recType != 'quoteTemplate') {
                $addCatUrl        = "index.php?module=project_quoteCategory&_spAction=new&quote_id={$quote_id}&showHTML=0";
                $editQuoteUrl     = "index.php?module=project_quote&_spAction=edit&recType={$recType}&recId={$recId}&quote_id={$quote_id}&showHTML=0";
                $delQuoteUrl      = "index.php?module=project_quote&_spAction=delete&quote_id={$quote_id}&showHTML=0";
                $printQuoteUrl    = "index.php?module=project_quote&_spAction=reportsMenu&quote_id={$quote_id}&showHTML=0";
                $duplicateQuoteUrl= "index.php?module=project_quote&_spAction=duplicate&recType={$recType}&recId={$recId}&quote_id={$quote_id}&showHTML=0";

                $quotes .= "
                <tr class='quote' quote_id='{$quote_id}'>
                    <td class='quoteNo'><a href='#' class='arrowRight'>{$quote['quote_code']}</a></td>
                    <td class='date'>{$quote['quote_date']}</td>
                    <td class='status'>{$quote['status']}</td>
                    <td class='amount'>{$quote['total']}</td>
                    <td class='amount'>{$quote['total_other']}</td>
                    <td class='type'>&nbsp;</td>
                    <td class='right actBtns'>
                       <a href='{$addCatUrl}'     title='Add Quote Category' class='addItem addQuoteCat'><span class='hid'>-</span></a>
                       <a href='{$editQuoteUrl}'  title='Edit Quote' class='editItem editQuote'><span class='hid'>-</span></a>
                       <a href='{$delQuoteUrl}'   title='Delete Quote' class='removeItem deleteQuote'><span class='hid'>+</span></a>
                       <a href='{$duplicateQuoteUrl}' title='Duplicate Quote' class='duplicate duplicateQuote'><span class='hid'>-</span></a>
                       <a href='{$printQuoteUrl}' title='Print Quote' class='printIcon printQuote'><span class='hid'>-</span></a>
                    </td>
                </tr>
                ";
            }

            $catArr = isset($quote['categories']) ? $quote['categories'] : array();

            $categories = '';

            /************ step2: loop through category records **********/
            foreach ($catArr as $category_id => $category) {
                $itemArr = is_array($category['items']) ? $category['items'] : array();

                $items = '';

                /************ step3: loop through quote items **********/
                foreach ($itemArr as $item_id => $item) {

                    $editItemUrl    = "index.php?module=project_quoteItem&_spAction=edit&item_id={$item_id}&showHTML=0";
                    $delItemUrl     = "index.php?module=project_quoteItem&_spAction=delete&item_id={$item_id}&showHTML=0";
                    $desc = ($item['description'] != '') ? "<br /><small>{$item['description']}</small>" : '';

                    $items .= "
                    <tr>
                        <td class='title'>{$item['title']}{$desc}</td>
                        <td class='amount'>{$item['amount']}</td>
                        <td class='amount'>{$item['amount_other']}</td>
                        <td class='type'>{$item['item_type']}</td>
                        <td class='right actBtns'>
                            <a href='{$editItemUrl}' title='Edit Line Item' class='editItem editLineItem'><span class='hid'>-</span></a>
                            <a href='{$delItemUrl}'  title='Delete Line Item' class='removeItem deleteLineItem'><span class='hid'>+</span></a>
                        </td>
                    </tr>
                    ";
                }

                $addItemUrl = "index.php?module=project_quoteItem&_spAction=new&quote_id={$quote_id}&category_id={$category_id}&showHTML=0";
                $editCatUrl = "index.php?module=project_quoteCategory&_spAction=edit&category_id={$category_id}&showHTML=0";
                $delCatUrl  = "index.php?module=project_quoteCategory&_spAction=delete&category_id={$category_id}&showHTML=0";

                $categories .= "
                <table>
                    <thead>
                    <tr>
                        <th>{$category['quote_cat_title']}</th>
                        <th class='amount'>{$mainCurDisplay}</th>
                        <th class='amount'>Other$</th>
                        <th>Type</th>
                        <th class='right actBtns'>
                            <a href='{$addItemUrl}' title='Add Line Item' class='addItem addLineItem'><span class='hid'>-</span></a>
                            <a href='{$editCatUrl}' title='Edit Category' class='editItem editCategory'><span class='hid'>-</span></a>
                            <a href='{$delCatUrl}'  title='Delete Category' class='removeItem deleteCategory'><span class='hid'>+</span></a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        {$items}
                        <tr>
                            <th class='title'>&nbsp;</th>
                            <th class='amount'>{$category['amount_sum']}</th>
                            <th class='amount'>{$category['amount_other_sum']}</th>
                            <th class='type'>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                    </tbody>
                </table>
                ";
            }

            $quotes .= "
            <tr>
                <td colspan='10' class='p0'>
                <div class='quoteCategories' quote_id='{$quote_id}'>
                    {$categories}
                </div>
                </td>
            </tr>
            ";
        }

        $expandedVal = 1;

        if ($recType == 'quoteTemplate') {
            $addCatUrl = "index.php?module=project_quoteCategory&_spAction=new&quote_id={$recId}&showHTML=0";

            $text = "
            <div>
                <div expanded='{$expandedVal}' class='header'>
                    <div class='floatbox'>
                        <div style='float:left;'>Quote Items</div>
                        <div class='toggle'>&nbsp;</div>
                        <div style='float:right;' class='actBtns'>
                            <strong><a href='{$addCatUrl}' title='Add Quote Category' class='addQuoteCat'>Raise New Category</a></strong>
                        </div>
                    </div>
                </div>
                <div>
                    <table id='quotes'>
                    {$quotes}
                    </table>
                </div>
            </div>
    
            <script>
                $(function(){
                    $('.quoteCategories').show();
                });
            </script>
            ";
        } else {
            $raiseQuoteUrl   = "index.php?module=project_quote&_spAction=new&recType={$recType}&recId={$recId}&showHTML=0";
            $raiseFromTplUrl = "index.php?module=project_quote&_spAction=newFromTemplate&recType={$recType}&recId={$recId}&showHTML=0";
            $script = '';

            if ($tv['action'] == 'detail'){
                $script = " 
                <script>
                    $(function(){
                        $('#quotesOuter .header .actBtns \
                          ,#quotesOuter .actBtns .duplicateQuote \
                          ,#quotesOuter .actBtns .deleteQuote \
                          ,#quotesOuter .actBtns .editQuote \
                          ,#quotesOuter .actBtns .addQuoteCat \
                         ').hide();
                    });
                    $(function(){
                        $('#quotesOuter .quoteCategories .actBtns').html('');
                    });
                </script>
                ";
            }
            
            $text = "
            <div id='quotesPortal' class='linkPortalWrapper'>
                <div expanded='{$expandedVal}' class='header'>
                    <div class='floatbox'>
                        <div style='float:left;'>Quotes</div>
                        <div class='toggle'>&nbsp;</div>
                        <div style='float:right;' class='actBtns'>
                            <strong><a href='{$raiseQuoteUrl}' title='Raise New Quote' id='raiseNewQuote'>Raise New Quote</a></strong>
                        </div>
                        <div style='float:right;' class='actBtns'>
                            <strong>
                            <a href='{$raiseFromTplUrl}' title='Raise New Quote From Template' 
                                id='raiseNewQuoteFromTemplate'>Raise From Template
                            </a>
                            </strong> 
                            | &nbsp;
                        </div>
                    </div>
                </div>
                <div class='linkPortalDataWrapper'>
                    <table id='quotes'>
                        <thead>
                        <tr>
                            <th>Quote Code</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class='amount'>{$mainCurDisplay}</th>
                            <th class='amount'>Other$</th>
                            <th class='type'></th>
                            <th></th>
                        </tr>
                        </thead>
                    {$quotes}
                    </table>
                </div>
            </div>
            {$script}
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getNew() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $recId   = $fn->getGetParam('recId');
        $recType = $fn->getGetParam('recType');

        if (trim($recId) == '') {
            return;
        }

        $sqlType    = $fn->getValueListSQL('quoteType');
        $sqlCurr    = $fn->getValueListSQL('quoteCurrency');
        $sqlStat    = $fn->getValueListSQL('quoteStatus');

        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

        $formAction = "index.php?module=project_quote&_spAction=add&showHTML=0";

        $exp = array('sqlType' => 'OneField');

        $today = date('Y-m-d');

        $text = "
        <form id='quoteForm' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>
                {$formObj->getDateRow('Quote Date', 'quote_date', $today)}
                {$formObj->getDDRowBySQL('Quote Type', 'quote_type', $sqlType, $cpCfg['m.project.baseCurrency'], $exp)}
                {$formObj->getDDRowBySQL('Currency', 'currency_item', $sqlCurr, '', $exp)}
                {$formObj->getDDRowBySQL('Use Signature of', 'sign_staff_id', $sqlStaff)}
                {$formObj->getTARow('Notes', 'note')}
                {$formObj->getTARow('Conditions', 'condition')}
            </fieldset>
            <input type='hidden' name='recId' value='{$recId}'>
            <input type='hidden' name='recType' value='{$recType}'>
        </form>
        <script>
            $(function(){
                $('#fld_currency_item').parent().hide();
            });
        </script>
        ";

        return $text;
    }

    /**
     *
     */
    function getNewFromTemplate() {
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $recId   = $fn->getGetParam('recId');
        $recType = $fn->getGetParam('recType');

        if (trim($recId) == '') {
            return;
        }

        $SQL = "
        SELECT quote_id 
              ,template_title
        FROM quote 
        WHERE template = 1
        ORDER BY sort_order, template_title
        ";

        $formAction = "index.php?module=project_quote&_spAction=addFromTemplate&showHTML=0";

        $today = date('Y-m-d');

        $text = "
        <form id='quoteForm' class='yform columnar' method='post' action='{$formAction}'>
            <div id='errorDisplayBox'></div>
            <fieldset>
                {$formObj->getDDRowBySQL('Template', 'template_id', $SQL)}
            </fieldset>
            <input type='hidden' name='recId' value='{$recId}'>
            <input type='hidden' name='recType' value='{$recType}'>
            <input type='hidden' name='raiseFromTemplate' value='1'>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $recId    = $fn->getGetParam('recId');
        $recType  = $fn->getGetParam('recType');
        $quote_id = $fn->getGetParam('quote_id');

        if ($quote_id == '') {
            return;
        }

        $SQL     = "
        SELECT * 
        FROM quote 
        WHERE quote_id = {$quote_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows == 0) {
            return;
        }

        $row     = $db->sql_fetchrow($result);

        $text    = '';

        $sqlType    = $fn->getValueListSQL('quoteType');
        $sqlCurr    = $fn->getValueListSQL('quoteCurrency');
        $sqlStat    = $fn->getValueListSQL('quoteStatus');

        $sqlStaff = $fn->getDDSql('core_staff', array('condn' => "status = 'Current'"));

        $formAction = "index.php?module=project_quote&_spAction=save&showHTML=0";

        $exp = array('sqlType' => 'OneField');

        $hideCurrencyText = '';

        if($row['quote_type'] != 'other $') {
            $hideCurrencyText = "
            <script>
                $(function(){
                    $('#fld_currency_item').parent().hide();
                });
            </script>
            ";
        }

        $text  .= "
        <form id='quoteForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Quote Code', 'quote_code', $row['quote_code'], array('isEditable' => 0))}
                {$formObj->getDateRow('Quote Date', 'quote_date', $row['quote_date'])}
                {$formObj->getDDRowBySQL('Quote Type', 'quote_type', $sqlType, $row['quote_type'], $exp)}
                {$formObj->getDDRowBySQL('Currency', 'currency_item', $sqlCurr, $row['currency_item'], $exp)}
                {$formObj->getDDRowBySQL('Quote Status', 'status', $sqlStat, $row['status'], $exp)}
                {$formObj->getDDRowBySQL('Use Signature of', 'sign_staff_id', $sqlStaff, $row['sign_staff_id'])}
                {$formObj->getTARow('Note', 'note', $row['note'])}
                {$formObj->getTARow('Condition', 'condition', $row['condition'])}
                <input type='hidden' name='recId' value='{$recId}'>
                <input type='hidden' name='recType' value='{$recType}'>
                <input type='hidden' name='quote_id' value='{$quote_id}' />
            </fieldset>
        </form>
		{$hideCurrencyText}
        ";

        return $text;
    }

    /**
     *
     */
    function getReportsMenu() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        
        $quote_id = $fn->getReqParam('quote_id');
        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        
        $signArr  = $media->getMediaFilesArray('core_staff', 'signature', $quoteRec['sign_staff_id']);

        if (count($signArr) == 0){
        	exit('Signature missing, please attach the signature of the staff and proceed');
        }

        $printQuoteUrl = "index.php?_spAction=printReport&record_id={$quote_id}&showHTML=0&roomName={$tv['module']}&report_heading=Quotation&";
        $printQuoteUrl2= "index.php?_spAction=printReport&record_id={$quote_id}&showHTML=0&roomName={$tv['module']}&report_heading=Cost+Estimate&";

        if ($quoteRec['project_id'] != '') {
            $projRec = $fn->getRecordRowByID('project', 'project_id', $quoteRec['project_id']);
            $mainCurDisplay = isset($projRec['currency']) && $projRec['currency'] != '' ? $projRec['currency'] : $cpCfg['m.project.baseCurrency'];
        } else {
            $oppRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $quoteRec['opportunity_id']);
            $mainCurDisplay = isset($oppRec['currency']) && $oppRec['currency'] != '' ? $oppRec['currency'] : $cpCfg['m.project.baseCurrency'];
        }

        $text = "
        <ul class='printOptions'>
            <li><a href='{$printQuoteUrl}report=quote'>Quote ({$mainCurDisplay})</a></li>
            <li><a href='{$printQuoteUrl}report=quoteOther'>Quote (Other$)</a></li>
            <li><a href='{$printQuoteUrl}report=quoteNoCategory'>Quote No Category ({$mainCurDisplay})</a></li>
            <li><a href='{$printQuoteUrl}report=quoteNoItems'>Quote (No Line Items)</a></li>
            <li><a href='{$printQuoteUrl}report=quoteWOLogo'>Quote without Logo ({$mainCurDisplay})</a></li>
            <li><a href='{$printQuoteUrl}report=quoteOtherWOLogo'>Quote without Logo (Other$)</a></li>
            <li><a href='{$printQuoteUrl}report=quoteNoItemsWOLogo'>Quote without Logo (No Line Items)</a></li>
        </ul>
        <hr>
        <h3>Cost Estimates</h3>
        <ul class='printOptions'>
            <li><a href='{$printQuoteUrl2}report=quote'>Estimate ({$mainCurDisplay})</a></li>
            <li><a href='{$printQuoteUrl2}report=quoteOther'>Estimate (Other$)</a></li>
            <li><a href='{$printQuoteUrl2}report=quoteNoCategory'>Estimate No Category ({$mainCurDisplay})</a></li>
            <li><a href='{$printQuoteUrl2}report=quoteNoItems'>Estimate (No Line Items)</a></li>
            <li><a href='{$printQuoteUrl2}report=quoteWOLogo'>Estimate without Logo ({$mainCurDisplay})</a></li>
            <li><a href='{$printQuoteUrl2}report=quoteOtherWOLogo'>Estimate without Logo (Other$)</a></li>
            <li><a href='{$printQuoteUrl2}report=quoteNoItemsWOLogo'>Estimate without Logo (No Line Items)</a></li>
        </ul>
        ";

        return $text;
    }
}
