<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Keep track of enabled modules so they can be cleaned up.
   *
   * @var array
   */
  protected $modules = array();

  /**
   * @Given the :module_name module is enabled
   */
  public function theModuleIsEnabled($module_name) {
    $module_list = $this->getDriver()->getCore()->getModuleList();
    if (!in_array($module_name, $module_list)) {
      /** @var \Drupal\Core\Extension\ModuleInstallerInterface $installer */
      $installer = \Drupal::service('module_installer');
      $installer->install([$module_name]);
      $this->modules[] = $module_name;
      $this->getDriver()->clearCache();
    }
  }

}
