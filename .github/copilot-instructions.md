# Información para el desarrollo del plugin packages/Reda/RedaAlojamiento en este proyecto de alojamientos (hospedajes) "Torbian" desarrollado en Laravel

## Plugin packages/Reda/Alojamiento
Este no es un proyecto propio, es de otro "autor" y es parecido a la famosa aplicación Airbnb para ofrecer hospedaje de apartamentos, casas, habitaciones, etc para hospedaje de temporada.
El usuario solicitó crear un módulo de "Experiencias" para ofrecer a los huéspedes, pero luego cambió de opinión y pidió que en lugar de "Experiencias" fuese algo más amplio como "Negocios". Cualquier negocio que ofrezca productos y/o servicios para los huéspedes. Así que de ahora en adelante las "experiencias" se refieren a "negocios" y las actividades de las experiencias ahora son los productos y servicios.

## Directrices del proyecto
Evitar en lo posible modificar los archivos originales del proyecto. Si es muy necesario agregar dos o tres líneas en un archivo original para agregar algún gancho o filtro parecidos a los usados en Wordpress para adicionar código personalizado
Evitar modificar las vistas .blade y más bien inyectar código html mediante javascript
Cambiar el comportamiento del proyecto usando Javascript
Evitar modificar las tablas originales de la base de datos, en su lugar crear tablas auxiliares que se vinculen a las tablas originales
La creación o modificación de nuevas tablas, hacerlas con archivos de migraciones
Considerar siempre hacer una buena vista para escritorio y para dispositivos móviles
El idioma del plugin es español: Los nombres de los archivos de modelos, vistas, controladores y otros deben estar en español.
Los nombres de tablas y columnas deben estar en español.
Los nombres de variables deben estar en español.

## Archivos de migraciones
- Solo crear los archivos de migraciones, no ejecutarlos.
- Los nombres de tablas y columnas deben estar en español
- Solicitar a Gemini que cree los archivos de migraciones en la carpeta: packages/Reda/RedaAlojamiento/database/migrations en mi IDE Cloud Shell Editor.
- Subir el archivo de migraciones al servidor Vesta de Desarollo
- Ejecutar la migración en el servidor Vesta de Desarollo:
    sudo -u appvac php8.2 artisan migrate
- Los nombres de los archivos de migración deben ser en español
- Cuando se haga una migración todas las columnas nuevas de cualquier tabla, deben aceptar valores nulos, excepto el ID de la tabla.
- Recordad cuando se haga una migración crear o modificar el archivo modelo correspondiente en el directorio: 
    packages/Reda/RedaAlojamiento/app/Models

## Estilo de Código General
- Los comentarios deben estar en español.
- Usar nombres de variables descriptivos en español.

## Estilos CSS
- En este proyecto se usan las siguientes versiones de Bootstrap:
    - En el Frontend se utiliza Bootstrap 4.5
    - En el Backend se utiliza Bootstrap 5.2.3
- El plugin Reda/Alojamiento tiene sus propios estilos:
Los estilos del frontend deben estar inspirados en los estilos de la popular aplicación Airbnb. Creo que son estilos con directrices de Material Design de Google aunque no estoy seguro.
Para el admin: packages/Reda/RedaAlojamiento/resources/sass/admin/main.scss (por favor codificar los estilos del admin en este archivo)
Para el frontend: packages/Reda/RedaAlojamiento/resources/sass/frontend/main.scss (por favor codificar los estilos del frontend en este archivo)
Esos estilos se agregan al proyecto principal en los archivos:
"packages/Reda/RedaAlojamiento/resources/views/admin/general/main_head.blade.php"
"packages/Reda/RedaAlojamiento/resources/views/general/main_head.blade.php"
Los índices o listas para las vistas de escritorio pueden hacerse con "table" pero para las vistas de celular deben ser más modernos tipo tarjetas bien ordenadas y con una buena interfaz agradable para manipular en el celular.

## JavaScript Específico (Frontend)
- Utiliza la sintaxis moderna de ES6+ (`const`, `let`, funciones flecha).
- Se usará javascript puro con jquery. Aprovechando al máximo jquery y cualquier otra librería de javascript que permita simplificar el código y economizar tiempo de desarrollo.
- El código Javascript del plugin se encuentra en: 
Para el admin: packages/Reda/RedaAlojamiento/resources/js/admin/
    Y se divide en dos carpetas: 
        general (javascript para uso general en el proyecto)
        vistas (javascript para cada vista)
Para el frontend: packages/Reda/RedaAlojamiento/resources/js
    Igualmente se divide en dos carpetas:
        general (javascript para uso general en el proyecto)
        vistas (javascript para cada vista)
