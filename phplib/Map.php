<?php

class Map {

	public static $map = array(
	    '/' => 'main.xml',
            //news
	    'news' => 'news/list.xml',
	    'news/new' => 'news/new.xml',
	    'news/%d' => 'news/show.xml',
	    'news/%d/edit' => 'news/edit.xml',
            // core
	    'register' => 'register/index.xml',
	    'emailconfirm/%d/%s' => 'misc/email_confirm.xml',
	    404 => 'errors/p404.xml',
	    502 => 'errors/p502.xml',
	    'user/%s' => 'users/user.xml',
	    'user/%s/edit' => 'users/edit.xml',
	);
	public static $sinonim = array(
	    'user/%d' => 'user/%s',
	    'user/%d/edit' => 'user/%s/edit',
	);

}
