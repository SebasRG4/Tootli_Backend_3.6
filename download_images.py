import os
import pandas as pd
import requests
from concurrent.futures import ThreadPoolExecutor, as_completed

def descargar_imagen(row, target_dir):
    product_id = row['Id']
    url = row['Image']
    
    # Si ya es un nombre de archivo local o está vacío, no hacer nada
    if not url or not url.startswith('http'):
        return product_id, url, False
        
    filename = f"justo_{product_id}.jpg"
    filepath = os.path.join(target_dir, filename)
    
    headers = {
        "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    try:
        response = requests.get(url, headers=headers, timeout=10)
        if response.status_code == 200:
            with open(filepath, 'wb') as f:
                f.write(response.content)
            return product_id, filename, True
        else:
            print(f"⚠️ Error al descargar imagen de ID {product_id} (Status: {response.status_code})")
            return product_id, url, False
    except Exception as e:
        print(f"⚠️ Excepción al descargar imagen de ID {product_id}: {e}")
        return product_id, url, False

def iniciar_descarga_masiva():
    excel_path = "grocery_catalog_mexico.xlsx"
    target_directory = "storage/app/public/product"
    
    # Verificar que el Excel exista
    if not os.path.exists(excel_path):
        print(f"❌ Error: No se encontró el archivo '{excel_path}' en la raíz de tu proyecto.")
        return
        
    # Crear el directorio de destino si no existe
    os.makedirs(target_directory, exist_ok=True)
    
    # Leer el Excel
    print(f"📖 Leyendo el archivo '{excel_path}'...")
    df = pd.read_excel(excel_path)
    
    print(f"🚀 Iniciando descarga en paralelo de {len(df)} imágenes en '{target_directory}'...")
    
    # Descargar imágenes en paralelo usando 20 hilos simultáneos
    actualizaciones = {}
    total_descargadas = 0
    
    rows_to_process = df.to_dict('records')
    
    with ThreadPoolExecutor(max_workers=20) as executor:
        futures = {executor.submit(descargar_imagen, row, target_directory): row for row in rows_to_process}
        
        for future in as_completed(futures):
            product_id, new_value, success = future.result()
            actualizaciones[product_id] = new_value
            if success:
                total_descargadas += 1
                if total_descargadas % 50 == 0:
                    print(f"   -> Descargadas {total_descargadas}/{len(df)} imágenes...")
                    
    # Actualizar la columna 'Image' en el DataFrame
    df['Image'] = df['Id'].map(actualizaciones)
    
    # Guardar de nuevo el Excel
    print(f"✍️ Guardando catálogo actualizado en '{excel_path}'...")
    df.to_excel(excel_path, index=False)
    
    print(f"\n========================================================")
    print(f"✨ ¡DESCARGA Y ACTUALIZACIÓN COMPLETADAS CON ÉXITO!")
    print(f"========================================================")
    print(f"📂 Carpeta de destino: {target_directory}")
    print(f"📦 Total de imágenes descargadas físicamente: {total_descargadas}")
    print(f"📝 Archivo Excel actualizado con nombres locales: {excel_path}")
    print(f"========================================================\n")

if __name__ == "__main__":
    iniciar_descarga_masiva()
