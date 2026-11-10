<?
class CP_Www_Widgets_Common_LatestComments_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $SQL = "
        SELECT c.*
              ,s.title as section_title
              ,ct.title as category_title
        FROM content c
        LEFT JOIN category ct ON (ct.category_id = c.category_id)
        LEFT JOIN section s ON (s.section_id = c.section_id)
        WHERE c.published = 1
          AND c.latest = 1
        ORDER BY content_date DESC
        ";


        return $SQL;
    }

    //========================================================//
    function getDataArray() {
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $SQL = $this->getSQL();
        $result = $db->sql_query($SQL);
        
        $dataArray = array();
        $counter = 0;
        
        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            $arrTemp = &$dataArray[$counter];
            $arrTemp['title']       = $ln->gfv($row, 'title');
            $arrTemp['url']         = $cpUrl->getUrlByRecord($row, 'content_id');
            $arrTemp['desc_short']  = $ln->gfv($row, 'description_short');
            $arrTemp['content_date']= $row['content_date'];
            $counter++;
        }

        return $dataArray;
    }
}