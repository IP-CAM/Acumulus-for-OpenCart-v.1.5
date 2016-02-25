<?php

use Siel\Acumulus\OpenCart\InvoiceAdd;
use Siel\Acumulus\OpenCart\OpenCartAcumulusConfig;
use Siel\Acumulus\Common\WebAPI;

/**
 * Class ModelModuleAcumulus
 *
 * @property \ModelSettingSetting $model_setting_setting
 * @property \ModelCatalogProduct $model_catalog_product
 * @property \ModelModuleAcumulusEntry $model_module_acumulus_entry
 * @property \ModelCheckoutOrder model_checkout_order
 * @property \ModelSaleOrder model_sale_order
 * @property \Loader $load
 * @property \Language $language
 * @property \Config $config
 */
class ModelModuleAcumulus extends Model {

  const LOG_DEBUG = 1;
  const LOG_NOTICE = 2;
  const LOG_WARNING = 3;
  const LOG_ERROR = 4;

  /** @var \Siel\Acumulus\OpenCart\OpenCartAcumulusConfig */
  protected $acumulusConfig;

  /** @var WebAPI */
  protected $webAPI;

  /** @var bool */
  protected $interactive = false;

  /** @var bool */
  protected $initialized = false;

  /** @var bool */
  protected $doSend = false;

  /** @var int */
  protected $orderId;

  /** @var object */
  public $orderModel = false;

  protected function getSeverityString($severity) {
    switch ($severity) {
      case self::LOG_ERROR:
        return 'Error';
      case self::LOG_WARNING:
        return 'Warning';
      case self::LOG_NOTICE:
        return 'Notice';
      case self::LOG_DEBUG:
      default:
        return 'Debug';
    }
  }

  /**
   * @param int $severity
   * @param string $message
   *
   * Other params are accepted and will turn $message into a format for sprintf.
   */
  protected function log($severity, $message) {
    global $log;
    if (func_num_args() > 2) {
      $args = func_get_args();
      array_shift($args);
      array_shift($args);
      $message = vsprintf($message, $args);
    }
    $version = $this->acumulusConfig->getEnvironment();
    $version = $version['moduleVersion'];
    $message = sprintf('Acumulus %s %s: %s', $version, $this->getSeverityString($severity), $message);
    $log->write($message);
  }

  /**
   * Helper method that loads some libraries.
   */
  protected function init() {
    if (!$this->initialized) {
      // Load the settings model.
      $this->load->model('setting/setting');

      // No autoload in OpenCart: load manually.
      $this->load->library('Siel/Acumulus/Common/TranslatorInterface');
      $this->load->library('Siel/Acumulus/Common/BaseTranslator');
      $this->load->library('Siel/Acumulus/Common/ConfigInterface');
      $this->load->library('Siel/Acumulus/Common/BaseConfig');
      $this->load->library('Siel/Acumulus/Common/WebAPICommunication');
      $this->load->library('Siel/Acumulus/Common/WebAPI');
      $this->load->library('Siel/Acumulus/OpenCart/OpenCartAcumulusConfig');
      $this->load->library('Siel/Acumulus/OpenCart/InvoiceAdd');

      $this->acumulusConfig = new OpenCartAcumulusConfig($this->language->get('code'), $this->model_setting_setting);

      $this->initialized = true;
    }
  }

  protected function loadOrderModel() {
    if (strrpos(DIR_APPLICATION, '/catalog/') === strlen(DIR_APPLICATION) - strlen('/catalog/')) {
      // We are in the catalog section, use the order model of account.
      $this->load->model('checkout/order');
      $this->orderModel = $this->model_checkout_order;
    }
    else {
      // We are in the admin section, use the order model of sale.
      $this->load->model('sale/order');
      $this->orderModel = $this->model_sale_order;
    }
  }

  /**
   * "Hook" that executes on the creation of an order.
   *
   * The order is sent to Acumulus if it is created with the status that
   * triggers the sending to Acumulus.
   *
   * @param int $order_id
   * @param int $order_status_id
   */
  public function orderCreated($order_id, $order_status_id) {
    // Check if the status of the created order is the one we should react on.
    $this->init();
    $this->log(self::LOG_DEBUG, 'orderCreated(%d, %d)', $order_id, $order_status_id);
    $settings = $this->acumulusConfig->getInvoiceSettings();
    if ($order_status_id == $settings['triggerOrderStatus']) {
      $this->sendOrderToAcumulus($order_id);
    }
  }

  /**
   * "Hook" that executes on the update of an order.
   *
   * The order is sent to Acumulus if it is changed to the status that
   * triggers the sending to Acumulus. But the actual sending is deferred to
   * after OpenCArt has updated the order and accompanying objects.
   *
   * @param int $order_id
   * @param int $order_status_id
   *   The new order status
   */
  public function orderUpdateStart($order_id, $order_status_id) {
    // Check if the new status of the updated order is the one we should react on.
    $this->init();
    $this->log(self::LOG_DEBUG, 'orderUpdateStart(%d, %d)', $order_id, $order_status_id);
    $settings = $this->acumulusConfig->getInvoiceSettings();
    if ($order_status_id == $settings['triggerOrderStatus']) {
      // Check if the status has changed and not only other order info.
      $this->orderId = $order_id;
      $this->loadOrderModel();
      $order = $this->orderModel->getOrder($this->orderId);
      if ($order && $order['order_status_id'] != $order_status_id) {
        $this->doSend = true;
      }
    }
  }

