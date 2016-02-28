<?php

use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\OpenCart\Helpers\Registry;
use Siel\Acumulus\Shop\Config as ShopConfig;
use Siel\Acumulus\Shop\ModuleTranslations;

/** @noinspection PhpUndefinedClassInspection */
/**
 * Class ModelModuleAcumulus
 *
 * @property \ModelCheckoutOrder $model_checkout_order
 * @property \Language $language
 * @property \Loader $load
 */
class ModelModuleAcumulus extends Model {

  /** @var \Siel\Acumulus\Shop\Config */
  private $acumulusConfig;

  /**
   * Helper method that initializes some object properties:
   * - language
   * - model_Setting_Setting
   * - acumulusConfig
   */
  private function init() {
    if ($this->acumulusConfig === NULL) {
      // Load autoloader
      require_once(DIR_SYSTEM . 'library/Siel/psr4.php');

      $languageCode = $this->language->get('code');
      if (empty($languageCode)) {
        $languageCode = 'nl';
      }
      Registry::setRegistry($this->registry);
      $this->acumulusConfig = new ShopConfig('OpenCart', $languageCode);
      $this->acumulusConfig->getTranslator()->add(new ModuleTranslations());
    }
  }

  /**
   * Event handler that executes on the cration or update of an order.
   *
   * @param int $order_id
   */
  public function eventOrderUpdate($order_id) {
    $this->init();
    $source = $this->acumulusConfig->getSource(Source::Order, $order_id);
    $this->acumulusConfig->getManager()->sourceStatusChange($source);
  }
}
