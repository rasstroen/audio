
<xsl:template match="users_module[@action='show' and not(@mode)]">
	<xsl:variable name="profile" select="profile" />
	<script src="{&prefix;}static/default/js/profileModule.js"></script>
	<div class="users-show module">
		<div class="users-show-image">
			<img src="{$profile/@picture}?{$profile/@lastSave}" alt="[Image]" />
		</div>
		<div class="users-show-text">
			<h1>
				<xsl:value-of select="$profile/@nickname"/>
			</h1>
			<p>
				<xsl:value-of select="$profile/@rolename"></xsl:value-of>
			</p>
			<p>
				<a href="{&prefix;}user/{$profile/@id}/books">Полки</a>
			</p>
			<p>
				<xsl:if test="&page;/@name = 'me'">
					<a href="{&prefix;}me/wall">Стена</a>
				</xsl:if>
				<xsl:if test="&page;/@name != 'me'">
					<a href="{&prefix;}user/{$profile/@id}/wall">Стена</a>
				</xsl:if>
			</p>
			<p>
				<xsl:text>Живет в городе</xsl:text>
				<b>
					<xsl:value-of select="$profile/@city" disable-output-escaping="yes"/>
				</b>
			</p>
			<p>
				<xsl:text>День рождения </xsl:text>
				<b>
					<xsl:value-of select="$profile/@bdays" disable-output-escaping="yes"/>
				</b>
			</p>
			<xsl:if test="$profile/@about != ''">
				<p>
					<xsl:text>Пара слов о себе:</xsl:text>
					<b>
						<xsl:value-of select="$profile/@about" disable-output-escaping="yes"/>
					</b>
				</p>
			</xsl:if>
			<xsl:if test="$profile/@quote != ''">
				<p>
					<xsl:text>Любимые цитаты:</xsl:text>
					<b>
						<xsl:value-of select="$profile/@quote" disable-output-escaping="yes"/>
					</b>
				</p>
			</xsl:if>
			<xsl:if test="$profile/@id = &current_profile;/@id">
				<a href="{&prefix;}user/{&current_profile;/@id}/edit">редактировать профиль</a>
			</xsl:if>
			<!-- friending. div id="friending" required -->
			<div id="friending" style="display:none"/>
			<script>
				<xsl:text>profileModule_checkFriend(</xsl:text>
				<xsl:value-of select="$profile/@id" />
				<xsl:text>,'</xsl:text>
				<xsl:value-of select="&prefix;" />
				<xsl:text>','</xsl:text>
				<xsl:value-of select="'friending'" />
				<xsl:text>');</xsl:text>
			</script>
		</div>
	</div>
</xsl:template>

<xsl:template match="users_module[@action='show' and @mode='auth']">
	<div class="users-show-auth module">
		<xsl:choose>
			<xsl:when test="profile/@authorized = '1'">
				<a href="{&prefix;}me">
					<img src="{profile/@picture}" />
					<xsl:value-of select="profile/@nickname" />
				</a>
				<a href="{&prefix;}logout">
					<xsl:text>выход</xsl:text>
				</a>	
			</xsl:when>	
			<xsl:otherwise>
				<form method="post">
					<input type="hidden" name="writemodule" value="AuthWriteModule"></input>
					<input type="text" name="email"></input>
					<input type="password" name="password"></input>
					<input type="submit" value="войти"/>
				</form>
				<a href="{&prefix;}register">
					<xsl:text>регистрация</xsl:text>
				</a>
			</xsl:otherwise>
		</xsl:choose>
	</div>
</xsl:template>
