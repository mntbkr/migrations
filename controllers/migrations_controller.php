<?php
/**
 * CakePHP Migrations
 *
 * Copyright 2009 - 2010, Cake Development Corporation
 *                        1785 E. Sahara Avenue, Suite 490-423
 *                        Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009 - 2010, Cake Development Corporation
 * @link      http://codaset.com/cakedc/migrations/
 * @package   plugins.migrations
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class MigrationsController extends AppController {

/**
 * Models used in the Controller
 * 
 * @var array
 * @access public
 */
	public $uses = array();

/**
 * MigrationVersion library instance
 * 
 * @var MigrationVersion
 * @access public
 */
	public $MigrationVersion;

/**
 * Plugins ignored by the Web ui
 * 
 * @var array
 * @access public
 */	
	public $ignoredPlugins = array('Migrations');
	
/**
 * Before filter callback
 * 
 * @return void
 * @access public
 */
	public function beforeFilter() {
		if (App::import('Lib', 'Migrations.MigrationVersion')) {
			$this->MigrationVersion = new MigrationVersion();
		}
		parent::beforeFilter();
	}

/**
 * Admin index action
 * List all the available migrations along with their current status
 * 
 * @return void
 * @access public
 */
	public function admin_index() {
		$plugins = $this->_getPlugins();
		$mapping = array();
		foreach($plugins as $plugin) {
			try {
				$mapping[$plugin] = $this->MigrationVersion->getMapping($plugin);				
			} catch (MigrationVersionException $e) {
				$this->log($e->getMessage());
			}
		}
		$this->set('mapping', $mapping);
	}

/**
 * Admin run action
 * Migrate a plugin to a given migration, or run several migrations in a row
 * 
 * @param string $plugin Name of the plugin to run migrations for
 * @param string $version Version of the migration to migrate to. Leave null to run all migrations
 * @return void
 * @access public
 */
	public function admin_run($plugin = null, $version = null) {
		if (!is_null($plugin)) {
			$toRun = array(compact('plugin', 'version'));
		} elseif (!empty($this->data)) {
			$toRun = array();
			foreach($this->data as $plugin => $version) {
				$toRun[] = array('plugin' => $plugin, 'version' => $version['version']);
			}
		}
		
		if (empty($toRun)) {
			$this->Session->setFlash(__d('migrations', 'No migration to run', true));
		} else {
			try {
				foreach($toRun as $migration) {
					$this->MigrationVersion->run(array(
						'type' => $migration['plugin'],
						'version' => $migration['version']));
				}
				$this->Session->setFlash(__d('migrations', 'All migrations have completed.', true));
			} catch (MigrationException $e) {
				$out = __d('migrations', 'An error occurred when processing the migration:', true);
				$out .= '<br />' . sprintf(__d('migrations', 'Migration: %s', true), $e->Migration->info['name']);
				$out .= '<br />' . sprintf(__d('migrations', 'Error: %s', true), $e->getMessage());
				$this->Session->setFlash($out);
			}
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Returns a list of plugin managed by the web ui
 * 
 * @return array List of plugins
 * @access protected
 */
	protected function _getPlugins() {
		$ignored = empty($this->ignoredPlugins) ? array() : $this->ignoredPlugins;
		return array_diff(array_merge(array('app'), App::objects('plugin')), $ignored);
	}

}
?>