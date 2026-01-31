const $ = window.jQuery;

// 2. Importamos el plugin de validación. 
// Webpack lo encontrará en node_modules, pero lo aplicará al jQuery de arriba.
import 'jquery-validation';
import 'jquery-validation/dist/additional-methods.js';

// 3. Aseguramos que el signo $ y jQuery globales tengan el plugin asignado
window.jQuery = window.$ = $;

// Importamos los scripts de cada una de las vistas.
// Webpack los incluirá todos en el archivo compilado reda.js.
import './vistas/administrativo/administrativos/indexAdministrativos.js';
// import './vistas/billetera_huesped/billetera_huespedes/indexBilleteraHuespedes.js';
// import './vistas/disputa/disputas/indexDisputa.js';
import './vistas/experiencia/experiencias/indexExperiencias.js';
import './vistas/experiencia/experiencias/createExperiencias.js';
import './vistas/experiencia/formularios_de_pasos/formularioDePasos.js';
import './vistas/experiencia/actividades_experiencias/indexActividadesExperiencias.js';
import './vistas/experiencia/horarios_experiencias/indexHorariosExperiencias.js';
import './vistas/experiencia/informaciones_experiencias/indexInformacionesExperiencias.js';
import './vistas/experiencia/reservaciones_experiencias/indexReservacionesExperiencias.js';
import './vistas/experiencia/anfitriones_experiencias/indexAnfitrionesExperiencias.js';
import './vistas/experiencia/ui/addPublicaExperienciaBtn.js';
