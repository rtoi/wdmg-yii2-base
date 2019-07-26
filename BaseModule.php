<?php

namespace wdmg\base;

/**
 * Yii2 Base module
 *
 * @category        Module
 * @version         1.0.5
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-base
 * @copyright       Copyright (c) 2019 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use yii\base\BootstrapInterface;
use Yii;
use yii\base\Module;

/**
 * Base module definition class
 */
class BaseModule extends Module implements BootstrapInterface
{

    /**
     * @var string the prefix for routing of module
     */
    public $routePrefix = "admin";

    /**
     * @var string, the name of module
     */
    public $name = "Base module";

    /**
     * @var string, the description of module
     */
    public $description = "Base module interface";

    /**
     * @var string the vendor name of module
     */
    private $vendor = "wdmg";

    /**
     * @var string the alias of module base path
     */
    private $alias;

    /**
     * @var array the eta data of current module
     */
    private $meta;

    /**
     * @var string the module version
     */
    private $version = "1.0.5";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 10;

    /**
     * @var array of strings missing translations
     */
    public $missingTranslation;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set controller namespace for console commands
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'wdmg\\' . $this->id . '\commands';
            $this->defaultRoute = 'init';
        }

        // Set alias of module base path
        $this->alias = '@'.$this->vendor.'/'.$this->id;
        $this->setAliases([
            $this->alias => $this->basePath
        ]);

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

        // Register translations
        $this->registerTranslations();

        // Normalize route prefix
        $this->routePrefixNormalize();

    }

    /**
     * Get module vendor
     * @return string of current module vendor
     */
    public function getVendor() {
        return $this->vendor;
    }

    /**
     * Get module version
     * @return string of current module version
     */
    public function getVersion() {
        $this->version = parent::getVersion();
        return $this->version;
    }

    /**
     * Set current module version
     * @param $version string
     */
    public function setVersion($version)
    {
        parent::setVersion($version);
        $this->version = $version;
    }

    /**
     * Get module priority
     * @return integer of current module priority
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * Set current module priority
     * @param $priority integer
     */
    public function setPriority($priority) {
        $this->priority = $priority;
    }

    /**
     * Get alias
     * @return string of current module alias
     */
    public function getBaseAlias() {
        return $this->alias;
    }

    /**
     * Set meta data
     * @return array of current module meta data
     */
    public function setMetaData() {
        $module = $this;
        $data['id'] = $module->id;
        $data['uniqueId'] = $module->getUniqueId();
        $data['name'] = str_replace(Yii::getAlias('@vendor').'/',"", $module->getBasePath());
        $data['label'] = $module->name;
        $data['version'] = $module->getVersion();
        //$data['_version'] = Yii::$app->extensions[$data['name']]['version'];
        $data['vendor'] = $module->vendor;
        $data['alias'] = $module->getBaseAlias();
        $data['paths']['basePath'] = $module->getBasePath();
        $data['paths']['controllerPath'] = $module->getControllerPath();
        $data['paths']['layoutPath'] = $module->getLayoutPath();
        $data['paths']['viewPath'] = $module->getViewPath();
        $data['components'] = $module->getComponents();
        $data['parent']['id'] = $module->module->id;
        $data['parent']['uniqueId'] = $module->module->getUniqueId();
        $data['parent']['version'] = $module->module->getVersion();
        $data['parent']['paths']['basePath'] = $module->module->getBasePath();
        $data['parent']['paths']['controllerPath'] = $module->module->getControllerPath();
        $data['parent']['paths']['layoutPath'] = $module->module->getLayoutPath();
        $data['parent']['paths']['viewPath'] = $module->module->getViewPath();
        $data['extensions'] = Yii::$app->extensions;
        $this->meta = $data;
    }

    /**
     * Get meta data
     * @return array of current module meta data
     */
    public function getMetaData() {
        return $this->meta;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {

        // Log to debuf console missing translations
        if (is_array($this->missingTranslation) && YII_ENV == 'dev')
            Yii::warning('Missing translations: ' . var_export($this->missingTranslation, true), 'i18n');

        $result = parent::afterAction($action, $result);
        return $result;

    }

    /**
     * Registers translations for module
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['app/modules/' . $this->id] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/' . $this->vendor . '/yii2-' . $this->id . '/messages',
            'on missingTranslation' => function($event) {

                if (YII_ENV == 'dev')
                    $this->missingTranslation[] = $event->message;

            },
        ];

        // Name and description translation of module
        $this->name = Yii::t('app/modules/' . $this->id, $this->name);
        $this->description = Yii::t('app/modules/' . $this->id, $this->description);
    }

    /**
     * Public translation function, Module::t('app/modules/admin', 'Dashboard');
     * @return string of current message translation
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('app/modules/' . self::id .'/'. $category, $message, $params, $language);
    }

    /**
     * Normalize route
     * @return string of current route
     */
    public function normalizeRoute($route)
    {
        $route = str_replace('/', '', $route);
        $route = '/'.$route;
        $route = str_replace('//', '/', $route);
        return $route;
    }

    /**
     * Normalize route prefix
     * @return string of current route prefix
     */
    public function routePrefixNormalize()
    {
        if(!empty($this->routePrefix))
            $this->routePrefix = self::normalizeRoute($this->routePrefix);

        return $this->routePrefix;
    }

    /**
     * Build dashboard navigation items for NavBar
     * @param $createLink boolean, if you need to add a menu
     * item to create a new model (entity)
     * @return array of current module nav items
     */
    public function dashboardNavItems($createLink = false)
    {
        $items = [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/'. $this->id .'/'],
            'active' => (\Yii::$app->controller->module->id == $this->id) ? true : false
        ];

        if ($createLink) {
            $items['items'] = [
                /*'<li class="divider"></li>',*/
                [
                    'label' => 'Create',
                    'icon' => 'fa-plus',
                    'url' => [$this->routePrefix . '/' . $this->id . '/create'],
                    'active' => ((\Yii::$app->controller->module->id == $this->id) && (\Yii::$app->controller->action->id == 'create')) ? true : false
                ],
            ];
        }

        return $items;
    }

    /**
     * Check if module exist
     * @return boolean, null or intance
     */
    public function moduleLoaded($id, $returnInstance = false)
    {
        // If module configured of parent module
        $parent = $this->module->id;
        if ($parent)
            $id = $parent . '/' . $id;

        if (Yii::$app->hasModule($id)) {
            if($returnInstance)
                return Yii::$app->getModule($id);
            else
                return true;
        } else {
            return false;
        }

        return null;
    }

    /**
     * Bootstrap interface, to be called during application loaded module.
     * @param $app object, the application currently running
     */
    public function bootstrap($app)
    {
        // Set meta data of current module
        $this->setMetaData();

        // Get URL path prefix if exist
        if (isset($this->routePrefix)) {
            $app->getUrlManager()->enableStrictParsing = true;
            $prefix = $this->routePrefix . '/';
        } else {
            $prefix = '';
        }

        // Add module URL rules
        $app->getUrlManager()->addRules([
            $prefix . '<module:' . $this->id . '>/' => '<module>/' . $this->id . '/index',
            $prefix . '<module:' . $this->id . '>/<controller:\w+>/' => '<module>/<controller>',
            $prefix . '<module:' . $this->id . '>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>' => '<module>/<controller>/<action>',
            $prefix . '<module:' . $this->id . '>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>' => '<module>/<controller>/<action>',
            [
                'pattern' => $prefix . '<module:' . $this->id . '>/',
                'route' => '<module>/' . $this->id . '/index',
                'suffix' => ''
            ], [
                'pattern' => $prefix . '<module:' . $this->id . '>/<controller:\w+>/',
                'route' => '<module>/<controller>',
                'suffix' => ''
            ], [
                'pattern' => $prefix . '<module:' . $this->id . '>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/',
                'route' => '<module>/<controller>/<action>',
                'suffix' => ''
            ], [
                'pattern' => $prefix . '<module:' . $this->id . '>/<controller:\w+>/<action:[0-9a-zA-Z_\-]+>/<id:\d+>/',
                'route' => '<module>/<controller>/<action>',
                'suffix' => ''
            ]
        ], true);
    }

    /**
     * Main method of installation module
     * @return boolean, false if install failure
     */
    public function install() {

        if (Yii::$app->getModule('admin/options') && isset(Yii::$app->options)) {
            $props = get_class_vars($this::className());

            unset($props['name']);
            unset($props['description']);
            unset($props['controllerNamespace']);
            unset($props['defaultRoute']);
            unset($props['routePrefix']);
            unset($props['vendor']);
            unset($props['controllerMap']);

            foreach ($props as $prop => $defaultValue) {
                if (is_array($defaultValue))
                    Yii::$app->options->set($this->id .'.'. $prop, serialize($defaultValue), 'array', null, true, false);
                elseif (is_object($defaultValue))
                    Yii::$app->options->set($this->id .'.'. $prop, serialize($defaultValue), 'object', null, true, false);
                elseif (is_bool($defaultValue))
                    Yii::$app->options->set($this->id .'.'. $prop, $defaultValue, 'boolean', null, true, false);
                else
                    Yii::$app->options->set($this->id .'.'. $prop, $defaultValue, null, null, true, false);
            }
            return true;
        }

        return true;
    }

    /**
     * Main method of uninstallation module
     * @return boolean, false if uninstall failure
     */
    public function uninstall() {
        return true;
    }
}