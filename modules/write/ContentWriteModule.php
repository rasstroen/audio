<?php

class ContentWriteModule extends BaseWriteModule {

	function write() {
		if ($id = Request::post('id')) {
			$this->edit();
		} else {
			$this->enew();
		}
	}

	function getContentFilePath($id, $pf = '') {
		if ($pf == 'medium') {
			$ext = 'gif';
		} else {
			$ext = 'gif';
		}
		if ($pf)
			$pf = '_' . $pf;
		$filename = Config::need('static_path') . DIRECTORY_SEPARATOR . 'upload/pictures/' . $id . $pf . '.' . $ext;
		return $filename;
	}

	function enew() {
		$title = Request::post('title', '');
		$url = Request::post('url', '');
		$tags = Request::post('tags', '');
		Database::query('START TRANSACTION');
		$query = 'INSERT INTO `content_pictures` SET
            `title`=' . Database::escape($title) . ',
            `time`=' . time();
		Database::query($query);
		$content_id = Database::lastInsertId();
		if (!$content_id)
			throw new Exception('DB Error on insert');

		$filename = $this->getContentFilePath($content_id, 'small');
		$filename_big = $this->getContentFilePath($content_id, 'big');
		$filename_medium = $this->getContentFilePath($content_id, 'medium');
		$filename_small = $this->getContentFilePath($content_id, 'small');
		if ($url) {
			$data = file_get_contents($url);
			file_put_contents($filename, $data);
			if (!resizeImagick($filename, $filename_big, 1000, 1000)) {
				throw new Exception('cant copy file to ' . $filename_big);
			}
			if (!resizeImagick($filename, $filename_medium, 600, 600)) {
				throw new Exception('cant copy file to ' . $filename_medium);
			}
			if (!resizeImagick($filename, $filename_small, 250, 250)) {
				throw new Exception('cant copy file to ' . $filename_small);
			}
		} else
		if (isset($_FILES['file']) && $_FILES['file']['tmp_name']) {
			$filename = $this->getContentFilePath($content_id);
			if (!resizeImagick($_FILES['file']['tmp_name'], $filename_big, 1000, 1000)) {
				throw new Exception('cant copy file to ' . $filename_big);
			}
			if (!resizeImagick($_FILES['file']['tmp_name'], $filename_medium, 600, 600)) {
				throw new Exception('cant copy file to ' . $filename_medium);
			}
			if (!resizeImagick($_FILES['file']['tmp_name'], $filename_small, 250, 250)) {
				throw new Exception('cant copy file to ' . $filename_small);
			}
		} else
			throw new Exception('No any file');


		$tags_prepared = array();
		if ($tags) {
			$tags = explode(',', $tags);
			foreach ($tags as $tag) {
				if (trim($tag))
					$tags_prepared[trim($tag)] = Database::escape(trim($tag));
			}
		}
		$to_insert_tag = array();
		$tags_existing = array();
		if (count($tags_prepared)) {
			$query = 'SELECT * FROM `tags` WHERE `title` IN (' . implode(',', $tags_prepared) . ')';
			$tags_existing_db = Database::sql2array($query, 'title');
			foreach ($tags_prepared as $tag => $prepared) {
				if (!isset($tags_existing_db[$tag])) {
					$to_insert_tag[$tag] = $prepared;
				} else {
					$tags_existing[$tag] = $tags_existing_db[$tag]['id'];
				}
			}
		}

		if (count($to_insert_tag)) {
			foreach ($to_insert_tag as $tag) {
				Database::query('INSERT INTO `tags` SET `title`=' . $tag);
				$tags_existing[$tag] = Database::lastInsertId();
			}
		}

		if (count($tags_existing)) {
			$q = array();
			foreach ($tags_existing as $tag => $id) {
				$q[] = '(' . $content_id . ',' . $id . ')';
			}
			$query = 'INSERT INTO `content_pictures_tags`(`id_content_picture`,`id_tag`) VALUES ' . implode(',', $q);
			Database::query($query);
		}
		Database::query('COMMIT');
		if ($content_id) {
			header('Location: /pictures/' . $content_id);
			exit;
		}
	}

}