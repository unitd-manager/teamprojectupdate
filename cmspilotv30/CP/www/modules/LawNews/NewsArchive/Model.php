<?
class CP_Www_Modules_LawNews_NewsArchive_Model extends CP_Common_Lib_ModuleModelAbstract
{

    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $keyword  = $fn->getReqParam('keyword');

        $relevance = '';
        if($keyword != ''){
            //$relevance = ", MATCH (c.title, c.description, c.description_short) AGAINST ('{$keyword}') AS relevance";
        }
        $SQL = "
        SELECT c.*
              ,s.title AS section_title
              ,s.section_type
              ,ca.title AS category_title
              ,ca.category_type
              ,sc.title AS sub_category_title
              ,sc.sub_category_type
              {$relevance}
        FROM content c
        LEFT JOIN (section s)      ON (c.section_id       = s.section_id)
        LEFT JOIN (category ca)    ON (c.category_id      = ca.category_id)
        LEFT JOIN (sub_category sc)ON (c.sub_category_id  = sc.sub_category_id)
        ";
        return $SQL;
    }

    /**
     * Extending the webBasic_content searchvar
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        getCPModuleObj('webBasic_content')->model->setSearchVar();
        $removeArr = array(
             'category_id'
            ,'sub_category_id'
            ,'category_id_2'
            ,'sub_category_id_2'
            ,'keyword'
        );
        $fn->removeSearchVars($removeArr);
        $searchVar = Zend_Registry::get('searchVar');

        $keyword  = $fn->getReqParam('keyword', '',true);
        $order_by = $fn->getReqParam('order_by', 'newest', true);
        $period   = $fn->getReqParam('period', 'all', true);
        $range_from_month = (int)$fn->getReqParam('range_from_month', '', true);
        $range_from_year  = (int)$fn->getReqParam('range_from_year', '', true);
        $range_to_month   = (int)$fn->getReqParam('range_to_month', date('m'), true);
        $range_to_year    = (int)$fn->getReqParam('range_to_year', date('Y'), true);
        $jurisdiction_idArr  = $fn->getReqParam('jurisdiction_id', array());

        if ($keyword != ''){
            $searchVar->sqlSearchVar['keyword'] = "(
                c.title        LIKE '%{$tv['keyword']}%' OR
                c.description  LIKE '%{$tv['keyword']}%' OR
                c.description_short  LIKE '%{$tv['keyword']}%'
            )";

            $searchVar->sqlSearchVar[] = "ca.category_type != 'External Links'";
            $searchVar->sqlSearchVar[] = "ca.category_type != 'Country Update External'";

            if(count($jurisdiction_idArr) > 0){
                $jurisdiction_ids = implode(",", $jurisdiction_idArr);
                $searchVar->sqlSearchVar[] = "
                c.content_id IN (
                    SELECT DISTINCT jc.content_id
                    FROM jurisdiction_content jc
                    WHERE jc.jurisdiction_id IN ({$jurisdiction_ids})
                )
                ";
            }

            if($period == '3Months'){
                $searchVar->sqlSearchVar[] = "
                c.content_date BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 3 MONTH ) AND DATE_SUB( CURDATE( ) ,INTERVAL 0 MONTH )
                ";
            } else if($period == '6Months'){
                $searchVar->sqlSearchVar[] = "
                c.content_date BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 6 MONTH ) AND DATE_SUB( CURDATE( ) ,INTERVAL 0 MONTH )
                ";
            } else if($period == '1Year'){
                $searchVar->sqlSearchVar[] = "
                c.content_date BETWEEN DATE_SUB( CURDATE( ) ,INTERVAL 12 MONTH ) AND DATE_SUB( CURDATE( ) ,INTERVAL 0 MONTH )
                ";
            } else if($period == 'dateRange'){
                $searchVar->sqlSearchVar[] = "
                c.content_date BETWEEN  '{$range_from_year}-{$range_from_month}-01 00:00:00' AND '{$range_to_year}-{$range_to_month}-31 23:59:59'
                ";
            }


            if($order_by == 'relevance'){
                $searchVar->sortOrder = "";
            } else if ($order_by == 'oldest'){
                $searchVar->sortOrder = "c.content_date ASC";
            } else {
                $searchVar->sortOrder = "c.content_date DESC";
            }
        }

    }

    /**
     *
     * @param type $content_id
     */
    function getJurisdictionsLinkedArray($content_id){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT jurisdiction_id
        FROM jurisdiction_content
        WHERE content_id = {$content_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $dataArray = $dbUtil->getResultsetAsArray($result);

        return $dataArray;
    }

    /**
     *
     * @param int $content_id
     * @return array
     */
    function getReportersArray($content_id){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT r.*
              ,c.title AS correspondent_title
        FROM reporter r
        LEFT JOIN correspondent c ON r.correspondent_id = c.correspondent_id
        LEFT JOIN reporter_content rc ON rc.reporter_id = r.reporter_id
        WHERE r.published = 1
          AND rc.content_id = {$content_id}
        ";

        $result  = $db->sql_query($SQL);
        $dataArray = $dbUtil->getResultsetAsArray($result);

        return $dataArray;

    }

    function updateReadCount($content_id){
        $db = Zend_Registry::get('db');

        $SQL = "
        UPDATE content
        SET click_count = click_count + 1
        WHERE content_id={$content_id}
        ";

        $result = $db->sql_query($SQL);

    }
}