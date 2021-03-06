<?php
namespace Application;

class ShoppingCartAdapter
{
    function databaseDetails()
    {
        switch($this->whichShoppingCart()) {
            case 'prestashop':
                require_once $this->shoppingCartRoot().'/config/settings.inc.php';
                return array(
                    'dbname' => _DB_NAME_,
                    'username' => _DB_USER_,
                    'password' => _DB_PASSWD_,
                    'product_table' => 'ps_product',
                    'product_sku_field' => 'reference',
                    'product_id_field' => 'id_product',
                );
            case 'magento':
                $config = new \Zend_Config_Xml($this->shoppingCartRoot() . 'app/etc/local.xml');
                $dbConfig = $config->toArray();
                $dbinfo = $dbConfig['global']['resources']['default_setup']['connection'];
                $dbinfo = $dbinfo + array(
                        'product_table' => 'catalog_product_entity',
                        'product_sku_field' => 'sku',
                        'product_id_field' => 'entity_id',
                    );
                return $dbinfo;
            default:
                throw new \Exception('Unable to detect shopping cart');
        }
    }

    /**
     * Attempts to detect which shopping cart is in the document root (path immediately where VF admin panel is install)
     * Calculates path to scan based on SCRIPT_FILENAME env variable, so that symlinks are not resolved.
     * This means even if the admin panel is symlinked in, it finds the path where the symlink exists, not where the admin itself is.
     */
    public function whichShoppingCart()
    {
        $shoppingCartRoot = $this->shoppingCartRoot();
        if(is_null($shoppingCartRoot)) {
            return false;
        }

        $indexCode = file_get_contents($shoppingCartRoot.'index.php');

        if(preg_match('/PrestaShop/', $indexCode)) {
            return 'prestashop';
        }
        if(preg_match('/magento/', $indexCode)) {
            return 'magento';
        }
    }

    // this returns the "simulated" (unresolved) path if run through a symlink
    public function shoppingCartRoot()
    {
        $script = dirname($_SERVER["SCRIPT_FILENAME"]);
        if(preg_match('/vfadmin\/public/', $script)) {
            return str_replace('vfadmin/public', '', $script);
        }
        if(preg_match('/vfadmin/', $script)) {
            return str_replace('vfadmin', '', $script);
        }
    }
}