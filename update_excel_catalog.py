import pandas as pd
import shutil
import os

def update_excel_catalog():
    excel_path = "grocery_catalog_mexico.xlsx"
    backup_path = "grocery_catalog_mexico_backup.xlsx"
    
    if not os.path.exists(excel_path):
        print(f"Error: No se encontró el archivo {excel_path} en el directorio actual.")
        return
        
    # Crear un backup de seguridad por si acaso
    shutil.copyfile(excel_path, backup_path)
    print(f"✓ Backup creado con éxito en: {backup_path}")
    
    # Cargar el Excel
    df = pd.read_excel(excel_path)
    print(f"Cargado catálogo con {len(df)} productos.")
    
    # ----------------------------------------------------
    # CONFIGURACIÓN DE IDS DE BASE DE DATOS LOCAL
    # ----------------------------------------------------
    # Store ID: 1 (Super RIGOVI - Módulo Grocery)
    # Module ID: 1 (Módulo Super Tootli - Grocery)
    # ----------------------------------------------------
    store_id = 1
    module_id = 1
    
    # Mapeo de Categorías en base al volcado de la Base de Datos Local de Tootli:
    # 10 (Leches/Lácteos) -> 7 (Lácteos y Huevo)
    # 11 (Panadería)      -> 11 (Pan y tortillas)
    # 12 (Refrescos/Jugos)-> 31 (Bebidas - a crear en la BD local, será el ID 31 autoincrementable)
    # 13 (Verduras)       -> 1 (Frutas y Verduras)
    # 14 (Abarrotes/Arroz)-> 9 (Granel)
    # 15 (Frutas)         -> 1 (Frutas y Verduras)
    # 16 (Higiene/Jabón)  -> 12 (Higiene personal y belleza)
    
    category_mapping = {
        10: 7,   # Leches -> Lácteos y Huevo
        11: 11,  # Panadería -> Pan y tortillas
        12: 38,  # Refrescos -> Refrescos y bebidas (ID 38 real en BD en vivo)
        13: 1,   # Verduras -> Frutas y Verduras
        14: 9,   # Abarrotes -> Granel / Despensa
        15: 1,   # Frutas -> Frutas y Verduras
        16: 12   # Cuidado personal -> Higiene personal y belleza
    }
    
    # Modificar valores en el DataFrame
    df['StoreId'] = store_id
    df['ModuleId'] = module_id
    
    # Aplicar el mapeo de categorías
    df['CategoryId'] = df['CategoryId'].map(category_mapping).fillna(df['CategoryId']).astype(int)
    
    # IMPORTANTE: El importador de Laravel de Tootli exige que la columna SubCategoryId no esté vacía.
    # Si no hay subcategoría, se debe colocar el mismo ID de la categoría principal (CategoryId).
    df['SubCategoryId'] = df['CategoryId']
    
    # Guardar los cambios en el Excel original
    df.to_excel(excel_path, index=False)
    print("✓ Archivo de catálogo Excel actualizado con éxito.")
    
    # Mostrar resumen
    print("\nResumen del Mapeo aplicado en el catálogo:")
    print(f"- StoreId unificado a: {store_id} (Super RIGOVI)")
    print(f"- ModuleId unificado a: {module_id} (Grocery)")
    print("- Mapeo de CategoryId completado:")
    for key, val in category_mapping.items():
        name_orig = {10: "Lácteos/Leches", 11: "Panadería", 12: "Refrescos/Bebidas", 13: "Verduras", 14: "Abarrotes/Granel", 15: "Frutas", 16: "Cuidado Personal"}[key]
        name_dest = {7: "Lácteos y Huevo (ID 7)", 11: "Pan y tortillas (ID 11)", 38: "Refrescos y bebidas (ID 38)", 1: "Frutas y Verduras (ID 1)", 9: "Granel (ID 9)", 12: "Higiene personal y belleza (ID 12)"}[val]
        print(f"  * original {key} ({name_orig}) -> nuevo {val} ({name_dest})")
    
    print("- SubCategoryId: Limpiado (Null) para evitar conflictos al importar.")

if __name__ == "__main__":
    update_excel_catalog()
