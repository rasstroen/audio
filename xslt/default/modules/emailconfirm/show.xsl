
<xsl:template match="emailconfirm_module[@action ='show']">
	<xsl:choose>
		<xsl:when test="write/@error">
			<xsl:value-of select="write/@error" />
		</xsl:when>
		<xsl:when test="write/@success">
			<p>Вы успешно подтвердили свой почтовый ящик!</p>
		</xsl:when>
	</xsl:choose>
</xsl:template>