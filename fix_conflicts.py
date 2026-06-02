import sys
import re

def fix_conflicts(filepath):
    with open(filepath, 'r') as f:
        lines = f.readlines()

    out = []
    in_stashed = False
    for line in lines:
        if line.startswith("<<<<<<< Updated upstream"):
            continue
        elif line.startswith("======="):
            in_stashed = True
            continue
        elif line.startswith(">>>>>>> Stashed changes"):
            in_stashed = False
            continue
        else:
            if not in_stashed:
                out.append(line)

    with open(filepath, 'w') as f:
        f.writelines(out)

if __name__ == "__main__":
    files = [
        "app/Http/Controllers/Admin/SaboresController.php",
        "app/Http/Controllers/Api/V1/SaboresCiudadController.php",
        "app/Models/Reservation.php",
        "resources/views/admin-views/sabores/restaurants/edit.blade.php",
        "resources/views/layouts/admin/partials/_sidebar_sabores.blade.php"
    ]
    for file in files:
        fix_conflicts(file)
        print(f"Fixed {file}")
