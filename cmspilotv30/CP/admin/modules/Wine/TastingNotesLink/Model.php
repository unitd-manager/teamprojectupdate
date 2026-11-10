<?
class CP_Admin_Modules_Wine_TastingNotesLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $lnPfx = $ln->gfp();
        
        $fa = array();

        $fa = $fn->addToFieldsArray($fa, "{$lnPfx}notes");
        $fa = $fn->addToFieldsArray($fa, 'published');

        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['product_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $published = $fn->getPostParam('published', 0);
        if($published == 1){// only one tasting note will be published
            $tasting_notes_id = $fn->getPostParam('tasting_notes_id');
            $tastingNotesRow = $fn->getRecordRowByID('tasting_notes', 'tasting_notes_id', $tasting_notes_id);
            $SQL = "
            UPDATE `tasting_notes` 
               SET  published = 0
            WHERE product_id = {$tastingNotesRow['product_id']}
            ";
            
            $result = $db->sql_query($SQL);
        }
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
        
    }
}