<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<xsl:template match="module[@name='features' and @action='new']" mode="p-module">
		<form method="post" action="">
			<input type="hidden" name="writemodule" value="FeaturesWriteModule" />
			<input type="hidden" name="id" value="0" />
			<div class="form-group">
				<h2>Добавление теста</h2>
				<div class="form-field">
					<label>Название теста</label>
					<input name="title"/>
				</div>
				<h2>Добавление теста</h2>
				<div class="form-field">
					<label>Пояснение к тесту</label>
					<textarea name="description"></textarea>
				</div>
				<div class="form-field">
					<label>Путь до файла (относительно папки features/)</label>
					<input name="filepath"/>
				</div>
			</div>
			<div class="form-control">
				<input type="submit" value="Сохранить информацию"/>
			</div>
		</form>
		<script type="text/javascript">
      tinyMCE.init({mode:"textareas"});
		</script>
	</xsl:template>

	<xsl:template match="*" mode="p-feature-groups">
		<xsl:if test="parent::module/write/@run_result">
			<div style="width:100%;overflow:auto">
				<pre>
					<xsl:value-of select="parent::module/write/@run_result" disable-output-escaping="yes" />	
				</pre>
			</div>
		</xsl:if>
		<table width="100%">
			<tr>
				<td>
					заголовок
				</td>
				<td>
					файл теста
				</td>
				<td>
					последний запуск
				</td>
				<td>
				</td>
			</tr>
			<xsl:apply-templates select="item" mode="p-feature-group-list-item"/>
		</table>
	</xsl:template>
	
	<xsl:template match="*" mode="p-feature-group-list-item">
		<tr>
			<td colspan="4">
				<h2>
					<xsl:value-of select="@title" />
				</h2>
			</td>
		</tr>
		<xsl:apply-templates select="features" mode="p-feature-list" />		
	</xsl:template>

	<xsl:template match="*" mode="p-feature-show">
		<ul>
			<xsl:apply-templates select="." mode="p-feature-item" />
		</ul>
	</xsl:template>

	<xsl:template match="*" mode="p-feature-list">
		<xsl:apply-templates select="item" mode="p-feature-list-item" />
	</xsl:template>

	<xsl:template match="*" mode="p-feature-list-item">
    <tr class="p-feature-list {@status_description}">
			<td class="p-feature-list-title">
				<a href="{@path}">
					<xsl:value-of select="@title" />
				</a>
			</td>
			<td>
				<xsl:value-of select="@filepath" />
			</td>
			<td>
				<xsl:value-of select="@last_run" />
			</td>
			<td>
				<form method="post">
					<input type="hidden" value="FeaturesWriteModule" name="writemodule" />
					<input type="hidden" value="run" name="action" />
					<input type="hidden" value="{@id}" name="id" />
					<input type="submit" value="run" />
				</form>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="*" mode="p-feature-item">
		<tr>
			<td>
				<a href="{@path}">
					<xsl:value-of select="@title" />
				</a>
			</td>
			<td>
				<xsl:value-of select="@status" />
			</td>
			<td>
				<xsl:value-of select="@filepath" />
			</td>
			<td>
				<xsl:value-of select="@last_run" />
			</td>
		</tr>
		<div>
			<h3>описание теста</h3>
			<xsl:value-of select="@description" disable-output-escaping="yes"/>
		</div>
		<div>
			<h3>последний результат тестирования</h3>
			<pre>
				<xsl:value-of select="@last_message" disable-output-escaping="yes"/>
			</pre>
		</div>
	</xsl:template>



</xsl:stylesheet>
