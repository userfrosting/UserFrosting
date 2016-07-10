<?php

namespace UserFrosting;

/**
 * The Breadcrumbs class, which manage the breadcrumbs in the Application
 *
 * @package Breadcrumbs
 * @author Louis Charette
 * @link http://www.userfrosting.com/
 */
class Breadcrumbs {

    /**
     * @var items[] An array of item in the breadcrumbs list.
     */
    protected $items = [];

    /**
     * Create a new Breadcrumbs object.
     *
     */
    public function __construct($app) {

		//We need app for translation later
        $this->_app = $app;

	    //TODO: Add site setting to enabled this or not
	    $this->addItem("SITE_INDEX", "/");
    }

    /**
     * Add an item to the breadcrumbs list.
     *
     * This method does NOT modify the database.  Call `store` to persist to database.
     * @param string $name The name of the page that will be displayed.
     * @param string $uri optional The uri this entry will point to.
     * @param bool $uri optional If this entry is active or not
     * @return null
     */
	public function addItem($name, $uri = "", $active = true){

		//Translate the name. Doing this here allow to pass or not translation keys
		$n = $this->_app->translator->translate($name);

		//Before we add the new one, any item are not the last one
		foreach ($this->items as $key => $value) {
			$this->items[$key]["last"] = false;
		}

		//Add the item to the array
		$this->items[] = array(
			"title" => $n,
			"uri" => $uri,
			"active" => $active,
			"last" => true
		);
	}

    /**
     * Determine if the property for this object exists.
     *
     * This method does NOT modify the database.  Call `store` to persist to database.
     * @param string $name The name of the page that will be displayed.
     * @param string $uri optional The uri this entry will point to.
     * @return array[items] Array of all the entry in the Breadcrumbs list
     */
	public function getItems() {
		return $this->items;
	}

}
