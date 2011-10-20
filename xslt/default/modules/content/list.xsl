
<xsl:template match="content_module[@action='list']">
    <xsl:apply-templates select="pictures/item" mode="picture-list-item"></xsl:apply-templates>
</xsl:template>


<xsl:template match="*" mode="picture-list-item">
    <div class="picture-list-item container">
        <div class="picture-list-item title">
            <a href="{&prefix;}pictures/{@id}" alt="{@title}" title="{@title}">
                <xsl:value-of select="@title" />
            </a>
        </div>
        <div class="picture-list-item image" style="background: url({@source}) center center no-repeat">
            <a href="{&prefix;}pictures/{@id}" alt="{@title}" title="{@title}">
            </a>
        </div>       
    </div>
</xsl:template>