<?php

/**
 *
 * Plugin for submitting additional files to Figshare
 * Written by Andy Byers, Ubiquity Press
 * As part of the Streamingling Deposit JISC Project 
 *
 */

class FigshareDAO extends DAO {
	function create_figshare_file($params) {
		$sql = <<< EOF
			INSERT INTO figshare_files
			(file_id, article_id, figshare_id, title, description, type, status, doi)
			VALUES
			(?, ?, ?, ?, ?, ?, ?, ?)
EOF;
		$commit = $this->update($sql, $params);

		return $commit;
	}

	function fetch_figshare_articles($article_id) {
		$sql = <<< EOF
			SELECT fig.*, af.original_file_name, af.date_uploaded FROM figshare_files AS fig
			JOIN article_files AS af ON af.file_id = fig.file_id
			WHERE fig.article_id = ?
EOF;
		return $this->retrieve($sql, array($article_id));
	}

	function delete_figshare_file($figshare_file_id) {
		$sql = <<< EOF
			DELETE FROM figshare_files
			WHERE id = ?
EOF;
		$commit = $this->update($sql, array($figshare_file_id));

		return $commit;
	}
}

