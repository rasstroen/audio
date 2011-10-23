<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

  <xsl:template match="module[@name='users' and @action='show' and not(@mode)]" mode="p-module">
    <xsl:variable name="profile" select="profile" />
    <input type="hidden" name="id" value="{$profile/@id}" />
    <script src="{&prefix;}static/default/js/profileModule.js"></script>
    <script src="{&prefix;}static/default/js/users_module.js"></script>
    <div class="p-user-show-image">
      <img src="{$profile/@picture}?{$profile/@lastSave}" alt="[Image]" />
    </div>
    <div class="p-user-show-text">
      <h1><xsl:value-of select="$profile/@nickname"/></h1>

      <div class="p-user-show-text-role">
        <xsl:value-of select="$profile/@rolename"/>
      </div>

      <p>
        <xsl:if test="($profile/@id = &current_profile;/@id) or (&role; > 49)">
          <a href="{$profile/@path_edit}">Редактировать профиль</a>
        </xsl:if>
      </p>

      <p>
        <xsl:if test="not ($profile/@id = &current_profile;/@id)">
          <a href="{$profile/@path_message}">Написать сообщение</a>
        </xsl:if>
      </p>

      <p><a href="{&prefix;}user/{$profile/@id}/books">Полки</a></p>

      <xsl:choose>
        <xsl:when test="$profile/@id = &current_profile;/@id">
          <p><a href="{&prefix;}me/wall">Стена</a></p>
        </xsl:when>
        <xsl:otherwise>
          <p><a href="{&prefix;}user/{$profile/@id}/wall">Стена</a></p>
        </xsl:otherwise>
      </xsl:choose>

      <p>
        <xsl:text>Живет в городе</xsl:text>
        <b><xsl:value-of select="$profile/@city" disable-output-escaping="yes"/></b>
      </p>
      <p>
        <xsl:text>День рождения </xsl:text>
        <b><xsl:value-of select="$profile/@bdays" disable-output-escaping="yes"/></b>
      </p>
      <xsl:if test="$profile/@about != ''">
        <p>
          <xsl:text>Пара слов о себе:</xsl:text>
          <b><xsl:value-of select="$profile/@about" disable-output-escaping="yes"/></b>
        </p>
      </xsl:if>
      <xsl:if test="$profile/@quote != ''">
        <p>
          <xsl:text>Любимые цитаты:</xsl:text>
          <b><xsl:value-of select="$profile/@quote" disable-output-escaping="yes"/></b>
        </p>
      </xsl:if>

      <xsl:if test="&role; > 39">
        <a href="{&page;/@current_url}log">Изменения, сделанные пользователем</a>

        <div class="p-user-show-text-vandal">
          <a class="make-vandal" href="#">Сделать
            <xsl:choose>
              <xsl:when test="$profile/@role='20'">читателем</xsl:when>
              <xsl:otherwise>вандалом</xsl:otherwise>
            </xsl:choose>
          </a>
        </div>
      </xsl:if>

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
  </xsl:template>

  <xsl:template match="module[@name='users' and @action='show' and @mode='auth']" mode="p-module">
    <div class="p-user-auth">
      <xsl:choose>
        <xsl:when test="profile/@authorized = '1'">
          <div class="p-user-auth-image"><xsl:apply-templates select="profile" mode="helpers-user-image"/></div>
          <xsl:apply-templates select="profile" mode="helpers-user-link"/> | 
          <a href="{&prefix;}logout">Выход</a>	
        </xsl:when>	
        <xsl:otherwise>
          <form method="post">
            <input type="hidden" name="writemodule" value="AuthWriteModule"></input>
            <input type="text" name="email"></input>
            <input type="password" name="password"></input>
            <input type="submit" value="Войти" name="login"/>
          </form>
          <a href="{&prefix;}register">
            <xsl:text>Регистрация</xsl:text>
          </a>
        </xsl:otherwise>
      </xsl:choose>
    </div>
  </xsl:template>

  <xsl:template match="*" mode="p-user-list">
    <li class="p-user-list">
      <xsl:apply-templates select="." mode="helpers-user-image"/>
      <p class="p-user-list-name">
        <xsl:apply-templates select="." mode="helpers-user-link"/>
      </p>
    </li>
  </xsl:template>

  <xsl:template match="module[@name='users' and @action='edit']" mode="p-module">
    <xsl:variable name="profile" select="profile"/>
    <form method="post" enctype="multipart/form-data" action="{&prefix;}user/{$profile/@id}">
      <input type="hidden" name="writemodule" value="ProfileWriteModule" />
      <input type="hidden" name="id" value="{$profile/@id}" />
      <div class="form-group">
        <h2>Информация</h2>
        <div class="form-field">
          <label>Ник</label>
          <b><xsl:value-of select="$profile/@nickname"></xsl:value-of></b>
        </div>
        <div class="form-field">
          <label>Почта</label>
          <b><xsl:value-of select="$profile/@email"></xsl:value-of></b>
        </div>
        <div class="form-field">
          <label>Роль</label>
          <select name="role">
            <xsl:for-each select="roles/item">
              <option value="{@id}">
                <xsl:if test="$profile/@role = current()/@id">
                  <xsl:attribute name="selected" />	
                </xsl:if>
                <xsl:value-of select="@title" />
              </option>
            </xsl:for-each>
          </select>
        </div>
        <div class="form-field">
          <label>Дата рождения</label>
          <input name="bday" value="{$profile/@bday}" />
        </div>
        <div class="form-field">
          <label>Аватар</label>
          <input type="file" name="picture"></input>
        </div>
        <xsl:call-template name="profile_edit_cityLoader">
          <xsl:with-param name="current_city" select="$profile/@city_id" />
        </xsl:call-template>
        <div class="form-field">
          <label for="">Пару слов о себе</label>
          <textarea name="about">
            <xsl:value-of select="$profile/@about" disable-output-escaping="yes" />	
          </textarea>
        </div>
        <div class="form-field">
          <label for="">Мои любимые цитаты</label>
          <textarea name="quote">
            <xsl:value-of select="$profile/@quote" disable-output-escaping="yes" />	
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <h2>Контакты</h2>
        <div class="form-field">
          <label for="">Facebook</label>
          <input name="link_fb" value="{$profile/@link_fb}"></input>
        </div>
        <div class="form-field">
          <label for="">Livejournal</label>
          <input name="link_lj" value="{$profile/@link_lj}"></input>
        </div>
        <div class="form-field">
          <label for="">Vkontakte</label>
          <input name="link_vk" value="{$profile/@link_vk}"></input>
        </div>
        <div class="form-field">
          <label for="">Twitter</label>
          <input name="link_tw" value="{$profile/@link_tw}"></input>
        </div>
      </div>
      <div class="form-control">
        <input type="submit" value="Сохранить информацию"/>
      </div>
    </form>
    <script type="text/javascript">
      $(function() {
      $("input[name='bday']").datepicker($.datepicker.regional["ru"] = {dateFormat: 'yy-mm-dd'});
      });
    </script>
  </xsl:template>

  <xsl:template name="profile_edit_cityLoader">
    <xsl:param name="current_city"></xsl:param>
    <div class="form-field">
      <label>Страна:</label>
      <div id="counry_div">загружаем...</div>
    </div>
    <div class="form-field">
      <label>Город:</label>
      <div id="city_div">загружаем...</div>
    </div>
    <script>
      <xsl:text>profileModule_cityInit('counry_div','city_div','</xsl:text>
      <xsl:value-of select="$current_city"></xsl:value-of>
      <xsl:text>','</xsl:text>
      <xsl:value-of select="&prefix;"></xsl:value-of>
      <xsl:text>');</xsl:text>
    </script>
  </xsl:template>

</xsl:stylesheet>
