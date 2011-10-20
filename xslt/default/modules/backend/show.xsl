	
<xsl:template>
	<script>
		<xsl:text disable-output-escaping="yes">
		function toggle(id){
			var tg = document.getElementById(id);
			if(tg){
				if(tg.style.display == 'block')
					tg.style.display = 'none';
				else
					tg.style.display = 'block';
			}
		}
		function showAddModule(id_page){
			document.getElementById('moduleList').style.display = 'block';
			document.getElementById('moduleListPageId').value = id_page;
		}
		function showAddPage(id){
			toggle(id)
		}
		</xsl:text>
	</script>
	<style>
		.modtitle{
			font-size:20px;
		}
		.smallint{
			width:44px;
		}
		.block-subtitle-1-add{
			font-size:12px;
		}
		
		.block-subtitle-1-add span{
			margin-left:10px;
		}
		.block-subtitle-1-add span{
			cursor:pointer;
		}
		#moduleList{
			background:#eee;
			width:400px;
			border:1px solid #555;
			height:300px;
			position:absolute;
			top:100px;
			left:50%;
			overflow:auto;
			margin:10px;
		}
		.onoff{
			margin-right:20px;
			float:left;
		}
		
		.onoffr{
			
		}
		a{
			text-decoration:none;
			color:#225!important;
			font-weight:bolder;
		}
		.block-subtitle-1{
			padding:2px 2px 2px 20px;
			background:#bababa;
		}
		.block-subtitle-2{
			margin:2px 2px 2px 50px;
			padding:2px;
			background:#bababa;
		}
		.block-subtitle-3{
			padding-left:80px;
			margin:2px 2px 2px 80px;
			background:#bababe;
		}
		
		.block-subtitle-1-title{
			margin:10px 10px 10px 0px;
		}
		
		.block-subtitle-1-title-input{
			width:200px;
		}
		.block-module-1-title-input{
			width:200px;
		}
		.block-subtitle-1-position-input{
			width:20px;
		}
		
		.block-subtitle-settings{
			background:#efefef;
		}
		
		.block-subtitle-2 .block-subtitle-settings{
			background:#bfbfbf;
		}
		
		.block-subtitle-settings-header{
			font-weight:normal;
			color:#333;
			font-size:15px;
			padding-left:10px;
		}
		
		.block-subtitle-settings-header-1{
			font-weight:bolder;
			margin-left:10px;
		}
		
		.block-subtitle-settings-header-m{
			font-weight:bolder;
			margin-left:10px;
			font-size:13px;
		}
		
		.block-subtitle-settings-header a{
			font-weight:normal;
			color:#333;
			font-size:15px;
			padding:2px 10px 2px 0px;
		}
		
		.module_in_page{
			padding-left:40px;
		}
	</style>
	<xsl:choose>
		<xsl:when test="data/@page = 'pages'">
			<h3>Страницы</h3>
			<div>
				<a href="{&prefix;}backend/pages/generate">сохранить в конфиг</a>
			</div>
			<div>
				<form method="post">
					<input type="hidden" name="writemodule" value="BackendWriteModule"></input>
					<input type="hidden" name="action" value="edit_pages"></input>
					<xsl:call-template name="backend_pages_list" />	
					<input type="submit" value="сохранить"></input>
				</form>
				
			</div>
			<div id="moduleList" style="display:none">
				<form method="post">
					<input type="hidden" name="writemodule" value="BackendWriteModule"></input>
					<input type="hidden" name="action" value="edit_pages_addmodule"></input>
					<input type="hidden" name="id_page" id="moduleListPageId" value="0" />
					<xsl:for-each select="//moduleList/item">
						<div>
							<input type="radio" name="id_module" value="{@id}"></input>
							<xsl:value-of select="@title" />
						</div>	
					</xsl:for-each>
					<div>
						<input type="submit" value="добавить" onclick="addPageModule()" />
					</div>
				</form>
			</div>
			
		</xsl:when>
		<xsl:when test="data/@page = 'modules'">
			<h3>Модули</h3>
			<div>
				<a href="{&prefix;}backend/modules/generate">сохранить в конфиг</a>
			</div>
			<div>
				<form method="post">
					<input type="hidden" name="writemodule" value="BackendWriteModule"></input>
					<input type="hidden" name="action" value="edit_modules"></input>
					<xsl:call-template name="backend_modules_list" />	
					<input type="submit" value="сохранить"></input>
				</form>
				
			</div>
			
		</xsl:when>
	</xsl:choose>
