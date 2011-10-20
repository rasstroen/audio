
<xsl:template match="content_module[@action='new']">
    <div class="content-new module">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" value="ContentWriteModule" name="writemodule" />
            <div class="form-field">
                <label>URL изображения</label>
                <input type="text" name="url" />
            </div>
            <div class="form-field">
                <label>Файл с компьютера</label>
                <input type="file" name="file" />
            </div>
            <div class="form-field">
                <label>Название</label>
                <input type="text" name="title" />
            </div>
            <div class="form-field">
                <label>Теги (через запятую)</label>
                <input type="text" name="tags" />
            </div>
            <div class="form-control">
                <input type="submit" name="submit" value="Жмячне!" />
            </div>
        </form>
    </div>
</xsl:template>
