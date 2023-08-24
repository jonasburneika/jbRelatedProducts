<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class JbRelatedProducts extends Module implements WidgetInterface
{
    protected $html;

    protected $templateFile;
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

    private $prefix = 'JB_RELATED_PRODUCTS_';
    private $configurations = [
        'PRODUCTS_QUANTITY' => 8,
        'RELATION_CATEGORY' => 0,
        'RELATION_DEFAULT_CATEGORY' => 1,
        'RELATION_FEATURES' => 0,
        'RELATION_MANUFACTURER' => 0,
        'RELATION_SUPPLIERS' => 0,
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
        return parent::install() &&
            $this->setModuleHooks() &&
            $this->setConfigurations();
    }


    public function uninstall()
    {
        return parent::uninstall() && $this->deleteConfigurations();
    }

    private function setModuleHooks()
    {
        $result = true;
        foreach ($this->hooks as $hook => $parameters) {
            $result &= $this->registerHook($hook, null, $parameters);
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
            if($formSubmission){
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

    public function getContent()
    {
        $this->html = '';

        if (Tools::isSubmit('submitRelatedProductSettings')) {
            $this->setConfigurationValues();
        }

        $this->html .= $this->renderForm();

        return $this->html;
    }


    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Amount of products', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Number of related product to display', [], 'Modules.JbRelatedProducts.Admin'),
                        'name' => $this->prefix . 'PRODUCTS_QUANTITY',
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Same Category', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which shares at least one category.', [], 'Modules.JbRelatedProducts.Admin'),
                        'name' => $this->prefix . 'RELATION_CATEGORY',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Same Default Category', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which are from same default category.', [], 'Modules.JbRelatedProducts.Admin'),
                        'name' => $this->prefix . 'RELATION_DEFAULT_CATEGORY',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Same Features', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which has at least one matching feature.', [], 'Modules.JbRelatedProducts.Admin'),
                        'name' => $this->prefix . 'RELATION_FEATURES',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global'),
                            ],
                        ],

                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Same Manufacturer', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which are from same manufacturer.', [], 'Modules.JbRelatedProducts.Admin'),
                        'name' => $this->prefix . 'RELATION_MANUFACTURER',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Same supplier', [], 'Modules.JbRelatedProducts.Admin'),
                        'desc' => $this->trans('Display products, which are supplied by same Supplier.', [], 'Modules.JbRelatedProducts.Admin'),
                        'name' => $this->prefix . 'RELATION_SUPPLIERS',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled', [], 'Admin.Global'),
                            ],
                        ],
                    ],

                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRelatedProductSettings';
        $helper->currentIndex = $this->context->link->getAdminLink(
                'AdminModules',
                false
            ) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getConfigFieldsValues()
    {
        $values = [];
        foreach ($this->configurations as $name => $value) {
            $values[$this->prefix . $name] = Configuration::get($this->prefix . $name, null, null, null, $value);
        }
        return $values;
    }

    public function setConfigurationValues()
    {
        $productAmount = (int) Tools::getValue($this->prefix . 'PRODUCTS_QUANTITY');
        if ($productAmount <= 0) {
            $this->html .= $this->displayError($this->trans('Invalid value for display price.', [], 'Modules.JbRelatedProducts.Admin'));
            $this->_clearCache($this->templateFile);
            return;
        }
        if($this->setConfigurations(true)) {
            $this->_clearCache($this->templateFile);
            $this->html .= $this->displayConfirmation($this->trans('The settings have been updated.', [], 'Admin.Notifications.Success'));
        }
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