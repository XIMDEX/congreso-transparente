<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template name="votacion" match="Votacion">
     <tr>
       <td>
    <xsl:value-of select="Diputado"/>
   </td>
       <td>
    <xsl:value-of select="Grupo"/>
   </td>
       <td>
    <xsl:value-of select="Voto"/>
   </td>
      </tr>
    </xsl:template>
</xsl:stylesheet>