</xsl:template>
<!-- pages -->
<xsl:template name="backend_pages_list">
	<xsl:call-template name="backend_pages_list_level">
		<xsl:with-param name="parent_id" select="0"/>
		<xsl:with-param name="data" select="pages"/>
		<xsl:with-param name="level" select="0"/>
	</xsl:call-template>
</xsl:template>

<xsl:template name="backend_pages_list_level">
	<xsl:param name="data" />
	<xsl:param name="parent_id" />
	<xsl:param name="level" />
	<xsl:for-each select="$data/item[@parent = $parent_id]">
		<xsl:sort select="@name"></xsl:sort>
		<div class="block">
			<xsl:call-template name="backend_pages_list_item">
				<xsl:with-param name="item" select="."/>
				<xsl:with-param name="level" select="$level+1"/>
			</xsl:call-template>
			<xsl:variable name="item" select="." />
			<xsl:if test="//pages/item[@id = ./@id]/@title">
				<div class="block-subtitle-1-add">
					<xsl:attribute name="style">
						<xsl:text>padding-left:</xsl:text>
						<xsl:value-of select="$level*50" />
						<xsl:text>px;</xsl:text>
					</xsl:attribute>
					<span onclick="showAddPage('add{$item/@id}')">добавить вложенный уровень меню к
						<xsl:value-of select="//pages/item[@id = $item/@id]/@title"></xsl:value-of>
					</span> 
					
					
					<div id="add{$item/@id}" style="display:none">
						<input class="block-subtitle-1-position-input" name="position[0][{$item/@id}]" value="" />
						title:
						<input class="block-subtitle-1-title-input" name="title[0][{$item/@id}]" value="" />
						name:
						<input class="block-subtitle-1-title-input" name="name[0][{$item/@id}]" value="" />
						xslt:
						<input class="block-subtitle-1-title-input" name="xslt[0][{$item/@id}]" value=".xsl" />
						uri_path:
						<input class="block-subtitle-1-title-input" name="uri_path[0][{$item/@id}]" value=".xsl" />
						redirect_to_page:
						<input class="block-subtitle-1-title-input" name="uri_redirect[0][{$item/@id}]" value=".xsl" />
					</div>
					<br clear="all" />
				</div>
			</xsl:if>
			<xsl:call-template name="backend_pages_list_level">
				<xsl:with-param name="parent_id" select="@id"/>
				<xsl:with-param name="data" select="$data"/>
				<xsl:with-param name="level" select="$level+1"/>
			</xsl:call-template>
			
		</div>
	</xsl:for-each>
	
	
</xsl:template>

