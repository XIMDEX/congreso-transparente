<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template name="resultado" match="Resultado|resultado">
        <div uid="{@uid}">
            <xsl:apply-templates/>
        </div>
    </xsl:template>
</xsl:stylesheet>