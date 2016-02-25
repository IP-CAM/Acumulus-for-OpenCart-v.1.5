<?php

use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\OpenCart\Helpers\Registry;
use Siel\Acumulus\Shop\Config as ShopConfig;
use Siel\Acumulus\Shop\ModuleTranslations;

/**
 * Class ControllerModuleAcumulus
 *
 * @property \ModelCheckoutOrder $model_checkout_order
 * @property \Language $language
 * @property \Loader $load
 */
class ControllerModuleAcumulus extends Controller {

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
      // Load models.
      $this->load->model('checkout/order');

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
   * Event handler that executes on the update of an order.
   *
   * The order is sent to Acumulus if it is changed to the status that
   * triggers the sending to Acumulus.
   *
   * @param int $order_id
   */
  public function eventAddOrderHistory($order_id) {
    $this->init();
    $source = $this->acumulusConfig->getSource(Source::Order, $order_id);
    $this->acumulusConfig->getManager()->sourceStatusChange($source);
  }

  /**
   * Event handler that executes on the creation of an order.
   *
   * The order is sent to Acumulus if the status is the status that triggers the
   * sending to Acumulus.
   *
   * @param int $order_id
   */
  public function eventAddOrder($order_id) {
    // Check if the status of the created order is the one we should react on.
    $this->init();
    $source = $this->acumulusConfig->getSource(Source::Order, $order_id);
    $this->acumulusConfig->getManager()->sourceStatusChange($source);
  }

}