<xsl:template name="backend_pages_list_item">
	<xsl:param name="item" />
	<xsl:param name="level" />
	<div class="block-subtitle-{$level}">
		<xsl:choose>
			<xsl:when test="position() mod 2 = 1">
				<xsl:attribute name="class">
					<xsl:value-of select="concat('block-subtitle-',$level,' odd')"></xsl:value-of>
				</xsl:attribute>
			</xsl:when>
		</xsl:choose>
		<input type="hidden" value="{@id}" name="id[{@id}]" />
		<div class="block-subtitle-1-title">
			<input type="hidden" value="{$item/@parent}" name="parent[{$item/@id}]" />
			<input class="block-subtitle-1-position-input" name="position[{@id}]" value="{$item/@position}" />
			title:
			<input class="block-subtitle-1-title-input" name="title[{@id}]" value="{$item/@title}" />
			name:
			<input class="block-subtitle-1-title-input" name="name[{@id}]" value="{$item/@name}" />
			xslt:
			<input class="block-subtitle-1-title-input" name="xslt[{@id}]" value="{$item/@xslt}" />
			uri_path:
			<input class="block-subtitle-1-title-input" name="uri_path[{@id}]" value="{$item/@uri_path}" />
			grab from pagename:
			<input class="block-subtitle-1-title-input" name="uri_redirect[{@id}]" value="{$item/@uri_redirect}" />
			
		</div>
		<div>
			<xsl:value-of select="$item/@comment" />
		</div>
		<div class="block-subtitle-settings">
			<div class="block-subtitle-settings-header">
				<a href="javascript:void()" onclick="toggle('subplank_m{@id}')">Настройки модулей страницы</a>
				<a href="javascript:void()" onclick="toggle('subplank_c{@id}')">Настройки кеширования</a>
				
			</div>
			<div id="subplank_m{@id}" style="display:none">
				<div class="block-subtitle-settings-header-1">Настройки модулей страницы</div>
				<div class="block-subtitle-settings-header-m">Модули на странице:</div>
				<xsl:call-template name="backend_pages_list_modules">
					<xsl:with-param name="data" select="."/>
				</xsl:call-template>
				<!--div class="block-subtitle-settings-header-m">Наследуемые модули на странице:</div-->
				<!--xsl:call-template name="backend_pages_list_modules_inherited">
					<xsl:with-param name="data" select="//inherited_modules"/>
					<xsl:with-param name="page" select="."/>
				</xsl:call-template-->
			</div>
			<div id="subplank_c{@id}" style="display:none">
				<div class="block-subtitle-settings-header-1">Настройки кеширования</div>
				хранить шаблон(сек):
				<input class="smallint" name="cache_sec[{@id}]" value="{@cache_sec}"></input>
			</div>
		</div>
	</div>	
</xsl:template>

<xsl:template name="backend_pages_list_modules">
	<xsl:param name="data" />
	<xsl:for-each select="$data/modules/item[@inherited=0]">
		<xsl:variable name="item" select="." />
		<div id="subplank_m{$data/@id}_module_{@id}" class="module_in_page">
			<hr/>
			<div class="onoffr">
				<div class="modtitle">
					<xsl:value-of select="@title" />
				</div>
			</div>
			<div class="onoff">
				<span>on</span>
				<input type="radio" name="modules_pages[{$data/@id}][{@id}][enabled][{position()}]" checked="checked" value="1"></input>
				<span>off</span>
				<input type="radio" name="modules_pages[{$data/@id}][{@id}][enabled][{position()}]"  value="0"></input>
			</div>
			action:
			<input name="actions[{$data/@id}][{$item/@id}][{position()}]" value="{@action}"></input>
			mode:
			<input name="mode[{$data/@id}][{$item/@id}][{position()}]" value="{@mode}"></input>
			comment:
			<input style="width:300px" name="comment[{$data/@id}][{$item/@id}][{position()}]" value="{@comment}"></input>
			<br clear="all" />
			<!--div>блок рисуется только для:</div>
			<xsl:variable name="roles" select="//module/data/roles/item" /-->
			<!--xsl:for-each select="$roles">
				<xsl:variable name="role" select="." />
				<input type="checkbox" name="pmr[{$data/@id}][{$item/@id}][{@id}][../position()]">
					<xsl:if test="$item/roles/item[@id = $role/@id]">
						<xsl:attribute name="checked">
							<xsl:text>checked</xsl:text>
						</xsl:attribute>
					</xsl:if>		
					
				</input>
				<xsl:value-of select="@name" />
			</xsl:for-each-->
			<!--div>
				<xsl:call-template name="drawPMBlocks">
					<xsl:with-param name="id_module" select="@id"></xsl:with-param>
					<xsl:with-param name="id_page" select="$data/@id"></xsl:with-param>
					<xsl:with-param name="id_block" select="//pages/item[@id = $data/@id]/modules/item[@id = $item/@id]/@block"></xsl:with-param>
				</xsl:call-template>
			</div-->
			<div>
				<b>параметры:</b>
			</div>
			<xsl:for-each select="params/item">
				<div>
					<input name="paramname[{$data/@id}][{$item/@id}][{position()}][{../../@action}][m{../../@mode}]" value="{@name}" />
					<input value="{@type}" name="paramtype[{$data/@id}][{$item/@id}][{position()}][{../../@action}][m{../../@mode}]">
						
					</input>
					<input name="param[{$data/@id}][{$item/@id}][{position()}][{../../@action}][m{../../@mode}]" value="{@value}"></input>
				</div>
			</xsl:for-each>
			<div>
				<input name="paramname[{$data/@id}][{$item/@id}][{-position()}][{@action}][m{@mode}]" value="" />
				<input value="" name="paramtype[{$data/@id}][{$item/@id}][{-position()}][{@action}][m{@mode}]" />
					
				<input name="param[{$data/@id}][{$item/@id}][{-position()}][{@action}][m{@mode}]" value=""></input>
			</div>
			<br clear="all" />
		</div>
	</xsl:for-each>
	<a href="javascript:void()" onclick="showAddModule({$data/@id})">[добавить модуль]</a>
