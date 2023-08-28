<?php

class JbRelatedProductsLog extends ObjectModel
{
    const TYPE_SUCCESS = 1;

    const TYPE_ERROR = 2;

    const TYPE_WARNING = 3;

    const TYPE_INFO = 4;

    public $id_jb_related_products_log;

    public $type;

    public $id_product;

    public $message;

    public $id_employee;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'jb_relprod_log',
        'primary' => 'id_jb_relprod_log',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'message' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function __construct($id = NULL, $id_lang = NULL, $id_shop = NULL, $translator = NULL)
    {
        parent::__construct($id, $id_lang, $id_shop, $translator);
    }

    public static function logSuccess($message, $idProduct = NULL)
    {
        self::logMessage($message, $idProduct, self::TYPE_SUCCESS);
    }

    public static function logError($message, $idProduct = NULL)
    {
        self::logMessage($message, $idProduct, self::TYPE_ERROR);
    }

    public static function logWarning($message, $idProduct = NULL)
    {
        self::logMessage($message, $idProduct, self::TYPE_WARNING);
    }

    public static function logInfo($message, $idProduct = NULL)
    {
        self::logMessage($message, $idProduct);
    }

    private static function logMessage($message, $idProduct, $type = self::TYPE_INFO)
    {
        $context = Context::getContext();
        $log = new self();
        $log->id_product = $idProduct;
        $log->type = (int)$type;
        $log->message = $message;
        $log->id_employee = $context->employee->id;
        try {
            $log->save();
        } catch (Exception $e) {
            $translator = $context->getTranslator();
            PrestaShopLogger::addLog(
                $translator->trans(
                    'Unable to save Log message. Error: %s ',
                    ['%s' => $e->getMessage()],
                    'Modules.JbRelatedProducts.Log'
                ),
                1,
                NULL,
                '',
                0,
                true,
                (int)$context->employee->id
            );
        }
    }

    public static function deleteLogs($all = false, $old = 31): bool
    {
        $date = date('Y-m-d H:i:s', strtotime('-' . (int)$old . ' days'));
        $where = $all ? '' : ' WHERE `date_add` < "' . pSQL($date) . '"';

        return DB::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'jb_relprod_log` ' . $where . ';');
    }
}