<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>
	<xsl:output indent="yes"/>
	<xsl:template match="/">
		<html>
			<head>
				<title>Админко</title>
			</head>
			<body>
				<table border="1" cellspacing="0" cellpadding="0" width="100%" height="100%">
					<tr>
						<td valign="top" width="300px" style="padding:10px;">
							<ul>
								<li>
									<a href="{&prefix;}backend/">Админка</a>
								</li>
								<ul>
									<li>
										<a href="{&prefix;}backend/pages">Страницы</a>
									</li>
									<li>
										<a href="{&prefix;}backend/modules">Модули</a>
									</li>
									<li>
										<a href="{&prefix;}backend/translate">Переводы</a>
									</li>
								</ul>
							</ul>
						</td>	
						<td valign="top" style="padding:10px;">
							<xsl:apply-templates />	
						</td>
					</tr>	
				</table>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
