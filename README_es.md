[English version](README.md)

# eXeLearning Style Designer

**Licencia:** AGPL

## Descripción

eXeLearning Style Designer es una herramienta diseñada para facilitar la creación de estilos personalizados para **eXeLearning 3**.

Con esta herramienta puedes editar y personalizar fácilmente la apariencia de tus proyectos eXeLearning generando nuevos estilos y empaquetándolos para su distribución.

---

## Avisos importantes

ℹ️ **Aviso sobre el público objetivo:**

Esta herramienta **está destinada a usuarios técnicos**. Está diseñada para diseñadores y desarrolladores web. Para usarla correctamente debes ser capaz de instalar y configurar un proyecto PHP y trabajar cómodamente con CSS (y preferiblemente JavaScript).

⚠️ **Esta aplicación nunca se debe usar en producción.**

No incluye las medidas de seguridad necesarias. Esta herramienta está destinada únicamente a diseñadores para simplificar la creación de estilos para eXeLearning 3.

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

Si eliges subir tus propias exportaciones, asegúrate de que **todas las preferencias de exportación** (contador de páginas, buscador, etc.) estén habilitadas. Esto permite que tus exportaciones incluyan todos los elementos opcionales y que tu estilo sea totalmente compatible con los mismos. Incluye todas las combinaciones de cajas: con y sin icono, con y sin título, sin el botón de minimizar, etc., para verificar que tu estilo funcione correctamente en todos los casos.

---

### 2. Cargar el Style Designer

Una vez cargados los contenidos, puedes abrir el **Style Designer**.

En la parte superior de la página, encontrarás tres botones de vista previa:

- **Website (Sitio web)**
- **Single page (Página única)**
- **SCORM 1.2**

Haz clic en cada uno para ver tu estilo en ese formato de exportación.

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

### 6. Recursos adicionales

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
  Usado para crear capturas de pantalla de los estilos en eXeLearning 3.
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
  Fuente: [Google Fonts](https://fonts.google.com/specimen/Open+Sans)
  Licencia: Apache License, Version 2.0

> Todos los demás archivos del proyecto han sido creados por [@ignaciogros](https://github.com/ignaciogros) y tienen licencia AGPL.
