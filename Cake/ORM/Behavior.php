<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 3.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\ORM;

use Cake\Event\EventListener;

/**
 * Base class for behaviors.
 *
 * Behaviors allow you to simulate mixins, and create
 * reusable blocks of application logic, that can be reused across
 * several models. Behaviors also provide a way to hook into model
 * callbacks and augment their behavior.
 *
 * ### Mixin methods
 *
 * Behaviors can provide mixin like features by declaring public
 * methods. These methods will be accessible on the tables the
 * behavior has been added to.
 *
 * {{{
 * function doSomething($arg1, $arg2) {
 *   // do something
 * }
 * }}}
 *
 * Would be called like `$table->doSomething($arg1, $arg2);`.
 *
 * ## Callback methods
 *
 * Behaviors can listen to any events fired on a Table. By default
 * CakePHP provides a number of lifecycle events your behaviors can
 * listen to:
 *
 * - `beforeFind(Event $event, Query $query)`
 *   Fired before a query is converted into SQL.
 *
 * - `beforeDelete(Event $event, Entity $entity)`
 *   Fired before an entity is deleted.
 *
 * - `afterDelete(Event $event, Entity $entity)`
 *   Fired after an entity has been deleted. The entity parameter
 *   will contain the entity state from before it was deleted.
 *
 * - `beforeSave(Event $event, Entity $entity)`
 *   Fired before an entity is saved. In the case where
 *   multiple entities are being saved, one event will be fired
 *   for each entity.
 *
 * - `afterSave(Event $event, Entity $entity)`
 *   Fired after an entity is saved. The saved entity will be provided
 *   as a parameter.
 *
 * In addition to the core events, behaviors can respond to any
 * event fired from your Table classes including custom application
 * specific ones.
 *
 * You can set the priority of a behaviors callbacks by using the
 * `priority` setting when attaching a behavior. This will set the
 * priority for all the callbacks a behavior provides.
 *
 * ## Finder methods
 *
 * Behaviors can provide finder methods that hook into a Table's
 * find() method. Custom finders are a great way to provide preset
 * queries that relate to your behavior. For example a SluggableBehavior
 * could provide a find('slugged') finder. Behavior finders
 * are implemented the same as other finders. Any method
 * starting with `find` will be setup as a finder. Your finder
 * methods should expect the following arguments:
 *
 * {{{
 * findSlugged(Query $query, array $options = [])
 * }}}
 *
 *
 * @see Cake\ORM\Table::addBehavior()
 * @see Cake\Event\EventManager
 */
class Behavior implements EventListener {

/**
 * Contains configuration settings.
 *
 * @var array
 */
	protected $_settings = [];

/**
 * Constructor
 *
 * Does not retain a reference to the Table object. If you need this
 * you should override the constructor.
 *
 * @param Table $table The table this behavior is attached to.
 * @param array $settings The settings for this behavior.
 */
	public function __construct(Table $table, array $settings = []) {
		$this->_settings = $settings;
	}

/**
 * Read the settings being used.
 *
 * @return array
 */
	public function settings() {
		return $this->_settings;
	}

/**
 * Get the Model callbacks this behavior is interested in.
 *
 * By defining one of the callback methods a behavior is assumed
 * to be interested in the related event.
 *
 * Override this method if you need to add non-conventional event listeners.
 * Or if you want you behavior to listen to non-standard events.
 *
 * @return array
 */
	public function implementedEvents() {
		$eventMap = [
			'Model.beforeFind' => 'beforeFind',
			'Model.beforeSave' => 'beforeSave',
			'Model.afterSave' => 'afterSave',
			'Model.beforeDelete' => 'beforeDelete',
			'Model.afterDelete' => 'afterDelete',
		];
		$settings = $this->settings();
		$priority = isset($settings['priority']) ? $settings['priority'] : null;
		$events = [];

		foreach ($eventMap as $event => $method) {
			if (!method_exists($this, $method)) {
				continue;
			}
			if ($priority === null) {
				$events[$event] = $method;
			} else {
				$events[$event] = [
					'callable' => $method,
					'priority' => $priority
				];
			}
		}
		return $events;
	}

}