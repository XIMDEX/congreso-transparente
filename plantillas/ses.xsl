<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
                                     <xsl:template name="ses" match="Ses">
                                       <div class="row sesions">
                                      	<div class="col-md-4 sesion-info">
                                          <span class="session-text">Sesión </span>  <xsl:value-of select="Numero"/> 
                                          <span class="vote-text">Votaciones</span>
                                       </div>
                                     </div>
                                     <div class="row col-md-12 votes">
                                        <xsl:for-each select="Vots/Vot">
                                          <xsl:sort select="Numero" data-type="number" />
                                          <xsl:variable name="Id" select="Id"/>
                                          <div class="sesion-voting">
                                            <a href="@@@RMximdex.pathto({$Id})@@@">
                  <xsl:value-of select="Numero"/>
</a>
                         
                          </div>
                       </xsl:for-each>
                   </div>
                 </xsl:template>
</xsl:stylesheet>