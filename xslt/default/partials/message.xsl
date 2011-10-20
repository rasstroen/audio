<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

  <xsl:template match="*" mode="p-message-list">
    <xsl:param select="users" name="users"/>
    <xsl:param name="uid" select="@id_author"/>
    <xsl:param name="user" select="$users/item[@id=$uid]"/>
    <li id="message-{@thread_id}-{@id}">
      <xsl:attribute name="class">p-message-list <xsl:if test="@is_new=1">new</xsl:if></xsl:attribute>
      <div class="p-message-list-image">
        <xsl:apply-templates select="$user" mode="helpers-user-image"/>
      </div>
      <div class="p-message-list-text">
        <div class="p-message-list-text-time">
          <xsl:call-template name="helpers-abbr-time">
            <xsl:with-param select="@time" name="time"/>
          </xsl:call-template>
        </div>
        <p class="p-message-list-text-user">
          <xsl:apply-templates select="$user" mode="helpers-user-link"/>
        </p>
        <p class="p-message-list-text-subject">
          <xsl:value-of select="@subject"></xsl:value-of>
        </p>
        <a class="p-message-list-text-delete" href="#">Удалить</a>
        <div class="p-message-list-text-doby">
          <a href="{&prefix;}me/messages/{@thread_id}">
            <xsl:value-of select="@html"></xsl:value-of>
          </a>
        </div>
      </div>
    </li>
  </xsl:template>

  <xsl:template match="*" mode="p-message-thread">
    <xsl:param select="users" name="users"/>
    <xsl:param name="uid" select="@id_author"/>
    <xsl:param name="user" select="$users/item[@id=$uid]"/>
    <li class="messages-list-item">
      <xsl:value-of select="@time" />
      <div class="messages-list-item-image">
        <xsl:apply-templates select="$user" mode="helpers-user-image"/>
      </div>
      <div class="messages-list-item-text">
        <p class="messages-list-item-text-user">
          <xsl:apply-templates select="$user" mode="helpers-user-link"/>
        </p>
        <p class="messages-list-item-text-subject">
          <i><xsl:value-of select="@subject"></xsl:value-of></i>
        </p>
        <div class="messages-list-item-text-doby">
          <xsl:value-of select="@html"></xsl:value-of>
        </div>
      </div>
    </li>
  </xsl:template>

  <xsl:template match="module[@name='messages' and @action='new']" mode="p-module">
    <xsl:if test="(&current_profile;)/@id">
      <h2>Новое сообщение</h2>
      <form method="post">
        <input type="hidden" value="MessagesWriteModule" name="writemodule" />
        <input type="hidden" value="{message/@thread_id}" name="thread_id" />
        <input type="hidden" value="{&current_profile;/@id}" name="id_author" />
        <xsl:if test="message/@thread_id=0">
          <div class="form-field">
            <label for="subject">Тема сообщения</label>
            <input type="text" name="subject" />
          </div>
        </xsl:if>
        <div class="form-field">
          <label for="body">Текст сообщения</label>
          <textarea name="body"/>
        </div>
        <xsl:if test="message/@thread_id=0">
          <div class="form-field">
            <label for="to">Получатели</label>
            <input type="text" name="to[]" value="{&page;/variables/@to}"/>
          </div>
        </xsl:if>
        <div class="form-control">
          <input type="submit" value="Оставить отзыв" />
        </div>
      </form>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>

