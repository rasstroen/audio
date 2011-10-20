<?php  /* GENERATED AUTOMATICALLY AT 2011-10-06, DO NOT MODIFY */
class LibPages{
public static $pages = 
array (
  'main' => 
  array (
    'title' => 'Жмячне',
    'name' => 'main',
    'params' => 
    array (
      'cache' => false,
      'cache_sec' => 0,
    ),
    'xslt' => 'main.xsl',
    'modules' => 
    array (
      0 => 
      array (
        'content' => 
        array (
          'roles' => 
          array (
            '' => '',
          ),
          'action' => 'show',
          'mode' => 'random',
          'params' => 
          array (
          ),
        ),
      ),
    ),
  ),
  'backend' => 
  array (
    'title' => 'backend',
    'name' => 'backend',
    'params' => 
    array (
      'cache' => false,
      'cache_sec' => 0,
    ),
    'xslt' => 'admin/admin.xsl',
    'modules' => 
    array (
      0 => 
      array (
        'backend' => 
        array (
          'roles' => 
          array (
            '' => '',
          ),
          'action' => 'show',
          'mode' => 'meow',
          'params' => 
          array (
          ),
        ),
      ),
    ),
  ),
  'pictures' => 
  array (
    'title' => 'Картинки',
    'name' => 'pictures',
    'params' => 
    array (
      'cache' => false,
      'cache_sec' => 0,
    ),
    'xslt' => 'content/list.xsl',
    'modules' => 
    array (
      0 => 
      array (
        'content' => 
        array (
          'roles' => 
          array (
            '' => '',
          ),
          'action' => 'list',
          'mode' => '',
          'params' => 
          array (
            0 => 
            array (
              'name' => 'type',
              'type' => 'var',
              'value' => 'picture',
            ),
          ),
        ),
      ),
    ),
  ),
  'pictures_add' => 
  array (
    'title' => 'Добавление картинки',
    'name' => 'pictures_add',
    'params' => 
    array (
      'cache' => false,
      'cache_sec' => 0,
    ),
    'xslt' => 'content/new.xsl',
    'modules' => 
    array (
      0 => 
      array (
        'content' => 
        array (
          'roles' => 
          array (
            '' => '',
          ),
          'action' => 'new',
          'mode' => '',
          'params' => 
          array (
            0 => 
            array (
              'name' => 'type',
              'type' => 'var',
              'value' => 'picture',
            ),
          ),
        ),
      ),
    ),
  ),
  'pictures_item' => 
  array (
    'title' => 'Картинка',
    'name' => 'pictures_item',
    'params' => 
    array (
      'cache' => false,
      'cache_sec' => 0,
    ),
    'xslt' => 'content/show.xsl',
    'modules' => 
    array (
      0 => 
      array (
        'content' => 
        array (
          'roles' => 
          array (
            '' => '',
          ),
          'action' => 'show',
          'mode' => '',
          'params' => 
          array (
            0 => 
            array (
              'name' => 'type',
              'type' => 'var',
              'value' => 'picture',
            ),
            1 => 
            array (
              'name' => 'id',
              'type' => 'get',
              'value' => '1',
            ),
          ),
        ),
      ),
    ),
  ),
);
}