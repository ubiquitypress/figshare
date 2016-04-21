<?php

/**
 *
 * Plugin for submitting additional files to Figshare
 * Written by Andy Byers, Ubiquity Press
 * As part of the Streamingling Deposit JISC Project 
 *
 */


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

function login_required($user) {
	if ($user === NULL) {
		redirect($journal->getUrl() . '/login/signIn?source=' . $_SERVER['REQUEST_URI']);
	}
}

function login_owner_required($request, $article_id) {
	$user =& $request->getUser();
	$journal =& $request->getJournal();
	$articleDao =& DAORegistry::getDAO('ArticleDAO');
	$article =& $articleDao->getArticle($article_id);

	if (!$article) {
		redirect($journal->getUrl() . '/user/');
	} elseif ($user == NULL){
		redirect($journal->getUrl() . '/login/signIn?source=' . $_SERVER['REQUEST_URI']);
	} elseif ($user->getId() != $article->getUserId()) {
		echo 'article and user dont match';
		redirect($journal->getUrl() . '/user/');
	} else {
		return True;
	}
}

class FigshareHandler extends Handler {

	public $dao = null;

	function FigshareHandler() {
		parent::Handler();
		$this->dao = new FigshareDAO();
	}
	
	// utils

	function file_path($articleId, $file_name) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($articleId);
		$journalId = $article->getJournalId();
		return Config::getVar('files', 'files_dir') . '/journals/' . $journalId .  '/articles/' . $articleId . '/supp/' . $file_name;
	}

	function file_public_path($articleId, $file_name) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($articleId);
		$journalId = $article->getJournalId();
		return Config::getVar('files', 'files_dir') . '/journals/' . $journalId .  '/articles/' . $articleId . '/public/' . $file_name;
	}
	
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

		$plugin =& PluginRegistry::getPlugin('generic', FIGSHARE_PLUGIN_NAME);
		$tp = $plugin->getTemplatePath();
		$context["template_path"] = $tp;
		$context["article_select_template"] = $tp . "article_select_snippet.tpl";
		$context["article_pagination_template"] = $tp . "article_pagination_snippet.tpl";
		$context["disableBreadCrumbs"] = true;
		$templateMgr->assign($context); // http://www.smarty.net/docsv2/en/api.assign.tpl

		// render the page
		$templateMgr->display($tp . $fname);
	}

	/* Makes a call to the figshare api */
	function api_call($data, $url, $method="POST", $file=false) {
		$consumer_key = "93tNF6iUvZHlHrjhxruI2g";
		$consumer_secret = "yftm1PU6TYNhwProLHTWqw";

		$oauth = new OAuth($consumer_key, $consumer_secret);
		$oauth->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);


		$OA_header = $oauth->getRequestHeader($method, $url);
		if ($file != false){
			$headers = array("Content-Type: multipart/form-data", "Authorization: $OA_header");
		} else {
			$headers = array("Content-Type: application/json", "Authorization: $OA_header");
		}


		$ch = curl_init();

		if ($method == 'PUT') {
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS    ,$data);
		} else {
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		}

		return json_decode(curl_exec($ch));
	}

	function v2_call($path, $data=null, $method=null) {
		// This function takes a personal token and authenticates the user with figshare.
		//echo $_SESSION['token'];
		$url = "https://api.figshare.com/v2/{$path}?access_token={$_SESSION['token']}";

		$ch = curl_init();

		if ($method == "POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
		}

		if ($data != null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = json_decode(curl_exec($ch));

		return $response;

	}

	function upload_call($url, $data=null, $method=null) {
		$ch = curl_init();

		if ($method == "POST") {
			curl_setopt($ch, CURLOPT_POST, 1);
		} elseif ($method == "PUT") {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		}

		if ($data != null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = json_decode(curl_exec($ch));

		return $response;
	}

	//
	// views
	//
	
	/* handles requests to:
		/figshare/
		/figshare/index/
	*/
	function index($args, &$request) {
		$article_id = $_GET["article_id"];
		$journal =& $request->getJournal();
		$message = null;

		login_owner_required($request, $article_id);

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$_SESSION['token'] = $_POST["token"];

			$check = $this->v2_call('account', null, null);

			if ($check->active == 1) {
			 	redirect($journal->getUrl() . '/figshare/submission/' . $article_id);
			} else {
				$message = 'Either your token is incorrect, or your account is inactive.';
			}
		}
	
		$context = array(
			"page_title" => "Figshare Uploader",
			"message" => $message,
		);
		$this->display('token.tpl', $context);
	}

	/* handles requests to:
		/figshare/submission/
		/figshare/<submission_id>/
	*/
	function submission($args, &$request) {
		$article_id = clean_string(array_shift($args));
		$journal =& $request->getJournal();
		
		login_owner_required($request, $article_id);
		$this->validate($request, $article_id, 4);

		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($article_id);

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			import('classes.file.ArticleFileManager');
			$article_file_manager = new ArticleFileManager($article_id);
			$file_id = $article_file_manager->uploadSuppFile('uploadFigFile');

			$file = $article_file_manager->getFile($file_id);
			// create the figshare record
			$data = json_encode(array('title'=>$_POST["title"], 'description'=>$_POST["description"], 'defined_type'=>$_POST["type"], 'tags'=>explode(",", $_POST["tags"]), 'categories'=>array(2)));
			$figshare_article = $this->v2_call($path='account/articles', $data, $method="POST");
			$figshare_article_id = end(explode("/", $figshare_article->location));

			//$reserve_doi_response = $this->v2_call($path='account/articles/' . $figshare_article_id . '/reserve_doi', $method="POST");

			// initiate file upload using figshare's wierd file service.
			$file_path = $this->file_path($article_id, $file->getFileName());
			$data = json_encode(array('name' => $file->getFileName(), 'size' => filesize($file_path)));
			$file_location = $this->v2_call($path='account/articles/' . $figshare_article_id . '/files', $data=$data, $method="POST");
			$figshare_file_id = end(explode("/", $file_location->location));

			$file_info = $this->v2_call($path='account/articles/' . $figshare_article_id . '/files/' . $figshare_file_id);
			$file_parts = $this->upload_call($url=$file_info->upload_url);

			foreach ($file_parts->parts as $part) {
				$file_data = file_get_contents($file_path, NULL, NULL, $part->startOffset, $part->endOffset);
				$data = array('file' => '@' . $file_data);
				$part_url = $file_info->upload_url . '/' . $part->partNo;
				$upload = $this->upload_call($url=$part_url, $data=$data, $method="PUT");
			}

			$mark_as_complete = $this->v2_call($path='account/articles/' . $figshare_article_id . '/files/' . $figshare_file_id, $method="POST");
			$publish_article = $this->v2_call($path='account/articles/' . $figshare_article_id . '/publish', $method="POST");

			$params = array($file_id, $article_id, $figshare_article_id, $_POST["title"], $_POST["description"], $_POST["type"], 'draft', '');
			//$params[] = '';//$reserve_doi_response->{'doi'};
			$this->dao->create_figshare_file($params);

		} elseif (isset($_GET['remove_file']) && isset($_GET['ojs_file'])) {
			$figshare_file_id = $_GET['remove_file'];
			$ojs_file_id = $_GET['ojs_file'];

			import('classes.file.ArticleFileManager');
			$article_file_manager = new ArticleFileManager($article_id);
			$article_file_manager->deleteFile($ojs_file_id);
			$this->dao->delete_figshare_file($figshare_file_id);
			redirect($journal->getUrl() . '/figshare/submission/' . $article_id);
		}

		$figshare_files =& $this->dao->fetch_figshare_articles($article_id);
		
		$context = array(
			"page_title" => "Figshare Uploader for article: " . $article->getArticleTitle(),
			"article" => $article,
			"figshare_files" => $figshare_files,
		);
		$this->display('index.tpl', $context);
	}

	function published($args, &$request) {
		$journal =& $request->getJournal();
		$user =& $request->getUser();
		login_required($user);

		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$submissions = $authorSubmissionDao->getAuthorSubmissions($user->getId(), $journal->getId(), False, null, null, null);

		$article_id = $_GET['article_id'];

		if ($article_id) {
			$articleDao =& DAORegistry::getDAO('ArticleDAO');
			$article =& $articleDao->getArticle($article_id);
			$galleys = $this->dao->get_article_galleys($article->getId());

			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$galley_files = $_POST['galleys'];

				var_dump($galley_files);

				import('classes.file.ArticleFileManager');
				$article_file_manager = new ArticleFileManager($article_id);

				$data = json_encode(array('title'=>$article->getArticleTitle(), 'description'=>$article->getArticleAbstract(), 'defined_type'=>'paper', 'categories'=>array(2)));
				$figshare_article = $this->v2_call($path='account/articles', $data, $method="POST");
				$figshare_article_id = end(explode("/", $figshare_article->location));

				$reserve_doi_response = $this->v2_call($path='account/articles/' . $figshare_article_id . '/reserve_doi', $method="POST");

				foreach ($galley_files as $key => $file_id) {
					$file = $article_file_manager->getFile($file_id);					
					// initiate file upload using figshare's wierd file service.
					$file_path = $this->file_public_path($article_id, $file->getFileName());
					$data = json_encode(array('name' => $file->getFileName(), 'size' => filesize($file_path)));
					$file_location = $this->v2_call($path='account/articles/' . $figshare_article_id . '/files', $data=$data, $method="POST");
					$figshare_file_id = end(explode("/", $file_location->location));

					$file_info = $this->v2_call($path='account/articles/' . $figshare_article_id . '/files/' . $figshare_file_id);
					$file_parts = $this->upload_call($url=$file_info->upload_url);

					foreach ($file_parts->parts as $part) {
						$file_data = file_get_contents($file_path, NULL, NULL, $part->startOffset, $part->endOffset);
						$data = array('file' => '@' . $file_data);
						$part_url = $file_info->upload_url . '/' . $part->partNo;
						$upload = $this->upload_call($url=$part_url, $data=$data, $method="PUT");
					}

					$mark_as_complete = $this->v2_call($path='account/articles/' . $figshare_article_id . '/files/' . $figshare_file_id, $method="POST");
					$publish_article = $this->v2_call($path='account/articles/' . $figshare_article_id . '/publish', $method="POST");


				}

				$this->dao->add_article_setting(array($article_id, 'figshare_depost', 1, 'bool'));
			}
		}

		$context = array(
			"page_title" => "Figshare Deposit",
			"submissions" => $submissions,
			"article" => $article,
			"galleys" => $galleys,
		);
		$this->display('published.tpl', $context);
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