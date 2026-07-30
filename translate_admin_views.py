import os
import re
import ssl
import json
import time
import urllib.request
import urllib.parse

# Base Directory to translate Blade views
BASE_DIR = "/Users/giovannavilchis/Herd/back3.6/resources/views"
LOG_FILE = "/Users/giovannavilchis/Herd/back3.6/translation_progress.log"

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

# Dictionary for curated Mexican Spanish translations
MEXICAN_DICTIONARY = {
    "dashboard": "Panel de Control",
    "Dashboard": "Panel de Control",
    "welcome": "Bienvenido",
    "welcome_message": "Gestiona la operación de tu plataforma desde aquí",
    "All_Zones": "Todas las Zonas",
    "This_Year": "Este Año",
    "This_Month": "Este Mes",
    "This_Week": "Esta Semana",
    "Total rides": "Viajes Totales",
    "Completed rides": "Viajes Completados",
    "Active rides": "Viajes Activos",
    "Pending requests": "Solicitudes Pendientes",
    "completed overall": "completados en total",
    "In progress now": "En curso ahora",
    "Awaiting assignment": "Esperando asignación",
    "46% of all rides": "46% de todos los viajes",
    "Drivers": "Repartidores",
    "Riders": "Clientes",
    "Ride earnings": "Ganancias de Viajes",
    "Commission": "Comisión",
    "Restaurants": "Restaurantes",
    "Food orders": "Pedidos de Comida",
    "orders": "Pedidos",
    "order": "Pedido",
    "stores": "Negocios",
    "store": "Negocio",
    "items": "Productos",
    "item": "Producto",
    "customers": "Clientes",
    "customer": "Cliente",
    "delivery_man": "Repartidor",
    "deliveryman": "Repartidor",
    "unassigned_orders": "Pedidos Sin Asignar",
    "accepted_by_dm": "Aceptado por Repartidor",
    "packaging": "Empacando",
    "out_for_delivery": "En Camino de Entrega",
    "delivered": "Entregado",
    "canceled": "Cancelado",
    "failed": "Fallido",
    "refunded": "Reembolsado",
    "pending": "Pendiente",
    "settings": "Configuración",
    "Settings": "Configuración",
    "Users": "Usuarios",
    "Transactions & Reports": "Transacciones y Reportes",
    "Dispatch": "Despacho",
    "Market Intelligence": "Inteligencia de Mercado",
    "Log in": "Iniciar Sesión",
    "Log in →": "Iniciar Sesión →",
    "Remember me": "Recordarme",
    "Forgot password?": "¿Olvidaste tu contraseña?",
    "your_email": "Tu correo electrónico",
    "password": "Contraseña",
    "software_version": "Versión del sistema",
    "Send Mail": "Enviar Correo",
    "Got_It": "Entendido",
}

translation_cache = {}

def translate_text(text):
    if not text or not text.strip():
        return text
    
    clean_key = text.strip().replace("messages.", "")
    if clean_key in MEXICAN_DICTIONARY:
        return MEXICAN_DICTIONARY[clean_key]
    if text in MEXICAN_DICTIONARY:
        return MEXICAN_DICTIONARY[text]
    
    if text in translation_cache:
        return translation_cache[text]

    # Use Google Translate API for fallback to Mexican Spanish
    try:
        url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=es&dt=t&q=" + urllib.parse.quote(clean_key.replace('_', ' '))
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=5, context=ctx) as response:
            result = json.loads(response.read().decode('utf-8'))
            translated = "".join([item[0] for item in result[0] if item[0]])
            if translated:
                translation_cache[text] = translated
                return translated
    except Exception:
        pass
    
    return clean_key.replace('_', ' ').capitalize()

def process_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        # Pattern to match translate('...') or translate("...") or translate('messages....')
        pattern = r"translate\(\s*(['\"])(.*?)\1\s*\)"
        
        def replace_match(match):
            original = match.group(2)
            translated = translate_text(original)
            escaped = translated.replace("'", "\\'")
            return f"'{escaped}'"

        new_content = re.sub(pattern, replace_match, content)

        if new_content != content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            return True
    except Exception as e:
        with open(LOG_FILE, 'a', encoding='utf-8') as log:
            log.write(f"Error procesando {filepath}: {e}\n")
    return False

def main():
    with open(LOG_FILE, 'w', encoding='utf-8') as log:
        log.write("Iniciando servicio de traducción a Español Mexicano...\n")

    files_processed = 0
    files_changed = 0

    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.blade.php'):
                filepath = os.path.join(root, file)
                files_processed += 1
                if process_file(filepath):
                    files_changed += 1
                if files_processed % 50 == 0:
                    with open(LOG_FILE, 'a', encoding='utf-8') as log:
                        log.write(f"Procesados {files_processed} archivos... ({files_changed} modificados)\n")

    with open(LOG_FILE, 'a', encoding='utf-8') as log:
        log.write(f"Proceso finalizado con éxito. Total archivos procesados: {files_processed}, Modificados: {files_changed}\n")

if __name__ == "__main__":
    main()
