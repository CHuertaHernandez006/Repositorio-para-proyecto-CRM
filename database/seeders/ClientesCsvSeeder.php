<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientesCsvSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            ['Mauricio', 'Salinas', 'mauricio.salinas@grupohorizonte.mx', '5584731925', 'Grupo Horizonte Capital', 'grupohorizonte.mx', 'CDMX', 'Mexico City'],
            ['Patricia', 'Navarro', 'patricia.navarro@nexoventures.mx', '5547192836', 'Nexo Ventures', 'nexoventures.mx', 'Estado de Mexico', 'Naucalpan'],
            ['Ricardo', 'Campos', 'ricardo.campos@vertexlabs.mx', '5519284736', 'Vertex Labs', 'vertexlabs.mx', 'CDMX', 'Cuauhtemoc'],
            ['Andrea', 'Herrera', 'andrea.herrera@alturatech.mx', '5582147896', 'Altura Technologies', 'alturatech.mx', 'CDMX', 'Mexico City'],
            ['Fernando', 'Rivas', 'fernando.rivas@deltaanalytics.mx', '5578192345', 'Delta Analytics', 'deltaanalytics.mx', 'Nuevo Leon', 'Monterrey'],
            ['Sofia', 'Molina', 'sofia.molina@impulsocapital.mx', '5554127896', 'Impulso Capital', 'impulsocapital.mx', 'Sonora', 'Hermosillo'],
            ['Daniel', 'Fuentes', 'daniel.fuentes@novadigital.mx', '5512356789', 'Nova Digital', 'novadigital.mx', 'Nuevo Leon', 'Monterrey'],
            ['Valeria', 'Castro', 'valeria.castro@integra360.mx', '5534567891', 'Integra 360', 'integra360.mx', 'Jalisco', 'Guadalajara'],
            ['Hector', 'Ortega', 'hector.ortega@orionconsulting.mx', '5576543210', 'Orion Consulting', 'orionconsulting.mx', 'CDMX', 'Mexico City'],
            ['Gabriela', 'Vega', 'gabriela.vega@astracorp.mx', '5589123456', 'Astra Corporate', 'astracorp.mx', 'Queretaro', 'Queretaro'],

            ['Luis', 'Santos', 'luis.santos@grupoevolucion.mx', '5511456789', 'Grupo Evolucion', 'grupoevolucion.mx', 'Nuevo Leon', 'Monterrey'],
            ['Ana', 'Cortes', 'ana.cortes@vertexcapital.mx', '5522897412', 'Vertex Capital', 'vertexcapital.mx', 'CDMX', 'Mexico City'],
            ['Carlos', 'Pineda', 'carlos.pineda@impulsolabs.mx', '5587741234', 'Impulso Labs', 'impulsolabs.mx', 'Puebla', 'Puebla'],
            ['Mariana', 'Ramirez', 'mariana.ramirez@gruponexo.mx', '5555567890', 'Grupo Nexo', 'gruponexo.mx', 'Baja California', 'Tijuana'],
            ['Roberto', 'Lopez', 'roberto.lopez@orionventures.mx', '5522223333', 'Orion Ventures', 'orionventures.mx', 'Nuevo Leon', 'San Pedro Garza Garcia'],
            ['Claudia', 'Fuentes', 'claudia.fuentes@integrapharma.mx', '5571112222', 'Integra Pharma', 'integrapharma.mx', 'Jalisco', 'Guadalajara'],
            ['Jorge', 'Vargas', 'jorge.vargas@novaretail.mx', '5512349999', 'Nova Retail', 'novaretail.mx', 'Guanajuato', 'Leon'],
            ['Laura', 'Campos', 'laura.campos@deltafintech.mx', '5578901234', 'Delta Fintech', 'deltafintech.mx', 'CDMX', 'Mexico City'],
            ['Victor', 'Ramirez', 'victor.ramirez@vertixlogistics.mx', '5556781234', 'Vertix Logistics', 'vertixlogistics.mx', 'Nuevo Leon', 'Monterrey'],
            ['Monica', 'Herrera', 'monica.herrera@alturahealth.mx', '5519872345', 'Altura Health', 'alturahealth.mx', 'Jalisco', 'Guadalajara'],

            ['Rafael', 'Navarro', 'rafael.navarro@bluecore.mx', '5588881122', 'Blue Core', 'bluecore.mx', 'CDMX', 'Mexico City'],
            ['Carmen', 'Ortega', 'carmen.ortega@zenithgroup.mx', '5577772211', 'Zenith Group', 'zenithgroup.mx', 'Nuevo Leon', 'Monterrey'],
            ['Diego', 'Mendoza', 'diego.mendoza@orbita.mx', '5511112233', 'Orbita Technologies', 'orbita.mx', 'Yucatan', 'Merida'],
            ['Teresa', 'Paredes', 'teresa.paredes@horizon.mx', '5544445678', 'Horizon Legal', 'horizon.mx', 'CDMX', 'CDMX'],
            ['Raul', 'Silva', 'raul.silva@innova.mx', '5566667890', 'Innova Labs', 'innova.mx', 'Puebla', 'Puebla'],
            ['Susana', 'Martinez', 'susana.martinez@vortex.mx', '5533321122', 'Vortex Media', 'vortex.mx', 'Queretaro', 'Queretaro'],
            ['Alberto', 'Garcia', 'alberto.garcia@tecforce.mx', '5545678901', 'TecForce', 'tecforce.mx', 'Nuevo Leon', 'Monterrey'],
            ['Paola', 'Guzman', 'paola.guzman@capitaledge.mx', '5523459876', 'Capital Edge', 'capitaledge.mx', 'CDMX', 'Mexico City'],
            ['Martin', 'Castro', 'martin.castro@logisync.mx', '5511239988', 'LogiSync', 'logisync.mx', 'Baja California', 'Tijuana'],
            ['Elena', 'Diaz', 'elena.diaz@nextbridge.mx', '5578002233', 'NextBridge', 'nextbridge.mx', 'Jalisco', 'Guadalajara'],

            ['Gerardo', 'Salazar', 'gerardo.salazar@quantum.mx', '5519988776', 'Quantum Systems', 'quantum.mx', 'Guanajuato', 'Leon'],
            ['Lucia', 'Reyes', 'lucia.reyes@openmind.mx', '5567665544', 'OpenMind', 'openmind.mx', 'Nuevo Leon', 'Monterrey'],
            ['Mario', 'Lozano', 'mario.lozano@linkpro.mx', '5534561234', 'LinkPro', 'linkpro.mx', 'Queretaro', 'Queretaro'],
            ['Daniela', 'Rocha', 'daniela.rocha@urbania.mx', '5588123456', 'Urbania Group', 'urbania.mx', 'CDMX', 'Mexico City'],
            ['Sergio', 'Cruz', 'sergio.cruz@adaptive.mx', '5522123434', 'Adaptive Ventures', 'adaptive.mx', 'Puebla', 'Puebla'],
            ['Patricia', 'Velasco', 'patricia.velasco@evolve.mx', '5571234567', 'Evolve Partners', 'evolve.mx', 'Nuevo Leon', 'Monterrey'],
            ['Rene', 'Mejia', 'rene.mejia@trion.mx', '5544332211', 'Trion Capital', 'trion.mx', 'CDMX', 'CDMX'],
            ['Karla', 'Luna', 'karla.luna@bridgecorp.mx', '5566554433', 'Bridge Corp', 'bridgecorp.mx', 'Jalisco', 'Guadalajara'],
            ['Oscar', 'Ponce', 'oscar.ponce@zenware.mx', '5599112233', 'Zenware', 'zenware.mx', 'Baja California', 'Tijuana'],
            ['Irene', 'Morales', 'irene.morales@globalone.mx', '5533445566', 'Global One', 'globalone.mx', 'CDMX', 'Mexico City'],

            ['Adrian', 'Flores', 'adrian.flores@novalink.mx', '5588012345', 'NovaLink', 'novalink.mx', 'Nuevo Leon', 'Monterrey'],
            ['Beatriz', 'Soto', 'beatriz.soto@intelliga.mx', '5576547890', 'Intelliga', 'intelliga.mx', 'Queretaro', 'Queretaro'],
            ['Guillermo', 'Nuñez', 'guillermo.nunez@orionsoft.mx', '5567893210', 'Orion Software', 'orionsoft.mx', 'CDMX', 'CDMX'],
            ['Natalia', 'Suarez', 'natalia.suarez@axion.mx', '5514567899', 'Axion Group', 'axion.mx', 'Yucatan', 'Merida'],
            ['Francisco', 'Vega', 'francisco.vega@metrix.mx', '5587123450', 'Metrix Solutions', 'metrix.mx', 'Puebla', 'Puebla'],
            ['Veronica', 'Rios', 'veronica.rios@clearview.mx', '5512121212', 'ClearView', 'clearview.mx', 'Nuevo Leon', 'Monterrey'],
            ['Enrique', 'Torres', 'enrique.torres@primecore.mx', '5577332211', 'PrimeCore', 'primecore.mx', 'CDMX', 'Mexico City'],
            ['Silvia', 'Aranda', 'silvia.aranda@smartbridge.mx', '5555667788', 'SmartBridge', 'smartbridge.mx', 'Guanajuato', 'Leon'],
            ['Javier', 'Cervantes', 'javier.cervantes@alphatech.mx', '5544778899', 'AlphaTech', 'alphatech.mx', 'Jalisco', 'Guadalajara'],
            ['Marisol', 'Rendon', 'marisol.rendon@futurelabs.mx', '5533112244', 'Future Labs', 'futurelabs.mx', 'CDMX', 'CDMX'],
        ];

        foreach ($clientes as $cliente) {

            $nombre = $cliente[0];
            $apellido = $cliente[1];
            $correo = $cliente[2];
            $telefono = $cliente[3];
            $empresaNombre = $cliente[4];
            $slug = $cliente[5];
            $estado = $cliente[6];
            $ciudad = $cliente[7];

            /*
             * Crear empresa si todavía no existe.
             */
            $empresaId = DB::table('empresas')
                ->where('slug', $slug)
                ->value('id_empresa');

            if (!$empresaId) {
                $empresaId = DB::table('empresas')->insertGetId([
                    'nombre' => $empresaNombre,
                    'slug' => $slug,
                    'estado' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            /*
             * Crear cliente si todavía no existe.
             */
            $clienteExiste = DB::table('clientes')
                ->where('correo', $correo)
                ->exists();

            if (!$clienteExiste) {
                DB::table('clientes')->insert([
                    'id_empresa' => $empresaId,
                    'id_tipo_cliente' => 1,
                    'id_estado_lead' => 1,
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellido,
                    'apellido_materno' => null,
                    'empresa' => $empresaNombre,
                    'correo' => $correo,
                    'telefono_principal' => $telefono,
                    'telefono_secundario' => null,
                    'pais' => 'Mexico',
                    'estado' => $estado,
                    'ciudad' => $ciudad,
                    'fuente' => 'Importación CSV Demo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('50 clientes procesados correctamente.');
    }
}