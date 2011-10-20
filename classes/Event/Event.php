<?php

class Event {
	const EVENT_BOOKS_ADD = 1;
	const EVENT_BOOKS_EDIT = 2;
	const EVENT_BOOKS_ADD_SHELF = 3;
	const EVENT_BOOKS_REVIEW_ADD = 10;
	const EVENT_BOOKS_RATE_ADD = 11;
	const EVENT_POST = 21;
	const EVENT_LOVED_ADD_AUTHOR = 31;
	const EVENT_LOVED_ADD_BOOK = 32;
	const EVENT_LOVED_ADD_GENRE = 33;
	const EVENT_LOVED_ADD_SERIE = 34;
	


	public static $event_type = array(
	    self::EVENT_BOOKS_ADD => 'books-add',
	    self::EVENT_POST => 'post',
	    self::EVENT_BOOKS_EDIT => 'books-edit',
	    self::EVENT_BOOKS_REVIEW_ADD => 'books-review-new',
	    self::EVENT_BOOKS_RATE_ADD => 'books-rate-new',
	    self::EVENT_LOVED_ADD_AUTHOR => 'loved-add-author',
	    self::EVENT_LOVED_ADD_BOOK => 'loved-add-book',
	    self::EVENT_LOVED_ADD_GENRE => 'loved-add-genre',
	    self::EVENT_LOVED_ADD_SERIE => 'loved-add-serie',
	    self::EVENT_BOOKS_ADD_SHELF => 'books-add-shelf',
	);
	public $data = array();

	function __construct() {
		$this->data = array('time' => time());
	}
	
	public function event_addShelf($id_user, $id_book, $id_shelf){
		$this->canPushed = true;
		$this->setUser($id_user);
		$this->setBook($id_book);
		$this->setShelf($id_shelf);
		$this->setType(self::EVENT_BOOKS_ADD_SHELF);
	}

	public function event_LovedAdd($id_user, $id_target, $target_type) {
		$this->canPushed = true;
		switch ($target_type) {
			case 'author':
				$this->setAuthor($id_target);
				$this->setType(self::EVENT_LOVED_ADD_AUTHOR);
				break;
			case 'book':
				$this->setBook($id_target);
				$this->setType(self::EVENT_LOVED_ADD_BOOK);
				break;
			case 'genre':
				$this->setGenre($id_target);
				$this->setType(self::EVENT_LOVED_ADD_GENRE);
				break;
			case 'serie':
				$this->setSerie($id_target);
				$this->setType(self::EVENT_LOVED_ADD_SERIE);
				break;
			default:
				throw new Exception('event_LovedAdd illegal type#' . $target_type);
		}
		$this->setUser($id_user);
	}

	public function event_BooksAdd($id_user, $id_book) {
		$this->canPushed = true;
		$this->setBook($id_book);
		$this->setType(self::EVENT_BOOKS_ADD);
		$this->setUser($id_user);
	}

	public function event_PostAdd($id_user, $body) {
		$this->canPushed = true;
		$this->setType(self::EVENT_POST);
		$this->setUser($id_user);
		$this->setBody($body);
		$this->setShortBody($body);
	}

	public function event_BookReviewAdd($id_user, $data) {
		$this->canPushed = true;
		$this->setBook($data['target_id']);
		$this->setMark($data['rate'] - 1);
		$this->setBody($data['comment']);
		$reviewHTML = close_dangling_tags(_substr($data['comment'], 200));
		$this->setReview($reviewHTML);
		$this->setType(self::EVENT_BOOKS_REVIEW_ADD);
		$this->setUser($id_user);
	}

	public function event_BookRateAdd($id_user, $data) {
		$this->canPushed = true;
		$this->setBook($data['target_id']);
		$this->setMark($data['rate'] - 1);
		$reviewHTML = close_dangling_tags(_substr($data['comment'], 200));
		$this->setType(self::EVENT_BOOKS_RATE_ADD);
		$this->setUser($id_user);
	}

	// выставляем текст
	private function setBody($body) {
		$this->data['body'] = $body;
	}

	// выставляем текст сниппета
	private function setShortBody($body) {
		$this->data['short'] = close_dangling_tags(trim(_substr($body, 900)));
	}

	// выставляем тип
	private function setTargetType($type) {
		$this->data['target_type'] = $type;
	}

	// выставляем тип
	private function setUser($id) {
		$this->data['user_id'] = $id;
	}

	// выставляем тип
	private function setReview($html) {
		$this->data['review'] = $html;
	}

	// оценка
	private function setMark($mark) {
		if ($mark)
			$this->data['mark'] = $mark;
	}

	// выставляем тип
	private function setType($type) {
		$this->data['type'] = $type;
	}

	// цепляем книгу
	private function setBook($id) {
		if ($id)
			$this->data['book_id'] = $id;
	}
	
	// цепляем полку
	private function setShelf($id) {
		if ($id)
			$this->data['shelf_id'] = $id;
	}

	private function setSerie($id) {
		if ($id)
			$this->data['serie_id'] = $id;
	}

	private function setGenre($id) {
		if ($id)
			$this->data['genre_id'] = $id;
	}

	// цепляем автора
	private function setAuthor($id) {
		if ($id)
			$this->data['author_id'] = $id;
	}

	// цепляем журнал
	private function setMagazine($id) {
		if ($id)
			$this->data['magazine_id'] = $id;
	}

	public function push() {
		global $current_user;
		if (!$this->canPushed)
			return;

		$eventId = false;
		if ($this->data['type'] == self::EVENT_BOOKS_REVIEW_ADD || $this->data['type'] == self::EVENT_BOOKS_RATE_ADD) {
			// ищем старую
			$eventId = MongoDatabase::findReviewEvent($current_user->id, $this->data['book_id']);
			if ($eventId) {
				// есть старая? нужно удалить запись на стене со ссылкой на старую запись со всех стен
				MongoDatabase::deleteWallItemsByEventId($eventId);
				MongoDatabase::updateEvent($eventId, $this->data);
			}
		}

		if (!$eventId) {
			$eventId = MongoDatabase::addEvent($this->data);
			$query = 'INSERT INTO `events` SET `mongoid`=' . Database::escape($eventId);
			Database::query($query, false);
			$eventDbId = Database::lastInsertId();
			if (!$eventDbId) {
				throw new Exception('cant push event id to database');
			}
		}


		if ($eventId) {
			$user = Users::getById($this->data['user_id']);
			/* @var $user User */
			$followerIds = $user->getFollowers();
			$followerIds[$user->id] = $user->id;
			MongoDatabase::pushEvents($this->data['user_id'], $followerIds, $eventId, $this->data['time']);
		}
		return $eventDbId;
	}

}