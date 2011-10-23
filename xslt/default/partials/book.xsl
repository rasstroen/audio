<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

  <xsl:template match="*" mode="p-book-list">
    <xsl:param name="mode"/>
    <xsl:param name="prefix"/>
    <xsl:param name="authors" select="authors"/>
    <xsl:param name="owner_id"/>
    <xsl:variable name="author" select="$authors/item[@id=current()/@author_id]"/>
    <xsl:variable name="fprefix">
      <xsl:choose>
        <xsl:when test="$prefix">-<xsl:value-of select="$prefix"/></xsl:when>
        <xsl:otherwise/>
      </xsl:choose>
    </xsl:variable>
    <li class="p-book-list{$fprefix}" id="book-{@id}">
      <xsl:variable select="$mode='shelves' or $mode='shelf'" name="is_shelf"></xsl:variable>
      <xsl:if test="$is_shelf and (&current_profile;/@id = $owner_id)">
        <div class="p-book-list{$fprefix}-del">
          <a href="#" class="del-from-shelf">x</a>
        </div>
      </xsl:if>
      <div class="p-book-list{$fprefix}-image">
        <xsl:apply-templates select="." mode="helpers-book-cover"/>
      </div>
      <div class="{$fprefix}p-book-list{$fprefix}-info">
        <div class="{$fprefix}p-book-list-info-title">
          <xsl:apply-templates select="." mode="helpers-book-link"/>
        </div>
        <xsl:if test="not($mode='author')">
          <div class="{$fprefix}p-book-list{$fprefix}-info-author">
            <xsl:apply-templates select="$author" mode="helpers-author-link"/>
          </div>
        </xsl:if>
      </div>
    </li>
  </xsl:template>

  <xsl:template name="p-book-event">
    <xsl:param name="book" select="book"/>
    <xsl:param name="author" select="author"/>
    <div class="p-book-event">
      <div class="p-book-event-image">
        <xsl:apply-templates select="$book" mode="helpers-book-cover"/>
      </div>
      <div class="p-book-event-name">
        <xsl:apply-templates select="$book" mode="helpers-book-link"/>
      </div>
      <div class="p-book-event-author">
        <xsl:apply-templates select="$author" mode="helpers-author-link"/>
      </div>
    </div>
  </xsl:template>

  <xsl:template match="module[@name='books' and @action='show']" mode="p-module">
    <input type="hidden" name="id" value="{book/@id}" />
    <div class="p-book-show-image">
      <img src="{book/@cover}" alt="Обложка книги «{book/@title}»"/>
    </div>
    <div class="p-book-show-info">
      <h1><xsl:value-of select="book/@title"/></h1>
      <xsl:if test="book/@subtitle">
        <h2><xsl:value-of select="book/@subtitle"/></h2>
      </xsl:if>

      <xsl:if test="&current_profile;/@id">

        <div class="p-book-show-info-loved">
          <p/>
          <a href="#" class="add-loved-book">Добавить в любимые книги</a>
        </div>

        <div class="p-book-show-info-shelf">
          <div class="book-shelf-info-shelf-name"/>
          <p><a href="#" class="add-to-shelf">Положить на полку</a></p>
          <div class="add-to-shelf-form" style="display:none">
            <select name="add-to-shelf-shelf-id">
              <option value="1">Читал</option>
              <option value="2">Читаю</option>
              <option value="3">Буду читать</option>
            </select>
            <a class="add-to-shelf-confirm" href="#">Положить</a>
          </div>
        </div>

        <p><a href="{&page;/@current_url}edit">Редактировать книгу</a></p>

        <xsl:if test="&role; > 39">
          <a href="{&page;/@current_url}log">Лог изменений книги</a>
        </xsl:if>

      </xsl:if>

      <div class="p-book-show-info-files">
        <h3>Файлы</h3>
        <xsl:for-each select="book/files/item">
          <li class="p-book-show-info-files-item">
            <xsl:apply-templates select="." mode="helpers-file-link"/>
          </li>
        </xsl:for-each>
      </div>

      <div class="p-book-show-info-genres">
        <h3>Жанры</h3>
        <xsl:for-each select="book/genres/item">
          <li class="p-book-show-info-genres-item">
            <xsl:apply-templates select="." mode="helpers-genre-link"/>
          </li>
        </xsl:for-each>
      </div>

      <div class="p-book-show-info-series">
        <h3>Серии</h3>
        <xsl:for-each select="book/series/item">
          <li class="p-book-show-info-series-item">
            <xsl:apply-templates select="." mode="helpers-serie-link"/>
          </li>
        </xsl:for-each>
      </div>

      <xsl:for-each select="book/authors/item">
        <div>
          <xsl:value-of select="@roleName" />:
          <a href="/a/{@id}">
            <xsl:call-template name="helpers-author-name">
              <xsl:with-param name="author" select="."/>
            </xsl:call-template>
          </a>
        </div>
      </xsl:for-each>

      <xsl:if test="book/rightsholder/@title != ''">Издатель:
        <a href="{&prefix;}rightsholder/{rightsholder/@id}">
          <xsl:value-of select="book/rightsholder/@title"/>
        </a>
      </xsl:if>

      <xsl:if test="book/@isbn != ''">ISBN: <xsl:value-of select="book/@isbn"/></xsl:if>

      <xsl:if test="book/@annotation != ''">
        <div class="p-book-show-info-annotation">
          <xsl:value-of select="book/@annotation" disable-output-escaping="yes" />	
        </div>
      </xsl:if>

    </div>
  </xsl:template>

  <xsl:template match="module[@name='books' and @action='edit']" mode="p-module">
    <xsl:variable select="book" name="book"/>
    <form method="post" enctype="multipart/form-data" action="{&prefix;}book/{book/@id}/edit">
      <input type="hidden" name="writemodule" value="BookWriteModule" />
      <input type="hidden" name="id" value="{book/@id}" />
      <input type="hidden" name="n" value="{write/@n}" />
      <input type="hidden" name="m" value="{write/@m}" />
      <div class="form-group">
        <h2>Редактирование книги «<xsl:value-of select="book/@title"/>»
        </h2>
        <xsl:if test="&role; > 39">
          <div class="form-field">
            <label>Качество</label>
            <select name="quality">
              <xsl:for-each select="book/qualities/item">
                <option value="{@id}">
                  <xsl:if test="$book/@quality = current()/@id"><xsl:attribute name="selected"/></xsl:if>
                  <xsl:value-of select="@title" />
                </option>
              </xsl:for-each>
            </select>
          </div>
        </xsl:if>
        <div class="form-field">
          <label>Название</label>
          <input name="title" value="{book/@title}" />
        </div>
        <div class="form-field">
          <label>Доп. инфо</label>
          <input name="subtitle" value="{book/@subtitle}" />
        </div>
        <div class="form-field">
          <label>ISBN</label>
          <input name="isbn" value="{book/@isbn}" />
        </div>
        <div class="form-field">
          <label>Год издания</label>
          <input name="year" value="{book/@year}" />
        </div>
        <div class="form-field">
          <label>Язык книги</label>
          <xsl:call-template name="helpers-lang-code-select">
            <xsl:with-param name="object" select="book"/>
          </xsl:call-template>
        </div>
        <div class="form-field">
          <label>Правообладатель</label>
          <input name="rightholder" value="{book/@rightholder}" />
        </div>
        <div class="form-field">
          <label>Анотация</label>
          <textarea name="annotation">
            <xsl:value-of select="book/@annotation" />	
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <xsl:for-each select="book/files/item">
          <div>Залитый файл 
            <xsl:value-of select="@filetypedesc"/>
            (<xsl:value-of select="@size"/> байт)
          </div>

        </xsl:for-each>
        <h2>Добавить файл</h2>
        <div class="form-field">
          <input type="file" name="file"></input>
        </div>
      </div>
      <div class="form-group">
        <h2>Обложка</h2>
        <img src="{book/@cover}?{book/@lastSave}" alt="[Обложка]" />
        <div class="form-field">
          <input type="file" name="cover"></input>
        </div>
      </div>
      <div class="form-control">
        <input type="submit" value="Сохранить информацию"/>
      </div>
    </form>
    <div class="form-group">
      <h2>Авторы</h2>
      <div class="p-book-edit-authors">
        <xsl:call-template name="p-book-edit-author"/>
        <xsl:for-each select="book/authors/item">
          <xsl:call-template name="p-book-edit-author"/>
        </xsl:for-each>
        <xsl:call-template name="p-book-edit-author-new"/>
      </div>
    </div>
    <div class="form-group">
      <h2>Жанры</h2>
      <div class="p-book-edit-genres">
        <xsl:call-template name="p-book-edit-genre"/>
        <xsl:for-each select="book/genres/item">
          <xsl:call-template name="p-book-edit-genre"/>
        </xsl:for-each>
        <xsl:call-template name="p-book-edit-genre-new"/>
      </div>
    </div>
    <div class="form-group">
      <h2>Серии</h2>
      <div class="p-book-edit-series">
        <xsl:call-template name="p-book-edit-serie"/>
        <xsl:for-each select="book/series/item">
          <xsl:call-template name="p-book-edit-serie"/>
        </xsl:for-each>
        <xsl:call-template name="p-book-edit-serie-new"/>
      </div>
    </div>
    <div class="form-group">
      <h2>Переводы, редакции, дубликаты</h2>
      <div class="p-book-edit-relations">
        <xsl:call-template name="p-book-edit-relation"/>
        <xsl:variable select="book/relations/books" name="books"/>
        <xsl:for-each select="book/relations/item">
          <xsl:call-template name="p-book-edit-relation">
            <xsl:with-param name="books" select="$books"/>
          </xsl:call-template>
        </xsl:for-each>
        <xsl:call-template name="p-book-edit-relation-new"/>
      </div>
    </div>
    <script type="text/javascript">
      tinyMCE.init({mode:"textareas"});
    </script>
  </xsl:template>

  <xsl:template name="p-book-edit-author">
    <xsl:variable name="class">
      <xsl:text>p-book-edit-author</xsl:text>
      <xsl:choose>
        <xsl:when test="@id"> author-<xsl:value-of select="@id"/></xsl:when>
        <xsl:otherwise> hidden</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <div class="{$class}">
      <a href="#" class="p-book-edit-author-delete">Удалить</a>
      <input type="hidden" name="id_author" value="{@id}"/>    
      <div class="p-book-edit-author-role">
        <xsl:value-of select="@roleName"/>
      </div>
      <xsl:text>:</xsl:text>
      <div class="p-book-edit-author-name">
        <xsl:call-template name="helpers-author-name">
          <xsl:with-param name="author" select="."/>
        </xsl:call-template>
      </div>
    </div>
  </xsl:template>

  <xsl:template name="p-book-edit-genre">
    <xsl:variable name="class">
      <xsl:text>p-book-edit-genre</xsl:text>
      <xsl:choose>
        <xsl:when test="@id"> genre-<xsl:value-of select="@id"/></xsl:when>
        <xsl:otherwise> hidden</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <div class="{$class}">
      <a href="#" class="p-book-edit-genre-delete">Удалить</a>
      <input type="hidden" name="id_genre" value="{@id}"/>    
      <div class="p-book-edit-genre-title">
        <xsl:value-of select="@title"/>
      </div>
    </div>
  </xsl:template>

  <xsl:template name="p-book-edit-serie">
    <xsl:variable name="class">
      <xsl:text>p-book-edit-serie</xsl:text>
      <xsl:choose>
        <xsl:when test="@id"> serie-<xsl:value-of select="@id"/></xsl:when>
        <xsl:otherwise> hidden</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <div class="{$class}">
      <a href="#" class="p-book-edit-serie-delete">Удалить</a>
      <input type="hidden" name="id_serie" value="{@id}"/>    
      <div class="p-book-edit-serie-title">
        <xsl:value-of select="@title"/>
      </div>
    </div>
  </xsl:template>

  <xsl:template name="p-book-edit-relation">
    <xsl:param name="books" select="books"/>
    <xsl:param name="book_id" select="@id2"/>
    <xsl:param name="book" select="$books/item[@id=$book_id]"/>
    <xsl:variable name="class">
      <xsl:text>p-book-edit-relation</xsl:text>
      <xsl:choose>
        <xsl:when test="@id2"> relation-<xsl:value-of select="@id2"/></xsl:when>
        <xsl:otherwise> hidden</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <div class="{$class}">
      <a href="#" class="p-book-edit-relation-delete">Удалить</a>
      <input type="hidden" name="id_relation" value="{$book/@id}"/>    
      <div class="p-book-edit-relation-type">
        <xsl:value-of select="@relation_type_name"/>
      </div>
      <xsl:text>:</xsl:text>
      <div class="p-book-edit-relation-title">
        <xsl:apply-templates select="$book" mode="helpers-book-link"/>
      </div>
    </div>
  </xsl:template>

  <xsl:template name="p-book-edit-author-new">
    <div class="p-book-edit-author-new">
      <xsl:call-template name="helpers-role-select">
        <xsl:with-param name="object" select="book"/>
      </xsl:call-template>
      <input name="id_author" type="text" class="p-book-edit-author-new-id" />
      <a href="#" class="p-book-edit-author-new-submit">Добавить</a>
    </div>
  </xsl:template>

  <xsl:template name="p-book-edit-relation-new">
    <div class="p-book-edit-relation-new">
      <xsl:call-template name="helpers-relation-type-select">
        <xsl:with-param name="object" select="book"/>
      </xsl:call-template>
      <input name="book_id" type="text" class="p-book-edit-relation-new-id" />
      <a href="#" class="p-book-edit-relation-new-submit">Добавить</a>
    </div>
  </xsl:template>

  <xsl:template name="p-book-edit-genre-new">
    <div class="p-book-edit-genre-new">
      <input name="id_genre" type="text" class="p-book-edit-genre-new-id" />
      <a href="#" class="p-book-edit-genre-new-submit">Добавить</a>
    </div>
  </xsl:template>

  <xsl:template name="p-book-edit-serie-new">
    <div class="p-book-edit-serie-new">
      <input name="id_serie" type="text" class="p-book-edit-serie-new-id" />
      <a href="#" class="p-book-edit-serie-new-submit">Добавить</a>
    </div>
  </xsl:template>

  <xsl:template match="module[@name='books' and @action='new']" mode="p-module">
    <form method="post" enctype="multipart/form-data" action="{&prefix;}book/new">
      <input type="hidden" name="writemodule" value="BookWriteModule" />
      <input type="hidden" name="n" value="{write/@n}" />
      <input type="hidden" name="m" value="{write/@m}" />
      <input type="hidden" name="author_id" value="{book/author/@id}" />
      <div class="form-group">
        <h2>Добавление книги
          <xsl:if test="book/author">
            автора «<xsl:apply-templates select="book/author" mode="helpers-author-link"/>»
          </xsl:if>
        </h2>
        <div class="form-field">
          <label>Название</label>
          <input name="title" value="{write/@title}"/>
        </div>
        <div class="form-field">
          <label>Доп. инфо</label>
          <input name="subtitle" value="{write/@subtitle}"/>
        </div>
        <div class="form-field">
          <label>ISBN</label>
          <input name="isbn"/>
        </div>
        <div class="form-field">
          <label>Год издания</label>
          <input name="year" value="{write/@year}" />
        </div>
        <div class="form-field">
          <label>Язык книги</label>
          <xsl:call-template name="helpers-lang-code-select">
            <xsl:with-param name="object" select="book"/>
          </xsl:call-template>
        </div>
        <div class="form-field">
          <label>Правообладатель</label>
          <input name="rightholder"/>
        </div>
        <div class="form-field">
          <label>Анотация</label>
          <textarea name="annotation">
            <xsl:value-of select="book/@annotation" />	
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <h2>Обложка</h2>
        <div class="form-field">
          <input type="file" name="cover"></input>
        </div>
      </div>
      <div class="form-control">
        <input type="submit" value="Сохранить информацию"/>
      </div>
    </form>
    <script type="text/javascript">
      tinyMCE.init({mode:"textareas"});
    </script>
  </xsl:template>
</xsl:stylesheet>
