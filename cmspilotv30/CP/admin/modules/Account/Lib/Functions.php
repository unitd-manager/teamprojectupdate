<?
class CP_Admin_Modules_Account_Lib_Functions
{
    function setActionsArray($actArray){
        $fn = Zend_Registry::get('fn');

        //counter module buy
        $module = 'account_counterMaster';
        $topRm = $fn->getTopRoomName($module);
        $url = "index.php?_topRm={$topRm}&module={$module}&_action=new&subAction=buy";
        $actObj = $actArray->getActionObj('counterBuy');
        $actArray->registerAction($actObj, array(
            'title' => 'Buy'
           ,'url' => $url
        ));

        //counter module sell
        $actObj = $actArray->getActionObj('counterSell');
        $actArray->registerAction($actObj, array(
            'title' => 'Sell'
        ));
        
        //cash module receipt
        $module = 'account_cashMaster';
        $topRm = $fn->getTopRoomName($module);
        $url = "index.php?_topRm={$topRm}&module={$module}&_action=new&subAction=receipt";
        $actObj = $actArray->getActionObj('cashReceipt');
        $actArray->registerAction($actObj, array(
            'title' => 'Receipt'
           ,'url' => $url
        ));

        //cash module pay
        $actObj = $actArray->getActionObj('cashPayment');
        $actArray->registerAction($actObj, array(
            'title' => 'Payment'
        ));
    }

    /**
     *
     */
    function getAccCatDropdown($selectedId = '') {
        $db = Zend_Registry::get('db');

        $rows = "";

        $SQL = "
        SELECT acc_category_id
              ,title
        FROM acc_category
        WHERE parent_id = 0
        ORDER BY code
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $selected = ($selectedId == $row['acc_category_id']) ? " selected='selected'" : '';
            $rows .= "
            <option class='level1' value='{$row['acc_category_id']}'{$row['acc_category_id']}{$selected}>{$row['title']}</option>
            {$this->getAccCatChildDropdown($row['acc_category_id'], $selectedId)}
            ";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getAccCatChildDropdown($parent_id, $selectedId, $level=2) {
        $db = Zend_Registry::get('db');

        $rows = "";

        $SQL = "
        SELECT acc_category_id
              ,title
        FROM acc_category
        WHERE parent_id = {$parent_id}
        ORDER BY code
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $selected = ($selectedId == $row['acc_category_id']) ? " selected='selected'" : '';
            $rows .= "
            <option class='level{$level}' value='{$row['acc_category_id']}'{$selected}>{$row['title']}</option>
            {$this->getAccCatChildDropdown($row['acc_category_id'], $selectedId, $level+1)}
            ";
        }

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getFormatCreditDebit($number) {
        $fn = Zend_Registry::get('fn');
        $formattedNum = $fn->getFormatNumber($number);
        if ($number > 0) {
            $text = "<span class='credit-color'>{$formattedNum}</span>";
        } else if ($number < 0) {
            $text = "<span class='debit-color'>{$formattedNum}</span>";
        } else {
            $text = $formattedNum;
        }

        return $text;
    }

    function getActualAction($c_action) {
        $arr = array(
            'receipt' => 'buy',
            'payment' => 'sell',
            'buy' => 'buy',
            'sell' => 'sell',
        );

        return $arr[$c_action];
    }
}