</xsl:template>
<xsl:template name="backend_pages_list_modules_inherited">
	<xsl:param name="data" />
	<xsl:param name="page" />
	<xsl:for-each select="$data/item">
		<xsl:variable name="item" select="." />
		<div class="module_in_page">
			<input type="hidden" name="modules_pages[{$page/@id}][{@id}][inherited]" value="1" />
			<div class="onoff">
				<span>on</span>
				<input type="radio" name="modules_pages[{$page/@id}][{@id}][enabled]" value="1">
					<xsl:if test="//pages/item[@id = $page/@id]/modules/item[@id = $item/@id]/@enabled=1">
						<xsl:attribute name="checked">
							<xsl:text>checked</xsl:text>
						</xsl:attribute>
					</xsl:if>					
				</input>
				<span>off</span>
				<input type="radio" name="modules_pages[{$page/@id}][{@id}][enabled]"  value="0">
					<xsl:if test="//pages/item[@id = $page/@id]/modules/item[@id = $item/@id]/@enabled=0">
						<xsl:attribute name="checked">
							<xsl:text>checked</xsl:text>
						</xsl:attribute>
					</xsl:if>	
				</input>
			</div>
			<div class="onoffr">
				<div class="modtitle">
					<xsl:value-of select="@title" />
				</div>
			</div>
			<br clear="all" />
			<div>блок рисуется только для:</div>
			<xsl:variable name="roles" select="//module/data/roles/item" />
			<xsl:for-each select="$roles">
				<xsl:variable name="role" select="." />
				<input type="checkbox" name="pmr[{$page/@id}][{$item/@id}][{@id}]">
					<xsl:if test="//pages/item[@id = $page/@id]/modules/item[@id = $item/@id]/roles/item[@id = $role/@id]">
						<xsl:attribute name="checked">
							<xsl:text>checked</xsl:text>
						</xsl:attribute>
					</xsl:if>		
					
				</input>
				<xsl:value-of select="@name" />
			</xsl:for-each>
			<!--div>
				<xsl:call-template name="drawPMBlocks">
					<xsl:with-param name="id_module" select="@id"></xsl:with-param>
					<xsl:with-param name="id_page" select="$page/@id"></xsl:with-param>
					<xsl:with-param name="id_block" select="//pages/item[@id = $page/@id]/modules/item[@id = $item/@id]/@block"></xsl:with-param>
				</xsl:call-template>
			</div-->
			<br clear="all" />
		</div>
	</xsl:for-each>
