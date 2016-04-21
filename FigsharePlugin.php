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
			HookRegistry::register('Templates::Author::Index::AdditionalItems', array(&$this, 'deposit_link'));
			HookRegistry::register('Templates::Author::Submit::Step4::OtherSubmissionTypes', array(&$this, 'supp_deposit_link'));
			$tm =& TemplateManager::getManager();
			$tm->assign("collectionsEnabled", true);
			define('FIGSHARE_PLUGIN_NAME', $this->getName());
		}
		return true;
	}

	function handleRequest($hookName, $args) {
		$page =& $args[0];
		$op =& $args[1];
		$sourceFile =& $args[2];

		if ($page == 'figshare') {
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

	function deposit_link($hookName, $args) {
		$output =& $args[2];

		$templateMgr =& TemplateManager::getManager();
		$currentJournal = $templateMgr->get_template_vars('currentJournal');
		$output .=  <<< EOF
		<br />
		<h2>Deposit Public Files to Figshare</h2>
		<p>You can deposit your published article files to Figshare.</p>
		<ul><li><a target="_blank" href="{$currentJournal->getUrl()}/figshare/published/">Deposit Published Files</a></li></ul><br />
EOF;

		
		return false;
	}

	function supp_deposit_link($hookName, $args) {
		$output =& $args[2];

		$templateMgr =& TemplateManager::getManager();
		$articleId = $templateMgr->get_template_vars('articleId');
		$currentJournal = $templateMgr->get_template_vars('currentJournal');
		$output .=  <<< EOF
		<br />
		<h2>Deposit Supplementary Files to Figshare</h2>
		<p>You can deposit your supplementary article files to Figshare. This way they will be given a citable DOI of their own.</p>
		<ul><li><a target="_blank" href="{$currentJournal->getUrl()}/figshare/?article_id={$articleId}">Deposit Supplementary Files</a></li></ul>
		<p>Otherwise, you can upload them below as normal.</p><div class="separator"></div>
EOF;

		
		return false;
	}

}
