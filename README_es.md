[English version](README.md)

# eXeLearning Style Designer

**Versión:** 4.0.6 · **Licencia:** AGPL

## Descripción

eXeLearning Style Designer es una herramienta diseñada para facilitar la creación de estilos personalizados para **eXeLearning 4.0.6**.

Con esta herramienta puedes editar y personalizar fácilmente la apariencia de tus proyectos eXeLearning generando nuevos estilos y empaquetándolos para su distribución.

---

## Avisos importantes

ℹ️ **Aviso sobre el público objetivo:**

Esta herramienta **está destinada a usuarios técnicos**. Está diseñada para diseñadores y desarrolladores web. Para usarla correctamente debes ser capaz de instalar y configurar un proyecto PHP y trabajar cómodamente con CSS (y preferiblemente JavaScript).

⚠️ **Esta aplicación nunca se debe usar en producción.**

No incluye las medidas de seguridad necesarias. Esta herramienta está destinada únicamente a diseñadores para simplificar la creación de estilos para eXeLearning 4.

⚠️ **Compatibilidad de versiones: eXeLearning 4.0.6.**

Esta entrega está hecha para **eXeLearning 4.0.6**, y es la versión con la que se recomienda trabajar. Las tres exportaciones que cargues deben venir de 4.0.6: **el contenido exportado con versiones anteriores no está soportado**.

El motivo es que el Diseñador previsualiza tu estilo contra el marcado exportado real, y las últimas versiones lo han cambiado: el color de foco que los componentes de efectos leen del estilo, el conmutador de modo profesor, los General Icons dibujados como Material Symbols y el ayudante de URL que usa la navegación llegaron en 4.0.5, y 4.0.6 añade un modo presentación para los sitios web que maneja el menú y los botones de navegación del propio estilo. Un estilo comprobado contra una exportación antigua puede parecer terminado y estar mal en 4.0.6.

---

## Instalación

### 1. Instalación directa en Apache

Puedes ejecutar la aplicación directamente en un servidor Apache local:

1. Asegúrate de tener **PHP 8+** y Apache instalados.
2. Copia los archivos del proyecto al directorio de documentos de Apache (por ejemplo, `htdocs` o `www`).
3. Asegúrate de que la carpeta `path-to-project/` sea accesible desde tu navegador.
4. Si es necesario, ajusta la configuración de PHP para subir archivos grandes:

```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 128M
```

5. Abre tu navegador y visita:

```
http://localhost/path-to-project/index.php
```

---

### 2. Docker

Esta es la forma más fácil de ejecutar la aplicación sin instalar PHP o Apache localmente.

1. Asegúrate de tener **Docker Desktop** instalado y en ejecución.
2. Clona o descarga este repositorio.
3. Navega a la raíz del proyecto en un terminal.
4. Construye e inicia el contenedor:

```bash
docker-compose up --build
```

5. Abre tu navegador y visita:

```
http://localhost:8000/
```

> Puedes cambiar el puerto en `docker-compose.yml` si es necesario.

---

## Cómo usar la herramienta — Guía rápida

### 1. Primeros pasos

1. Descarga e instala la herramienta.
2. Haz clic en **Start** para comenzar.

Verás dos opciones:

- **Upload three exported contents (Subir tres contenidos exportados)** en formato ZIP:
  - Un sitio web
  - Una página única
  - Un paquete SCORM 1.2
- **Use the sample contents (Usar los contenidos de ejemplo)** (la forma más fácil de empezar)

Si eliges subir tus propias exportaciones, tienen que venir de **eXeLearning 4.0.6** (ver el aviso de compatibilidad de arriba), y asegúrate de que **todas las preferencias de exportación** (contador de páginas, buscador, etc.) estén habilitadas. Esto permite que tus exportaciones incluyan todos los elementos opcionales y que tu estilo sea totalmente compatible con los mismos. Incluye todas las combinaciones de cajas: con y sin icono, con y sin título, sin el botón de minimizar, etc., para verificar que tu estilo funcione correctamente en todos los casos.

---

### 2. Cargar el Style Designer