Los archivos de javascript se agregan al proyecto principal en:
"packages/Reda/RedaAlojamiento/resources/views/admin/general/main_footer.blade.php"
"packages/Reda/RedaAlojamiento/resources/views/general/main_footer.blade.php"

Los peticiones ajax tendrán esta estructura:
    Función llamadora:
        import { eliminarExperiencia } from './eliminarExperiencia.js';

        (function( $ ) {
        "use strict";
        const containerId = '#index_experiencias';
        if ($(containerId).length) {
            $(function() {
                $(document).on('click', '.btn-eliminar-experiencia', async function(e) {
                    e.preventDefault();
                    const respuestaEliminarExperiencia = await eliminarExperiencia(id);
                    if (respuestaEliminarExperiencia.success) {
                        //
                    } else {
                        //
                    }
                });
            });
        }
        })(jQuery);

    Función llamada:
        // import ...

        export const eliminarExperiencia = (idExperiencia) => {
            return new Promise((resolve) => {
                (function( $ ) {
                    $.ajax({
                        url: APP_URL + '/reda/experiencias/eliminar-experiencia/' + idExperiencia, // Ajusta la ruta según tu web.php
                        type: 'DELETE',
                        data: {
                            "_token": $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function(data) {
                            resolve(data);
                        },
                        error: function (x, xs, xt) {
                            // 1. Intentamos obtener el JSON que el servidor envió junto con el error 400
                            let respuestaServidor = {};
                            try {
                                // x.responseText contiene el cuerpo del JSON enviado por Laravel
                                respuestaServidor = JSON.parse(x.responseText);;
                            } catch (e) {
                                respuestaServidor = {};
                            }
                            console.log('respuestaServidor', respuestaServidor);

                            const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                            const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                            // 2. Construimos la respuesta usando los datos reales del servidor si existen

                            let respuesta = {
                                'success': false,
                                'message' : window.RedaAlojamientoJson["Error eliminando experiencia"] || 'Error eliminando experiencia',
                                'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                'respuesta': respuestaServidor.respuesta || '',
                                'code': x.status !== 0 ? x.status : 504,
                            };
                            resolve(respuesta);
                        }
                    })
                })(jQuery);
            });
        }
Siempre que se haga una petición ajax se debe mostrar una animación "Espera" hasta que responda el servidor

## Herramienta de desarrollo
webpack.mix.js

## PHP 
- Para la conexión a base de datos, usa la librería `PDO`. Nunca uses funciones antiguas como `mysql_*`.
- Este proyecto está desarrollado en Laravel versión 11.

Las respuestas del servidor para funciones internas y peticiones ajax tendrán esta estructura:
    $respuesta = [
        'success' => true,
        'message' => __('Experiencia eliminada'), // Un mensaje corto para uso del desarrollador o soporte técnico
        'mensaje_usuario' => __(Experiencia eliminada con exito'), // Un mensaje explicativo y traducido para el usuario
        'respuesta' => '', // La respuesta esperada por la función llamadora, puede ser '', un string, vector u objeto
        'code' => 200
    ];
    return response()->json($respuesta, $respuesta['code']);
Esa estructura de respuesta debe aplicarse para cualquier tipo de función en el servidor, en los controladores y otros archivos que ejecute funciones globales, ya que si por ejemplo se accede a la base de datos o tal vez una respuesta negativa de una API externa, puede ocurrir un error y el detalle de ese error debe ir en el atributo "respuesta" y cuando todo es positivo y la función llamadora necesita una respuesta, tal vez un string, un valor numérico, un vector u objeto eso debe ir en el atributo "respuesta" y la función llamadora accedería a ese atributo para obtener la respuesta requerida y ejecutar algún otro proceso dependiendo de la respuesta o tal vez mostrarla al usuario.
Si por ejemplo en alguna respuesta de una función el atributo "respuesta" del json no aplica o no hace falta se debe enviar entonces un cadena vacía, pero siempre deben estar todos los atributos de la estructura de la respuesta, por ejemplo: 

    $respuesta = [
        'success' => true,
        'message' => __('Experiencia eliminada'), 
        'mensaje_usuario' => __(Experiencia eliminada con exito'), 
        'respuesta' => '', // Si no hay nada que enviar se asigna un cadena vacía, pero siempre deben estar presentes todos los atributos de la estructura de la respuesta
        'code' => 200
    ];


## Traducciones
En este plugin se usan dos tipos de traducciones: 
    Archivo php: packages/Reda/RedaAlojamiento/resources/lang/es/messages.php
    Archivo .json: packages/Reda/RedaAlojamiento/resources/lang/es.json
Inicialmente se comenzó a usar la traducción con archivo php, pero resulta muy incómoda así que se decidió agregar la traducción con archivo .json que es más sencilla. Los textos que usan el archivo php se mantendran y cualquier nueva traducción se hará con el archivo .json.
Las traducciones con .json se harán así:
    En los controladores:
        $respuesta = [
            'mensaje_respuesta' => __('Verificación exitosa'), ....
    En las vistas blade se accede a las traducciones:
        <p>{{ __('Prueba de integración con Mercado Libre para importar productos') }}</p>
    En los archivos Javascript se accede a las traducciones de esta manera:
        En las validaciones:
            messages: {
                titulo: {
                    // Si no encuentra la traducción, muestra el texto que tú escribas a la derecha
                    required: window.RedaAlojamientoJson["Nombre del negocio"] || "Nombre del negocio",
                    minlength: window.RedaAlojamientoJson["Mínimo 5 caracteres"] || "Mínimo 5 caracteres"
                }
            }
        En los contenidos dinámicos cargados con Javascript:
            // HTML del Modal
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">${window.RedaAlojamientoJson["Listado del Importador"] || "Listado del Importador"}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ${window.RedaAlojamientoJson["Listado del Importador"] || "Listado del Importador"}
                        </div>
                        </div>
                    </div>
                </div>
            En cualquier otra parte del archivo javascript:
                alert(window.RedaAlojamientoJson["Contenido dinámico para Index Importadores ha sido cargado y el modal está listo"] || "Contenido dinámico para Index Importadores ha sido cargado y el modal está listo.");
Y las traducciones ya existentes con el archivo php (que ya no seguirán haciéndose con el archivo php) se crearon así:
La clave lo más parecido al texto del mensaje, unidas por guión bajo, sin acentos y sin carácteres especiales. Ejemplo:
'este_es_un_mensaje_con_acento_informacion_y_caracteres_especiales' => 'Este es un mensaje con acento información y caracteres especiales !!!!'
En los archivos html se aplicaron así:
<label for="nombre">{{ __('reda-alojamiento::messages.general.nombre_descripcion') }} <span class="text-danger">*</span></label>
En los archivos Javascript, se usaron así:
const mensajeErrorBase = window.RedaAlojamiento?.general?.error_en_el_servidor_de_Torbian || 'Error en el servidor de Torbian';
Y en los archivos php, se codificaron así:
'mensaje_usuario' => __('reda-alojamiento::messages.general.experiencia_eliminada_con_exito'),
Y en cada vista .blade se colocó este script en la sección 'validation_script':
    <script>window.RedaTrans = @json(__('reda-alojamiento::messages'));</script>
Cuando se modifique un código existente y se encuentre con una traducción que está en packages/Reda/RedaAlojamiento/resources/lang/es/messages.php se debe crear esas traducciones en es.json y ya no usar la de message.php, así poco a poco se van a ir sustituyendo las traducciones de message.php por las de es.json.


## PC LOCAL, servidor del IDE Cloud Shell Editor y servidor VESTA DE DESARROLLO
- Este proyecto en mi computadora personal es solo para mantener los archivos fuentes, no para hacer pruebas. Las pruebas se hacen en un servidor Vesta creado especialmente para desarrollo.
- En el IDE Cloud Shell Editor no se ejecuta este proyecto para realizar pruebas, solo es como un lugar donde se tienen los archivos fuentes y se codifica, más no se hace pruebas, así que cuando la IA este revisando un problema en uno o más archivos y quiera por ejemplo ejecutar php artisan... o algún comando de Linux, no creo que de los resultados esperados. Donde se pueden ejecutar esos comandos es el servidor Vesta que es donde se ejecuta y hacen las pruebas de funcionamiento de la aplicación y las IA no tienen acceso al servidor Vesta quien pudiera ejecutar esos comandos es que personalmente acceda vía remota al servidor Vesta y ejecute esos comandos usando estos prefijos:
sudo -u appvac
Si requiere php
sudo -u appvac php8.2

## Escribir en el log de Laravel
Hacerlo de esta manera:
    Agregar al inicio:
        use Illuminate\Support\Facades\Log;
    Para vectores u objeto
        Log::info("Contenido de datosUsuarioConectado: " . print_r($datosUsuarioConectado, true));
    Para string o cualquier otro valor:
        Log::error("...");

## Interacción con la IA
Por favor explicar de manera pedagógica cualquier cambio realizado en el plugin o cualquier código nuevo agregado. Cuando sean cambios particionar la pantalla, en el lado izquierdo mostrar el archivo original completo y en el lado derecho el archivo modificado completo. Resaltando con color las líneas modificadas, eliminadas o agregadas y mostrar la opción de aceptar o rechazar el cambio

## Autorización de codigo nuevo o modificado
Cuando se terminen de agregar código nuevo en un archivo o se haya modificado el existente, siempre se debe hacer una pausa y mostrar los cambios en una pantalla dividida en dos: En el lado izquierdo el archivo original y en el derecho el archivo con las sugerencias de código nuevo o modificado, con un botón de aceptar o rechazar y siempre se debe esperar que yo ACEPTE O RECHACE el código por favor

## Manipulación de imágenes
Para la manipulación de imágenes en las vistas se usará el script:
<script type="text/javascript" src="{{ asset('public/js/reda/general/reda-general-media.min.js?v=' . time()) }}"></script>
Y el controlador:
packages/Reda/RedaAlojamiento/src/Http/Controllers/General/MediaController.php

## Íconos personalizados .svg
Para cualquier ícono personalizado .svg que se requiera crear en el plugin packages/Reda/RedaAlojamiento se debe crear el respectivo archivo .svg en packages/Reda/RedaAlojamiento/resources/js/general/iconos e importar en el archivo .js que lo vaya a usar

## Paginación en el BACKEND
Siempre que se cree una lista debe usarse la paginación de 10 en 10 en el controlador y en la vista con sus respectivos controles de paginación en la parte de abajo
Cuando se haga clic o se toquen los botones de control de paginación hacia atrás o hacia adelante de debe mostrar una animación de "Espera" hasta que responda el servidor

## Paginación en el frontend
En el frontend se crearán carruseles para mostrar los elementos de una lista
Se deben mostrar los primeros 10 elementos de la lista
Para la vista de escritorio el carrusel tendrá dos controles en la parte superior derecha para avanzar o retroceder en la lista
En la vista del celular se desplazará se desplazará el carrusel desplazándose con el dedo
En el frontend no tendrá botones de controles de paginación en la vista de celular y tampoco en la de escritorio, sino que después de mostrar los primeros 10 elementos de la lista o si son menos de 10 los que existan, se creará un último elemento con una collage de fotos explotado con algunas de las imágenes de la lista. Y abajo dirá "Ver todos" o "Ver todas" según el caso. Al hacer clic o tocar en Ver todos se abrirá un modal que mostrará los primeros 10 elementos de la lista. Al paginear en el modal y llegar al final de la lista se mostrará una animación y se enviará una petición ajax para traer los siguientes 10 elementos los cuales se agregarán al final de la lista y así cada vez que se llegue al final de lista. Osea se irá creando un listado infinito.
Cuando una vista de escritorio o celular tenga varios carruseles, deben ser independientes uno de otro. Lo que se esté manipulando en un carrusel no tiene que afectar los otros carruseles

## Creación de nuevas vistas
Cuando se cree una nueva vista, se debe:
Crear el modelo si aplica
Crear y ejecutar la migración si aplica
Crear el archivo blade
Crear el archivo javascript correspondiente
Agregar el archivo para su compilación en webpack.mix.js
Agregar al final del archivo blade la dirección del archivo Javascript minificado
Crear la ruta en el archivo web.php si aplica
Crear la acción en el controlador

## Procesos de cambios masivos
Cuando se requiera hacer un cambio masivo en la aplicación se deben ignorar los archivos que en su nombre contenga la palabra "copy" o "copia" u "original"

## Formatos numéricos PHP
Para los formatos numéricos en php se usará packages/Reda/RedaAlojamiento/src/Helpers/helpers.php

## Animación de espera en el frontend
Se debe usar packages/Reda/RedaAlojamiento/resources/js/general/notificaciones.js para la animación de espera en el frontend ya sea en el dashboard del usuario o en cualquier vista en el frontend a la que tenga el usuario común indistintamente si ha hecho login o no

## Subida de archivos al servidor Vesta de Desarrollo
Los archivos se suben vía FTP al servidor Vesta de Desarrollo para sus respectivas pruebas. Para subir los archivos se usa el script: subir.sh y subir_archivos_puntuales.sh. Nunca se debe ejecutar automáticamente ./subir.sh solo se deben descomentar de la lista de archivos el archivo modificado o agregar el nuevo archivo creado a subir_archivos_puntuales.sh y se deben comentar aquellos archivos que no fueron modificados para que no se vuelvan a subir 
