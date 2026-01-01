#!/bin/bash
###############################################################################
# Script de Instalación - Sistema de Hojas de Servicio
# Ejecutar con: sudo bash /var/www/html/forms/install.sh
###############################################################################

set -e  # Salir en caso de error

echo "=========================================="
echo "Instalación - Sistema de Hojas de Servicio"
echo "=========================================="
echo ""

# Verificar si se ejecuta como root
if [[ $EUID -ne 0 ]]; then
   echo "❌ Este script debe ejecutarse como root (usar: sudo bash install.sh)"
   exit 1
fi

# Directorio de la aplicación
APP_DIR="/var/www/html/forms"
WEB_USER="www-data"
DB_SCRIPT="$APP_DIR/setup/database.sql"

echo "📁 Directorio de aplicación: $APP_DIR"
echo ""

# ============================================
# 1. Crear directorio de PDFs con permisos
# ============================================
echo "1️⃣  Configurando directorios..."
mkdir -p "$APP_DIR/pdfs"
chown -R $WEB_USER:$WEB_USER "$APP_DIR/pdfs"
chmod -R 755 "$APP_DIR/pdfs"
echo "   ✅ Directorio de PDFs configurado"

# ============================================
# 2. Asegurar permisos generales
# ============================================
echo "2️⃣  Asignando permisos..."
chown -R $WEB_USER:$WEB_USER "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 644 "$APP_DIR"/*.php
find "$APP_DIR/config" -name "*.php" -exec chmod 640 {} \;
echo "   ✅ Permisos asignados"

# ============================================
# 3. Crear base de datos
# ============================================
echo ""
echo "3️⃣  Creando base de datos..."
echo "   Ejecutando script SQL..."

# Ejecutar script SQL
if mysql < "$DB_SCRIPT" 2>/dev/null; then
    echo "   ✅ Base de datos creada exitosamente"
else
    echo "   ⚠️  Error al crear base de datos"
    echo "   Intenta ejecutar manualmente:"
    echo "   mysql -u root -p < $DB_SCRIPT"
fi

# ============================================
# 4. Verificar instalación
# ============================================
echo ""
echo "4️⃣  Verificando instalación..."

# Verificar que los archivos existan
if [ -f "$APP_DIR/index.php" ] && [ -f "$APP_DIR/dashboard.php" ]; then
    echo "   ✅ Archivos de la aplicación encontrados"
else
    echo "   ❌ Archivos faltantes"
    exit 1
fi

# Verificar permisos de directorio
if [ -w "$APP_DIR/pdfs" ]; then
    echo "   ✅ Permisos de escritura en directorio PDFs"
else
    echo "   ❌ Sin permisos de escritura en PDFs"
    exit 1
fi

# ============================================
# 5. Resumen
# ============================================
echo ""
echo "=========================================="
echo "✅ Instalación Completada"
echo "=========================================="
echo ""
echo "📋 Próximos pasos:"
echo ""
echo "1. Abre tu navegador:"
echo "   http://localhost/forms"
echo ""
echo "2. Usa estas credenciales:"
echo "   👤 Usuario: admin"
echo "   🔐 Contraseña: admin123"
echo ""
echo "   O:"
echo "   👤 Usuario: juan"
echo "   🔐 Contraseña: juan123"
echo ""
echo "3. Para cPanel, actualiza en config/database.php:"
echo "   - DB_PASS con tu contraseña de cPanel"
echo "   - APP_URL con tu dominio"
echo ""
echo "=========================================="