Una vez cargados los contenidos, puedes abrir el **Style Designer**.

En la parte superior de la página, encontrarás tres botones de vista previa:

- **Website (Sitio web)**
- **Single page (Página única)**
- **SCORM 1.2**

Haz clic en cada uno para ver tu estilo en ese formato de exportación.

El interruptor **Presentation mode (Modo presentación)**, junto a ellos, recarga el sitio web con `?exe-presentation=1` para que compruebes cómo se comporta tu estilo cuando alguien lo presenta con el teclado: el menú se pliega, las flechas cambian de página y aparece un distintivo *Presentation mode* abajo a la derecha.

---

### 3. Editar tu estilo

Todos los archivos editables se encuentran en la carpeta **theme** dentro del directorio de la aplicación.

Puedes modificar:

- `style.css`
- `style.js`
- Cualquier otro archivo requerido por tu diseño

#### Imágenes
Coloca todas las imágenes necesarias en `/theme/img`.
Si agregas `favicon.png` o `favicon.ico` en esta carpeta, las exportaciones lo usarán en lugar del favicon predeterminado de eXeLearning.

#### Tipografías
Puedes añadir tipografías personalizadas en `/theme/fonts`.

#### Iconos de iDevice
La carpeta `/theme/icons` contiene los iconos de los iDevices.
Se recomienda trabajar con esos iconos durante el desarrollo y reemplazarlos con tu propia colección una vez completado el estilo.

#### Archivos CSS de ejemplo
La carpeta `/files/example_css_files` contiene varios archivos CSS similares al usado en el estilo incluido en la herramienta. Tal vez te sirvan como punto de partida para tu diseño.

---

### 4. Finalizar tu estilo

Cuando el diseño esté listo, haz clic en **Finish / Restart (Finalizar / Reiniciar)** y sigue las instrucciones:

1. Edita el archivo `config.xml` de tu estilo.
2. Descarga tu estilo en formato ZIP.
3. Importa el ZIP en eXeLearning.

Si deseas empezar desde cero, ve a **Finish / Restart** y selecciona **Delete all files and create a new Style (Eliminar todos los archivos y crear un nuevo estilo)**.

---

### 5. Pruebas y licencias

- Prueba tu estilo en eXeLearning para asegurarte de que todo funcione correctamente, y comprueba que tu CSS no interfiera con la presentación de la aplicación.
- Respeta todas las licencias de terceros para imágenes, fuentes o cualquier otro recurso utilizado.
- Añade la información legal o créditos necesarios en el fichero `config.xml` de tu estilo.

---

### 6. Revisar tu estilo automáticamente

El Diseñador incluye un **skill de agente** que revisa un estilo, en
`.agents/skills/review-style/`. Forma parte de la herramienta, no es una nota de trabajo
personal: está versionado con el proyecto y disponible para cualquiera que lo clone.

