<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * @param JbRelatedProducts $module
 * @return bool
 */
function upgrade_module_1_1_0($module)
{
    $res = true;

    $res &= $module->setModuleHooks();
    $res &= $module->registerModuleTabs();
    $res &= $module->createModuleDatabaseTables();

    return $res;
}