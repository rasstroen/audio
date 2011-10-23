<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>

	<xsl:template match="module">
    <xsl:apply-templates select="conditions/item[not(@mode='paging')]" mode="p-misc-condition"/>
    <div class="m-{@name}-{@action} module">
      <xsl:apply-templates select="." mode="p-module"/>
    </div>
    <xsl:apply-templates select="conditions/item[@mode='paging']" mode="p-misc-condition"/>
	</xsl:template>
 
  <xsl:template match="module[@name='features' and @action='list']" mode="p-module">
    <xsl:param name="title" select="features/@title"/>
    <xsl:param name="amount" select="features/@count"/>
    <h2><xsl:value-of select="$title" /> (<xsl:value-of select="$amount"/>)</h2>
    <xsl:apply-templates select="feature_groups" mode="p-feature-groups"/>
  </xsl:template>
  
  <xsl:template match="module[@name='features' and @action='show']" mode="p-module">
    <xsl:apply-templates select="feature" mode="p-feature-show"/>
  </xsl:template>

  <xsl:template match="module[@name='series' and @action='list']" mode="p-module">
    <xsl:param name="amount" select="20"/>
    <h1 class="series-list-title">
      Серии <xsl:if test="series/@count"> (<xsl:value-of select="series/@count"/>)</xsl:if>
    </h1>
    <xsl:apply-templates select="series/item[not (position()>$amount)]" mode="p-serie-list">
      <xsl:with-param select="authors" name="authors"></xsl:with-param>
    </xsl:apply-templates>
  </xsl:template>

  <xsl:template match="module[@name='series' and @action='list' and @mode='loved']" mode="p-module">
    <xsl:param name="amount" select="5"/>
    <h2 class="books-list-title">
      <xsl:value-of select="series/@title"/>
      <xsl:if test="series/@count"> (<xsl:value-of select="series/@count"/>)</xsl:if>
    </h2>
    <xsl:apply-templates select="series/item" mode="p-serie-loved"/>
    <xsl:if test="series/@link_title and series/@link_url">
      <div class="m-series-list-link">
        <a href="{&prefix;}{series/@link_url}">
          <xsl:value-of select="series/@link_title"></xsl:value-of>
        </a>
      </div>
    </xsl:if>
  </xsl:template>

  <xsl:template match="module[@name='users' and @action='list']" mode="p-module">
    <xsl:param name="users" select="users"/>
    <xsl:param name="amount" select="4"/>
    <h2>
      <xsl:value-of select="$users/@title"/>
      <xsl:if test="$users/@count"> (<xsl:value-of select="$users/@count"/>)</xsl:if>
    </h2>
    <xsl:apply-templates select="users/item[not (position()>$amount)]" mode="p-user-list" />
    <xsl:if test="$users/@link_title and $users/@link_url">
      <div class="m-users-list-link">
        <a href="{&prefix;}{$users/@link_url}">
          <xsl:value-of select="$users/@link_title"></xsl:value-of>
        </a>
      </div>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>
