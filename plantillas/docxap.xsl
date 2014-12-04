<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html" encoding="utf-8"/>
    <xsl:param name="xmlcontent"/>
    <xsl:include href="SERVER_URL_XIMDEX_INSTANCE/data/nodes/Congreso/templates/templates_include.xsl"/>
    <xsl:template name="docxap" match="docxap">
        <!--<xsl:text disable-output-escaping="yes">
            <![CDATA[
                <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
             ]]>
           </xsl:text>
           -->
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>
                <link rel="stylesheet" type="text/css" href="@@@RMximdex.dotdot(css/bootstrap.min.css)@@@"/>
                <link rel="stylesheet" type="text/css" href="@@@RMximdex.dotdot(css/bootstrap-theme.min.css)@@@"/>
                <link rel="stylesheet" type="text/css" href="@@@RMximdex.dotdot(css/main.css)@@@"/>
                <title>Congreso Transparente</title>
                <link href="http://fonts.googleapis.com/css?family=Voces|Lato:100,300,400,700,900,100italic,300italic,400italic,700italic,900italic|PT+Sans:400,700,400italic,700italic|Cabin+Condensed:400,500,600,700|Kameron:400,700|Rokkitt:400,700|Copse|Cantarell:400,700,400italic,700italic|Oswald:400,300,700|Coda:400,800|Francois+One" rel="stylesheet" type="text/css"/>
                <script src="http://d3js.org/d3.v3.min.js"/>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"/>
            </head>
            <body uid="{@uid}">
                <div id="container">
                    <div class="row header">
                        <div class="col-md-8">
                            <div class="page-header">
                                <xsl:if test="@schema='rng-sesionvotacion.xml'">
                                    <div class="nav-control">
                                        <a class="goback" href="@@@RMximdex.pathto(index,#{@language})@@@">Go Back</a>
                                    </div>
                                </xsl:if>
                                <h1>Congreso Transparente</h1>
                            </div>
                        </div>
                        <xsl:if test="@schema='rng-index.xml'">
                            <div class="col-md-4 text-right brand-header">
                                <span>Powered by</span>
                                <a href="http://www.ximdex.com/" target="_blank">
                                    <img src="@@@RMximdex.dotdot(common/logo_ximdex.png)@@@"/>
                                </a>

                            </div>
                        </xsl:if>
                    </div>

                    <div class="row">
                        <div class="">
                            <xsl:apply-templates/>
                        </div>
                    </div>
                    <xsl:if test="@schema='rng-sesionvotacion.xml'">
                        <div class="row footer text-right">
                            <div class="col-md-12">
                                <span>Powered by</span>
                                <a href="http://www.ximdex.com/" target="_blank">
                                    <img src="@@@RMximdex.dotdot(common/logo_xim.png)@@@"/>
                                </a>
                            </div>
                        </div>
                    </xsl:if>
                </div>
                <script>
                    $('.sesions:first-child').addClass('opened');
                    $('.sesions').not(':first').addClass('closed');
                    $('.sesions').click(function () {
                    $(this).toggleClass('closed');
                    $(this).toggleClass('opened');
                    });
                </script>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>