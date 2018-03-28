<?php

require_once 'google-api-php-client/vendor/autoload.php';
set_include_path(get_include_path() . PATH_SEPARATOR . 'google-api-php-client/vendor');
require_once 'eventgooglecalendersync.civix.php';
use CRM_Eventgooglecalendersync_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function eventgooglecalendersync_civicrm_config(&$config) {
  _eventgooglecalendersync_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function eventgooglecalendersync_civicrm_xmlMenu(&$files) {
  _eventgooglecalendersync_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function eventgooglecalendersync_civicrm_install() {
  _eventgooglecalendersync_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function eventgooglecalendersync_civicrm_postInstall() {
  _eventgooglecalendersync_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function eventgooglecalendersync_civicrm_uninstall() {
  CRM_Core_DAO::executeQuery("
    DROP TABLE IF EXISTS civicrm_google_event
  ");
  _eventgooglecalendersync_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function eventgooglecalendersync_civicrm_enable() {
  _eventgooglecalendersync_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function eventgooglecalendersync_civicrm_disable() {
  _eventgooglecalendersync_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function eventgooglecalendersync_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _eventgooglecalendersync_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function eventgooglecalendersync_civicrm_managed(&$entities) {
  _eventgooglecalendersync_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function eventgooglecalendersync_civicrm_caseTypes(&$caseTypes) {
  _eventgooglecalendersync_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function eventgooglecalendersync_civicrm_angularModules(&$angularModules) {
  _eventgooglecalendersync_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function eventgooglecalendersync_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _eventgooglecalendersync_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_post().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function eventgooglecalendersync_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if (strtolower($objectName) == 'event' && in_array($op, ['create', 'edit'])) {
    $createUpdateGCal = CRM_Core_Smarty::singleton()->get_template_vars('createUpdateGCal');
    if (!empty($createUpdateGCal)) {
      CRM_Eventgooglecalendersync_Utils::createGCalEvent($objectRef->id);
      CRM_Core_Smarty::singleton()->assign('createUpdateGCal', '');
    }
  }
}

/**
 * Implements hook_civicrm_pre().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pre
 */
function eventgooglecalendersync_civicrm_pre($op, $objectName, $id, &$params) {
  if (strtolower($objectName) == 'event' && in_array($op, ['create', 'edit'])) {
    if (!empty($params['start_date']) || !empty($params['end_date'])) {
      $createUpdateGCal = FALSE;
      if ($op == 'create') {
        if (!empty($params['start_date']) || !empty($params['end_date'])) {
          $createUpdateGCal = TRUE;
        }
      }
      else {
        $events = civicrm_api3('event', 'getsingle', [
          'id' => $id,
          'return' => array('start_date', 'end_date')
        ]);
        // TODO::
        // check date diff
        // add to $eventStartEndDates
        $createUpdateGCal = TRUE;
      }
      if (!empty($createUpdateGCal)) {
        CRM_Core_Smarty::singleton()->assign('createUpdateGCal', $createUpdateGCal);
      }
    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
 */
function eventgooglecalendersync_civicrm_navigationMenu(&$menu) {
  _eventgooglecalendersync_civix_insert_navigation_menu($menu, 'Administer/System Settings', [
    'label' => ts('Googlecal Settings', ['domain' => 'org.civicrm.eventgooglecalendersync']),
    'name' => 'Googlecal_Settings',
    'url' => CRM_Utils_System::url('civicrm/googlecal/settings', 'reset=1', TRUE),
    'active' => 1,
    'operator' => NULL,
    'permission' => 'administer CiviCRM',
  ]);
}

/**
 * Implements hook_civicrm_alterSettingsMetaData().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsMetaData
 *
 */
function eventgooglecalendersync_civicrm_alterSettingsMetaData(&$settingsMetadata, $domainID, $profile) {
  $settingsMetadata['gc_client_key'] = [
    'group_name' => 'Googlecal Preferences',
    'group' => 'core',
    'name' => 'gc_client_key',
    'type' => 'String',
    'html_type' => 'text',
    'quick_form_type' => 'Element',
    'default' => '',
    'add' => '4.7',
    'title' => ts('Client Key'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => '',
    'html_attributes' => [
      'size' => 48,
    ],
  ];
  $settingsMetadata['gc_client_secret'] = [
    'group_name' => 'Googlecal Preferences',
    'group' => 'core',
    'name' => 'gc_client_secret',
    'type' => 'String',
    'html_type' => 'text',
    'quick_form_type' => 'Element',
    'default' => '',
    'add' => '4.7',
    'title' => ts('Client Secret'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => '',
    'html_attributes' => [
      'size' => 48,
    ],
  ];
  $settingsMetadata['gc_domain_name'] = [
    'group_name' => 'Googlecal Preferences',
    'group' => 'core',
    'name' => 'gc_domain_name',
    'type' => 'String',
    'html_type' => 'text',
    'quick_form_type' => 'Element',
    'default' => '',
    'add' => '4.7',
    'title' => ts('Domain names'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => '',
    'html_attributes' => [
      'size' => 48,
    ],
  ];
  $settingsMetadata['gc_access_token'] = [
    'group_name' => 'Googlecal Preferences',
    'group' => 'core',
    'name' => 'gc_access_token',
    'type' => 'String',
    'html_type' => 'hidden',
    'quick_form_type' => 'Element',
    'default' => '',
    'add' => '4.7',
    'title' => ts('Access Token'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => '',
    'html_attributes' => [
      'size' => 48,
    ],
  ];
}
