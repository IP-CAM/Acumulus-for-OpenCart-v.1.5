<?php

/** @noinspection PhpUndefinedClassInspection */

/**
 * Class ControllerModuleAcumulus is the Acumulus admin site controller.
 *
 * @property \Response response
 */
class ControllerModuleAcumulus extends Controller
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
        /** @noinspection PhpUndefinedClassInspection */
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
     * Install controller action, called when the module is installed.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function install()
    {
        return $this->ocHelper->install();
    }

    /**
     * Uninstall function, called when the module is uninstalled by an admin.
     *
     * @throws \Exception
     */
    public function uninstall()
    {
        $this->ocHelper->uninstall();
    }

    /**
     * Main controller action: show/process the basic settings form for this
     * module.
     *
     * @throws \Exception
     */
    public function index()
    {
        $this->ocHelper->config();
        $this->renderForm();
    }

    /**
     * Main controller action: show/process the advanced settings form for this
     * module.
     */
    public function advanced()
    {
        $this->ocHelper->advancedConfig();
        $this->renderForm();
    }

    /**
     * Main controller action: show/process the batch form for this module.
     */
    public function batch()
    {
        $this->ocHelper->batch();
        $this->renderForm();
    }

    /**
     * Controller action: show/process the register form for this module.
     */
    public function register()
    {
        $this->ocHelper->register();
        $this->renderForm();
    }

    /**
     * Explicit confirmation step to allow to retain the settings.
     *
     * The normal uninstall action will unconditionally delete all settings.
     *
     * @throws \Exception
     */
    public function confirmUninstall()
    {
        $this->ocHelper->confirmUninstall();
        $this->renderForm();
    }

    protected function renderForm()
    {
        // Set the template and its data.
        $this->data = $this->ocHelper->data;
        $this->template = 'module/acumulus-form.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
        );

        // Send the output.
        $this->response->setOutput($this->render());
    }
}
