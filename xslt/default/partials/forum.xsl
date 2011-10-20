<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

  <xsl:template match="*" mode="p-forum-list">
    <xsl:param select="users" name="users"/>
    <xsl:param select="@last_comment_uid" name="uid"/>
    <xsl:param select="$users/item[@id = $uid]" name="user"/>
    <tr>
      <xsl:attribute name="class">
        <xsl:text>p-forum-list </xsl:text>
        <xsl:choose>
          <xsl:when test="position() mod 2 = 1">odd</xsl:when>
          <xsl:otherwise>even</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <td>
        <div class="p-forum-list-title">
          <a href="{&prefix;}forum/{../@tid}/{@nid}"><xsl:value-of select="@title"/></a>
        </div>
        <div class="p-forum-list-comment">
          Последний комментарий: <xsl:value-of select="@last_comment_timestamp"/>, пользователь
          <xsl:apply-templates select="$user" mode="helpers-user-link"/>
        </div>
      </td>	
      <td class="p-forum-list-count"><xsl:value-of select="@comment"/></td>	
      <td class="p-forum-list-created"><xsl:value-of select="@created"/></td>	
      <td/>
    </tr>
  </xsl:template>

  <xsl:template match="module[@name='forum' and @action='show']" mode="p-module">
    <xsl:variable select="theme/@user_id" name="uid"/>
    <xsl:variable select="theme/users/item[@id=$uid]" name="user"/>
    <h1><xsl:value-of select="theme/@title"></xsl:value-of></h1>
    <div class="forum-show-back">
      <a href="{&prefix;}forum/{theme/@tid}">Назад, к списку тем</a>
    </div>
    <div class="forum-show-user">
      <div class="forum-show-user-image">
        <xsl:apply-templates select="$user" mode="helpers-user-image"/>
      </div>
      <xsl:apply-templates select="$user" mode="helpers-user-link"/>
    </div>
    <div class="forum-show-body">
      <xsl:value-of select="theme/@body" disable-output-escaping="yes"></xsl:value-of>
    </div>
    <ul class="forum-show-comments">
      <h2>Комментарии:</h2>
      <xsl:apply-templates select="theme/comments/item" mode="p-comment-forum">
        <xsl:with-param select="theme/users" name="users"/>
      </xsl:apply-templates>
    </ul>
  </xsl:template>

</xsl:stylesheet>
