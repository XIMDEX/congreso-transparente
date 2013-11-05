#Congreso Transparente

![Esquema del proceso implementado para el Congreso Transparente](congreso-transparente.svg "Exquema del proceso implementado para el Congreso Transparente")

Este proyecto muestra de forma simple e integrada todas las votaciones en las sesiones plenarias del Congreso de los Diputados de España. Para ello hace uso de los XMLs que la página web del Congreso publica después de cada sesión y directamente los inserta en el CMS Ximdex que reconoce nativamente el formato XML y lo transforma en HTML para su posterior visualización del contenido de cada documento en Internet.

##Motivación

Ximdex por definición es un sistema gestor de contenidos (CMS) basado en XML que permite mantener el repositorio de información completamente desacoplado de los sistemas de publicación, lo que permite obtener unos niveles de seguridad óptimos en cuanto a la integridad de la información.

No son pocas las entidades que usan formatos como XML a la hora de gestionar su información debido al carácter estructurado, estándar y neutral del mismo. Ya sea a través de una intranet, en portal de información o en sistemas de sindicación, dichas organizaciones deben de procesar dicha información estructurada para proporcionarla de una forma útil y directa al usuario.

##Propuesta

En este proyecto se propone usar Ximdex como sistema repositorio de documentos XML los cuales serán publicados en formato web (HTML) para que sean accesibles por cualquier usuario con acceso a internet.

En esa línea se puede definir qué información contenida en dichos XMLs será mostrada a los usuarios y cuáles no, pudiendo así definir niveles de acceso público y privado de los datos.

Para el enriquecimiento del repositorio se usará la [API pública](https://www.google.com/url?q=https%3A%2F%2Fwww.dropbox.com%2Fs%2F2snvtwzviiabtuu%2FXimdex_API_en.pdf&sa=D&sntz=1&usg=AFQjCNHRs5-PgJV_Ayk_rMde53iWQUJKPg "Enlace al documento de la API de Ximdex en PDF") que el sistema Ximdex tiene definida como método de acceso al mismo y así facilitar la integración e inteoperabilidad entre diversos sistemas.

Dado el alto grado de configuración del sistema Ximdex, en el futuro esta misma información XML puede ser procesada para ser mostrada en otros formatos tales como RSS, Markdown, JSON o SmartTV. Y porqué no, formatos que aún no hayan sido ni definidos ni imaginados.

##El proceso

Se puede dividir el proceso en dos partes fundamentales:

- Implementación de esquemas y plantillas en Ximdex.
- Recolección de documentos XMLs y su importación en Ximdex.

### Esquemas y plantillas
Ximdex define sus esquemas de documentos XML basado en el formato [RelaxNG (RNG)](http://relaxng.org/tutorial-20011203.html "Web de  RelaxNG") definido por [Oasis](https://www.oasis-open.org/ "Web de Oasis"). En este proyecto se han definido **dos modelos de esquema**: uno que hace referencia a los resultados de las votaciones en sesiones plenarias del Congreso de los Diputados y otro que se encarga de indexar en una página todas las votaciones para proporcionar una mejor navegación sobre las mismas.

Además se han preparado una **serie de documentos [XSL](http://www.w3.org/Style/XSL/ "Extensible Stylesheet Language")** para transformar los documentos XML en páginas HTML para su correcta visualización en navegadores. Estas plantillas nos permiten recorrer todos los datos contenidos en los XML además de operar con ellos con el objetivo de añadirlos a la estructura de elementos HTML que se desee generar.

### Recolección e inmportación
Este proceso se basa en cuatro apartados:

- **Scraping**: Los documentos en los que se muestran las votaciones del Congreso de los Diputados están en una web concreta del sitio web de la institución por lo que hay que acceder a los diferentes recursos de forma automática para obtenerlos.
- **Recolección**: Dada la tarea anterior se automatiza la recogida de recursos de la web para así obtenerlos todos. Estos documentos se aglutinan en un fichero comprimido, el cual debe ser descomprimido y luego trabajar con los documentos XMl que incluye. Se obtiene un documento XML por cada votación realizada.
- **Inyección**: Con todos los documentos XML que se obtienen en el paso anterior se le aplica un proceso de inyección haciendo uso de la API que Ximdex define. Para ello se genera por cada documento una primera petición de acceso al sistema para autenticarse en el mismo y a continuación se realiza la creación y asignación de contenido. Además, en cada paso, se inyecta al documento índice (index-ides.html) una referencia a todo aquel documento procesado.
- **Publicación**: Por último se publica cada documento para que sea transformado teniendo en cuenta las plantillas implementadas en el paso [Esquemas y plantillas](#esquemas-y-plantillas).