  /**
   * "Hook" that executes after the update of an order.
   *
   * It it was determined that the order should be sent to Acumulus, it is done
   * now.
   */
  public function orderUpdated() {
    $this->log(self::LOG_DEBUG, 'orderUpdated(%s)', $this->doSend ? 'true' : 'false');
    if ($this->doSend) {
      $this->sendOrderToAcumulus($this->orderId);
    }
  }

  /**
   * Sends an order to Acumulus.
   *
   * @param int $order_id
   */
  protected function sendOrderToAcumulus($order_id) {
    $this->webAPI = new WebAPI($this->acumulusConfig);
    $this->loadOrderModel();
    $order = $this->orderModel->getOrder($order_id);
    $this->load->model('catalog/product');
    $this->load->library('Siel/Acumulus/OpenCart/InvoiceAdd');
    $addInvoice = new InvoiceAdd($this->acumulusConfig, $this, $this->webAPI);
    $invoice = $addInvoice->convertOrderToAcumulusInvoice($order);
    // VQMOD: insert your 'acumulus.invoice.add' event code here.
    // END VQMOD: insert your 'acumulus.invoice.add' event code here.
    $result = $this->webAPI->invoiceAdd($invoice, $order['order_id']);
    $this->log(self::LOG_DEBUG, 'sendOrderToAcumulus(%d): invoiceAdd result: %s', $order_id, str_replace(array("\r", "\n", "\t"), '', var_export($result, TRUE)));

    // Store entry id and token.
    if (!empty($result['invoice'])) {
      $this->load->model('module/acumulus_entry');
      $this->model_module_acumulus_entry->save($result['invoice'], $order);
    }

    // Send a mail if there are messages.
    $messages = $this->webAPI->resultToMessages($result);
    if (!empty($messages)) {
      $this->sendMail($result, $messages, $order);
    }
  }

  protected function sendMail($result, $messages, $order) {
    $replacements = array(
      '{order_id}' => $order['order_id'],
      '{invoice_id}' => isset($result['invoice']['invoicenumber']) ? $result['invoice']['invoicenumber'] : $this->acumulusConfig->t('message_no_invoice'),
      '{status}' => $result['status'],
      '{status_text}' => $this->webAPI->getStatusText($result['status']),
      '{status_1_text}' => $this->webAPI->getStatusText(1),
      '{status_2_text}' => $this->webAPI->getStatusText(2),
      '{status_3_text}' => $this->webAPI->getStatusText(3),
      '{messages}' => $this->webAPI->messagesToText($messages),
      '{messages_html}' => $this->webAPI->messagesToHtml($messages),
    );

    $credentials = $this->acumulusConfig->getCredentials();
    $mail = new Mail();

    $mail->protocol = $this->config->get('config_mail_protocol');
    $mail->parameter = $this->config->get('config_mail_parameter');
    $mail->hostname = $this->config->get('config_smtp_host');
    $mail->username = $this->config->get('config_smtp_username');
    $mail->password = $this->config->get('config_smtp_password');
    $mail->port = $this->config->get('config_smtp_port');
    $mail->timeout = $this->config->get('config_smtp_timeout');
    $mail->setFrom($this->config->get('config_email'));
    $mail->setSender($this->config->get('config_name'));
    $mail->setTo(isset($credentials['emailonerror']) ? $credentials['emailonerror'] : $this->config->get('config_email'));
    $mail->setSubject($this->acumulusConfig->t('mail_subject'));
    $text = $this->acumulusConfig->t('mail_text');
    $text = strtr($text, $replacements);
    $mail->setText($text);
    $html = $this->acumulusConfig->t('mail_html');
    $html = strtr($html, $replacements);
    $mail->setHtml($html);

    $mail->send();
  }

  /**
   * Checks requirements and installs tables for this module.
   *
   * @return array
   *   Array of error messages, empty if no errors occurred.
   */
  public function install() {
    $this->init();

    // Check requirements.
    $webAPI = new WebAPI($this->acumulusConfig);
    $result = $webAPI->checkRequirements();

    // Install tables.
    $configurationValues = $this->model_setting_setting->getSetting('acumulus_siel');
    $currentDataModelVersion = isset($configurationValues['acumulus_siel_datamodel_version']) ? $configurationValues['acumulus_siel_datamodel_version'] : '';

    if (empty($result) && ($currentDataModelVersion === '' || version_compare($currentDataModelVersion, '1.0', '<'))) {
      $this->load->model('module/acumulus_entry');
      $this->model_module_acumulus_entry->install();
      $this->model_setting_setting->editSettingValue('acumulus_siel', 'acumulus_siel_datamodel_version', '1.0');
    }

    return $result;
  }

  /**
   * Uninstalls data and settings from this module.
   *
   * @return bool
   *   Whether the uninstall was successful.
   */
  public function uninstall() {
    $this->init();

    $this->load->model('module/acumulus_entry');
    $this->model_module_acumulus_entry->uninstall();
    // Delete all config values.
    $this->model_setting_setting->deleteSetting('acumulus_siel');

    return TRUE;
  }

}
