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
		<script src="{&prefix;}static/default/js/jquery.min.js"></script>
    <script src="{&prefix;}static/default/js/application.js"></script>
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
				<div class="l-sidebar">
          <ul class="l-sidebar-add">
            <li class="l-sidebar-add-item"><a href="{&prefix;}book/new">Добавить книгу</a></li>
          	<li class="l-sidebar-add-item"><a href="{&prefix;}author/new">Добавить автора</a></li>
          	<li class="l-sidebar-add-item"><a href="{&prefix;}series/new">Добавить серию</a></li>
          </ul>
					<xsl:apply-templates select="&structure;/blocks/sidebar/module" mode="l-sidebar"/>
				</div>
			</div>
			<div class="l-footer">
				<xsl:apply-templates select="&root;" mode="l-footer" />
				<xsl:call-template name="l-debug"></xsl:call-template>
			</div>
		</body>
	</xsl:template>

	<xsl:template match="*" mode="l-header">
		<div class="l-header-logo">
			<h1>
        <a>
          <xsl:if test="&prefix;!=&page;/@current_url">
            <xsl:attribute name="href">
              <xsl:value-of select="&prefix;">
              </xsl:value-of>
            </xsl:attribute>
          </xsl:if>
          Либрусек
        </a>
			</h1>
		</div>
    <div class="l-header-module">
      <xsl:apply-templates select="&structure;/blocks/header/module" mode="l-header-module"/>
    </div>
		<div class="l-header-search">
			<xsl:apply-templates select="&root;" mode="l-header-search" />
		</div>
		<div class="l-header-nav">
			<xsl:apply-templates select="&root;" mode="l-header-nav" />
		</div>
	</xsl:template>

	<xsl:template match="*" mode="l-content">
    <xsl:apply-templates select="." mode="modules"/>
	</xsl:template>

	<xsl:template match="*" mode="l-sidebar">
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

	<xsl:template match="*" mode="l-footer">
		<div class="l-footer-nav">
			<xsl:apply-templates select="&root;" mode="l-footer-nav" />
		</div>
	</xsl:template>

	<xsl:template match="*" mode="l-header-search">
    <form action="{&prefix;}search">
      <input name="q" type="text" value="{&page;/variables/@q}"/>
    </form>
	</xsl:template>

	<xsl:template match="*" mode="l-header-nav">
		<ul>
			<p>
				<a href="{&prefix;}books">Книги</a>
			</p>
			<li>
				<a href="{&prefix;}new">Новые</a>
			</li>
			<li>
				<a href="{&prefix;}popular">Популярные</a>
			</li>
			<li>
				<a href="{&prefix;}authors">Авторы</a>
			</li>
			<li>
				<a href="{&prefix;}genres">Жанры</a>
			</li>
			<li>
				<a href="{&prefix;}series">Серии</a>
			</li>
		</ul>
		<ul>
			<p>
				<a href="{&prefix;}">Клуб</a>
			</p>
			<li>
				<a href="{&prefix;}forum">Форум</a>
			</li>
			<li><a href="{&prefix;}tracker">Активность</a></li>
			<!--<li>-->
				<!--<a href="{&prefix;}">Вычитка</a>-->
			<!--</li>-->
		</ul>
		<!--<ul>-->
			<!--<p>-->
				<!--<a href="{&prefix;}">Абонемент</a>-->
			<!--</p>-->
			<!--<li>-->
				<!--<a href="{&prefix;}">Оплатить</a>-->
			<!--</li>-->
			<!--<li>-->
				<!--<a href="{&prefix;}">Заработать</a>-->
			<!--</li>-->
		<!--</ul>-->
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
