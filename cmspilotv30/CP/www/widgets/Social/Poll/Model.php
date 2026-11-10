<?
class CP_Www_Widgets_Social_Poll_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT p.*
        FROM poll p
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'p';
        $searchVar->sqlSearchVar[] = "p.published = 1";
        $searchVar->sqlSearchVar[] = "p.latest = 1";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'social_poll');

        $arr = array();
        foreach ($dataArray as $row){
            $arr['poll_id'] = $row['poll_id'];
            $arr['title']   = $ln->gfv($row, 'title');
            $arr['answers'] = array();

            $SQL = "
            SELECT *
            FROM poll_history
            WHERE poll_id = '{$row['poll_id']}'
            ORDER by sort_order
            ";
            $result = $db->sql_query($SQL);
            while ($row2 = $db->sql_fetchrow($result)){
                $tmpArr = array();
                $tmpArr['poll_history_id'] = $row2['poll_history_id'];
                $tmpArr['title'] = $ln->gfv($row2, 'title');
                $tmpArr['answer_count'] = $row2['answer_count'];
                $arr['answers'][] = $tmpArr;
            }
        }

        $this->dataArray = $arr;
        return $this->dataArray;
    }

    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $poll_ans = $fn->getPostParam('poll_ans', '', true);

        if ($poll_ans != ''){
            $rec = $fn->getRecordRowByID('poll_history', 'poll_history_id', $poll_ans);

            if (is_array($rec)){
                $fa['answer_count'] = $rec['answer_count'] + 1;
                $fn->saveRecord($fa, 'poll_history', 'poll_history_id', $poll_ans);
                $_SESSION['cpPollAlreadyTaken'] = true;
                $exp = array(
                    'gaTrackPageUri' => '/poll-submit/'
                );
                return $validate->getSuccessMessageXML('','',$exp);
            }
        }
        return $validate->getSuccessMessageXML();
    }
}