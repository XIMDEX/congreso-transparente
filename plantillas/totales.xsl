<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
                                                                                                                                                                    <xsl:template name="totales" match="Totales"> 
                                                                                                                                                                               <div class="row result">
                                                                                                                                                                                 <xsl:variable name="asentimiento" select="Asentimiento"/>
                                                                                                                                                                                 
                                                                                                                                                                                 <xsl:variable name="presentes">
                                                                                                                                                                                  <xsl:choose>
                                                                                                                                                                                  <xsl:when test="$asentimiento = 'No'">
                                                                                                                                                                                  <xsl:value-of select="Presentes"/>
                                                                                                                                                                                  </xsl:when>
                                                                                                                                                                                  <xsl:otherwise>
                                                                                                                                                                                  350
                                                                                                                                                                                  </xsl:otherwise>
                                                                                                                                                                                  </xsl:choose>
                                                                                                                                                                                 </xsl:variable>
                                                                                                                                                                                 
                                                                                                                                                                                 <xsl:variable name="afavor">
                                                                                                                                                                                  <xsl:choose>
                                                                                                                                                                                  <xsl:when test="$asentimiento = 'No'">
                                                                                                                                                                                  <xsl:value-of select="AFavor"/>
                                                                                                                                                                                  </xsl:when>
                                                                                                                                                                                  <xsl:otherwise>
                                                                                                                                                                                  0
                                                                                                                                                                                  </xsl:otherwise>
                                                                                                                                                                                  </xsl:choose>
                                                                                                                                                                                 </xsl:variable>
                                                                                                                                                                                 
                                                                                                                                                                                 <xsl:variable name="encontra">
                                                                                                                                                                                  <xsl:choose>
                                                                                                                                                                                  <xsl:when test="$asentimiento = 'No'">
                                                                                                                                                                                  <xsl:value-of select="EnContra"/>
                                                                                                                                                                                  </xsl:when>
                                                                                                                                                                                  <xsl:otherwise>
                                                                                                                                                                                  0
                                                                                                                                                                                  </xsl:otherwise>
                                                                                                                                                                                  </xsl:choose>
                                                                                                                                                                                 </xsl:variable>
                                                                                                                                                                                 
                                                                                                                                                                                 <xsl:variable name="abstenciones">
                                                                                                                                                                                  <xsl:choose>
                                                                                                                                                                                  <xsl:when test="$asentimiento = 'No'">
                                                                                                                                                                                  <xsl:value-of select="Abstenciones"/>
                                                                                                                                                                                  </xsl:when>
                                                                                                                                                                                  <xsl:otherwise>
                                                                                                                                                                                  0
                                                                                                                                                                                  </xsl:otherwise>
                                                                                                                                                                                  </xsl:choose>
                                                                                                                                                                                 </xsl:variable>
                                                                                                                                                                                   
                                                                                                                                                                                   <div id="graph-presentes" class="col-md-2 col-md-offset-2 box presentes text-center general-view">
                                                                                                                                                                                     
                                                                                                                                                                                  <!--<p>Total
                                                                                                                                                                                      <strong>
                                                                                                                                                                                              <xsl:value-of select="Presentes"/>
                                                                                                                                                                                      </strong>
                                                                                                                                                                              </p>-->
                                                                                                                                                                                </div>
                                                                                                                                                                                
                                                                                                                                                                            <div class="detail-view right">
                                                                                                                                                                            <div class="col-md-2 box afavor text-center">
                                                                                                                                                                              <div id="graph-afavor">
</div>
                    <p>
                      <strong>
                              <xsl:value-of select="AFavor"/>
                      </strong>
                    	A favor
                    </p>
                    </div>
                       <div class="col-md-2 box encontra text-center">
                        <div id="graph-encontra">
</div>
                   <p>
                           <strong>
                                   <xsl:value-of select="EnContra"/>
                           </strong>
                   En contra</p>
                     </div>
                   <div class="col-md-2 box abstenciones text-center">
                       <div id="graph-abstenciones">
