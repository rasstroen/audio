<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

  <xsl:template mode="p-comment-list" match="*">
    <xsl:param name="users" select="users"/>
    <xsl:param name="level" select="1"/>
    <xsl:variable name="comment" select="."/>
    <xsl:variable select="$users/item[@id = $comment/@commenter_id]" name="user"></xsl:variable>
    <li class="p-comment-event">
      <div class="p-comment-event-image">
        <img src="{$user/@picture}" alt="[{$user/@nickname}]"/>
      </div>
      <div class="p-comment-event-text">
        <div class="p-comment-event-text-time">
          <xsl:call-template name="helpers-abbr-time">
            <xsl:with-param select="@time" name="time"/>
          </xsl:call-template>
        </div>
        <xsl:value-of select="$user/@nickname" />:
        <xsl:value-of select="@comment" />
      </div>            
    </li>
    <xsl:if test="$level = 1">
      <ul class="p-comment-event-answers">
        <xsl:apply-templates select="answers/item" mode="p-comment-event">
          <xsl:with-param select="$users" name="users"></xsl:with-param>
          <xsl:with-param select="2" name="level"></xsl:with-param>
        </xsl:apply-templates>
        <xsl:call-template name="p-comment-new">
          <xsl:with-param name="comment_id" select="$comment/@id" />
          <xsl:with-param name="id" select="@parent_id" />
          <xsl:with-param name="title_text" select="'Ответить'" />
        </xsl:call-template>
      </ul>
    </xsl:if>
  </xsl:template>

  <xsl:template name="p-comment-new">
    <xsl:param name="comment_id" select="0"></xsl:param>
    <xsl:param name="id" select="@id"></xsl:param>
    <xsl:param name="title_text" select="'Оставить комментарий'"></xsl:param>
    <div class="events-list-item-comments-add">
      <a class="add-comment" href="#"><xsl:value-of select="$title_text"/></a>
    </div>
    <div class="p-comment-new" style="display:none">
      <h3><xsl:value-of select="$title_text" /></h3>
      <form method="post">
        <input type="hidden" name="id" value="{$id}" />
        <input type="hidden" name="comment_id" value="{$comment_id}" />
        <input type="hidden" name="action" value="comment_new" />
        <input type="hidden" value="EventsWriteModule" name="writemodule" />
        <div class="form-group">
          <div class="form-field">
            <textarea name="comment"/>
          </div>
        </div>
        <div class="form-control">
          <input type="submit" value="Оставить комментарий"/>
        </div>
      </form>
    </div>
  </xsl:template>

  <xsl:template match="*" mode="p-comment-forum">
    <xsl:param select="users" name="users"/>
    <xsl:param select="@uid" name="uid"/>
    <xsl:variable select="$users/item[@id=$uid]" name="user"/>
    <li class="p-comment-forum">
      <div class="p-comment-forum-image">
        <xsl:apply-templates select="$user" mode="helpers-user-image"/>
      </div>
      <div class="p-comment-forum-text">
        <div class="p-comment-forum-text-user">
          <xsl:apply-templates select="$user" mode="helpers-user-link"/>
        </div>
        <xsl:value-of select="@comment" disable-output-escaping="yes"/>
      </div>
    </li>
  </xsl:template>


</xsl:stylesheet>
