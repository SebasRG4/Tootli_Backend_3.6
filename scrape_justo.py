import subprocess
import json
import pandas as pd
import time
import random
import re

def extraer_catalogo_justo_extenso(codigo_postal="06470", store_id=1, module_id=2):
    print(f"\n========================================================")
    print(f"🚀 INICIANDO EXTRACCIÓN AVANZADA DE JÜSTO PARA CP: {codigo_postal}")
    print(f"========================================================\n")
    
    # Términos de búsqueda clave para traer productos variados de todos los departamentos
    terminos_busqueda = [
        'leche', 'huevo', 'queso', 'crema', 'mantequilla', 'yoghurt',
        'pan', 'tortilla', 'bolillo', 'donas',
        'coca-cola', 'agua', 'jugo', 'refresco',
        'papas', 'galletas', 'botanas', 'chocolates',
        'arroz', 'frijol', 'aceite', 'mayonesa', 'atun', 'pasta', 'cafe',
        'platano', 'jitomate', 'aguacate', 'limon', 'cebolla', 'manzana',
        'detergente', 'jabon', 'higienico', 'shampoo', 'pasta dental'
    ]
    
    productos_totales = []
    seen_skus = set()
    id_counter = 1
    
    for indice, termino in enumerate(terminos_busqueda):
        print(f"[{indice + 1}/{len(terminos_busqueda)}] Buscando productos para: '{termino}'...")
        
        # Codificamos el término de búsqueda para la URL
        termino_encoded = termino.replace(" ", "%20")
        url = f"https://client-api-gateway.justo.mx/v1/search/results?zipCode={codigo_postal}&query={termino_encoded}&limit=16&page=0"
        
        try:
            # Ejecutamos curl directamente a través del sistema
            cmd = ['curl', '-s', '-H', 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', url]
            output = subprocess.check_output(cmd)
            
            data = json.loads(output)
            res_dict = data.get("data", {}).get("result", {})
            productos = res_dict.get("products", [])
            
            if not productos:
                print(f"   -> No se encontraron productos para '{termino}'.")
                continue
                
            nuevos_en_esta_busqueda = 0
            for p in productos:
                # Evitar duplicados usando el SKU / ID del producto
                sku = p.get("sku") or p.get("id")
                if sku in seen_skus:
                    continue
                seen_skus.add(sku)
                
                # Nombre del producto
                nombre = p.get("name") or p.get("title") or "Producto sin nombre"
                
                # Descripción limpia de HTML
                desc_raw = p.get("description") or ""
                desc_clean = re.sub('<[^<]+?>', '', desc_raw).strip()
                if not desc_clean:
                    desc_clean = f"Producto seleccionado en la sección de {termino}."
                
                # Imagen
                image_url = p.get("imageUrl") or ""
                if not image_url and isinstance(p.get("images"), list) and len(p.get("images")) > 0:
                    image_url = p.get("images")[0]
                
                # Precio
                precio = 0.0
                precio_raw = p.get("metadata", {}).get("rawPrice", {})
                if isinstance(precio_raw, dict):
                    precio = float(precio_raw.get("price") or 0.0)
                
                # Categoría de Jüsto
                cat_dict = p.get("metadata", {}).get("category", {})
                cat_name = cat_dict.get("name", "General") if isinstance(cat_dict, dict) else "General"
                
                # Mapear CategoryId por default o dejar secuenciales de mapeo rápido
                # (Lácteos = 10, Panadería = 11, Bebidas = 12, Botanas = 13, Despensa = 14, Frutas = 15, Limpieza = 16)
                cat_id = 14  # Despensa por defecto
                sub_cat_id = 141
                
                cat_lower = cat_name.lower()
                termino_lower = termino.lower()
                
                # Lógica avanzada de categorización cruzada (término + categoría de Jüsto)
                if any(x in cat_lower or x in termino_lower for x in ["leche", "lácteo", "lacteo", "queso", "huevo", "crema", "mantequilla", "yoghurt"]):
                    cat_id, sub_cat_id = 10, 101
                elif any(x in cat_lower or x in termino_lower for x in ["pan", "tortilla", "reposter", "bolillo", "dona"]):
                    cat_id, sub_cat_id = 11, 111
                elif any(x in cat_lower or x in termino_lower for x in ["bebida", "refresco", "agua", "jugo", "coca"]):
                    cat_id, sub_cat_id = 12, 121
                elif any(x in cat_lower or x in termino_lower for x in ["botana", "galleta", "papa", "dulce", "chocolate"]):
                    cat_id, sub_cat_id = 13, 131
                elif any(x in cat_lower or x in termino_lower for x in ["fruta", "verdura", "básico", "basico", "platano", "jitomate", "aguacate", "limon", "cebolla", "manzana"]):
                    cat_id, sub_cat_id = 15, 151
                elif any(x in cat_lower or x in termino_lower for x in ["limpieza", "cuidado", "jabón", "jabon", "shampoo", "dental", "higienico"]):
                    cat_id, sub_cat_id = 16, 161
                
                # Formato exacto de importación masiva de Tootli
                info_producto = {
                    "Id": id_counter,
                    "Name": nombre,
                    "Description": desc_clean,
                    "Image": image_url,
                    "Images": json.dumps([]),
                    "CategoryId": cat_id,
                    "SubCategoryId": sub_cat_id,
                    "UnitId": 1,
                    "Stock": 150,
                    "Price": precio,
                    "Discount": 0,
                    "DiscountType": "amount",
                    "AvailableTimeStarts": "00:00:00",
                    "AvailableTimeEnds": "23:59:59",
                    "Variations": json.dumps([]),
                    "ChoiceOptions": json.dumps([]),
                    "AddOns": json.dumps([]),
                    "Attributes": json.dumps([]),
                    "StoreId": store_id,
                    "ModuleId": module_id,
                    "Status": "active",
                    "Veg": "yes" if ("fruta" in cat_lower or "verdura" in cat_lower or "agua" in cat_lower) else "no",
                    "Recommended": "yes" if (random.random() > 0.85) else "no"
                }
                productos_totales.append(info_producto)
                id_counter += 1
                nuevos_en_esta_busqueda += 1
                
            print(f"   -> Encontrados {len(productos)} productos ({nuevos_en_esta_busqueda} nuevos añadidos).")
            
        except Exception as e:
            print(f"   -> Error al buscar '{termino}': {e}")
            
        # Descanso muy ligero para ser amigable con el servidor
        time.sleep(random.uniform(0.8, 1.5))
        
    # Transformar los datos recolectados en el archivo Excel masivo de Tootli
    if productos_totales:
        df = pd.DataFrame(productos_totales)
        nombre_archivo = "grocery_catalog_mexico.xlsx"
        df.to_excel(nombre_archivo, index=False)
        print(f"\n========================================================")
        print(f"✨ ¡PROCESO DE EXTRACCIÓN AVANZADA COMPLETADO!")
        print(f"========================================================")
        print(f"📂 Archivo generado: {nombre_archivo}")
        print(f"📦 Total de productos ÚNICOS de Jüsto: {len(productos_totales)}")
        print(f"🏪 Asociados a Store ID: {store_id}")
        print(f"🧩 Asociados a Module ID: {module_id}")
        print(f"\n💡 Consejos de Uso:")
        print(f"1. Abre este archivo en Excel / Google Sheets.")
        print(f"2. Mapea la columna 'CategoryId' y 'SubCategoryId' con los IDs reales de tus categorías.")
        print(f"3. Súbelo directo desde la sección 'Bulk Import' en el Panel Admin.")
        print(f"========================================================\n")
    else:
        print("❌ No se pudo recolectar información para exportar.")

# Ejecutar el scraper con StoreId=1 y ModuleId=2
extraer_catalogo_justo_extenso(codigo_postal="06470", store_id=1, module_id=2)
