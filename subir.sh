#!/bin/bash

# --- deploy.sh ---

# Carga las variables desde el archivo .env
set -o allexport
source .env
set +o allexport

echo "🚀 Iniciando despliegue en el servidor..."

# 1. Preguntar y compilar/subir los assets de frontend
read -p "¿Desea compilar y subir los archivos del frontend? (S/N): " RESPUESTA_FRONTEND

if [[ "$RESPUESTA_FRONTEND" == "S" || "$RESPUESTA_FRONTEND" == "s" ]]; then
    echo "Compilando y subiendo assets de frontend..."
    npm run prod # Esto ejecutará la compilación y el script de subida en webpack.mix.js
    echo "✅ Assets de frontend compilados y subidos."
else
    echo "⏩ Omitiendo compilación y subida de assets de frontend."
fi

# 2. Sube archivos PHP puntuales (del proyecto original o de módulos REDA)
echo "Subiendo archivos PHP puntuales (si aplica)..."

# --- CONFIGURACIÓN DE SUBIDA PUNTUAL ---
# La lista de archivos puntuales se carga desde un archivo externo.
if [ -f "./subir_archivos_puntuales.sh" ]; then
    source ./subir_archivos_puntuales.sh
    echo "Lista de archivos puntuales cargada desde ./subir_archivos_puntuales.sh"
else
    echo "Advertencia: No se encontró el archivo de configuración './subir_archivos_puntuales.sh'. No se subirán archivos puntuales."
    ARCHIVOS_PHP_PUNTUALES=() # Definir como vacío para evitar errores
fi

# Comprobar si se debe ejecutar la subida de archivos puntuales
if [ ${#ARCHIVOS_PHP_PUNTUALES[@]} -gt 0 ] && [ "${ARCHIVOS_PHP_PUNTUALES[0]}" != "Ninguno" ]; then
    lftp_commands_puntual="set ftp:ssl-allow no; open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;"
    archivos_encontrados=0
    for archivo in "${ARCHIVOS_PHP_PUNTUALES[@]}"; do
        if [ -f "$archivo" ]; then
            archivos_encontrados=$((archivos_encontrados + 1))
            NOMBRE_ARCHIVO=$(basename "$archivo")
            CARPETA_PADRE=$(basename "$(dirname "$archivo")")
            NOMBRE_RESPALDO="${CARPETA_PADRE}_${NOMBRE_ARCHIVO}"
            URL_ORIGEN_FTP="/$archivo"
            URL_RESPALDO_FTP="$FTP_REMOTE_PATH_OLD/$NOMBRE_RESPALDO"
            DIRECTORIO_REMOTO=$(dirname "$URL_ORIGEN_FTP")

            echo "  -> Preparando para subir (puntual): $NOMBRE_ARCHIVO..."
            lftp_commands_puntual+="mv ${URL_ORIGEN_FTP} ${URL_RESPALDO_FTP}; mkdir -p ${DIRECTORIO_REMOTO}; put -O ${DIRECTORIO_REMOTO}/ ${archivo};"
        else
            echo "  ⚠️  Archivo puntual no encontrado localmente, omitiendo: $archivo"
        fi
    done

    if [ $archivos_encontrados -gt 0 ]; then
        echo "📡 Ejecutando subida masiva (puntual) con lftp..."
        lftp -c "$lftp_commands_puntual"
        echo "✅ Archivos PHP puntuales han sido subidos."
    else
        echo "⏩ No se encontraron archivos puntuales válidos para subir."
    fi
fi

echo "🎉 Despliegue finalizado."
