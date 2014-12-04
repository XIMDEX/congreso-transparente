<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template name="sess" match="Sess">
        <div class="row">
            <div class="">
                <xsl:for-each select="Ses">
                    <xsl:sort select="Numero" data-type="number" order="descending"/>
                    <xsl:apply-templates select="."/>
                </xsl:for-each>
            </div>
        </div>
    </xsl:template>
</xsl:stylesheet>