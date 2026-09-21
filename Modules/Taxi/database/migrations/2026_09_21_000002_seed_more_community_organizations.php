<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $orgs = [
            [
                'name' => 'Universidad del Valle de México (UVM)',
                'short_name' => 'UVM',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@uvmnet.edu', '@my.uvm.edu.mx']),
                'address' => 'Planteles CDMX y Estado de México (Toluca, Lomas Verdes, Santa Fe, etc.)',
                'latitude' => 19.2941,
                'longitude' => -99.6012,
                'logo' => 'organizations/uvm.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Universidad Autónoma del Estado de México (UAEMex)',
                'short_name' => 'UAEMex',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@uaemex.mx', '@alumno.uaemex.mx']),
                'address' => 'Ciudad Universitaria, Toluca, Estado de México',
                'latitude' => 19.2833,
                'longitude' => -99.6764,
                'logo' => 'organizations/uaemex.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Universidad Autónoma Metropolitana (UAM)',
                'short_name' => 'UAM',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@uam.mx', '@correo.uam.mx', '@cua.uam.mx', '@izt.uam.mx', '@xoc.uam.mx']),
                'address' => 'Unidades Cuajimalpa, Iztapalapa, Xochimilco, Azcapotzalco, Lerma',
                'latitude' => 19.3591,
                'longitude' => -99.2778,
                'logo' => 'organizations/uam.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Universidad Iberoamericana (Ibero)',
                'short_name' => 'Ibero Santa Fe',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@ibero.mx', '@correo.uia.mx']),
                'address' => 'Prolongación Paseo de la Reforma 880, Santa Fe, CDMX',
                'latitude' => 19.3703,
                'longitude' => -99.2635,
                'logo' => 'organizations/ibero.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Universidad Anáhuac (Campus Norte y Sur)',
                'short_name' => 'Anáhuac',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@anahuac.mx']),
                'address' => 'Av. Universidad Anáhuac 46, Huixquilucan, Edo Méx',
                'latitude' => 19.4002,
                'longitude' => -99.2639,
                'logo' => 'organizations/anahuac.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Universidad Tecnológica de México (UNITEC)',
                'short_name' => 'UNITEC',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@my.unitec.edu.mx', '@unitec.edu.mx']),
                'address' => 'Campus Toluca, Atizapán, Cuitláhuac, Sur, Ecatepec',
                'latitude' => 19.3082,
                'longitude' => -99.5985,
                'logo' => 'organizations/unitec.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Instituto Tecnológico Autónomo de México (ITAM)',
                'short_name' => 'ITAM',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@itam.mx']),
                'address' => 'Río Hondo 1, Progreso Tizapán, Álvaro Obregón, CDMX',
                'latitude' => 19.3458,
                'longitude' => -99.1996,
                'logo' => 'organizations/itam.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Universidad Tecmilenio',
                'short_name' => 'Tecmilenio',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@tecmilenio.mx']),
                'address' => 'Campus Toluca y área Metropolitana',
                'latitude' => 19.2745,
                'longitude' => -99.5892,
                'logo' => 'organizations/tecmilenio.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Universidad La Salle (ULSA)',
                'short_name' => 'La Salle',
                'type' => 'university',
                'allowed_email_domains' => json_encode(['@lasalle.mx', '@lasallistas.org.mx']),
                'address' => 'Campus Condesa y Santa Teresa, CDMX',
                'latitude' => 19.4101,
                'longitude' => -99.1824,
                'logo' => 'organizations/lasalle.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Parque Industrial Exportec I y II',
                'short_name' => 'Exportec Toluca',
                'type' => 'industrial_park',
                'allowed_email_domains' => json_encode([]),
                'address' => 'Blvd. Aeropuerto Miguel Alemán, Toluca, Edo Méx',
                'latitude' => 19.3387,
                'longitude' => -99.5631,
                'logo' => 'organizations/exportec.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Parque Industrial Lerma',
                'short_name' => 'Parque Lerma',
                'type' => 'industrial_park',
                'allowed_email_domains' => json_encode([]),
                'address' => 'Carr México-Toluca Km 52, Lerma, Edo Méx',
                'latitude' => 19.2882,
                'longitude' => -99.5123,
                'logo' => 'organizations/lerma.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Corredor Corporativo Polanco',
                'short_name' => 'Corp Polanco',
                'type' => 'corporate',
                'allowed_email_domains' => json_encode([]),
                'address' => 'Av. Miguel de Cervantes Saavedra y Campos Elíseos, CDMX',
                'latitude' => 19.4395,
                'longitude' => -99.2045,
                'logo' => 'organizations/polanco.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Corredor Corporativo Reforma / Insurgentes',
                'short_name' => 'Corp Reforma',
                'type' => 'corporate',
                'allowed_email_domains' => json_encode([]),
                'address' => 'Paseo de la Reforma e Insurgentes Sur, CDMX',
                'latitude' => 19.4285,
                'longitude' => -99.1612,
                'logo' => 'organizations/reforma.png',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($orgs as $org) {
            $exists = DB::table('taxi_community_organizations')
                ->where('name', $org['name'])
                ->orWhere('short_name', $org['short_name'])
                ->exists();

            if (!$exists) {
                DB::table('taxi_community_organizations')->insert($org);
            }
        }
    }

    public function down(): void
    {
        // No delete needed
    }
};
