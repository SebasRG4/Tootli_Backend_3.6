import re

def patch_view(file_path):
    with open(file_path, "r") as f:
        content = f.read()

    # We will add it right before the Map section (Latitude/Longitude)
    # Search for latitude in index.blade.php
    marker = """                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="latitude">{{ 'Latitud' }}"""
                                        
    injection = """                                <!-- Directory Fields -->
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label d-flex align-items-center gap-2">
                                            <input type="checkbox" name="is_directory_only" value="1" {{ old('is_directory_only') ? 'checked' : '' }}>
                                            Es un lugar de directorio (Sin dueño, no recibe órdenes)
                                        </label>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label class="input-label" for="directory_description">Descripción para turismo (Opcional)</label>
                                        <textarea name="directory_description" id="directory_description" class="form-control" rows="3" placeholder="Descripción breve del lugar..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="latitude">{{ 'Latitud' }}"""
                                        
    content = content.replace(marker, injection)
    
    with open(file_path, "w") as f:
        f.write(content)
        
patch_view("resources/views/admin-views/vendor/index.blade.php")
# For edit, old might be different
def patch_edit_view(file_path):
    with open(file_path, "r") as f:
        content = f.read()

    marker = """                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="latitude">{{ 'Latitud' }}"""
                                        
    injection = """                                <!-- Directory Fields -->
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label d-flex align-items-center gap-2">
                                            <input type="checkbox" name="is_directory_only" value="1" {{ $store->is_directory_only ? 'checked' : '' }}>
                                            Es un lugar de directorio (Sin dueño, no recibe órdenes)
                                        </label>
                                    </div>
                                    <div class="form-group mt-2">
                                        <label class="input-label" for="directory_description">Descripción para turismo (Opcional)</label>
                                        <textarea name="directory_description" id="directory_description" class="form-control" rows="3" placeholder="Descripción breve del lugar...">{{ $store->directory_description }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="latitude">{{ 'Latitud' }}"""
                                        
    content = content.replace(marker, injection)
    
    with open(file_path, "w") as f:
        f.write(content)

patch_edit_view("resources/views/admin-views/vendor/edit.blade.php")
print("Updated views")