Sigue la convención `SKILL.md` y vive en la carpeta neutral `.agents/`, no bajo el directorio
propio de ningún asistente, por la misma razón por la que `AGENTS.md` está en la raíz: lo que
sabe es de eXeLearning, no del asistente que lo lee. Se escribió y se probó con
[Claude Code](https://claude.com/claude-code).

Claude Code solo descubre skills bajo `.claude/skills/`, así que el repositorio incluye además
un único archivo puntero, `.claude/skills/review-style/SKILL.md`, que no hace más que indicar
la ruta real. Es el único archivo del proyecto que da por supuesto un asistente concreto. Con
cualquier otra herramienta, señálale directamente `.agents/skills/review-style/SKILL.md`.

Pídele que *revise*, *audite* o *compruebe* el estilo y auditará `theme/` contra
las tres exportaciones de `contents/`, en ocho fases: inventario, revisión estática del CSS y
del JavaScript, tipografía, los créditos de terceros de `config.xml`, comportamiento
adaptable y una verificación en un navegador real sobre los tres formatos de exportación. En
cada ejecución pregunta qué nivel WCAG aplicar — AA es el predeterminado, y el que exige
`AGENTS.md`.

**El resultado no es un informe: es un `theme/` corregido.** El skill aplica los arreglos
sobre `theme/config.xml`, `theme/style.css` y `theme/style.js`, y luego cuenta lo que hizo.

Corrige un estilo, no lo rediseña. El código muerto, el CSS inválido, los selectores rotos,
el JavaScript que lanza errores, los estados de foco que faltan y los ajustes mínimos de
contraste se aplican directamente. Todo aquello que un diseñador *notaría* como un cambio de
diseño —otro color, otra fuente, otros espaciados, un componente añadido o quitado— se
propone y nunca se aplica sin preguntar.

Dos archivos concentran el conocimiento, y los dos se pueden leer por su cuenta:

- `references/checks.md` — el catálogo de comprobaciones: cómo interactúan `base.css`,
  Bootstrap y `exe_effects.css` con la especificidad de un estilo, qué hace distinto cada uno
  de los tres formatos de exportación, cómo se comporta el modo oscuro por filtro y qué
  cambia dentro del editor de eXeLearning.
- `assets/a11y-probe.js` — la sonda de contraste que ejecuta en el navegador.

Necesita que las previsualizaciones se sirvan por HTTP (Apache o Docker, como arriba) y un
navegador que pueda manejar. Una revisión sin `contents/` no sirve de nada, así que carga
antes los contenidos de ejemplo.

---

### 7. Recursos adicionales

- Haz clic en **Example style (Estilo de ejemplo)** dentro de la aplicación para descargar un estilo de ejemplo.
- Documentación oficial de estilos de eXeLearning (en inglés): https://exelearning.github.io/exelearning/development/styles/

---

## Notas

- La herramienta está destinada únicamente a **diseñadores**.
- **No es segura** para uso en producción.
- Al finalizar, es importante **probar tu estilo dentro de eXeLearning** para asegurarte de que el código CSS no afecta a la interfaz de la aplicación.

---

## Licencia

Este proyecto tiene licencia **AGPL**. Puedes usarlo, modificarlo y distribuirlo libremente bajo los términos de la Affero General Public License.

---

## Créditos / Atribuciones

Los siguientes recursos de terceros están incluidos en este proyecto:

- **Contenido de ejemplo:** `files/fixtures/leer-para-aprender.elpx`
  Usado para crear capturas de pantalla de los estilos en eXeLearning 4.
  Contenido original creado para [CEDEC](https://cedec.intef.es/).
  Licencia: [Creative Commons BY-SA](https://creativecommons.org/licenses/by-sa/3.0/)

- **Icono:** `files/img/new-window.svg`
  Fuente: [Google Fonts Icons](https://fonts.google.com/icons)
  Licencia: [Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0)

- **JavaScript de Bootstrap:** `files/js/bootstrap.bundle.min.js` y `bootstrap.bundle.min.js.map`
  Fuente: [Bootstrap](https://getbootstrap.com/)
  Licencia: [MIT License](https://github.com/twbs/bootstrap/blob/main/LICENSE)

- **jQuery:** `files/js/jquery.min.js`
  Fuente: [jQuery](https://jquery.com/)
  Licencia: [MIT License](https://jquery.org/license/)

- **CSS de Bootstrap:** `files/css/bootstrap.min.css` y `bootstrap.min.css.map`
  Fuente: [Bootstrap](https://getbootstrap.com/)
  Licencia: [MIT License](https://github.com/twbs/bootstrap/blob/main/LICENSE)

- **Logo de eXeLearning:**
  Fuente: [eXeLearning GitHub](https://github.com/exelearning/exelearning/)
  Licencia: AGPL (igual que este proyecto)

- **Fuente Open Sans:** `files/example.zip/fonts/*`
  Copyright 2020 The Open Sans Project Authors
  Fuente: [Open Sans en GitHub](https://github.com/googlefonts/opensans)
  Licencia: [SIL Open Font License, versión 1.1](https://openfontlicense.org/) — el texto
  completo viaja dentro del zip, en `fonts/OFL.txt`

> Todos los demás archivos del proyecto han sido creados por [@ignaciogros](https://github.com/ignaciogros) y tienen licencia AGPL.
