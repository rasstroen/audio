<?php

// модуль отвечает за отображение баннеров
class download_module extends BaseModule {

	function generateData() {
		global $current_user;
		/* @var $current_user CurrentUser */
		if (!$current_user->authorized)
			throw new Exception('Auth required');

		$filetype = Request::get(0);
		list($id_file, $id_book) = explode('_', Request::get(1));

		$book = Books::getInstance()->getByIdLoaded($id_book);
		/* @var $book Book */
		if (!$book->loaded)
			throw new Exception('Book doesn\'t exists');

		if (!$filetype || !$id_file || !$id_book) {
			throw new Exception('Wrong download url');
		}
		$realPath = getBookFilePath($id_file, $id_book, $filetype, Config::need('files_path'));

		if (!is_readable($realPath)) {
			throw new Exception('Sorry, file doesn\'t exists');
		}
		@ob_end_clean();
		$ft = Config::need('filetypes');
		header('Content-Disposition: attachment; filename="' . $book->getTitle(1) . '.' . $ft[$filetype]);
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		readfile($realPath);
		exit();
	}

}