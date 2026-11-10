<?
class CP_Common_Widgets_Media_Flickr_View extends CP_Common_Lib_WidgetViewAbstract
{
     /**
      * Update Flickr Cache to load faster in Front end 
      */
     function photosets_getPhotosUpdateCache(){
         $this->model->photosets_getPhotosUpdateCache();
     }
   
}
