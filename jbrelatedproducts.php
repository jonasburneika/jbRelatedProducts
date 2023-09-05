<?php

use PrestaShop\PrestaShop\Adapter\Product\ProductDataProvider;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class JbRelatedProducts extends Module implements WidgetInterface
{
    protected $html;

    protected $templateLocation;

    const CONTROLLER_SETTINGS = 'AdminJbRelatedProductsSettings';

    const CONTROLLER_RELATIONSHIPS = 'AdminJbRelatedProductsRelationShips';

    const CONTROLLER_LOGS = 'AdminJbRelatedProductsLogs';

    private $hooks = [
        'displayRelatedProducts' => [],
        'displayHeader' => [
            'show' => [
                'product' // Load css assets only on product controller
            ],
        ],
        'displayReassurance' => [
            'show' => 'product',
            'position' => 1
        ],
        'displayFooterProduct' => [
            'position' => 1
        ],
        'displayAdminProductsOptionsStepBottom' => [],
        'displayBackOfficeHeader' => [],
        'actionProductSave' => [],
    ];

    public $prefix = 'JB_RELATED_PRODUCTS_';
    public $configurations = [
        'PRODUCTS_QUANTITY' => 8,
        'RELATION_CATEGORY' => 0,
        'RELATION_DEFAULT_CATEGORY' => 1,
        'RELATION_FEATURES' => 0,
        'RELATION_MANUFACTURER' => 0,
        'RELATION_SUPPLIERS' => 0,
        'RELATION_APPEND' => 0,
        'PRODUCTS_LOG_DAYS' => 31,
        'RELATION_MANUALLY' => 1,
        'RELATION_REASSURANCE' => 1,
    ];

    public function __construct()
    {
        $this->name = 'jbrelatedproducts';
        $this->tab = 'front_office_features';
        $this->author = 'Jonas Burneika';
        $this->version = '1.1.0';

        $this->bootstrap = true;
        parent::__construct();
        $this->loadFiles();

        $this->displayName = $this->trans('Related product products', [], 'Modules.JbRelatedProducts.Admin');
        $this->description = $this->trans('Add a block on every product page that displays similar products based on configurations', [], 'Modules.JbRelatedProducts.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '1.7.8.9'];

        $this->templateLocation = 'module:jbrelatedproducts/views/templates/';
    }


    public function install()
    {
        if (!parent::install()) {
            $this->_errors[] = $this->l('Could not install module');

            return false;
        }

        if (!$this->setModuleHooks()) {
            $this->_errors[] = $this->l('Could not register module hooks');

            return false;
        }

        if (!$this->registerModuleTabs()) {
            $this->_errors[] = $this->l('Could not register module admin controllers');

            return false;
        }

        if (!$this->createModuleDatabaseTables()) {
            $this->_errors[] = $this->l('Could not create module database tables');

            return false;
        }

        if (!$this->setConfigurations()) {
            $this->_errors[] = $this->l('Could not set default configuration values');

            return false;
        }

        JbRelatedProductsLog::logSuccess('Module installed successfully');
        return true;

    }


    public function uninstall()
    {
        if (!$this->deleteModuleTabs()) {
            $this->_errors[] = $this->l('Could not delete module admin controllers');

            return false;
        }

        if (!$this->deleteModuleDatabaseTables()) {
            $this->_errors[] = $this->l('Could not delete module database tables');

            return false;
        }

        if (!parent::uninstall()) {
            $this->_errors[] = $this->l('Could not uninstall module');

            return false;
        }

        if (!$this->deleteConfigurations()) {
            $this->_errors[] = $this->l('Could not delete configuration settings');

            return false;
        }

        return true;
    }

    public function setModuleHooks()
    {
        $result = true;
        foreach ($this->hooks as $hook => $parameters) {
            $result &= $this->registerHook($hook, null, $parameters);
        }
        if (!$result) {
            $this->_errors[] = $this->trans('Unable to setup module hooks', [], 'Modules.JbRelatedProducts.Admin');
        }
        return $result;
    }

    /**
     * Setting predefined or form submitted Configuration values
     * @return int|true
     */
    private function setConfigurations($formSubmission = false)
    {
        $result = true;
        foreach ($this->configurations as $name => $value) {
            if ($formSubmission) {
                $value = Tools::getValue($this->prefix . $name);
            }
            $result &= Configuration::updateValue($this->prefix . $name, $value);
        }
        if (!$result && !$formSubmission) {
            $this->_errors[] = $this->trans('Unable to setup module configurations', [], 'Modules.JbRelatedProducts.Admin');
        }
        return $result;
    }

    private function deleteConfigurations()
    {
        $result = true;
        foreach ($this->configurations as $name => $value) {
            $result &= Configuration::deleteByName($this->prefix . $name);
        }
        return $result;
    }

    public function registerHook($hook, $shop_list = null, $exceptions = [])
    {
        if (parent::registerHook($hook, $shop_list)) {
            $idHook = Hook::getIdByName($hook);
            if ($idHook) {
                if (isset($exceptions['show']) && $exceptions['show']) {
                    $this->registerExceptions($idHook, $this->getHookExceptions($exceptions['show']));
                }
                if (isset($exceptions['position']) && $exceptions['position']) {
                    $this->updatePosition($idHook, false, (int)$exceptions['position']);
                }
            }
            return true;
        }
        $this->_errors[] = $this->trans('Unable to register module hooks', [], 'Modules.JbRelatedProducts.Admin');
        return false;
    }

    /**
     * Get list of Exceptions where hook can't be executed.
     * First getting all exceptions and removing allowing controllers
     * @param $allowedExceptions
     * @return array
     */
    private function getHookExceptions($allowedExceptions)
    {
        if (!is_array($allowedExceptions)) {
            $allowedExceptions = [$allowedExceptions];
        }
        $allExceptions = [];
        if (Tools::version_compare(_PS_VERSION_, '8.0.0', '>')) {
            $controllers = Dispatcher::getControllersPhpselfList(_PS_FRONT_CONTROLLER_DIR_);
        } else {
            $controllers = Dispatcher::getControllers(_PS_FRONT_CONTROLLER_DIR_);
        }
        asort($controllers);
        if ($controllers) {
            foreach ($controllers as $k => $v) {
                $allExceptions[] = $v;
            }
        }

        $modules_controllers_type = [
            'admin',
            'front'
        ];
        foreach ($modules_controllers_type as $type) {
            $all_modules_controllers = Dispatcher::getModuleControllers($type);
            foreach ($all_modules_controllers as $module => $modules_controllers) {
                foreach ($modules_controllers as $cont) {
                    $allExceptions[] = 'module-' . $module . '-' . $cont;
                }
            }
        }

        return array_diff($allExceptions, $allowedExceptions);

    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::CONTROLLER_SETTINGS));
    }

    private function getWidgetData($hookName, $configuration)
    {
        if (!empty($configuration['product'])) {
            $product = $configuration['product'];
            if ($product instanceof Product) {
                $product = (array)$product;
                $product['id_product'] = $product['id'];
            }
            $id_product = $product['id_product'];
        }

        if (empty($id_product)) {
            $id_product = Tools::getValue('id_product');
        }
        if ($id_product) {
            return [
                'id_product' => $id_product,
                'cache_id' => $this->getCacheId($this->generateCacheId($id_product, $hookName) . time()),
            ];
        }

        return false;
    }

    private function generateCacheId($idProduct, $hookName)
    {
        $cacheId = $this->name . '|' . $idProduct . '|' . $hookName;
        $values = [];
        foreach ($this->configurations as $name => $value) {
            $values[] = Tools::getValue($this->prefix . $name);
        }
        return $cacheId . implode('|', $values);
    }

    private function getRelatedProducts($idProduct): array
    {
        $relationShip = new JbRelatedProductsRelationShip();
        $productArray = $relationShip->getRelatedProducts($idProduct);
        $assignedProductsAmount = count($productArray);

        if (Configuration::get($this->prefix . 'RELATION_APPEND') && $assignedProductsAmount < $relationShip->limit) {
            $usedIds = array_merge(array_column($productArray, 'id_product'), [(int)$idProduct]);
            $limit = $relationShip->limit - $assignedProductsAmount;
            $appendedProducts = $relationShip->getRelatedProductsBySettings($idProduct, $usedIds, $limit);
            $productArray = array_merge($productArray, $appendedProducts);
        }

        return $relationShip->assembleProducts($productArray);
    }


    private function getTemplate($hookName): string
    {
        switch ($hookName) {
            case 'displayReassurance':
                return $this->templateLocation . 'hook/' . $hookName . '.tpl';
            case 'displayRelatedProducts':
            case 'displayFooterProduct':
            default:
                return $this->templateLocation . 'hook/' . 'displayRelatedProducts.tpl';
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/back.css', 'all');
    }
    public function hookActionProductSave()
    {
        $idProduct = Tools::getValue('id_product');
        $relatedProducts = Tools::getValue('jb_related_products');
        if ($idProduct && isset($relatedProducts['data']) && !empty($relatedProducts['data'])){
            JbRelatedProductsRelationShip::removeRelationShip($idProduct);
            JbRelatedProductsRelationShip::setRelatedProducts($idProduct, $relatedProducts['data']);
        }
    }
    
    public function hookDisplayAdminProductsOptionsStepBottom($params)
    {
        $translator = Context::getContext()->getTranslator();
        $relationShip = new JbRelatedProductsRelationShip();
        $relatedProducts = $relationShip->getRelatedProducts($params['id_product']);
        $products = [];
        if (!empty($relatedProducts)){
            foreach ($relatedProducts as $relatedProduct) {
                $product = (new ProductDataProvider())->getProduct($relatedProduct['id_product']);
                $products[] =[
                    'id' => $relatedProduct['id_product'],
                    'name' => reset($product->name) . ' (ref:' . $product->reference . ')',
                    'image' => $product->image,
                ];
            }
        }
        $this->smarty->assign([
            'remote_url' => Context::getContext()->link->getLegacyAdminLink(
                'AdminProducts',
                true,
                [
                    'ajax' => 1,
                    'disableCombination' => 1,
                    'action' => 'productsList',
                    'forceJson' => 1,
                    'excludeVirtuals' => 0,
                    'exclude_packs' => 0,
                    'limit' => 20
                ]
            ) . '&q=%QUERY',
            'placeholder' => $translator->trans('Search for a product', [], 'Admin.Catalog.Help'),
            'products' => $products
        ]);

        return $this->fetch($this->templateLocation . 'admin/displayAdminRelatedProducts.tpl');
    }

    public function renderWidget($hookName, array $configuration)
    {
        if ($hookName == 'displayReassurance' && !Configuration::get($this->prefix . 'RELATION_REASSURANCE')) {
            return;
        }

        $params = $this->getWidgetData($hookName, $configuration);

        if ($params) {
            if (!$this->isCached($this->getTemplate($hookName), $params['cache_id'])) {
                $variables = $this->getWidgetVariables($hookName, $configuration);

                if (empty($variables)) {
                    return false;
                }

                $this->smarty->assign($variables);
            }

            return $this->fetch($this->getTemplate($hookName), $params['cache_id']);
        }

        return false;
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $params = $this->getWidgetData($hookName, $configuration);
        if ($params) {
            $products = $this->getRelatedProducts($params['id_product']);

            if (!empty($products)) {
                return [
                    'products' => $products,
                ];
            }
        }

        return false;
    }

    private function loadFiles()
    {
        $classesDir = dirname(__FILE__) . '/classes/';
        $classes = glob($classesDir . '*.php');

        foreach ($classes as $class) {
            if ($class != $classesDir . 'index.php') {
                require_once($class);
            }
        }
    }

    public function createModuleDatabaseTables()
    {
        $installer = new JbRelatedProductsInstaller();
        $result = $installer->install();
        if (!$result) {
            $this->_errors[] = $this->trans(
                'Unable to create DataBases',
                [],
                'Modules.JbRelatedProducts.Admin'
            );
        }
        return $result;
    }

    public function deleteModuleDatabaseTables()
    {
        $installer = new JbRelatedProductsInstaller();
        $result = $installer->uninstall();
        if (!$result) {
            $this->_errors[] = $this->trans(
                'Unable to delete DataBases',
                [],
                'Modules.JbRelatedProducts.Admin'
            );
        }
        return $result;
    }


    public function getMenu()
    {
        $currentController = Tools::getValue('controller');

        $menu = array(
            array(
                'url' => $this->context->link->getAdminLink(self::CONTROLLER_SETTINGS),
                'title' => $this->l('Settings'),
                'current' => self::CONTROLLER_SETTINGS == $currentController,
                'icon' => 'icon icon-gear'
            ),
            array(
                'url' => $this->context->link->getAdminLink(self::CONTROLLER_RELATIONSHIPS),
                'title' => $this->l('Relationships'),
                'current' => self::CONTROLLER_RELATIONSHIPS == $currentController,
                'icon' => 'icon icon-group'
            ),
            array(
                'url' => $this->context->link->getAdminLink(self::CONTROLLER_LOGS),
                'title' => $this->l('Log'),
                'current' => self::CONTROLLER_LOGS == $currentController,
                'icon' => 'icon icon-list'
            ),
        );

        $this->context->smarty->assign('menu', $menu);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/menu.tpl');
    }

    private function getModuleTabs()
    {
        return array(
            'AdminJbRelatedProducts' => [
                'title' => $this->l('Related products'),
            ],
            self::CONTROLLER_SETTINGS => [
                'title' => $this->l('Settings'),
                'icon' => 'settings'
            ],
            self::CONTROLLER_RELATIONSHIPS => [
                'title' => $this->l('Relationships'),
                'icon' => 'assessment'
            ],
            self::CONTROLLER_LOGS => [
                'title' => $this->l('Log'),
                'icon' => 'list'
            ],
        );
    }

    public function registerModuleTabs()
    {
        $tabs = $this->getModuleTabs();

        if (empty($tabs)) {
            return true;
        }
        $idParent = false;
        foreach ($tabs as $controller => $tab) {
            if (!$idParent) {
                $idParent = $this->registerModuleTab($controller, $tab['title'], 0);
                continue;
            }

            if (!$this->registerModuleTab($controller, $tab['title'], $idParent, $tab['icon'])) {
                return false;
            }
        }
        return true;
    }

    private function registerModuleTab($controller, $tabName, $idParent, $icon = false)
    {
        $tabRepository = $this->get('prestashop.core.admin.tab.repository');
        $idTab = $tabRepository->findOneIdByClassName($controller);

        if ($idTab) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $controller;
        $tab->name = array();
        $languages = Language::getLanguages(false);
        $tab->module = $this->name;
        $tab->id_parent = (int)$idParent;

        $tab->wording = 'Related Products';
        $tab->wording_domain = 'Admin.JbRelatedProducts.Menu';

        foreach ($languages as $language) {
            $tab->name[$language['id_lang']] = $tabName;
        }
        if ($icon) {
            $tab->icon = $icon;
        }
        $tab->add();
        if ($idParent === 0) {
            return (int)$tab->id;
        } else {
            return (bool)$tab->id;
        }

    }

    private function deleteModuleTabs()
    {
        $tabs = $this->getModuleTabs();

        if (empty($tabs)) {
            return true;
        }

        foreach (array_keys($tabs) as $controller) {
            if (!$this->deleteModuleTab($controller)) {
                return false;
            }
        }

        return true;
    }

    private function deleteModuleTab($controller)
    {
        $tabRepository = $this->get('prestashop.core.admin.tab.repository');
        $idTab = $tabRepository->findOneIdByClassName($controller);
        $tab = new Tab((int)$idTab);

        if (!Validate::isLoadedObject($tab)) {
            return true;
        }

        if (!$tab->delete()) {
            return false;
        }

        return true;
    }

}