<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:output indent="yes"/>

	<xsl:template name="helpers-lang-code-select">
		<xsl:param name="object" select="book"/>
		<select name="lang_code" class="lang-code-select">
			<xsl:for-each select="$object/lang_codes/item">
				<option value="{@code}">
          <xsl:if test="(($object/@lang_id)=@id) or (not($object/@land_id) and @code='ru')">
						<xsl:attribute name="selected"/>
					</xsl:if>
          <xsl:value-of select="@title"/> (<xsl:value-of select="@code"/>)
				</option>
			</xsl:for-each>
		</select>
		<input name="lang_code" class="lang-code-input" value="{$object/@lang_code}" />
		<script>
    $('.lang-code-select').bind('change', function(){
      $(".lang-code-input").val($(".lang-code-select").val());
    });
		</script>
	</xsl:template>

	<xsl:template name="helpers-role-select">
		<xsl:param name="object" select="book"/>
		<select name="role" class="role-select">
			<xsl:for-each select="$object/roles/item">
				<option value="{@id}">
					<xsl:value-of select="@title"/>
				</option>
			</xsl:for-each>
		</select>
	</xsl:template>

	<xsl:template name="helpers-relation-type-select">
		<xsl:param name="object" select="book"/>
		<select name="relation_type" class="relation_type-select">
			<xsl:for-each select="$object/relation_types/item">
				<option value="{@id}">
					<xsl:value-of select="@name"/>
				</option>
			</xsl:for-each>
		</select>
	</xsl:template>

	<xsl:template name="helpers-this-amount">
		<xsl:param name="amount"/>
		<xsl:param name="words"/>
		<xsl:variable name="mod10" select="$amount mod 10"/>
		<xsl:variable name="f5t20" select="$amount>=5 and not($amount>20)"/>
    <xsl:value-of select="$amount"/>
    <xsl:text>&nbsp;</xsl:text>
		<xsl:choose>
			<xsl:when test="not($f5t20) and $mod10=1">
				<xsl:value-of select="substring-before($words,' ')"/>
			</xsl:when>
			<xsl:when test="not($f5t20) and (not($mod10>5) and $mod10>1)">
				<xsl:value-of select="substring-before(substring-after($words,' '),' ')"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="substring-after(substring-after($words,' '),' ')"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="helpers-abbr-time">
		<xsl:param name="time"/>
		<xsl:if test="$time">
			<abbr class="timeago" title="{$time}">
				<xsl:value-of select="$time"/>
			</abbr>
		</xsl:if>
	</xsl:template>

	<xsl:template match="*" mode="helpers-book-link">
    <a href="{@path}">
			<xsl:value-of select="@title"/>
		</a>
	</xsl:template>

	<xsl:template match="*" mode="helpers-file-link">
    <a href="{@path}">
      <xsl:value-of select="@filetypedesc"/>, <xsl:apply-templates select="." mode="helpers-file-size"/>
		</a>
	</xsl:template>

  <xsl:template match="*" mode="helpers-file-size">
    <xsl:variable select="@size div 1024" name="kb"/>
    <xsl:variable select="$kb div 1024" name="mb"/>
    <xsl:choose>
      <xsl:when test="$mb > 1">
        <xsl:value-of select="round(100*$mb) div 100"/> МБ
      </xsl:when>
      <xsl:when test="$kb > 1">
        <xsl:value-of select="round($kb)"/> КБ
      </xsl:when>
    	<xsl:otherwise></xsl:otherwise>
    </xsl:choose>
    <xsl:if test="$mb > 1">
    </xsl:if>
  </xsl:template>

	<xsl:template match="*" mode="helpers-book-cover">
    <a href="{@path}">
      <img src="{@cover}?{@lastSave}" alt="[{@title}]" />
		</a>
	</xsl:template>

	<xsl:template name="helpers-author-name">
		<xsl:param name="author" select="author"/>
		<xsl:value-of select="$author/@first_name"/>
		<xsl:if test="$author/@middle_name!=''">
			<xsl:text> </xsl:text>
			<xsl:value-of select="$author/@middle_name"/>
		</xsl:if>
		<xsl:if test="($author/@middle_name!='') or ($author/@first_name!='')">
			<xsl:text> </xsl:text>
		</xsl:if>
		<xsl:value-of select="$author/@last_name"/>
	</xsl:template>

	<xsl:template match="*" mode="helpers-author-link">
		<a href="{@path}">
      <xsl:call-template name="helpers-author-name">
        <xsl:with-param select="." name="author"/>
      </xsl:call-template>
		</a>
	</xsl:template>

	<xsl:template match="*" mode="helpers-author-image">
		<a href="{@path}">
      <img src="{@picture}?{@lastSave}" alt="[{@name}]" />
		</a>
	</xsl:template>

	<xsl:template match="*" mode="helpers-user-link">
		<a href="{@path}">
			<xsl:value-of select="@nickname"/>
		</a>
	</xsl:template>

	<xsl:template match="*" mode="helpers-user-image">
		<a href="{@path}">
      <img src="{@picture}?{@lastSave}" alt="[{@nickname}]" title="{@nickname}"/>
		</a>
	</xsl:template>

	<xsl:template match="*" mode="helpers-genre-link">
		<a href="{@path}">
			<xsl:value-of select="@title"/>
		</a>
	</xsl:template>

	<xsl:template match="*" mode="helpers-serie-link">
		<a href="{@path}">
			<xsl:value-of select="@title"/>
		</a>
	</xsl:template>

	<xsl:template match="*" mode="helpers-magazine-link">
		<a href="{@path}">
			<xsl:value-of select="@title"/>
		</a>
	</xsl:template>

  <xsl:template match="*" mode="helpers-variant-link">
		<a href="{@path}">
			<xsl:value-of select="@title"/>
		</a>
  </xsl:template>

  <xsl:template match="*" mode="h-stylesheet">
    <xsl:variable name="path" select="concat(&prefix;,'static/default/css/',@path,'.css')"/>
    <link href="{$path}" media="screen" rel="stylesheet" type="text/css"/>
  </xsl:template>

  <xsl:template match="*" mode="h-javascript">
    <xsl:variable name="path" select="concat(&prefix;,'static/default/js/',@path,'.js')"/>
    <script src="{$path}" type="text/javascript"></script>
  </xsl:template>

</xsl:stylesheet>
