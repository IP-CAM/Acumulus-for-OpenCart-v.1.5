<?php

use Siel\Acumulus\OpenCart\Helpers\Registry;
use Siel\Acumulus\Shop\Config as ShopConfig;
use Siel\Acumulus\Shop\ModuleTranslations;

/**
 * Class ControllerModuleAcumulus
 *
 * @property \ModelSettingSetting $model_setting_setting
 * @property \ModelExtensionEvent $model_extension_event
 * @property \Language $language
 * @property \Request $request
 * @property \Response $response
 * @property \Session $session
 * @property \Url $url
 * @property \Document $document
 * @property \Loader $load
 * @property array $data
 * @property string $template
 * @property array $children
 */
class ControllerModuleAcumulus extends Controller {

  /** @var \Siel\Acumulus\Shop\Config */
  private $acumulusConfig = NULL;

  /** @var array */
  private $data;

  /** @var \Siel\Acumulus\Helpers\Form */
  private $form;

  public function addError($message) {
    if (is_array($message)) {
      $this->data['error_warnings'] = array_merge($this->data['error_warnings'], $message);
    }
    else {
      $this->data['error_warnings'][] = $message;
    }
  }

  public function addSuccess($message) {
    $this->data['success'][] = $message;
  }

  /**
   * Helper method that initializes some object properties:
   * - language
   * - model_Setting_Setting
   * - webAPI
   * - acumulusConfig
   */
  private function init() {
    if ($this->acumulusConfig === NULL) {
      // Load models.
      $this->load->model('setting/setting');

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
   * Helper method to translate strings.
   *
   * @param string $key
   *  The key to get a translation for.
   *
   * @return string
   *   The translation for the given key or the key itself if no translation
   *   could be found.
   */
  protected function t($key) {
    return $this->acumulusConfig->getTranslator()->get($key);
  }

  /**
   * Install controller action, called when the module is installed.
   *
   * @return bool
   */
  public function install() {
    // Call the actual install method.
    $this->doInstall();

    return empty($this->data['error_warnings']);
  }

  /**
   * Uninstall function, called when the module is uninstalled by an admin.
   *
   * @todo: create confirm uninstall form.
   */
//  public function uninstall() {
//    // "Disable" (delete) events, regardless the confirmation answer.
//    $this->uninstallEvents();
//    $this->response->redirect($this->url->link('module/acumulus/confirmUninstall', 'token=' . $this->session->data['token'], 'SSL'));
//  }

  /**
   * Main controller action: show/process the settings form for this module.
   */
  public function index() {
    $this->displayFormCommon('config');

    // Are we posting? If not so, handle this as a trigger to update.
    if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
      $this->doUpgrade();
    }

    // Add an intermediate level to the breadcrumb.
    $this->data['breadcrumbs'][] = array(
      'text' => $this->t('modules'),
      'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => ' :: '
    );

    $this->renderFormCommon('config', 'button_save');
  }

  /**
   * Main controller action: show/process the settings form for this module.
   */
  public function batch() {
    $this->displayFormCommon('batch');
    $this->renderFormCommon('batch', 'button_send');
  }

  /**
   * Explicit confirmation step to allow to retain the settings.
   *
   * The normal uninstall action will unconditionally delete all settings.
   */
  public function confirmUninstall() {
    $this->displayFormCommon('uninstall');

    // Are we confirming, or should we show the confirm message?
    if ($this->request->server['REQUEST_METHOD'] === 'POST') {
      $this->doUninstall();
      $this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
    }

    // Add an intermediate level to the breadcrumb.
    $this->data['breadcrumbs'][] = array(
      'text' => $this->t('modules'),
      'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => ' :: '
    );

    $this->renderFormCommon('confirmUninstall', 'button_confirm_uninstall');
  }

  /**
   * Performs the common tasks when displaying a form.
   *
   * @param string $task
   */
  private function displayFormCommon($task) {
    $this->init();

    $this->form = $this->acumulusConfig->getForm($task);

    $this->document->addStyle('view/stylesheet/acumulus.css');

    $this->data['success'] = array();
    $this->data['error_warnings'] = array();

    // Set the page title.
    $this->document->setTitle($this->t("{$task}_form_title"));
    $this->data["heading_title"] = $this->t("{$task}_form_header");
    $this->data["page_title"] = $this->t("{$task}_form_title");
    $this->data["text_edit"] = $this->t("{$task}_form_header");

    // Set up breadcrumb.
    $this->data['breadcrumbs'] = array();
    $this->data['breadcrumbs'][] = array(
      'text' => $this->t('text_home'),
      'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => FALSE
    );

    // Render the common parts.
    $this->data['header'] = $this->load->controller('common/header');
    $this->data['column_left'] = $this->load->controller('common/column_left');
    $this->data['footer'] = $this->load->controller('common/footer');
  }

  /**
   * Performs the common tasks when processing and rendering a form.
   *
   * @param string $task
   * @param string $button
   */
  private function renderFormCommon($task, $button) {
    // Process the form if it was submitted and render it again.
    $this->form->process();

    // Show messages.
    foreach($this->form->getSuccessMessages() as $message) {
      $this->addSuccess($message);
    }
    foreach($this->form->getErrorMessages() as $message) {
      $this->addError($this->t($message));
    }

    $this->data['form'] = $this->form;
    $this->data['formRenderer'] = $this->acumulusConfig->getFormRenderer();

    // Complete the breadcrumb with the current path.
    $link = 'module/acumulus';
    if ($task !== 'config') {
      $link .= "/$task";
    }
    $this->data['breadcrumbs'][] = array(
      'text' => $this->t("{$task}_form_header"),
      'href' => $this->url->link($link, 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => ' :: '
    );

    // Set the action buttons (action + text).
    $this->data['action'] = $this->url->link($link, 'token=' . $this->session->data['token'], 'SSL');
    $this->data['button_icon'] = $task === 'batch' ? 'fa-envelope-o' : 'fa-save';
    $this->data['button_save'] = $this->t($button);
    $this->data['cancel'] = $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL');
    $this->data['button_cancel'] = $this->t('button_cancel');

    // Send the output.
    $this->response->setOutput($this->load->view('module/acumulus-form.tpl', $this->data));
  }

  /**
   * Checks requirements and installs tables for this module.
   *
   * @return bool
   *   Success.
   */
  private function doInstall() {
    $this->init();

    $result = TRUE;
    $setting = $this->model_setting_setting->getSetting('acumulus_siel');
    $currentDataModelVersion = isset($setting['acumulus_siel_datamodel_version']) ? $setting['acumulus_siel_datamodel_version'] : '';

    if ($currentDataModelVersion === '' || version_compare($currentDataModelVersion, '4.0', '<')) {
      // Check requirements  (we assume this has been done successfully before
      // if the data model is at the latest version.
      $requirements = new \Siel\Acumulus\Helpers\Requirements();
      $messages = $requirements->check();
      foreach ($messages as $message) {
        $this->addError($message['message']);
      }
      if (!empty($messages)) {
        return FALSE;
      }

      // Install tables.
      $result = $this->acumulusConfig->getAcumulusEntryModel()->install();
      $setting['acumulus_siel_datamodel_version'] = '4.0';
      $this->model_setting_setting->editSetting('acumulus_siel', $setting);
    }

    // Install events
    if (empty($this->data['error_warnings'])) {
      $this->installEvents();
    }

    return $result;
  }

  /**
   * Uninstalls data and settings from this module.
   *
   * @return bool
   *   Whether the uninstall was successful.
   */
  private function doUninstall() {
    $this->init();
    $this->acumulusConfig->getAcumulusEntryModel()->uninstall();

    // Delete all config values.
    $this->model_setting_setting->deleteSetting('acumulus_siel');

    return TRUE;
  }

  /**
   * Upgrades the data and settings for this module if needed.
   *
   * The install now checks for the data model and can do an upgrade instead of
   * a clean install.
   *
   * @return bool
   *   Whether the upgrade was successful.
   */
  private function doUpgrade() {
    return $this->doInstall();
  }

  /**
   * Installs our events.
   *
   * This will add them to the table 'event' from where they are registered on
   * the start of each request. The controller actions can be found in the
   * catalog controller.
   *
   * To support updating, this will also be called by the index function.
   * Therefore we will first remove any existing events from our module.
   */
  private function installEvents() {
    $this->uninstallEvents();
    $this->model_extension_event->addEvent('acumulus', 'post.order.add', 'module/acumulus/eventAddOrder');
    $this->model_extension_event->addEvent('acumulus', 'post.order.history.add', 'module/acumulus/eventAddOrderHistory');
  }

  /**
   * Removes the Acumulus event handlers from the event table.
   */
  private function uninstallEvents() {
    $this->load->model('extension/event');
    $this->model_extension_event->deleteEvent('acumulus');
  }

}
