<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class JbRelatedProducts extends Module implements WidgetInterface
{

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
        ]
    ];

    public function __construct()
    {
        $this->name = 'jbrelatedproducts';
        $this->tab = 'front_office_features';
        $this->author = 'Jonas Burneika';
        $this->version = '1.0.0';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Related product products', [], 'Modules.JbRelatedProducts.Admin');
        $this->description = $this->trans('Add a block on every product page that displays similar products based on configurations', [], 'Modules.JbRelatedProducts.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        $this->templateFile = 'module:jbrelatedproducts/views/templates/hook/displayRelatedProducts.tpl';
    }


    public function install()
    {
        return parent::install() && $this->setModuleHooks();
    }


    public function uninstall()
    {
        return parent::uninstall();
    }

    private function setModuleHooks()
    {
        $result = true;
        foreach ($this->hooks as $hook => $parameters) {
            $result &= $this->registerHook($hook, null, $parameters);
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
        $controllers = Dispatcher::getControllersPhpselfList(_PS_FRONT_CONTROLLER_DIR_);

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

    public function renderWidget($hookName, array $configuration)
    {
        // TODO: Implement renderWidget() method.
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        // TODO: Implement getWidgetVariables() method.
    }

}