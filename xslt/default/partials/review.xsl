<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

  <xsl:template match="*" mode="p-review-list">
    <xsl:param name="review" select="."/>
    <xsl:param name="users" select="users"/>
    <xsl:param name="user" select="$users/item[@id=$review/@id_user]"/>
    <xsl:param name="mode"/>
    <li class="p-review-list">
      <div class="p-review-list-image">
        <img src="{$user/@picture}" alt="[Image]"/>
      </div>
      <div class="p-review-list-text">
        <div class="p-review-list-text-time">
          <a href="{@path}">
            <xsl:call-template name="helpers-abbr-time">
              <xsl:with-param select="@time" name="time"/>
            </xsl:call-template>
          </a>
        </div>
        <div class="p-review-list-text-nickname">
          <xsl:apply-templates select="$user" mode="helpers-user-link"/>
        </div>
        <xsl:if test="@rate > 0">
          <div class="p-review-list-text-rate">Оценка: <xsl:value-of select="@rate" /></div>
        </xsl:if>
        <div class="p-review-list-text-html">
          <xsl:value-of select="@html" disable-output-escaping="yes"/>
        </div>
        <xsl:if test="@likesCount>0">
          <div class="p-review-list-text-likes">
            Рецензия понравилась
            <xsl:call-template name="helpers-this-amount">
              <xsl:with-param select="@likesCount" name="amount"/>
              <xsl:with-param select="'пользователю пользователям пользователям'" name="words"/>
            </xsl:call-template>
          </div>
        </xsl:if>
      </div>
    </li>
  </xsl:template>

  <xsl:template match="*" mode="p-review-rate">
    <xsl:param name="review" select="."/>
    <xsl:param name="users" select="users"/>
    <xsl:param name="user" select="$users/item[@id=$review/@id_user]"/>
    <li class="p-review-rate">
      <div class="p-review-rate-image"><xsl:apply-templates select="$user" mode="helpers-user-image"/></div>
      <div class="p-review-rate-text">
        <div class="p-review-rate-text-time">
          <a href="{@path}">
            <xsl:call-template name="helpers-abbr-time"><xsl:with-param select="@time" name="time"/></xsl:call-template>
          </a>
        </div>
        <div class="p-review-rate-text-title">
          <xsl:apply-templates select="$user" mode="helpers-user-link"/> оценил книгу на <xsl:value-of select="@rate"/>
        </div>
      </div>
    </li>
  </xsl:template>

  <xsl:template match="*" mode="p-review-user">
    <xsl:param name="review" select="."/>
    <xsl:param name="users" select="users"/>
    <xsl:param name="user" select="$users/item[@id=$review/@id_user]"/>
    <xsl:param name="books" select="books"/>
    <xsl:param name="book" select="$books/item[@id=$review/@book_id]"/>
    <li class="p-review-user">
      <div class="p-review-user-book">
        <div class="p-review-user-book-image">
          <xsl:apply-templates select="$book" mode="helpers-book-cover"/>
        </div>
        <p class="p-review-user-book-title">
          <xsl:apply-templates select="$book" mode="helpers-book-link"/>
        </p>
        <p class="p-review-user-book-author">
          <xsl:apply-templates select="$book/author" mode="helpers-author-link"/>
        </p>
      </div>
      <div class="p-review-user-text">
        <xsl:if test="@rate > 0">
          <div class="p-review-user-text-rate">Оценка: <xsl:value-of select="@rate" /></div>
        </xsl:if>
        <div class="p-review-user-text-html">
          <xsl:value-of select="@html" disable-output-escaping="yes"/>
        </div>
      </div>
    </li>
  </xsl:template>

  <xsl:template match="module[@name='reviews' and @action='new']" mode="p-module">
    <xsl:if test="(&current_profile;)/@id">
      <div class="reviews-new module">
        <h2>Оставьте отзыв</h2>
        <form method="post">
          <input type="hidden" value="ReviewsWriteModule" name="writemodule" />
          <input type="hidden" value="{review/@target_id}" name="target_id" />
          <input type="hidden" value="{review/@target_type}" name="target_type" />
          <div class="form-field">
            <label for="annotation">Текст отзыва</label>
            <textarea name="annotation">
              <xsl:value-of select="review/@html" disable-output-escaping="yes" />
            </textarea>
          </div>
          <div class="form-field">
            <label for="rate">Оценка</label>
            <select name="rate">
              <option value="0">-</option>
              <option value="1">
                <xsl:if test="review/@rate = 1">
                  <xsl:attribute name="selected">selected</xsl:attribute>	    
                </xsl:if>
                <xsl:text>1</xsl:text>
              </option>
              <option value="2">
                <xsl:if test="review/@rate = 2">
                  <xsl:attribute name="selected">selected</xsl:attribute>	    
                </xsl:if>
                <xsl:text>2</xsl:text>
              </option>
              <option value="3">
                <xsl:if test="review/@rate = 3">
                  <xsl:attribute name="selected">selected</xsl:attribute>	    
                </xsl:if>
                <xsl:text>3</xsl:text>
              </option>
              <option value="4">
                <xsl:if test="review/@rate = 4">
                  <xsl:attribute name="selected">selected</xsl:attribute>	    
                </xsl:if>
                <xsl:text>4</xsl:text>
              </option>
              <option value="5">
                <xsl:if test="review/@rate = 5">
                  <xsl:attribute name="selected">selected</xsl:attribute>	    
                </xsl:if>
                <xsl:text>5</xsl:text>
              </option>
            </select>
          </div>
          <div class="form-control">
            <input type="submit" value="Оставить отзыв" />
          </div>
        </form>
        <script type="text/javascript">
          tinyMCE.init({mode:"textareas"});
          //todo ajax will check if it's already review by current user
        </script>
      </div>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>
