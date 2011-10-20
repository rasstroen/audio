
<xsl:template>
	<h2>
		<xsl:value-of  disable-output-escaping="yes" select="@error_code" />
	</h2>
	<xsl:value-of  disable-output-escaping="yes" select="@error" />	
</xsl:template>