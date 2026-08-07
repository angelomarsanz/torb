#!/bin/bash
# --- compilar.sh en Servidor ---
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${YELLOW}--- SELECCIONE QUÉ COMPILAR EN PRUEBAS ---${NC}"
read -p "¿Compilar App Principal? (S/N): " RESP_APP
read -p "¿Compilar Reda Alojamiento? (S/N): " RESP_ALOJ

VARS=""
[[ "$RESP_APP" =~ ^[Ss]$ ]] && VARS+="BUILD_APP=true " || VARS+="BUILD_APP=false "
[[ "$RESP_ALOJ" =~ ^[Ss]$ ]] && VARS+="BUILD_ALOJAMIENTO=true " || VARS+="BUILD_ALOJAMIENTO=false "

echo -e "${YELLOW}Ejecutando compilación...${NC}"
sudo -u appvac env $VARS npm run prod
echo -e "${GREEN}✅ ¡Listo! Archivos generados en public/js y public/css${NC}"