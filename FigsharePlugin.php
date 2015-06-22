<?php

/**
 *
 * Plugin for submitting additional files to Figshare
 * Written by Andy Byers, Ubiquity Press
 * As part of the Streamingling Deposit JISC Project 
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');
require_once('FigshareDAO.inc.php');

class FigsharePlugin extends GenericPlugin {
	function register($category, $path) {
		if(!parent::register($category, $path)) {
			//debug("failed to register!");
			return false;
		}
		if($this->getEnabled()) {
			HookRegistry::register("LoadHandler", array(&$this, "handleRequest"));
			$tm =& TemplateManager::getManager();
			$tm->assign("collectionsEnabled", true);
			define('COLLECTION_PLUGIN_NAME', $this->getName());
		}
		return true;
	}

	function handleRequest($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];
		$sourceFile =& $args[2];

		if ($page == 'figshare' || $page == 'articles') {
			$this->import('FigshareHandler');
			Registry::set('plugin', $this);
			define('HANDLER_CLASS', 'FigshareHandler');
			return true;
		}
		return false;
	}

	function getDisplayName() {
		return "Figshare Files";
	}
	
	function getDescription() {
		return "Allows files to be uploaded into Figshare";
	}
	
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}

}
