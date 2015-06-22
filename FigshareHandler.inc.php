<?php

import('classes.handler.Handler');
require_once('FigshareDAO.inc.php');

function redirect($url) {
	header("Location: ". $url); // http://www.example.com/"); /* Redirect browser */
	/* Make sure that code below does not get executed when we redirect. */
	exit;
}

function raise404($msg='404 Not Found') {
	header("HTTP/1.0 404 Not Found");
	fatalError($msg);
	return;
}

function clean_string($v) {
	// strips non-alpha-numeric characters from $v	
	return preg_replace('/[^\-a-zA-Z0-9]+/', '',$v);
}

class FigshareHandler extends Handler {

	public $dao = null;

	function FigshareHandler() {
		parent::Handler();
		$this->dao = new FigshareDAO();
	}
	
	// utils
	
	/* sets up the template to be rendered */
	function display($fname, $page_context=array()) {
		// setup template
		AppLocale::requireComponents(LOCALE_COMPONENT_OJS_MANAGER, LOCALE_COMPONENT_PKP_MANAGER);
		parent::setupTemplate();
		
		// setup template manager
		$templateMgr =& TemplateManager::getManager();
		
		// default page values
		$context = array(
			"page_title" => "Figshare"
		);
		foreach($page_context as $key => $val) {
			$context[$key] = $val;
		}

		$plugin =& PluginRegistry::getPlugin('generic', COLLECTION_PLUGIN_NAME);
		$tp = $plugin->getTemplatePath();
		$context["template_path"] = $tp;
		$context["article_select_template"] = $tp . "article_select_snippet.tpl";
		$context["article_pagination_template"] = $tp . "article_pagination_snippet.tpl";
		$context["disableBreadCrumbs"] = true;
		$templateMgr->assign($context); // http://www.smarty.net/docsv2/en/api.assign.tpl

		// render the page
		$templateMgr->display($tp . $fname);
	}


	//
	// views
	//
	
	/* handles requests to:
		/figshare/
		/figshare/index/
	*/
	function index($args, &$request) {
	
		$context = array(
			"page_title" => "Figshare Uploader",
		);
		$this->display('index.tpl', $context);
	}

	/* handles requests to:
		/figshare/submission/
		/figshare/<submission_id>/
	*/
	function submission($args, &$request) {

		$article_id = clean_string(array_shift($args));
		$this->validate($request, $article_id, 4);

		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($article_id);
		
		$context = array(
			"page_title" => "Figshare Uploader",
			"article" => $article,
		);
		$this->display('index.tpl', $context);
	}

	/**
	 * Validation check for submission.
	 * Checks that article ID is valid, if specified.
	 * @param $articleId int
	 * @param $step int
	 */
	function validate($request, $articleId = null, $step = false, $reason = null) {
		parent::validate($reason);
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$user =& $request->getUser();
		$journal =& $request->getJournal();

		if ($step !== false && ($step < 1 || $step > 5 || (!$articleId && $step != 1))) {
			$request->redirect(null, null, 'submit', array(1));
		}

		$article = null;

		// Check that article exists for this journal and user and that submission is incomplete
		if ($articleId) {
			$article =& $articleDao->getArticle((int) $articleId);
			if (!$article || $article->getUserId() !== $user->getId() || $article->getJournalId() !== $journal->getId() || ($step !== false && $step > $article->getSubmissionProgress())) {
				$request->redirect(null, null, 'submit');
			}
		}

		$this->article =& $article;
		return true;
	}
	
}

?>