<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:template match="&page;" mode="l-head">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />	
		<title>
			<xsl:value-of select="@title"></xsl:value-of>
		</title>
    <script>
      var exec_url ='<xsl:value-of select="&prefix;"/>';
      var user_role = '<xsl:value-of select="&current_profile;/@role"/>';
    </script>
    <xsl:apply-templates select="&structure;/data/stylesheet" mode="h-stylesheet"/>
    <xsl:apply-templates select="&structure;/data/javascript" mode="h-javascript"/>
	</xsl:template>

	<xsl:template match="&root;">
		<head>
			<xsl:apply-templates select="&page;" mode="l-head" />
		</head>
		<body class="l-body">
			<div class="l-header">
				<xsl:apply-templates select="&root;" mode="l-header" />
			</div>
			<div class="l-wrapper">
				<div class="l-content">
					<xsl:apply-templates select="&structure;/blocks/content/module" mode="l-content"/>
				</div>
			</div>
			<div class="l-footer">
				<xsl:call-template name="l-debug"></xsl:call-template>
			</div>
		</body>
	</xsl:template>

	<xsl:template match="*" mode="l-header">
    <div class="l-header-module">
      <xsl:apply-templates select="&structure;/blocks/header/module" mode="l-header-module"/>
    </div>
		<div class="l-header-nav">
			<xsl:apply-templates select="&root;" mode="l-header-nav" />
		</div>
	</xsl:template>

	<xsl:template match="*" mode="l-content">
    <xsl:apply-templates select="." mode="modules"/>
	</xsl:template>

	<xsl:template match="*" mode="l-header-module">
    <xsl:apply-templates select="." mode="modules"/>
	</xsl:template>
	
  <xsl:template match="*" mode="modules">
    <xsl:if test="current()/@mode">
      <xsl:apply-templates select="&root;/module[@name = current()/@name and @action=current()/@action and @mode = current()/@mode]" />
    </xsl:if>
    <xsl:if test="not(current()/@mode)">
      <xsl:apply-templates select="&root;/module[@name = current()/@name and @action=current()/@action and not(@mode)]" />
    </xsl:if>
  </xsl:template>

	<xsl:template match="*" mode="l-header-search">
    <form action="{&prefix;}search">
      <input name="q" type="text" value="{&page;/variables/@q}"/>
    </form>
	</xsl:template>

	<xsl:template match="*" mode="l-header-nav">
		<ul>
			<li>
				<a href="{&prefix;}features">Список тестов</a>
			</li>
		</ul>
		<ul>
			<p>
				<a href="{&prefix;}features">Тесты</a>
			</p>	
		</ul>
		
	</xsl:template>

	<xsl:template match="*" mode="l-footer-nav">
		<ul>
			<li>
				<a href="">Условия использования</a>
			</li>
			<li>
				<a href="">О проекте</a>
			</li>
			<li>
				<a href="">Помощь</a>
			</li>
			<li>
				<a href="">Правила</a>
			</li>
		</ul>
	</xsl:template>

	<xsl:template name="l-debug">
		<div class="l-debug">
			<h2>Debuggg</h2>
      <a href="{&page;/@current_url}serxml" target="_blank">serxml</a>
		</div>
	</xsl:template>

</xsl:stylesheet>
