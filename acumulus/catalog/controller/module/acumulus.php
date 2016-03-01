<?php

use Siel\Acumulus\OpenCart\Helpers\OcHelper;

/** @noinspection PhpUndefinedClassInspection */
/**
 * Class ControllerModuleAcumulus is the Acumulus admin site controller.
 */
class ControllerModuleAcumulus extends Controller {
  /** @var \Siel\Acumulus\OpenCart\Helpers\OcHelper */
  private $ocHelper = null;

  /**
   * Constructor.
   *
   * @param \Registry $registry
   */
  public function __construct($registry)
  {
    /** @noinspection PhpUndefinedClassInspection */
    parent::__construct($registry);
    if ($this->ocHelper === null) {
      // Load autoloader and then our helper that contains OC1 and OC2
      // shared code.
      require_once(DIR_SYSTEM . 'library/Siel/psr4.php');
      $this->ocHelper = new OcHelper($this->registry, 'OpenCart\OpenCart1');
    }
  }

  /**
   * Event handler that executes on the creation or update of an order.
   *
   * @param int $order_id
   */
  public function eventOrderUpdate($order_id)
  {
    $this->ocHelper->eventOrderUpdate($order_id);
  }
}
