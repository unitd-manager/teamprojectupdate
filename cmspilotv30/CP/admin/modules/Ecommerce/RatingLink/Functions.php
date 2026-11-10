<?
class CP_Admin_Modules_Ecommerce_RatingLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ecommerce_ratingLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'rating'
           ,'keyField'  => 'rating_id'
        ));
    }
}
