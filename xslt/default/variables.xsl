<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns="http://www.w3.org/1999/xhtml" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:variable name="page" select="//page" />
	<xsl:variable name="current_user" select="//page/current_user" />
	<xsl:variable name="prefix">
		<xsl:value-of select="$page/@prefix" />
	</xsl:variable>
	<xsl:variable name="profile" select="//module[@name = 'AuthModule']/data/profile" />
	<xsl:variable name="current_url">
		<xsl:value-of select="$page/@current_url" />
	</xsl:variable>		
</xsl:stylesheet>
