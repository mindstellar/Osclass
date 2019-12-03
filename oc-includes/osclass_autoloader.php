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
        'Admin'                 => '/osclass/classes/model/Admin.php',
        'AdminBaseModel'        => '/osclass/classes/controller/base/AdminBaseModel.php',
        'AdminForm'             => '/osclass/classes/form/AdminForm.php',
        'AdminMenu'             => '/osclass/classes/AdminMenu.php',
        'AdminSecBaseModel'     => '/osclass/classes/controller/base/AdminSecBaseModel.php',
        'AdminThemes'           => '/osclass/classes/themes/AdminThemes.php',
        'AdminToolbar'          => '/osclass/classes/AdminToolbar.php',
        'AjaxUploadedFileForm'  => '/AjaxUploader.php',
        'AjaxUploadedFileXhr'   => '/AjaxUploader.php',
        'AjaxUploader'          => '/AjaxUploader.php',
        'Akismet'               => '/Akismet.class.php',
        'AlertForm'             => '/osclass/classes/form/AlertForm.php',
        'Alerts'                => '/osclass/classes/model/Alerts.php',
        'AlertsDataTable'       => '/osclass/classes/datatables/AlertsDataTable.php',
        'AlertsStats'           => '/osclass/classes/model/AlertsStats.php',
        'BanRule'               => '/osclass/classes/model/BanRule.php',
        'BanRuleForm'           => '/osclass/classes/form/BanRuleForm.php',
        'BanRulesDataTable'     => '/osclass/classes/datatables/BanRulesDataTable.php',
        'BaseModel'             => '/osclass/classes/controller/base/abstract/BaseModel.php',
        'Breadcrumb'            => '/osclass/classes/Breadcrumb.php',
        'CWebAjax'              => '/osclass/classes/controller/CWebAjax.php',
        'CWebContact'           => '/osclass/classes/controller/CWebContact.php',
        'CWebCustom'            => '/osclass/classes/controller/CWebCustom.php',
        'CWebItem'              => '/osclass/classes/controller/CWebItem.php',
        'CWebLanguage'          => '/osclass/classes/controller/CWebLanguage.php',
        'CWebLogin'             => '/osclass/classes/controller/CWebLogin.php',
        'CWebMain'              => '/osclass/classes/controller/CWebMain.php',
        'CWebPage'              => '/osclass/classes/controller/CWebPage.php',
        'CWebRegister'          => '/osclass/classes/controller/CWebRegister.php',
        'CWebSearch'            => '/osclass/classes/controller/CWebSearch.php',
        'CWebUser'              => '/osclass/classes/controller/CWebUser.php',
        'CWebUserNonSecure'     => '/osclass/classes/controller/CWebUserNonSecure.php',
        'Category'              => '/osclass/classes/model/Category.php',
        'CategoryForm'          => '/osclass/classes/form/CategoryForm.php',
        'CategoryStats'         => '/osclass/classes/model/CategoryStats.php',
        'City'                  => '/osclass/classes/model/City.php',
        'CityArea'              => '/osclass/classes/model/CityArea.php',
        'CityStats'             => '/osclass/classes/model/CityStats.php',
        'CommentForm'           => '/osclass/classes/form/CommentForm.php',
        'CommentsDataTable'     => '/osclass/classes/datatables/CommentsDataTable.php',
        'ContactForm'           => '/osclass/classes/form/ContactForm.php',
        'Cookie'                => '/osclass/classes/Cookie.php',
        'Country'               => '/osclass/classes/model/Country.php',
        'CountryStats'          => '/osclass/classes/model/CountryStats.php',
        'Cron'                  => '/osclass/classes/model/Cron.php',
        'Currency'              => '/osclass/classes/model/Currency.php',
        'DAO'                   => '/osclass/classes/database/DAO.php',
        'DBCommandClass'        => '/osclass/classes/database/DBCommandClass.php',
        'DBConnectionClass'     => '/osclass/classes/database/DBConnectionClass.php',
        'DBRecordsetClass'      => '/osclass/classes/database/DBRecordsetClass.php',
        'DataTable'             => '/osclass/classes/datatables/abstract/DataTable.php',
        'Dependencies'          => '/osclass/classes/Dependencies.php',
        'Dump'                  => '/osclass/classes/model/Dump.php',
        'EmailVariables'        => '/osclass/classes/EmailVariables.php',
        'Field'                 => '/osclass/classes/model/Field.php',
        'FieldForm'             => '/osclass/classes/form/FieldForm.php',
        'Form'                  => '/osclass/classes/form/Form.php',
        'ImageProcessing'       => '/osclass/classes/ImageProcessing.php',
        'Item'                  => '/osclass/classes/model/Item.php',
        'ItemActions'           => '/osclass/classes/actions/ItemActions.php',
        'ItemComment'           => '/osclass/classes/model/ItemComment.php',
        'ItemForm'              => '/osclass/classes/form/ItemForm.php',
        'ItemLocation'          => '/osclass/classes/model/ItemLocation.php',
        'ItemResource'          => '/osclass/classes/model/ItemResource.php',
        'ItemStats'             => '/osclass/classes/model/ItemStats.php',
        'ItemsDataTable'        => '/osclass/classes/datatables/ItemsDataTable.php',
        'LanguageForm'          => '/osclass/classes/form/LanguageForm.php',
        'LatestSearches'        => '/osclass/classes/model/LatestSearches.php',
        'LocationsTmp'          => '/osclass/classes/model/LocationsTmp.php',
        'Log'                   => '/osclass/classes/model/Log.php',
        'LogDatabase'           => '/osclass/classes/logger/LogDatabase.php',
        'LogOsclass'            => '/osclass/classes/logger/LogOsclass.php',
        'LogOsclassInstaller'   => '/osclass/classes/logger/LogOsclassInstaller.php',
        'Logger'                => '/osclass/classes/logger/abstract/Logger.php',
        'ManageItemsForm'       => '/osclass/classes/form/ManageItemsForm.php',
        'MediaDataTable'        => '/osclass/classes/datatables/MediaDataTable.php',
        'OSCLocale'             => '/osclass/classes/model/OSCLocale.php',
        'Object_Cache_Factory'  => '/osclass/classes/cache/Object_Cache_Factory.php',
        'Object_Cache_apc'      => '/osclass/classes/cache/drivers/Object_Cache_apc.php',
        'Object_Cache_apcu'     => '/osclass/classes/cache/drivers/Object_Cache_apcu.php',
        'Object_Cache_default'  => '/osclass/classes/cache/drivers/Object_Cache_default.php',
        'Object_Cache_memcache' => '/osclass/classes/cache/drivers/Object_Cache_memcache.php',
        'Page'                  => '/osclass/classes/model/Page.php',
        'PageForm'              => '/osclass/classes/form/PageForm.php',
        'PagesDataTable'        => '/osclass/classes/datatables/PagesDataTable.php',
        'Pagination'            => '/osclass/classes/Pagination.php',
        'Params'                => '/osclass/classes/Params.php',
        'PluginCategory'        => '/osclass/classes/model/PluginCategory.php',
        'Plugins'               => '/osclass/classes/Plugins.php',
        'Preference'            => '/osclass/classes/model/Preference.php',
        'RSSFeed'               => '/osclass/classes/RSSFeed.php',
        'Region'                => '/osclass/classes/model/Region.php',
        'RegionStats'           => '/osclass/classes/model/RegionStats.php',
        'Rewrite'               => '/osclass/classes/Rewrite.php',
        'Scripts'               => '/osclass/classes/Scripts.php',
        'Search'                => '/osclass/classes/model/Search.php',
        'SecBaseModel'          => '/osclass/classes/controller/base/SecBaseModel.php',
        'SendFriendForm'        => '/osclass/classes/form/SendFriendForm.php',
        'Session'               => '/osclass/classes/Session.php',
        'SiteInfo'              => '/osclass/classes/model/SiteInfo.php',
        'Sitemap'               => '/osclass/classes/Sitemap.php',
        'SocketWriteRead'       => '/Akismet.class.php',
        'Stats'                 => '/osclass/classes/Stats.php',
        'Styles'                => '/osclass/classes/Styles.php',
        'Themes'                => '/osclass/classes/themes/abstract/Themes.php',
        'Translation'           => '/osclass/classes/Translation.php',
        'User'                  => '/osclass/classes/model/User.php',
        'UserActions'           => '/osclass/classes/actions/UserActions.php',
        'UserEmailTmp'          => '/osclass/classes/model/UserEmailTmp.php',
        'UserForm'              => '/osclass/classes/form/UserForm.php',
        'UsersDataTable'        => '/osclass/classes/datatables/UsersDataTable.php',
        'View'                  => '/osclass/classes/View.php',
        'WebSecBaseModel'       => '/osclass/classes/controller/base/WebSecBaseModel.php',
        'WebThemes'             => '/osclass/classes/themes/WebThemes.php',
        'Widget'                => '/osclass/classes/model/Widget.php',
        'iObject_Cache'          => '/osclass/classes/cache/interface/iObject_Cache.php'
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
    $fullFile = __DIR__ . '/' . $filename; // its relative to this path

    if ((@include $fullFile) === false) {
        throw  new RuntimeException("AutoLoad Error: No file found. $fullFile");
    }
}


spl_autoload_register(static function ($class_name) {
    osc__auto($class_name);
});
