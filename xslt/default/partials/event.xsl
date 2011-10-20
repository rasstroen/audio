<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

  <xsl:template match="*" mode="p-event-list">
    <xsl:param name="users" select="users"/>
    <xsl:param name="authors" select="authors"/>
    <xsl:param name="books" select="books"/>
    <xsl:param name="genres" select="genres"/>
    <xsl:param name="series" select="series"/>

    <xsl:variable name="user" select="$users/item[@id=current()/@user_id]"/>
    <xsl:variable name="owner" select="$users/item[@id=current()/@owner_id]"/>
    <xsl:variable name="book" select="$books/item[@id=current()/@book_id]"/>
    <xsl:variable name="author" select="$authors/item[(string(current()/@book_id) and @id=$book/@author_id) or @id=current()/@author_id]"/>
    <xsl:variable name="serie" select="$series/item[@id=current()/@serie_id]"/>
    <xsl:variable name="genre" select="$genres/item[@id=current()/@genre_id]"/>

    <li class="p-event-list">
      <div class="p-event-list-image">
        <xsl:choose>
          <xsl:when test="$owner/@id != 0">
            <xsl:apply-templates select="$owner" mode="helpers-user-image"/>
          </xsl:when>
          <xsl:otherwise><xsl:apply-templates select="$user" mode="helpers-user-image"/></xsl:otherwise>
        </xsl:choose>
      </div>
      <div class="p-event-list-text">
        <p class="p-event-list-text-date">
          <a href="{&prefix;}{@link_url}">
            <xsl:call-template name="helpers-abbr-time">
              <xsl:with-param select="@time" name="time"/>
            </xsl:call-template>
          </a>
        </p>
        <p class="p-event-list-text-title">
          <xsl:if test="@retweet_from != 0">
            <xsl:apply-templates select="$owner" mode="helpers-user-link"/> понравилось, что
          </xsl:if>
          <xsl:apply-templates select="$user" mode="helpers-user-link"/>
          <xsl:choose>
            <xsl:when test="@type='books-add'">&#160;добавил новую книгу</xsl:when>
            <xsl:when test="@type='books-edit'">&#160;изменил книгу</xsl:when>
            <xsl:when test="@type='books-review-new'">&#160;написал рецензию на книгу</xsl:when>
            <xsl:when test="@type='books-rate-new'">&#160;оценил книгу на <xsl:value-of select="@mark"/></xsl:when>
            <xsl:when test="@type='books-add-shelf'">&#160;добавил книгу на полку «<xsl:value-of select="@shelf_title"/>»</xsl:when>
            <xsl:when test="@type='loved-add-author'">&#160;добавил в любимые автора </xsl:when>
            <xsl:when test="@type='loved-add-book'">&#160;добавил в любимые книгу </xsl:when>
            <xsl:when test="@type='loved-add-genre'">&#160;добавил в любимые жанр </xsl:when>
            <xsl:when test="@type='loved-add-serie'">&#160;добавил в любимые серию </xsl:when>
            <xsl:otherwise><xsl:value-of select="@type"/></xsl:otherwise>
          </xsl:choose>
        </p>
        <xsl:choose>
          <xsl:when test="
              @type='loved-add-book' or
              @type='books-add' or
              @type='books-edit' or
              @type='books-rate-new' or
              @type='books-add-shelf' or
              @type='books-review-new'">
            <xsl:call-template name="p-book-event">
              <xsl:with-param name="book" select="$book"/>
              <xsl:with-param name="author" select="$author"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="
              @type='loved-add-author' or
              @type='authors-add' or
              @type='authors-edit'">
            <xsl:call-template name="p-event-list-author">
              <xsl:with-param name="author" select="$author"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="@type='loved-add-genre'">
            <xsl:apply-templates select="$genre" mode="helpers-genre-link"/>
          </xsl:when>
          <xsl:when test="@type='loved-add-serie'">
            <xsl:apply-templates select="$serie" mode="helpers-serie-link"/>
          </xsl:when>
          <xsl:otherwise/>
        </xsl:choose>
        <div class="p-event-list-likes" id="{@id}" name="likes"/>
        <xsl:if test="@body">
          <div class="p-event-list-text-review">
            <xsl:value-of select="@body" disable-output-escaping="yes"/>
          </div>
        </xsl:if>
        <xsl:call-template name="p-comment-new"/>
        <xsl:if test="@commentsCount">
          <ul class="p-event-list-comments">
            <h3>Последние комментарии</h3>
            <xsl:apply-templates select="comments/item" mode="p-event-list-comments-item">
              <xsl:with-param select="$users" name="users"></xsl:with-param>
            </xsl:apply-templates>
          </ul>
        </xsl:if>
      </div>
    </li>
  </xsl:template>

  <xsl:template match="module[@name='events' and @action='new']" mode="p-module">
    <h2>Поделиться мыслями</h2>
    <form method ="post">
      <input type="hidden" name="action" value="post_new" />
      <input type="hidden" value="EventsWriteModule" name="writemodule" />
      <div class="form-group">
        <div class="form-field">
          <textarea name="post"/>
        </div>
      </div>
      <div class="form-control">
        <input type="submit" value="Отправить" /> или <a href="{&prefix;}me/wall/post">написать длинный пост</a>
      </div>
    </form>	
    <script type="text/javascript">
      tinyMCE.init({mode:"textareas"});
    </script>
  </xsl:template>

</xsl:stylesheet>