</xsl:template>
<!-- modules ////////////////////////////////////////////////////////////////////////////////////////////////////////////////-->
<xsl:template name="backend_modules_list">
	<xsl:call-template name="backend_modules_list_level">
		<xsl:with-param name="data" select="modules"/>
		<xsl:with-param name="level" select="0"/>
	</xsl:call-template>
</xsl:template>

<xsl:template name="backend_modules_list_level">
	<xsl:param name="data" />
	<xsl:param name="level" />
	<xsl:for-each select="$data/item">
		<div class="block">
			<xsl:call-template name="backend_modules_list_item">
				<xsl:with-param name="item" select="."/>
				<xsl:with-param name="level" select="$level+1"/>
			</xsl:call-template>
		</div>
	</xsl:for-each>
	<div class="block">
		<xsl:call-template name="backend_modules_list_item_add">

		</xsl:call-template>
	</div>
</xsl:template>

<xsl:template name="backend_modules_list_item">
	<xsl:param name="item" />
	<xsl:param name="level" />
	<div class="block-subtitle-{$level}">
		<input type="hidden" value="{@id}" name="id[{@id}]" />
		<div class="block-subtitle-1-title">
			title:
			<input class="block-subtitle-1-title-input" name="title[{@id}]" value="{$item/@title}" />
			name:
			<input class="block-subtitle-1-title-input" name="name[{@id}]" value="{$item/@name}" />
			xslt:
			<input class="block-subtitle-1-title-input" name="xslt[{@id}]" value="{$item/@xslt}" />
			<input type="checkbox" name="inherited[{@id}]" >
				<xsl:if test="@inherited = 1">
					<xsl:attribute name="checked">
						<xsl:text>checked</xsl:text>
					</xsl:attribute>
				</xsl:if>
			</input>inherited
			<input type="checkbox" name="delete[{@id}]" >
			</input>
			<b>delete</b>
		</div>
		<div>
			<xsl:value-of select="$item/@comment" />
		</div>
		<div class="block-subtitle-settings">
			<div class="block-subtitle-settings-header">
				<a href="javascript:void()" onclick="toggle('subplank_c{@id}')">Настройки кеширования</a>
				
			</div>
			<div id="subplank_c{@id}" style="display:none">
				<div class="block-subtitle-settings-header-1">Настройки кеширования</div>
				хранить(сек):
				<input class="smallint" name="cache_sec[{@id}]" value="{@cache_sec}"></input>
				хранить xHTML:
				<input class="smallint" name="xHTML[{@id}]" type="checkbox">
					<xsl:if test="@xHTML = 1">
						<xsl:attribute name="checked">
							<xsl:text>checked</xsl:text>
						</xsl:attribute>
					</xsl:if>	
				</input>
			</div>
		</div>
		
	</div>	
</xsl:template>

<xsl:template name="backend_modules_list_item_add">
	<div class="block-subtitle-1">
		<input type="hidden" value="0" name="id[0]" />
		<div class="block-subtitle-1-title">
			title:
			<input class="block-subtitle-1-title-input" name="title[0]" value="" />
			name:
			<input class="block-subtitle-1-title-input" name="name[0]" value="" />
			xslt:
			<input class="block-subtitle-1-title-input" name="xslt[0]" value="" />
			<input type="checkbox" name="inherited[{@id}]" >
			</input>inherited
			
		</div>		
	</div>	
</xsl:template>

<xsl:template name="drawPMBlocks">
	<xsl:param name="id_module" />
	<xsl:param name="id_page" />
	<xsl:param name="id_block" />
	блок:
	<select name="pm[{$id_page}][{$id_module}]">
		<xsl:for-each select="//blocks/item">
			<option value="{@id}">
				<xsl:if test="$id_block = @id">
					<xsl:attribute name="selected">
						<xsl:text>selected</xsl:text>	
					</xsl:attribute>	
				</xsl:if>
				<xsl:value-of select="@name" />
			</option>
		</xsl:for-each>
	</select>
</xsl:template>
