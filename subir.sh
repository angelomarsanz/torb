#!/bin/bash
# --- subir.sh en IDX ---
set -o allexport
source .env
set +o allexport

if [ -f "./subir_archivos_puntuales.sh" ]; then
    source ./subir_archivos_puntuales.sh
else
    echo "Error: No existe ./subir_archivos_puntuales.sh"
    exit 1
fi

echo "🚀 Subiendo archivos fuente a Pruebas..."

if [ ${#ARCHIVOS_PHP_PUNTUALES[@]} -gt 0 ] && [ "${ARCHIVOS_PHP_PUNTUALES[0]}" != "Ninguno" ]; then
    # Conexión FTP para subida rápida
    lftp_cmd="set ftp:ssl-allow no; open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;"
    
    for archivo in "${ARCHIVOS_PHP_PUNTUALES[@]}"; do
        if [ -f "$archivo" ]; then
            DIRECTORIO_REMOTO=$(dirname "/$archivo")
            echo "  -> Subiendo: $archivo"
            lftp_cmd+="mkdir -p $DIRECTORIO_REMOTO; put -O $DIRECTORIO_REMOTO/ $archivo;"
        fi
    done
    lftp -c "$lftp_cmd"
    echo "✅ Subida finalizada."
else
    echo "Nada que subir."
fi