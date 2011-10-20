<?php

// пишем комментарии
class EventsWriteModule extends BaseWriteModule {

	function write() {
		global $current_user;
		if (!$current_user->authorized)
			throw new Exception('Access Denied');
		switch (Request::$post['action']) {
			case 'comment_new':
				$this->addComment();
				break;
			case 'post_new':
				$this->addPost();
				break;
		}
	}

	function addPost() {
		global $current_user;
		if (!$current_user->id)
			return;
		$post = isset(Request::$post['post']) ? Request::$post['post'] : false;
		$post = prepare_review($post);
		if ($post) {
			$event = new Event();
			$event->event_PostAdd($current_user->id, $post);
			$event->push();
			ob_end_clean();
			header('Location: ' . Config::need('www_path') . '/me/wall/self');
			exit;
		}
	}

	function addComment() {
		global $current_user;
		if (!$current_user->id)
			return;
		$comment = isset(Request::$post['comment']) ? Request::$post['comment'] : false;
		$comment = trim(prepare_review($comment, ''));
		if (!$comment)
			throw new Exception('comment body expected');

		$post_id = Request::$post['id'];
		if ($post_id) {
			if (isset(Request::$post['comment_id']) && ($comment_id = Request::$post['comment_id'])) {
				MongoDatabase::addEventComment($post_id, $current_user->id, $comment, $comment_id);
			} else {
				MongoDatabase::addEventComment($post_id, $current_user->id, $comment);
			}
		}
	}

}