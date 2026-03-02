<?php
// database/seeders/FakerSeeder.php

namespace Database\Seeders;

use App\Models\Usuario\Usuario;
use App\Models\Cliente\Cliente;
use App\Models\Contratista\Contratista;
use App\Models\Contratista\Colaborador;
use App\Models\Cliente\Evaluador;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Evaluacion\Pregunta;
use App\Models\Evaluacion\Alternativa;
use App\Models\Aplicacion\Aplicacion;
use App\Models\Documento\Disco;
use App\Models\Documento\Documento;
use App\Models\Recurso\TipoRecurso;
use App\Models\Recurso\Recurso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FakerSeeder extends Seeder
{
    private $faker;
    private $usuarios = [];
    private $roles = [];
    private $usuariosPorRol = []; // Almacenar usuarios por ID de rol
    private $clientes = [];
    private $contratistas = [];
    private $evaluaciones = [];
    private $preguntas = [];
    private $discos = [];
    private $documentos = [];
    private $tipoRecursos = [];
    private $recursos = [];
    private $evaluadores = [];
    private $colaboradores = [];
    
    // Constantes para IDs de roles (AJUSTAR SEGÚN TUS DATOS REALES)
    private const ROL_ADMIN_ID = 1;
    private const ROL_CLIENTE_ID = 2;
    private const ROL_CONTRATISTA_ID = 3;
    private const ROL_EVALUADOR_ID = 4;
    private const ROL_COLABORADOR_ID = 5;
    
    private const CANTIDAD_USUARIOS = 20;
    private const CANTIDAD_CLIENTES = 6;
    private const CANTIDAD_CONTRATISTAS = 8;
    private const CANTIDAD_EVALUACIONES = 8;
    private const CANTIDAD_DISCOS = 4;
    private const CANTIDAD_DOCUMENTOS = 15;
    private const CANTIDAD_RECURSOS = 15;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->faker = \Faker\Factory::create('es_ES');
        
        // Desactivar restricciones de llaves foráneas temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Poblar tablas en orden
        $this->command->info('Creando usuarios...');
        $this->seedUsuarios();
        
        $this->command->info('Cargando roles...');
        $this->cargarRoles();
        
        $this->command->info('Asignando roles a usuarios...');
        $this->seedUsuarioRol();
        
        $this->command->info('Creando clientes...');
        $this->seedClientes();
        
        $this->command->info('Creando contratistas...');
        $this->seedContratistas();
        
        $this->command->info('Creando relaciones Cliente-Contratista...');
        $this->seedClienteContratista();
        
        $this->command->info('Creando relaciones Cliente-Usuario...');
        $this->seedClienteUsuario();
        
        $this->command->info('Creando relaciones Contratista-Usuario...');
        $this->seedContratistaUsuario();
        
        $this->command->info('Creando evaluadores (usuarios con rol evaluador)...');
        $this->seedEvaluadores();
        
        $this->command->info('Creando colaboradores (usuarios con rol colaborador)...');
        $this->seedColaboradores();
        
        $this->command->info('Creando evaluaciones...');
        $this->seedEvaluaciones();
        
        $this->command->info('Creando preguntas...');
        $this->seedPreguntas();
        
        $this->command->info('Creando alternativas (una por pregunta)...');
        $this->seedAlternativas();
        
        $this->command->info('Creando relaciones Cliente-Evaluacion...');
        $this->seedClienteEvaluacion();
        
        $this->command->info('Creando relaciones Evaluador-Evaluacion...');
        $this->seedEvaluadorEvaluacion();
        
        $this->command->info('Creando relaciones Colaborador-Evaluacion...');
        $this->seedColaboradorEvaluacion();
        
        $this->command->info('Creando relaciones Contratista-Evaluacion...');
        $this->seedContratistaEvaluacion();
        
        $this->command->info('Creando aplicaciones...');
        $this->seedAplicaciones();
        
        $this->command->info('Creando discos...');
        $this->seedDiscos();
        
        $this->command->info('Creando documentos...');
        $this->seedDocumentos();
        
        $this->command->info('Creando tipos de recurso...');
        $this->seedTipoRecursos();
        
        $this->command->info('Creando recursos...');
        $this->seedRecursos();
        
        $this->command->info('Creando relaciones Recurso-Usuario...');
        $this->seedRecursoUsuario();
        
        // Reactivar restricciones
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Liberar memoria
        $this->cleanupMemory();
        
        $this->command->info('Base de datos poblada con Faker exitosamente!');
    }

    /**
     * Limpiar memoria
     */
    private function cleanupMemory()
    {
        $this->usuarios = null;
        $this->roles = null;
        $this->usuariosPorRol = null;
        $this->clientes = null;
        $this->contratistas = null;
        $this->evaluaciones = null;
        $this->preguntas = null;
        $this->discos = null;
        $this->documentos = null;
        $this->tipoRecursos = null;
        $this->recursos = null;
        $this->evaluadores = null;
        $this->colaboradores = null;
        gc_collect_cycles();
    }

    /**
     * Cargar roles existentes y organizar usuarios por rol
     */
    private function cargarRoles()
    {
        // Obtener todos los roles existentes
        $rolesExistentes = DB::table('Rol')->get();
        
        if ($rolesExistentes->isEmpty()) {
            $this->command->error('No se encontraron roles en la tabla Rol. Verifica que los roles hayan sido creados previamente.');
            return;
        }
        
        // Guardar IDs de roles
        foreach ($rolesExistentes as $rol) {
            $this->roles[$rol->nombre] = $rol->id;
        }
        
        $this->command->info('Roles cargados: ' . implode(', ', array_keys($this->roles)));
    }

    /**
     * Obtener usuarios que tienen un rol específico
     */
    private function getUsuariosByRol($rolId)
    {
        $key = 'rol_' . $rolId;
        
        if (!isset($this->usuariosPorRol[$key])) {
            $this->usuariosPorRol[$key] = DB::table('Usuario_Rol')
                ->where('Rol_id', $rolId)
                ->where('bloqueado', 0)
                ->pluck('Usuario_id')
                ->toArray();
        }
        
        return $this->usuariosPorRol[$key];
    }

    /**
     * Obtener usuarios que NO tienen un rol específico
     */
    private function getUsuariosWithoutRol($rolId)
    {
        $usuariosConRol = $this->getUsuariosByRol($rolId);
        return array_diff($this->usuarios, $usuariosConRol);
    }

    /**
     * Seed de usuarios
     */
    private function seedUsuarios()
    {
        // Usuario administrador por defecto
        $admin = Usuario::create([
            'user' => 'admin',
            'password' => 'admin123',
            'rut' => '11111111-1',
            'nombre' => 'Administrador del Sistema',
            'email' => 'admin@sistema.com',
            'bloqueado' => 0,
            'vigencia' => null,
        ]);
        $this->usuarios[] = $admin->id;

        // Usuarios de prueba específicos
        $usuariosPrueba = [
            [
                'user' => 'jperez',
                'nombre' => 'Juan Pérez',
                'email' => 'juan.perez@example.com',
                'rut' => '12345678-9',
            ],
            [
                'user' => 'mgonzalez',
                'nombre' => 'María González',
                'email' => 'maria.gonzalez@example.com',
                'rut' => '98765432-1',
            ],
            [
                'user' => 'crodriguez',
                'nombre' => 'Carlos Rodríguez',
                'email' => 'carlos.rodriguez@example.com',
                'rut' => '56789012-3',
            ],
            [
                'user' => 'alopez',
                'nombre' => 'Ana López',
                'email' => 'ana.lopez@example.com',
                'rut' => '45678901-2',
            ],
        ];

        foreach ($usuariosPrueba as $usuarioData) {
            $usuario = Usuario::create([
                'user' => $usuarioData['user'],
                'password' => 'password123',
                'rut' => $usuarioData['rut'],
                'nombre' => $usuarioData['nombre'],
                'email' => $usuarioData['email'],
                'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'bloqueado' => 0,
                'vigencia' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 year'),
            ]);
            $this->usuarios[] = $usuario->id;
        }

        // Usuarios aleatorios adicionales
        $cantidadRestante = self::CANTIDAD_USUARIOS - count($this->usuarios);
        for ($i = 0; $i < $cantidadRestante; $i++) {
            $usuario = Usuario::create([
                'user' => $this->faker->unique()->userName(),
                'password' => 'password123',
                'rut' => $this->faker->unique()->numerify('########-#'),
                'nombre' => $this->faker->name(),
                'email' => $this->faker->unique()->safeEmail(),
                'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
                'bloqueado' => $this->faker->boolean(10) ? 1 : 0,
                'vigencia' => $this->faker->optional(0.2)->dateTimeBetween('now', '+1 year'),
            ]);
            $this->usuarios[] = $usuario->id;
        }
    }

    /**
     * Seed de usuario_rol con roles específicos para cada tipo de usuario
     */
    private function seedUsuarioRol()
    {
        // Verificar que tenemos roles definidos
        if (empty($this->roles)) {
            $this->command->error('No hay roles definidos. No se pueden asignar roles a usuarios.');
            return;
        }

        // Obtener IDs de roles por nombre
        $rolAdminId = $this->roles['SisAdmin'] ?? null;
        $rolClienteId = $this->roles['Cliente'] ?? null;
        $rolContratistaId = $this->roles['Contratista'] ?? null;
        $rolEvaluadorId = $this->roles['Evaluador'] ?? null;
        $rolColaboradorId = $this->roles['Colaborador'] ?? null;

        if (!$rolAdminId || !$rolClienteId || !$rolContratistaId || !$rolEvaluadorId || !$rolColaboradorId) {
            $this->command->error('Faltan roles requeridos. Verifica que existan: Administrador, Cliente, Contratista, Evaluador, Colaborador');
            return;
        }

        // Asignar rol de Administrador al primer usuario
        $admin = Usuario::find($this->usuarios[0]);
        $admin->roles()->attach($rolAdminId, ['bloqueado' => 0]);

        // Distribuir roles entre los usuarios restantes
        $usuariosRestantes = array_slice($this->usuarios, 1);
        
        // Definir cuántos usuarios queremos por cada rol
        $distribucionRoles = [
            $rolClienteId => 4,
            $rolContratistaId => 4,
            $rolEvaluadorId => 3,
            $rolColaboradorId => 3,
        ];

        // Mezclar usuarios para asignación aleatoria
        shuffle($usuariosRestantes);
        $indice = 0;

        // Asignar roles principales
        foreach ($distribucionRoles as $rolId => $cantidad) {
            for ($i = 0; $i < $cantidad && $indice < count($usuariosRestantes); $i++) {
                $usuario = Usuario::find($usuariosRestantes[$indice]);
                $usuario->roles()->attach($rolId, [
                    'fecha' => $this->faker->dateTimeBetween('-6 months', 'now'),
                    'bloqueado' => 0,
                ]);
                $indice++;
            }
        }

        // Asignar roles secundarios aleatorios a usuarios que ya tienen un rol principal
        for ($i = 0; $i < $indice; $i++) {
            $usuario = Usuario::find($usuariosRestantes[$i]);
            
            // 30% de probabilidad de tener un segundo rol
            if ($this->faker->boolean(30)) {
                $rolesDisponibles = [$rolClienteId, $rolContratistaId, $rolEvaluadorId, $rolColaboradorId];
                $rolesActuales = $usuario->roles->pluck('id')->toArray();
                
                // Elegir un rol que no tenga actualmente
                $rolesPosibles = array_diff($rolesDisponibles, $rolesActuales);
                
                if (!empty($rolesPosibles)) {
                    $rolSecundario = $rolesPosibles[array_rand($rolesPosibles)];
                    $usuario->roles()->attach($rolSecundario, [
                        'fecha' => $this->faker->dateTimeBetween('-3 months', 'now'),
                        'bloqueado' => 0,
                    ]);
                }
            }
        }
    }

    private function seedClientes()
    {
        $nombresClientes = [
            'Constructora ABC', 'Minera del Sur', 'Ingeniería XYZ', 
            'Servicios Industriales', 'Empresa de Energía', 'Proyectos Ltda.'
        ];

        for ($i = 0; $i < self::CANTIDAD_CLIENTES; $i++) {
            $cliente = Cliente::create([
                'nombre' => $nombresClientes[$i] ?? $this->faker->company(),
                'rut' => $this->faker->unique()->numerify('########-#'),
                'fecha' => $this->faker->dateTimeBetween('-2 years', 'now'),
            ]);
            $this->clientes[] = $cliente->id;
        }
    }

    private function seedContratistas()
    {
        $nombresContratistas = [
            'Contratista General', 'Obras y Servicios', 'Construcciones del Norte',
            'Mantenimiento Industrial', 'Instalaciones Eléctricas', 'Obras Civiles',
            'Servicios Generales', 'Construcciones Modernas'
        ];

        for ($i = 0; $i < self::CANTIDAD_CONTRATISTAS; $i++) {
            $contratista = Contratista::create([
                'nombre' => $nombresContratistas[$i] ?? $this->faker->company(),
                'rut' => $this->faker->unique()->numerify('########-#'),
                'fecha' => $this->faker->dateTimeBetween('-2 years', 'now'),
            ]);
            $this->contratistas[] = $contratista->id;
        }
    }

    private function seedClienteContratista($cantidad = 15)
    {
        $rolClienteId = $this->roles['Cliente'] ?? null;
        $rolContratistaId = $this->roles['Contratista'] ?? null;
        
        if (!$rolClienteId || !$rolContratistaId) {
            $this->command->warn('Roles Cliente o Contratista no encontrados. Saltando seedClienteContratista');
            return;
        }

        $usuariosCliente = $this->getUsuariosByRol($rolClienteId);
        $pares = [];
        
        for ($i = 0; $i < $cantidad; $i++) {
            $clienteId = $this->clientes[array_rand($this->clientes)];
            $contratistaId = $this->contratistas[array_rand($this->contratistas)];
            
            // Usar un usuario con rol Cliente (o cualquier usuario si no hay)
            $usuarioId = !empty($usuariosCliente) 
                ? $usuariosCliente[array_rand($usuariosCliente)] 
                : $this->usuarios[array_rand($this->usuarios)];
                
            $key = $clienteId . '-' . $contratistaId;
            
            if (!in_array($key, $pares)) {
                DB::table('Cliente_Contratista')->insert([
                    'Cliente_id' => $clienteId,
                    'Contratista_id' => $contratistaId,
                    'Usuario_id' => $usuarioId,
                    'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
                ]);
                $pares[] = $key;
            }
        }
    }

    private function seedClienteUsuario($cantidad = 20)
    {
        $rolClienteId = $this->roles['Cliente'] ?? null;
        
        if (!$rolClienteId) {
            $this->command->warn('Rol Cliente no encontrado. Saltando seedClienteUsuario');
            return;
        }

        $usuariosCliente = $this->getUsuariosByRol($rolClienteId);
        
        if (empty($usuariosCliente)) {
            $this->command->warn('No hay usuarios con rol Cliente. Saltando seedClienteUsuario');
            return;
        }

        $pares = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $clienteId = $this->clientes[array_rand($this->clientes)];
            $usuarioId = $usuariosCliente[array_rand($usuariosCliente)];
            $key = $clienteId . '-' . $usuarioId;
            
            if (!in_array($key, $pares)) {
                DB::table('Cliente_Usuario')->insert([
                    'Cliente_id' => $clienteId,
                    'Usuario_id' => $usuarioId,
                    'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
                ]);
                $pares[] = $key;
            }
        }
    }

    private function seedContratistaUsuario($cantidad = 20)
    {
        $rolContratistaId = $this->roles['Contratista'] ?? null;
        
        if (!$rolContratistaId) {
            $this->command->warn('Rol Contratista no encontrado. Saltando seedContratistaUsuario');
            return;
        }

        $usuariosContratista = $this->getUsuariosByRol($rolContratistaId);
        
        if (empty($usuariosContratista)) {
            $this->command->warn('No hay usuarios con rol Contratista. Saltando seedContratistaUsuario');
            return;
        }

        $pares = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $contratistaId = $this->contratistas[array_rand($this->contratistas)];
            $usuarioId = $usuariosContratista[array_rand($usuariosContratista)];
            $key = $contratistaId . '-' . $usuarioId;
            
            if (!in_array($key, $pares)) {
                DB::table('Contratista_Usuario')->insert([
                    'Contratista_id' => $contratistaId,
                    'Usuario_id' => $usuarioId,
                    'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
                ]);
                $pares[] = $key;
            }
        }
    }

    private function seedEvaluadores($cantidad = 8)
    {
        $rolEvaluadorId = $this->roles['Evaluador'] ?? null;
        
        if (!$rolEvaluadorId) {
            $this->command->warn('Rol Evaluador no encontrado. Saltando seedEvaluadores');
            return;
        }

        $usuariosEvaluador = $this->getUsuariosByRol($rolEvaluadorId);
        
        if (empty($usuariosEvaluador)) {
            $this->command->warn('No hay usuarios con rol Evaluador. Saltando seedEvaluadores');
            return;
        }

        $pares = [];
        for ($i = 0; $i < min($cantidad, count($usuariosEvaluador) * 2); $i++) {
            $clienteId = $this->clientes[array_rand($this->clientes)];
            $usuarioId = $usuariosEvaluador[array_rand($usuariosEvaluador)];
            $key = $clienteId . '-' . $usuarioId;
            
            if (!in_array($key, $pares)) {
                // Verificar que el usuario no sea ya evaluador de este cliente
                $existe = Evaluador::where('Cliente_id', $clienteId)
                    ->where('Usuario_id', $usuarioId)
                    ->exists();
                
                if (!$existe) {
                    $evaluador = Evaluador::create([
                        'Cliente_id' => $clienteId,
                        'Usuario_id' => $usuarioId,
                        'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
                    ]);
                    $this->evaluadores[] = $evaluador->id;
                    $pares[] = $key;
                }
            }
        }
    }

    private function seedColaboradores($cantidad = 10)
    {
        $rolColaboradorId = $this->roles['Colaborador'] ?? null;
        
        if (!$rolColaboradorId) {
            $this->command->warn('Rol Colaborador no encontrado. Saltando seedColaboradores');
            return;
        }

        $usuariosColaborador = $this->getUsuariosByRol($rolColaboradorId);
        
        if (empty($usuariosColaborador)) {
            $this->command->warn('No hay usuarios con rol Colaborador. Saltando seedColaboradores');
            return;
        }

        $pares = [];
        for ($i = 0; $i < min($cantidad, count($usuariosColaborador) * 2); $i++) {
            $contratistaId = $this->contratistas[array_rand($this->contratistas)];
            $usuarioId = $usuariosColaborador[array_rand($usuariosColaborador)];
            $key = $contratistaId . '-' . $usuarioId;
            
            if (!in_array($key, $pares)) {
                // Verificar que el usuario no sea ya colaborador de este contratista
                $existe = Colaborador::where('Contratista_id', $contratistaId)
                    ->where('Usuario_id', $usuarioId)
                    ->exists();
                
                if (!$existe) {
                    $colaborador = Colaborador::create([
                        'Contratista_id' => $contratistaId,
                        'Usuario_id' => $usuarioId,
                        'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
                    ]);
                    $this->colaboradores[] = $colaborador->id;
                    $pares[] = $key;
                }
            }
        }
    }

    private function seedEvaluaciones()
    {
        $tiposEvaluacion = [
            'Seguridad Industrial', 'Calidad de Obra', 'Medio Ambiente', 
            'Desempeño', 'Riesgos Laborales', 'Cumplimiento Normativo',
            'Mantenimiento', 'Gestión de Residuos'
        ];

        for ($i = 0; $i < self::CANTIDAD_EVALUACIONES; $i++) {
            $evaluacion = Evaluacion::create([
                'nombre' => 'Evaluación de ' . $tiposEvaluacion[$i % count($tiposEvaluacion)] . ' ' . (2020 + $i),
                'descripcion' => $this->faker->optional(0.7)->sentence(10),
                'sid' => Str::random(8),
                'byEvaluador' => $this->faker->boolean(30) ? 1 : 0,
                'permanent' => $this->faker->boolean(70) ? 1 : 0,
                'fecha' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ]);
            $this->evaluaciones[] = $evaluacion->id;
        }
    }

    private function seedPreguntas()
    {
        $textosPreguntas = [
            '¿El personal cuenta con todos los elementos de protección personal requeridos?',
            '¿Se han realizado las charlas de seguridad correspondientes?',
            '¿Existe señalización adecuada en las áreas de trabajo?',
            '¿Los materiales utilizados cumplen con las especificaciones técnicas?',
            '¿Se realizaron los ensayos de calidad requeridos?',
            '¿Se está realizando correctamente la gestión de residuos?',
            '¿Se están cumpliendo los procedimientos de control de emisiones?',
            '¿El trabajador cumple con los objetivos asignados?',
            '¿Demuestra trabajo en equipo y colaboración?',
            '¿Se mantiene el orden y limpieza en el área de trabajo?',
        ];

        $areas = ['SEG', 'CAL', 'AMB', 'DES', 'MANT', 'RIES'];
        
        $codigosUsados = [];
        
        foreach ($this->evaluaciones as $evaluacionId) {
            $numPreguntas = rand(3, 5);
            
            for ($j = 0; $j < $numPreguntas; $j++) {
                $area = $areas[array_rand($areas)];
                
                // Generar código único
                do {
                    $codigo = $area . '-' . str_pad(rand(1, 50), 3, '0', STR_PAD_LEFT);
                } while (in_array($codigo, $codigosUsados));
                
                $codigosUsados[] = $codigo;
                
                $pregunta = Pregunta::create([
                    'Evaluacion_id' => $evaluacionId,
                    'texto' => $textosPreguntas[array_rand($textosPreguntas)],
                    'codigo' => $codigo,
                    'fecha' => $this->faker->dateTimeBetween('-6 months', 'now'),
                ]);
                $this->preguntas[] = $pregunta->id;
            }
        }
    }

    private function seedAlternativas()
    {
        $textosAlternativas = [
            'Sí, completamente',
            'Parcialmente',
            'No cumple',
            'Sí',
            'No',
            'No aplica',
            'Excelente',
            'Bueno',
            'Regular',
            'Deficiente',
            'Siempre',
            'Frecuentemente',
            'A veces',
            'Nunca',
            'Cumple',
            'No cumple',
        ];

        foreach ($this->preguntas as $preguntaId) {
            $textoAlternativa = $textosAlternativas[array_rand($textosAlternativas)];
            $codigoAlternativa = strtoupper(Str::random(rand(2, 4)));
            
            Alternativa::create([
                'Pregunta_id' => $preguntaId,
                'texto' => $textoAlternativa,
                'codigo' => $codigoAlternativa,
                'fecha' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ]);
        }
    }

    private function seedClienteEvaluacion($cantidad = 20)
    {
        $pares = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $clienteId = $this->clientes[array_rand($this->clientes)];
            $evaluacionId = $this->evaluaciones[array_rand($this->evaluaciones)];
            $usuarioId = $this->usuarios[array_rand($this->usuarios)];
            $key = $clienteId . '-' . $evaluacionId;
            
            if (!in_array($key, $pares)) {
                DB::table('Cliente_Evaluacion')->insert([
                    'Cliente_id' => $clienteId,
                    'Evaluacion_id' => $evaluacionId,
                    'Usuario_id' => $usuarioId,
                    'fecha' => $this->faker->dateTimeBetween('-3 months', 'now'),
                ]);
                $pares[] = $key;
            }
        }
    }

    private function seedEvaluadorEvaluacion($cantidad = 20)
    {
        if (empty($this->evaluadores)) return;
        
        for ($i = 0; $i < $cantidad; $i++) {
            DB::table('Evaluador_Evaluacion')->insert([
                'Evaluador_id' => $this->evaluadores[array_rand($this->evaluadores)],
                'Evaluacion_id' => $this->evaluaciones[array_rand($this->evaluaciones)],
                'Usuario_id' => $this->usuarios[array_rand($this->usuarios)],
                'fecha' => $this->faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }

    private function seedColaboradorEvaluacion($cantidad = 25)
    {
        if (empty($this->colaboradores)) return;
        
        for ($i = 0; $i < $cantidad; $i++) {
            DB::table('Colaborador_Evaluacion')->insert([
                'Colaborador_id' => $this->colaboradores[array_rand($this->colaboradores)],
                'Evaluacion_id' => $this->evaluaciones[array_rand($this->evaluaciones)],
                'token' => Str::random(32),
                'fecha' => $this->faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }

    private function seedContratistaEvaluacion($cantidad = 15)
    {
        $pares = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $contratistaId = $this->contratistas[array_rand($this->contratistas)];
            $evaluacionId = $this->evaluaciones[array_rand($this->evaluaciones)];
            $usuarioId = $this->usuarios[array_rand($this->usuarios)];
            $key = $contratistaId . '-' . $evaluacionId;
            
            if (!in_array($key, $pares)) {
                DB::table('Contratista_Evaluacion')->insert([
                    'Contratista_id' => $contratistaId,
                    'Evaluacion_id' => $evaluacionId,
                    'Usuario_id' => $usuarioId,
                    'fecha' => $this->faker->dateTimeBetween('-3 months', 'now'),
                ]);
                $pares[] = $key;
            }
        }
    }

    private function seedAplicaciones($cantidad = 20)
    {
        if (empty($this->evaluadores) || empty($this->colaboradores)) return;
        
        for ($i = 0; $i < $cantidad; $i++) {
            Aplicacion::create([
                'Evaluador_id' => $this->evaluadores[array_rand($this->evaluadores)],
                'Evaluacion_id' => $this->evaluaciones[array_rand($this->evaluaciones)],
                'Colaborador_id' => $this->colaboradores[array_rand($this->colaboradores)],
                'token' => Str::random(32),
                'fecha' => $this->faker->dateTimeBetween('-2 months', 'now'),
            ]);
        }
    }

    private function seedDiscos()
    {
        $discosData = [
            [
                'nombre'      => 'recursos',
                'descripcion' => 'Almacenamiento de recursos',
            ],
            [
                'nombre'      => 'evaluaciones',
                'descripcion' => 'Documentos de evaluaciones completadas',
            ],
        ];

        foreach ($discosData as $discoData) {
            $disco = Disco::firstOrCreate(
                ['nombre' => $discoData['nombre']],
                [
                    'descripcion' => $discoData['descripcion'],
                    'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
                ]
            );
            $this->discos[] = $disco->id;
        }
        
        $this->command->info('Discos creados: ' . implode(', ', array_column($discosData, 'nombre')));
    }

    private function seedDocumentos()
    {
        $tiposDocumentos = [
            'Manual de Procedimientos', 'Normas de Seguridad', 'Protocolo de Emergencias',
            'Especificaciones Técnicas', 'Checklist de Inspección', 'Informe de Auditoría',
            'Plan de Capacitación', 'Matriz de Riesgos', 'Política de Calidad',
        ];

        for ($i = 0; $i < self::CANTIDAD_DOCUMENTOS; $i++) {
            $documento = Documento::create([
                'Disco_id' => $this->discos[array_rand($this->discos)],
                'nombre' => $tiposDocumentos[array_rand($tiposDocumentos)] . ' ' . rand(1, 20),
                'fecha' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ]);
            $this->documentos[] = $documento->id;
        }
    }

    private function seedTipoRecursos($cantidad = 5)
    {
        $tipos = [
            ['Video Tutorial', 'VID', '#FF5733'],
            ['Documento PDF', 'PDF', '#3357FF'],
            ['Imagen', 'IMG', '#33FF57'],
            ['Planilla Excel', 'XLS', '#F3FF33'],
            ['Presentación', 'PPT', '#FF33F3'],
        ];

        for ($i = 0; $i < min($cantidad, count($tipos)); $i++) {
            $tipo = $tipos[$i];
            
            $tipoRecurso = TipoRecurso::create([
                'nombre' => $tipo[0],
                'codigo' => $tipo[1],
                'color' => $tipo[2],
                'fecha' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ]);
            $this->tipoRecursos[] = $tipoRecurso->id;
        }
    }

    private function seedRecursos()
    {
        $nombresRecursos = [
            'Tutorial de Seguridad Básica', 'Manual de EPP', 'Diagrama de Procedimientos',
            'Checklist de Control', 'Video de Inducción', 'Guía Rápida de Seguridad',
            'Formulario de Inspección', 'Plan de Emergencia',
        ];

        for ($i = 0; $i < self::CANTIDAD_RECURSOS; $i++) {
            $recurso = Recurso::create([
                'TipoRecurso_id' => $this->tipoRecursos[array_rand($this->tipoRecursos)],
                'Evaluacion_id' => $this->evaluaciones[array_rand($this->evaluaciones)],
                'Documento_id' => $this->documentos[array_rand($this->documentos)],
                'Usuario_id' => $this->usuarios[array_rand($this->usuarios)],
                'nombre' => $nombresRecursos[array_rand($nombresRecursos)] . ' ' . rand(1, 10),
                'descripcion' => $this->faker->optional(0.6)->sentence(8),
                'fecha' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ]);
            $this->recursos[] = $recurso->id;
        }
    }

    private function seedRecursoUsuario($cantidad = 25)
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $recurso = Recurso::find($this->recursos[array_rand($this->recursos)]);
            $usuarioId = $this->usuarios[array_rand($this->usuarios)];
            
            // Verificar que no exista ya la relación
            if (!$recurso->usuarios()->where('Usuario_id', $usuarioId)->exists()) {
                $recurso->usuarios()->attach($usuarioId, [
                    'fecha' => $this->faker->dateTimeBetween('-3 months', 'now'),
                    'bloqueado' => 0,
                ]);
            }
        }
    }
}