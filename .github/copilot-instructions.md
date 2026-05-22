# Información para el desarrollo del plugin packages/Reda/RedaAlojamiento en este proyecto de alojamientos (hospedajes) "Torbian" desarrollado en Laravel

## Plugin packages/Reda/Alojamiento
Este no es un proyecto propio, es de otro "autor" y es parecido a la famosa aplicación Airbnb para ofrecer hospedaje de apartamentos, casas, habitaciones, etc para hospedaje de temporada.
El usuario solicitó crear un módulo de "Experiencias" para ofrecer a los huéspedes, pero luego cambió de opinión y pidió que en lugar de "Experiencias" fuese algo más amplio como "Negocios". Cualquier negocio que ofrezca productos y/o servicios para los huéspedes. Así que se creó inicialmente el módulo Experiencias. dentro del plugin packages/Reda/RedaAlojamiento. Este es el primer módulo que se está desarrollando.

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

## Estilo de Código General
- Los comentarios deben estar en español.
- Usar nombres de variables descriptivos en español.

## Estilos CSS
- En este proyecto se usan las siguientes versiones de Bootstrap:
    - En el Frontend se utiliza Bootstrap 4.5
    - En el Backend se utiliza Bootstrap 5.2.3
- El plugin Reda/Alojamiento tiene sus propios estilos:
Para el admin: packages/Reda/RedaAlojamiento/resources/sass/admin/main.scss (por favor codificar los estilos del admin en este archivo)
Para el frontend: packages/Reda/RedaAlojamiento/resources/sass/frontend/main.scss (por favor codificar los estilos del frontend en este archivo)
Esos estilos se agregan al proyecto principal en los archivos:
En el admin: resources/views/admin/common/head.blade.php
En el frontend: resources/views/common/head.blade.php
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

                            const mensajeErrorBase = window.RedaAlojamiento?.general?.error_en_el_servidor_de_Torbian || 'Error en el servidor de Torbian';
                            const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                            // 2. Construimos la respuesta usando los datos reales del servidor si existen

                            let respuesta = {
                                'success': false,
                                'message' : 'Error eliminando experiencia',
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

## Herramienta de desarrollo
webpack.mix.js

## PHP 
- Para la conexión a base de datos, usa la librería `PDO`. Nunca uses funciones antiguas como `mysql_*`.
- Este proyecto está desarrollado en Laravel versión 11.

Las respuestas del servidor para funciones internas y peticiones ajax tendrán esta estructura:
    $respuesta = [
        'success' => true,
        'message' => 'Experiencia eliminada', // Un mensaje corto para uso del desarrollador o soporte técnico
        'mensaje_usuario' => __('reda-alojamiento::messages.general.experiencia_eliminada_con_exito'), // Un mensaje explicativo y traducido para el usuario
        'respuesta' => '', // La respuesta esperada por la función llamadora, puede ser '', un string, vector u objeto
        'code' => 200
    ];
    return response()->json($respuesta, $respuesta['code']);

## Traducciones
Para crear los mensajes de traducción en el vector del archivo packages/Reda/RedaAlojamiento/resources/lang/es/messages.php, se deben seguir estas indicaciones:
La clave debe ser lo más parecido al texto del mensaje, debe contener todas las palabras del texto unidad por guión bajo, sin acentos y sin carácteres especiales. Ejemplo:
'este_es_un_mensaje_con_acento_informacion_y_caracteres_especiales' => 'Este es un mensaje con acento información y caracteres especiales !!!!'
Las traducciones en los archivos html deben ser como en este ejemplo:
<label for="nombre">{{ __('reda-alojamiento::messages.general.nombre_descripcion') }} <span class="text-danger">*</span></label>
En los archivos Javascript, se usará el signo de interrogación, por ejemplo:
const mensajeErrorBase = window.RedaAlojamiento?.general?.error_en_el_servidor_de_Torbian || 'Error en el servidor de Torbian';
En los archivos php, se usará así, por ejemplo:
'mensaje_usuario' => __('reda-alojamiento::messages.general.experiencia_eliminada_con_exito'),
Para que las traducciones funcionen se debe colocar este script:
    <script>window.RedaTrans = @json(__('reda-alojamiento::messages'));</script>
Al final de los archivos .blade preferiblemente al principio de la sección 'validation_script', en caso de que exista esa sección en el archivo .blade

## PC LOCAL, servidor del IDE Cloud Shell Editor y servidor VESTA DE DESARROLLO
- Este proyecto en mi computadora personal es solo para mantener los archivos fuentes, no para hacer pruebas. Las pruebas se hacen en un servidor Vesta creado especialmente para desarrollo.
- En el IDE Cloud Shell Editor no se ejecuta este proyecto para realizar pruebas, solo es como un lugar donde se tienen los archivos fuentes y se codifica, más no se hace pruebas, así que cuando la IA Gemini o Copilot este revisando un problema en uno o más archivos y quiera por ejemplo ejecutar php artisan... o algún comando de Linux, no creo que de los resultados esperados. Donde se pueden ejecutar esos comandos es el servidor Vesta que es donde se ejecuta y hacen las pruebas de funcionamiento de la aplicación y las IA no tienen acceso al servidor Vesta quien pudiera ejecutar esos comandos es que personalmente acceda vía remota al servidor Vesta y ejecute esos comandos usando estos prefijos:
sudo -u appvac
Si requiere php
sudo -u appvac php8.2
Para migraciones
sudo -u appvac php8.2 artisan migrate

## Escribir en el log de Laravel
Hacerlo de esta manera:
    Agregar al inicio:
        use Illuminate\Support\Facades\Log;
    Para vectores u objeto
        Log::info("Contenido de datosUsuarioConectado: " . print_r($datosUsuarioConectado, true));
    Para string o cualquier otro valor:
        Log::error("...");

## Interacción con la IA: Gemini o Copilot Github
Por favor explicar de manera pedagógica cualquier cambio realizado en el plugin o cualquier código nuevo agregado. Cuando sean cambios particionar la pantalla, en el lado izquierdo mostrar el archivo original completo y en el lado derecho el archivo modificado completo. Resaltando con color las líneas modificadas, eliminadas o agregadas y mostrar la opción de aceptar o rechazar el cambio
