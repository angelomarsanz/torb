// resources/js/main.js (migrado a packages/Reda/RedaAlojamientoJs)

// Importamos el archivo SASS principal para que Webpack lo procese.
import '../sass/main.scss';

// Importamos los scripts de cada una de las vistas.
// Webpack los incluirá todos en el archivo compilado reda.js.
import './vistas/administrativo/administrativos/indexAdministrativos.js';
// import './vistas/billetera_huesped/billetera_huespedes/indexBilleteraHuespedes.js';
// import './vistas/disputa/disputas/indexDisputa.js';
import './vistas/experiencia/experiencias/indexExperiencias.js';
import './vistas/experiencia/experiencias/createExperiencias.js';
import './vistas/experiencia/actividades_experiencias/indexActividadesExperiencias.js';
import './vistas/experiencia/horarios_experiencias/indexHorariosExperiencias.js';
import './vistas/experiencia/informaciones_experiencias/indexInformacionesExperiencias.js';
import './vistas/experiencia/reservaciones_experiencias/indexReservacionesExperiencias.js';
import './vistas/experiencia/anfitriones_experiencias/indexAnfitrionesExperiencias.js';
import './vistas/experiencia/ui/addPublicaExperienciaBtn.js';
