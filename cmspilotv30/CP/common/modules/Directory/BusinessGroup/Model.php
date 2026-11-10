<?
class CP_Common_Modules_Directory_BusinessGroup_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        
        $country_id = $fn->getSessionParam('cp_country_id');
        
        $tagsSQL = '';
        $countrySQL = '';
        
        if ($country_id != '') {
            $countrySQL = "
            AND b.country_id = '{$country_id}'
            ";
        }

        if ($tv['action'] != 'list'){
            $tagsSQL = "
            ,(
            SELECT GROUP_CONCAT(t.tag_text ORDER BY t.tag_text SEPARATOR ', ')
            FROM tags t, tags_history th
            WHERE t.tags_id = th.tags_id
              AND th.record_id   = bg.business_group_id
              AND th.record_type = 'Business Group'
            ) AS tags
            
            ,(
            SELECT GROUP_CONCAT(t.chi_tag_text ORDER BY t.chi_tag_text SEPARATOR ', ')
            FROM tags t, tags_history th
            WHERE t.tags_id = th.tags_id
              AND th.record_id   = bg.business_group_id
              AND th.record_type = 'Business Group'
            ) AS chi_tags
            
            ,(
            SELECT GROUP_CONCAT(t.pin_tag_text ORDER BY t.pin_tag_text SEPARATOR ', ')
            FROM tags t, tags_history th
            WHERE t.tags_id = th.tags_id
              AND th.record_id   = bg.business_group_id
              AND th.record_type = 'Business Group'
            ) AS pin_tags
            
            ,(
            SELECT GROUP_CONCAT(t.ch2_tag_text ORDER BY t.ch2_tag_text SEPARATOR ', ')
            FROM tags t, tags_history th
            WHERE t.tags_id = th.tags_id
              AND th.record_id   = bg.business_group_id
              AND th.record_type = 'Business Group'
            ) AS ch2_tags
            ";
        }
        
        $SQL = "
        SELECT bg.*
              ,c.title AS category_title
              ,sc.title AS sub_category_title        
              ,co.title AS country_title

              ,CASE
               WHEN bg.description = '' OR bg.description IS NULL THEN ''
               ELSE CONCAT_WS('', SUBSTRING_INDEX(bg.description, ' ', 15), '...')
               END AS description_list
               
              ,(SELECT 'Yes'
                FROM media m
                WHERE bg.business_group_id = m.record_id
                  AND m.room_name = 'directory_businessGroup'
                  AND m.record_type = 'logo'
                LIMIT 1
                ) AS has_logo
                
              ,(
              SELECT GROUP_CONCAT(sc.title ORDER BY sc.title SEPARATOR ', ')
              FROM social_media sc
                  ,bg_social_media bgsc
              WHERE sc.social_media_id = bgsc.social_media_id
                AND bgsc.business_group_id = bg.business_group_id
              ) AS social_medias
        
              {$tagsSQL}
                  
              ,(SELECT COUNT(*) 
                FROM business b
                WHERE b.business_group_id = bg.business_group_id
                   {$countrySQL}
                ) AS business_count
        FROM business_group bg
        LEFT JOIN category c ON bg.category_id = c.category_id
        LEFT JOIN sub_category sc ON bg.sub_category_id = sc.sub_category_id        
        LEFT JOIN country co ON co.country_id = bg.country_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'bg';

        
        $country_id = $fn->getSessionParam('cp_country_id');
        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "bg.business_group_id = {$tv['record_id']}";
        }
        
        if ($country_id != '' ) {
            $searchVar->sqlSearchVar[] = "bg.country_id = '{$country_id}'";
        }		
        
        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(bg.title   LIKE '%{$tv['keyword']}%'  
                                          )";
        }

		$searchVar->sortOrder = "bg.title";
    }
}
