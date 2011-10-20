<xsl:template match="register_module[@action ='show' and not(@mode)]">
  <div class="register-show module">
    <h2>Регистрация</h2>
    <xsl:choose>
      <xsl:when test="&current_profile;/@authorized = 1"/>
      <xsl:otherwise>
        <xsl:if test="write/@result">
          <xsl:choose>
            <xsl:when test="write/@success">
              <div class="form-notice">
                Вы успешно зарегистрированы. Проверьте почтовый ящик чтобы зайти на сайт
              </div>
            </xsl:when>
            <xsl:otherwise>
              <div class="form-error">
                Возникли проблемы при попытке зарегистрироваться
              </div>
              <xsl:call-template name="register-show-form"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:if>
        <xsl:if test="not(write/@result)">
          <xsl:call-template name="register-show-form"/>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </div>
</xsl:template>

<xsl:template name="register-show-form">
  <form method="post">
    <input type="hidden" value="RegisterWriteModule" name="writemodule" />
    <div class="form-group">
      <div class="form-field">
        <label>Электронная почта</label>
        <input name="email" value="{write/@email}" />
        <p class="form-error-exp"><xsl:value-of select="write/@email_error" /></p>
      </div>
      <div class="form-field">
        <label>Пароль</label>
        <input name="password" type="text" value="" />
        <p class="form-error-exp"><xsl:value-of select="write/@password_error" /></p>
      </div>
      <div class="form-field">
        <label>Никнейм</label>
        <input name="nickname" value="{write/@nickname}" />
        <p class="form-error-exp"><xsl:value-of select="write/@nickname_error" /></p>
      </div>
    </div>
    <div class="form-control">
      <input type="submit" value="Зарегистрироваться" />
    </div>
  </form>
</xsl:template>
