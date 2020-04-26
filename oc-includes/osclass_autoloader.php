<?php
/**
 * Load osclass core class through map
 *
 * @param $class_name
 *
 * @throws Exception
 */
function osc__auto($class_name)
{
    /** @var string[] Where $osc_autoload_map['namespace\Class']='folder\file.php' */
    $osc_autoload_map = [
        'Admin'                => LIB_PATH . '/osclass/classes/model/Admin.php',
        'AdminBaseModel'       => LIB_PATH . '/osclass/classes/controller/base/AdminBaseModel.php',
        'AdminForm'            => LIB_PATH . '/osclass/classes/form/AdminForm.php',
        'AdminMenu'            => LIB_PATH . '/osclass/classes/AdminMenu.php',
        'AdminSecBaseModel'    => LIB_PATH . '/osclass/classes/controller/base/AdminSecBaseModel.php',
        'AdminThemes'          => LIB_PATH . '/osclass/classes/themes/AdminThemes.php',
        'AdminToolbar'         => LIB_PATH . '/osclass/classes/AdminToolbar.php',
        'AjaxUploadedFileForm' => LIB_PATH . '/AjaxUploader.php',
        'AjaxUploadedFileXhr'  => LIB_PATH . '/AjaxUploader.php',
        'AjaxUploader'         => LIB_PATH . '/AjaxUploader.php',
        'Akismet'              => LIB_PATH . '/Akismet.class.php',
        'AlertForm'            => LIB_PATH . '/osclass/classes/form/AlertForm.php',
        'Alerts'               => LIB_PATH . '/osclass/classes/model/Alerts.php',
        'AlertsDataTable'      => LIB_PATH . '/osclass/classes/datatables/AlertsDataTable.php',
        'AlertsStats'          => LIB_PATH . '/osclass/classes/model/AlertsStats.php',
        'BanRule'              => LIB_PATH . '/osclass/classes/model/BanRule.php',
        'BanRuleForm'          => LIB_PATH . '/osclass/classes/form/BanRuleForm.php',
        'BanRulesDataTable'    => LIB_PATH . '/osclass/classes/datatables/BanRulesDataTable.php',
        'BaseModel'            => LIB_PATH . '/osclass/classes/controller/base/abstract/BaseModel.php',
        'Breadcrumb'           => LIB_PATH . '/osclass/classes/Breadcrumb.php',
        'CWebAjax'             => LIB_PATH . '/osclass/classes/controller/CWebAjax.php',
        'CWebContact'          => LIB_PATH . '/osclass/classes/controller/CWebContact.php',
        'CWebCustom'           => LIB_PATH . '/osclass/classes/controller/CWebCustom.php',
        'CWebItem'             => LIB_PATH . '/osclass/classes/controller/CWebItem.php',
        'CWebLanguage'         => LIB_PATH . '/osclass/classes/controller/CWebLanguage.php',
        'CWebLogin'            => LIB_PATH . '/osclass/classes/controller/CWebLogin.php',
        'CWebMain'             => LIB_PATH . '/osclass/classes/controller/CWebMain.php',
        'CWebPage'             => LIB_PATH . '/osclass/classes/controller/CWebPage.php',
        'CWebRegister'         => LIB_PATH . '/osclass/classes/controller/CWebRegister.php',
        'CWebSearch'           => LIB_PATH . '/osclass/classes/controller/CWebSearch.php',
        'CWebUser'             => LIB_PATH . '/osclass/classes/controller/CWebUser.php',
        'CWebUserNonSecure'    => LIB_PATH . '/osclass/classes/controller/CWebUserNonSecure.php',
        'Category'             => LIB_PATH . '/osclass/classes/model/Category.php',
        'CategoryForm'         => LIB_PATH . '/osclass/classes/form/CategoryForm.php',
        'CategoryStats'        => LIB_PATH . '/osclass/classes/model/CategoryStats.php',
        'City'                 => LIB_PATH . '/osclass/classes/model/City.php',
        'CityArea'             => LIB_PATH . '/osclass/classes/model/CityArea.php',
        'CityStats'            => LIB_PATH . '/osclass/classes/model/CityStats.php',
        'CommentForm'          => LIB_PATH . '/osclass/classes/form/CommentForm.php',
        'CommentsDataTable'    => LIB_PATH . '/osclass/classes/datatables/CommentsDataTable.php',
        'ContactForm'          => LIB_PATH . '/osclass/classes/form/ContactForm.php',
        'Cookie'               => LIB_PATH . '/osclass/classes/Cookie.php',
        'Country'              => LIB_PATH . '/osclass/classes/model/Country.php',
        'CountryStats'         => LIB_PATH . '/osclass/classes/model/CountryStats.php',
        'Cron'                 => LIB_PATH . '/osclass/classes/model/Cron.php',
        'Currency'             => LIB_PATH . '/osclass/classes/model/Currency.php',
        'DAO'                  => LIB_PATH . '/osclass/classes/database/DAO.php',
        'DBCommandClass'       => LIB_PATH . '/osclass/classes/database/DBCommandClass.php',
        'DBConnectionClass'    => LIB_PATH . '/osclass/classes/database/DBConnectionClass.php',
        'DBRecordsetClass'     => LIB_PATH . '/osclass/classes/database/DBRecordsetClass.php',
        'DataTable'            => LIB_PATH . '/osclass/classes/datatables/abstract/DataTable.php',
        'Dependencies'         => LIB_PATH . '/osclass/classes/Dependencies.php',
        'Dump'                 => LIB_PATH . '/osclass/classes/model/Dump.php',
        'EmailVariables'       => LIB_PATH . '/osclass/classes/EmailVariables.php',
        'Field'                => LIB_PATH . '/osclass/classes/model/Field.php',
        'FieldForm'            => LIB_PATH . '/osclass/classes/form/FieldForm.php',
        'Form'                 => LIB_PATH . '/osclass/classes/form/Form.php',
        'ImageProcessing'      => LIB_PATH . '/osclass/classes/ImageProcessing.php',
        'Item'                 => LIB_PATH . '/osclass/classes/model/Item.php',
        'ItemActions'          => LIB_PATH . '/osclass/classes/actions/ItemActions.php',
        'ItemComment'          => LIB_PATH . '/osclass/classes/model/ItemComment.php',
        'ItemForm'             => LIB_PATH . '/osclass/classes/form/ItemForm.php',
        'ItemLocation'         => LIB_PATH . '/osclass/classes/model/ItemLocation.php',
        'ItemResource'         => LIB_PATH . '/osclass/classes/model/ItemResource.php',
        'ItemStats'            => LIB_PATH . '/osclass/classes/model/ItemStats.php',
        'ItemsDataTable'       => LIB_PATH . '/osclass/classes/datatables/ItemsDataTable.php',
        'LanguageForm'         => LIB_PATH . '/osclass/classes/form/LanguageForm.php',
        'LatestSearches'       => LIB_PATH . '/osclass/classes/model/LatestSearches.php',
        'LocationsTmp'         => LIB_PATH . '/osclass/classes/model/LocationsTmp.php',
        'Log'                  => LIB_PATH . '/osclass/classes/model/Log.php',
        'LogDatabase'          => LIB_PATH . '/osclass/classes/logger/LogDatabase.php',
        'LogOsclass'           => LIB_PATH . '/osclass/classes/logger/LogOsclass.php',
        'LogOsclassInstaller'  => LIB_PATH . '/osclass/classes/logger/LogOsclassInstaller.php',
        'Logger'               => LIB_PATH . '/osclass/classes/logger/abstract/Logger.php',
        'ManageItemsForm'      => LIB_PATH . '/osclass/classes/form/ManageItemsForm.php',
        'MediaDataTable'       => LIB_PATH . '/osclass/classes/datatables/MediaDataTable.php',
        'OSCLocale'            => LIB_PATH . '/osclass/classes/model/OSCLocale.php',
        'Object_Cache_Factory' => LIB_PATH . '/osclass/classes/cache/Object_Cache_Factory.php',
        'Object_Cache_apc'     => LIB_PATH . '/osclass/classes/cache/drivers/Object_Cache_apc.php',
        'Object_Cache_apcu'    => LIB_PATH . '/osclass/classes/cache/drivers/Object_Cache_apcu.php',
        'Object_Cache_default' => LIB_PATH . '/osclass/classes/cache/drivers/Object_Cache_default.php',
        'Object_Cache_memcache'=> LIB_PATH . '/osclass/classes/cache/drivers/Object_Cache_memcache.php',
        'Page'                 => LIB_PATH . '/osclass/classes/model/Page.php',
        'PageForm'             => LIB_PATH . '/osclass/classes/form/PageForm.php',
        'PagesDataTable'       => LIB_PATH . '/osclass/classes/datatables/PagesDataTable.php',
        'Pagination'           => LIB_PATH . '/osclass/classes/Pagination.php',
        'Params'               => LIB_PATH . '/osclass/classes/Params.php',
        'PluginCategory'       => LIB_PATH . '/osclass/classes/model/PluginCategory.php',
        'Plugins'              => LIB_PATH . '/osclass/classes/Plugins.php',
        'Preference'           => LIB_PATH . '/osclass/classes/model/Preference.php',
        'RSSFeed'              => LIB_PATH . '/osclass/classes/RSSFeed.php',
        'Region'               => LIB_PATH . '/osclass/classes/model/Region.php',
        'RegionStats'          => LIB_PATH . '/osclass/classes/model/RegionStats.php',
        'Rewrite'              => LIB_PATH . '/osclass/classes/Rewrite.php',
        'Scripts'              => LIB_PATH . '/osclass/classes/Scripts.php',
        'Search'               => LIB_PATH . '/osclass/classes/model/Search.php',
        'SecBaseModel'         => LIB_PATH . '/osclass/classes/controller/base/SecBaseModel.php',
        'SendFriendForm'       => LIB_PATH . '/osclass/classes/form/SendFriendForm.php',
        'Session'              => LIB_PATH . '/osclass/classes/Session.php',
        'SiteInfo'             => LIB_PATH . '/osclass/classes/model/SiteInfo.php',
        'Sitemap'              => LIB_PATH . '/osclass/classes/Sitemap.php',
        'SocketWriteRead'      => LIB_PATH . '/Akismet.class.php',
        'Stats'                => LIB_PATH . '/osclass/classes/Stats.php',
        'Styles'               => LIB_PATH . '/osclass/classes/Styles.php',
        'Themes'               => LIB_PATH . '/osclass/classes/themes/abstract/Themes.php',
        'Translation'          => LIB_PATH . '/osclass/classes/Translation.php',
        'User'                 => LIB_PATH . '/osclass/classes/model/User.php',
        'UserActions'          => LIB_PATH . '/osclass/classes/actions/UserActions.php',
        'UserEmailTmp'         => LIB_PATH . '/osclass/classes/model/UserEmailTmp.php',
        'UserForm'             => LIB_PATH . '/osclass/classes/form/UserForm.php',
        'UsersDataTable'       => LIB_PATH . '/osclass/classes/datatables/UsersDataTable.php',
        'View'                 => LIB_PATH . '/osclass/classes/View.php',
        'WebSecBaseModel'      => LIB_PATH . '/osclass/classes/controller/base/WebSecBaseModel.php',
        'WebThemes'            => LIB_PATH . '/osclass/classes/themes/WebThemes.php',
        'Widget'               => LIB_PATH . '/osclass/classes/model/Widget.php',
        'iObject_Cache'         => LIB_PATH . '/osclass/classes/cache/interface/iObject_Cache.php'
    ];
    // special cases
    osc__loadIfExists($osc_autoload_map[$class_name]);
}


/**
 * We load the file.
 *
 * @param string $filename
 *
 * @throws Exception
 */
function osc__loadIfExists($filename)
{
    if ($filename !== null) {
          if ((@include $filename) !== false) {
          return true ;
        }
    }
}


spl_autoload_register(static function ($class_name) {
    osc__auto($class_name);
});
