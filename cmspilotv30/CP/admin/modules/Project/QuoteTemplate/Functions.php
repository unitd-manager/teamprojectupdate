<?
class CP_Admin_Modules_Project_QuoteTemplate_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_quoteTemplate');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'relatedTables' => array('quoteCategory', 'quoteItem')
           ,'title'         => 'Quote Templates'
           ,'tableName'     => 'quote'
           ,'keyField'      => 'quote_id'
           ,'depModulesForJSS' => array('project_quote')
        ));
    }
}