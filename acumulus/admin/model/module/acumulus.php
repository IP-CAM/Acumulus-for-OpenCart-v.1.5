<?php

use Siel\Acumulus\OpenCart\Helpers\OcHelper;

/** @noinspection PhpUndefinedClassInspection */
/**
 * Class ModelModuleAcumulus is the Acumulus admin and catalog site controller.
 */
class ModelModuleAcumulus extends Model
{
    /** @var \Siel\Acumulus\OpenCart\Helpers\OcHelper */
    private $ocHelper = null;

    /**
     * Constructor.
     *
     * @param \Registry $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        if ($this->ocHelper === null) {
            // Load autoloader and then our helper that contains OC1 and OC2
            // and admin and catalog shared code.
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
