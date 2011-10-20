<?php

// модуль отвечает за отображение баннеров
class forum_module extends BaseModule {

	function generateData() {
		$params = $this->params;

		$this->forum_id = isset($params['forum_id']) ? $params['forum_id'] : 0;
		$this->theme_id = isset($params['theme_id']) ? $params['theme_id'] : 0;

		switch ($this->action) {
			case 'list':
				if (!$this->forum_id && !$this->theme_id)
					$this->getForumList();
				if ($this->forum_id && !$this->theme_id)
					$this->getThemesList();
				break;
			case 'show':
				$this->getTheme();
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function getTheme() {
		if (!$this->theme_id)
			return;

		$query = 'SELECT uid as user_id,title,body FROM `node_revisions` WHERE `nid`=' . $this->theme_id . ' LIMIT 1';
		$theme = Database::sql2row($query);
		if (!$theme)
			throw new Exception('Мы проебали эту тему форума');
		$theme['body'] = _bbcode_filter_process($theme['body']);
		$this->data['theme'] = $theme;
		Request::pass('theme-title', $theme['title']);

		$query = 'SELECT subject,comment,timestamp,uid FROM `comments` WHERE `nid` = ' . $this->theme_id . ' ORDER BY `timestamp`';
		$comments = Database::sql2array($query);
		$uids = array();
		foreach ($comments as &$comment) {
			$uids[$comment['uid']] = $comment['uid'];
			$comment['comment'] = _bbcode_filter_process($comment['comment']);
		}
		$uids[$theme['user_id']] = $theme['user_id'];
		$this->data['theme']['users'] = $this->getUsers($uids);
		$this->data['theme']['tid'] = $this->forum_id;
		$this->data['theme']['comments'] = $comments;
	}

	function getForumList() {
		$query = 'SELECT t.tid, t . * , parent
		FROM term_data t
		INNER JOIN term_hierarchy h ON t.tid = h.tid
		WHERE t.vid = 1
		ORDER BY weight, name LIMIT 40';
		$forumList = Database::sql2array($query);
		$this->data['forums'] = $forumList;
	}

	function getThemesList() {
		$querycnt = 'SELECT COUNT(1) FROM `term_node` TN 
		LEFT JOIN `node` N ON TN.nid = N.nid 
		WHERE `tid`=' . $this->forum_id;
		
		
		
		$count = Database::sql2single($querycnt);
		$cond = new Conditions();

		$cond->setPaging($count, 15);
		$limit = $cond->getLimit();
		$this->data['conditions'] = $cond->getConditions();
		
		$query = 'SELECT NCS.last_comment_timestamp,NCS.last_comment_uid,N.title,N.nid,N.created,N.changed,N.comment,N.promote,N.sticky,N.status FROM `term_node` TN 
		LEFT JOIN `node` N ON TN.nid = N.nid 
		INNER JOIN `node_comment_statistics` NCS ON N.nid = NCS.nid 
		WHERE `tid`=' . $this->forum_id . '
		ORDER BY `last_comment_timestamp` DESC LIMIT '.$limit;
		
		Request::pass('forum-title', Database::sql2single('SELECT name FROM `term_data` WHERE `tid`=' . $this->forum_id));
		$themesList = Database::sql2array($query);
		foreach ($themesList as &$theme) {
			$uids[$theme['last_comment_uid']] = $theme['last_comment_uid'];
			$theme['last_comment_timestamp'] = date('Y/m/d H:i', $theme['last_comment_timestamp']);
			$theme['created'] = date('Y/m/d H:i', $theme['created']);
		}
		$this->data['users'] = $this->getUsers($uids);
		$this->data['themes'] = $themesList;
		$this->data['themes']['tid'] = $this->forum_id;
	}

	function getUsers($ids) {
		$users = Users::getByIdsLoaded($ids);
		$out = array();
		/* @var $user User */
		$i = 0;
		if (is_array($users))
			foreach ($users as $user) {
				$out[$user->id] = array(
				    'id' => $user->id,
				    'picture' => $user->getAvatar(),
				    'nickname' => $user->getNickName(),
				);
			}
		if (is_array($ids))
			foreach ($ids as $id) {
				if (!isset($out[$id])) {
					$out[$id] = array(
					    'id' => $id,
					    'picture' => Config::need('www_path') . '/static/upload/avatars/default.jpg',
					    'nickname' => 'аноним',
					);
				}
			}
		return $out;
	}

}