
<xsl:template match="content_module[@action='show' and @mode='random']">
	<xsl:apply-templates select="picture" mode="picture-item"></xsl:apply-templates>
</xsl:template>

<xsl:template match="content_module[@action='show' and not(@mode)]">
	<xsl:apply-templates select="picture" mode="picture-item"></xsl:apply-templates>
</xsl:template>

<xsl:template match="*" mode="picture-item">
	<div class="picture-item">
		<div class="picture-item header">
			<div class="picture-item id">
				<a href="{@link_url}">
					<xsl:text>#</xsl:text>
					<xsl:value-of select="@id" />
				</a>
			</div>
			<div class="picture-item title">
				<xsl:value-of select="@title" />
	
			</div>
		</div>
		<div class="picture-item image">
			<img src="{@source}" />
		</div>
	</div>
</xsl:template>
