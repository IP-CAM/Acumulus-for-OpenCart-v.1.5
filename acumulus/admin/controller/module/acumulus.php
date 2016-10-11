<?php

use Siel\Acumulus\OpenCart\Helpers\OcHelper;

/** @noinspection PhpUndefinedClassInspection */

/**
 * Class ControllerModuleAcumulus is the Acumulus admin site controller.
 *
 * @property \Response response
 */
class ControllerModuleAcumulus extends Controller
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
     * Install controller action, called when the module is installed.
     *
     * @return bool
     */
    public function install()
    {
        return $this->ocHelper->install();
    }

    /**
     * Uninstall function, called when the module is uninstalled by an admin.
     */
    public function uninstall()
    {
        $this->ocHelper->uninstall();
    }

    /**
     * Main controller action: show/process the basic settings form for this
     * module.
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
     * Explicit confirmation step to allow to retain the settings.
     *
     * The normal uninstall action will unconditionally delete all settings.
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