</div>
                   <p>
                           <strong>
                                   <xsl:value-of select="Abstenciones"/>
                           </strong>
                   Abstenciones</p>
                     </div>
                 </div>
                      
                      
                <script>
                  var dataset = {
                  	presentes: <xsl:value-of select="$presentes"/>,
                   	  afavor: <xsl:value-of select="$afavor"/>,
                   	  encontra: <xsl:value-of select="$encontra"/>,
                   	  abstenciones: <xsl:value-of select="$abstenciones"/>
                   };
                   var width = 380,
                    height = 380,
                    radius = Math.min(width, height) / 2,
                    width_small = 200,
                    height_small = 200,
                    p = Math.PI * 2,
                    duration = 2000,
                    endangleafavor = (dataset['afavor']*p)/dataset['presentes'],
                    endangleencontra = (dataset['encontra']*p)/dataset['presentes']
                    endangleabstenciones = (dataset['abstenciones']*p)/dataset['presentes']
                  
                  
                  var pie = d3.layout.pie()
                      .sort(null);
                  
                  var arc1 = d3.svg.arc()
                      .innerRadius(radius - 110)
                      .outerRadius(radius - 40);
                  
                  var arc2 = d3.svg.arc()
                      .innerRadius(radius - 110)
                      .outerRadius(radius - 60);
                  
                  var arc3 = d3.svg.arc()
                      .innerRadius(radius - 110)
                      .outerRadius(radius - 80);
                  
                  var arcafavor = d3.svg.arc()
                      .innerRadius(radius - 105)
                      .outerRadius(radius - 113)
              	      .startAngle(0)
                  	.endAngle(function(d) { return d.value; });
                  
                  var arcencontra = d3.svg.arc()
                      .innerRadius(radius - 75)
                      .outerRadius(radius - 86)
                      .startAngle(0)
                      .endAngle(function(d) { return d.value; });
                  
                  var arcabstenciones = d3.svg.arc()
                      .innerRadius(radius - 55)
                      .outerRadius(radius - 69)
                      .startAngle(0)
                      .endAngle(function(d) { return d.value; });
                
                   var canvas1 = d3.select("#graph-presentes").append("svg:svg")
                    .attr("width", width)
                    .attr("height", height)
                    .append("g")
                    .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
                
                   var path1 = canvas1.selectAll("path-1")
                         .data(pie([1]))
                       .enter().append("path")
                         .attr("fill", "#cef9ed")
                  	 .attr("class","opacity69")
                         .attr("d", arc1);
                  
                  var path2 = canvas1.selectAll("path-2")
                         .data(pie([1]))
                       .enter().append("path")
                         .attr("fill", "#e6d9fd")
                  	 .attr("class","opacity69")
                         .attr("d", arc2);
                  
                  var path3 = canvas1.selectAll("path-3")
                         .data(pie([1]))
                       .enter().append("path")
                         .attr("fill", "#79c691")
                         .attr("d", arc3);
                  
                  
                  <xsl:choose>
                   <xsl:when test="$asentimiento = 'No'">
                     
                       canvas1.append("text")
                      .attr("class", function(d) {
                  		if (dataset['afavor']&gt; dataset['encontra']) return "total-title-afavor";
                  		else return "total-title-encontra";
                  	})
                      .text('TOTAL')
                      .attr("transform", "translate(-20,0)");
                  
                  	canvas1.append("text")
                      .attr("class", function(d) {
                  		if (dataset['afavor'] &gt; dataset['encontra']) return "total-value-afavor";
                  		else return "total-value-encontra";
                  	})
                      .text(dataset['presentes'])
                      .attr("transform", "translate(-13,20)");
                     
                   </xsl:when>
                    
                    <xsl:otherwise>
                      canvas1.append("text")
                      .attr("class", "asentimiento-label")
                      .text('SE ASIENTE')
                      .attr("transform", "translate(-46,6)");
                    </xsl:otherwise>
                   
                  </xsl:choose>
                  
                  var pathafavor = canvas1.selectAll("path-afavor")
                         .data(buildPercBars)
                       .enter().append("path")
                  	.attr("class", "pathafavor")
                  	.attr("fill", "green")
                  	 .attr("d", arcafavor);
                  
                  var pathencontra = canvas1.selectAll("path-encontra")
                         .data(buildPercBars)
                       .enter().append("path")
                  	.attr("class", "pathencontra")
                         .attr("fill", "red")
                  	 .attr("d", arcencontra);
                  
                  var pathabstenciones = canvas1.selectAll("path-abstenciones")
                         .data(buildPercBars)
                       .enter().append("path")
                  	.attr("class", "pathabstenciones")
                         .attr("fill", "black")
                  	 .attr("d", arcabstenciones);
                  
                  var canvas2 = d3.select("#graph-afavor").append("svg:svg")
                    .attr("width", width_small)
                    .attr("height", height_small)
                    .append("g")
                    .attr("transform", "translate(" + width_small / 2 + "," + height_small / 2 + ")");
                   
                  var node1 = canvas2.selectAll(".node")
                  	.data([1])
                         .enter()
                         .append("g")
                             .attr("class", "node");
                  
                  node1.append("circle")
                  	.attr("r", dataset['presentes']/4)
                                      .attr("fill", "white");
                  
                  node1.append("circle")
                  	.attr("fill", "green")
                  	.attr("r", 0)
                  	.transition()
                  	.duration(duration)
                  	.attr("r", dataset['afavor']/4);
                  
                  var canvas3 = d3.select("#graph-encontra").append("svg:svg")
                    .attr("width", width_small)
                    .attr("height", height_small)
                    .append("g")
                    .attr("transform", "translate(" + width_small / 2 + "," + height_small / 2 + ")");
                  
                  var node2 = canvas3.selectAll(".node")
                  	.data([1])
                         .enter()
                         .append("g")
                             .attr("class", "node");
                  
                  node2.append("circle")
                  	.attr("r", dataset['presentes']/4)
                                      .attr("fill", "white");
                  
                  node2.append("circle")
                  	.attr("fill", "red")
                  	.attr("r", 0)
                  	.transition()
                  	.duration(duration)
                  	.attr("r", dataset['encontra']/4);
                  
                  var canvas4 = d3.select("#graph-abstenciones").append("svg:svg")
                    .attr("width", width_small)
                    .attr("height", height_small)
                    .append("g")
                    .attr("transform", "translate(" + width_small / 2 + "," + height_small / 2 + ")");
                  
                  var node3 = canvas4.selectAll(".node")
                  	.data([1])
                         .enter()
                         .append("g")
                             .attr("class", "node");
                  
                  node3.append("circle")
                  	.attr("r", dataset['presentes']/4)
                                      .attr("fill", "white");
                  
                  node3.append("circle")
                  	.attr("fill", "black")
                  	.attr("r", 0)
                  	.transition()
                  	.duration(duration)
                  	.attr("r", dataset['abstenciones']/4);
                  
                  
                  
                  // Generate the percentage bars
                  function buildPercBars() {
                      return [
                          {value: 0, index: .5},
                      ];
                  }
     
                  
             $( document ).ready(function() {
               selectArcs();
             });
                function selectArcs() {
                      d3.selectAll("g &gt; .pathafavor")
                          .each(arcTweenAFavor);
                      d3.selectAll("g &gt; .pathencontra")
                          .each(arcTweenEnContra);
                      d3.selectAll("g &gt; .pathabstenciones")
                          .each(arcTweenAbstenciones);
                }
                
                function arcTweenAFavor(){
                      d3.select(this)
                  .transition().duration(duration)
                        .attrTween("d", tweenArcAFavor({ value : endangleafavor }));
                }
                
                function arcTweenEnContra(){
                      d3.select(this)
                        .transition().duration(duration)
                        .attrTween("d", tweenArcEnContra({ value : endangleencontra }));
                }
                
                function arcTweenAbstenciones(){
                      d3.select(this)
                        .transition().duration(duration)
                        .attrTween("d", tweenArcAbstenciones({ value : endangleabstenciones }));
                }
                
                
                
                function tweenArcAFavor(b) {
                      return function(a) {
                        var i = d3.interpolate(a, b);
                        for (var key in b) a[key] = b[key]; // update data
                        return function(t) {
                              return arcafavor(i(t));
                        };
                      };
                }
                                  
                function tweenArcEnContra(b) {
                    return function(a) {
                      var i = d3.interpolate(a, b);
                        for (var key in b) a[key] = b[key]; // update data
                          return function(t) {
                              return arcencontra(i(t));
                          };
                  };
                }
                                  
                function tweenArcAbstenciones(b) {
                  return function(a) {
                    var i = d3.interpolate(a, b);
                      for (var key in b) a[key] = b[key]; // update data
                        return function(t) {
                          return arcabstenciones(i(t));
                        };
                  };
                }
                 
                                  
                 </script>      
                       
                       
               </div>
          </xsl:template>
</xsl:stylesheet>
