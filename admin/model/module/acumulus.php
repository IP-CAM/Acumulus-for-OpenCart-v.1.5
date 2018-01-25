<?php

/** @noinspection PhpUndefinedClassInspection */
/**
 * Class ModelModuleAcumulus is the Acumulus admin and catalog site controller.
 */
class ModelModuleAcumulus extends Model
{
    /** @var \Siel\Acumulus\OpenCart\OpenCart1\Helpers\OcHelper */
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
            // Load autoloader, container and then our helper that contains
            // OC1, OC2 and OC3 shared code.
            require_once(DIR_SYSTEM . 'library/siel/acumulus/SielAcumulusAutoloader.php');
            SielAcumulusAutoloader::register();
            $container = new \Siel\Acumulus\Helpers\Container($this->getShopNamespace());
            $this->ocHelper = $container->getInstance('OcHelper', 'Helpers', array($this->registry, $container));
        }
    }

    /**
     * Returns the Shop namespace to use for this OC version.
     *
     * @return string
     *   The Shop namespace to use for this OC version.
     */
    protected function getShopNamespace()
    {
        $result = sprintf('OpenCart\OpenCart%1$u\OpenCart%1$u%2$u', substr(VERSION, 0, 1), substr(VERSION, 2, 1));
        return $result;
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
