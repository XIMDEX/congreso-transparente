<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ext="http://exslt.org/common" version="1.0" exclude-result-prefixes="ext">
                                   
                                   <xsl:output omit-xml-declaration="yes" indent="yes"/>
                                   
                                                      <xsl:template name="informacion" match="Informacion">
                                                        <div class="row">
                                       
                                                        </div>
                                                        <div class="row content">
                                                          <div class="col-md-12">
                                                            
                                                         <div class="session-number">
                                                           <span class="session">
               <span>
                                 <xsl:value-of select="Sesion"/>
                                                              </span>
               </span>
                                          <span class="vote">
               <span>
                <xsl:value-of select="NumeroVotacion"/>
                                             </span>
               </span>
                                        </div>
                                    <h1 class="">
                                            
                                      <xsl:value-of select="Titulo"/>
               			</h1>
                                    <div class="date">
                                                   
               			<xsl:variable name="FechaMod">
                                       	<xsl:call-template name="split">
                                     		</xsl:call-template>  
                                     	</xsl:variable>
                                       
                                               
                                       <xsl:for-each select="ext:node-set($FechaMod)/*">
                                       
                                          <span class="date-item">
               <xsl:value-of select="."/>
</span>
                         
                        </xsl:for-each>
                                
                      </div>
           <p class="textoexpediente">
<xsl:value-of select="TextoExpediente"/>
</p>
              </div>
              </div>
   			</xsl:template>
  
  
  <xsl:template match="text()" name="split">
  <xsl:param name="pText" select="Fecha"/>
   <xsl:if test="string-length($pText) &gt;0">
    <item>
      <xsl:value-of select="substring-before(concat($pText, '/'), '/')"/>
    </item>
    <xsl:call-template name="split">
      <xsl:with-param name="pText" select="substring-after($pText, '/')"/>
    </xsl:call-template>
   </xsl:if>
 </xsl:template>
  
  
</xsl:stylesheet>
