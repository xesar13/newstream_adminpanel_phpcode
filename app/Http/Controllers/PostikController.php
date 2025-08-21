<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PostikController extends Controller
{
    /**
     * Muestra la vista para seleccionar integraciones de Postik.
     */
    public function showIntegrations()
    {
    $apiKey = DB::table('tbl_settings')->where('type', 'postik_api_key')->value('message');
    $endpoint = DB::table('tbl_settings')->where('type', 'postik_endpoint_url')->value('message');
        $integrations = [];
        if ($apiKey && $endpoint) {
            $url = rtrim($endpoint, '/') . 'api/public/v1/integrations';
            try {
                $response = Http::withHeaders([
                    'Authorization' => $apiKey,
                    'Accept' => 'application/json',
                ])->get($url);
                \Log::info('Postik API response', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                if ($response->successful()) {
                    $integrations = $response->json();
                } else {
                    \Log::warning('Postik API no exitosa', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error al conectar con Postik API', ['exception' => $e->getMessage()]);
            }
        }
    $active = DB::table('tbl_settings')->where('type', 'postik_integrations_active')->value('message');
        $activeIntegrations = $active ? json_decode($active, true) : [];
        return view('postik-configuration', [
            'apiKey' => $apiKey,
            'endpoint' => $endpoint,
            'integrations' => $integrations,
            'activeIntegrations' => $activeIntegrations,
        ]);
    }
    // Muestra la vista de configuración de Postik
    public function showConfiguration()
    {
    $apiKey = DB::table('tbl_settings')->where('type', 'postik_api_key')->value('message');
    $endpoint = DB::table('tbl_settings')->where('type', 'postik_endpoint_url')->value('message');
        // Obtener integraciones desde la API de Postik
        $integrations = [];
        if ($apiKey && $endpoint) {
            $url = rtrim($endpoint, '/') . 'api/public/v1/integrations';
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => $apiKey,
                    'Accept' => 'application/json',
                ])->get($url);
                if ($response->successful()) {
                    $integrations = $response->json();
                }
            } catch (\Exception $e) {
                // Silenciar error de conexión
            }
        }
        // Integraciones activas
    $active = DB::table('tbl_settings')->where('type', 'postik_integrations_active')->value('message');
        $activeIntegrations = $active ? json_decode($active, true) : [];
        return view('postik-configuration', [
            'apiKey' => $apiKey,
            'endpoint' => $endpoint,
            'integrations' => $integrations,
            'activeIntegrations' => $activeIntegrations,
        ]);
    }

    // Guarda la configuración de Postik (API Key)
    public function saveConfiguration(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'endpoint_url' => 'required|url',
        ]);
        DB::table('tbl_settings')->updateOrInsert(
            ['type' => 'postik_api_key'],
            ['message' => $request->api_key]
        );
        DB::table('tbl_settings')->updateOrInsert(
            ['type' => 'postik_endpoint_url'],
            ['message' => $request->endpoint_url]
        );
        Session::flash('success', 'Configuración de Postik guardada correctamente.');
       return response()->json(['success' => true, 'message' => 'Configuracion actualizada.']);
    }

    // Publica contenido en redes sociales usando la API de Postik
    public function publishToSocial(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'platform' => 'required|string', // Ej: facebook, twitter, etc
        ]);
    $apiKey = DB::table('tbl_settings')->where('type', 'postik_api_key')->value('message');
    $endpoint = DB::table('tbl_settings')->where('type', 'postik_endpoint_url')->value('message');
        if (!$apiKey || !$endpoint) {
            return response()->json(['error' => true, 'message' => 'API Key o Endpoint de Postik no configurados.'], 400);
        }
        $url = rtrim($endpoint, '/') . 'api/public/v1/posts';
        $response = Http::withHeaders([
            'Authorization' =>  $apiKey,
            'Accept' => 'application/json',
        ])->post($url, [
            'message' => $request->message,
            'platform' => $request->platform,
            // Puedes agregar más campos según la documentación de Postik
        ]);
        if ($response->successful()) {
            return response()->json(['error' => false, 'message' => 'Publicado correctamente', 'data' => $response->json()]);
        } else {
            return response()->json(['error' => true, 'message' => 'Error al publicar: ' . $response->body()], $response->status());
        }
    }

    // Obtiene el listado de integraciones desde la API de Postik y muestra la selección de activas
    public function integrationsList()
    {
        // Para AJAX Bootstrap Table
        $apiKey = DB::table('tbl_settings')->where('type', 'postik_api_key')->value('message');
        $endpoint = DB::table('tbl_settings')->where('type', 'postik_endpoint_url')->value('message');
        if (!$apiKey || !$endpoint) {
            return response()->json([
                'total' => 0,
                'rows' => [],
                'error' => 'API Key o Endpoint de Postik no configurados.'
            ]);
        }
        $url = rtrim($endpoint, '/') . '/api/public/v1/integrations';
        $response = Http::withHeaders([
            'Authorization' => $apiKey,
            'Accept' => 'application/json',
        ])->get($url);
        $integrations = [];
        if ($response->successful()) {
            $integrations = $response->json();
        }
        // Obtener integraciones activas guardadas (en settings como JSON)
        $active = DB::table('tbl_settings')->where('type', 'postik_integrations_active')->value('message');
        $activeIntegrationsArr = $active ? json_decode($active, true) : [];
        // Extraer solo los IDs activos (soporta array de objetos o de strings)
        $activeIds = [];
        foreach ($activeIntegrationsArr as $item) {
            if (is_array($item) && isset($item['id'])) {
                $activeIds[] = $item['id'];
            } elseif (is_string($item)) {
                $activeIds[] = $item;
            }
        }

        // Bootstrap Table params
        $request = request();
        $search = $request->input('search', '');
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'desc');
        $offset = (int) $request->input('offset', 0);
        $limit = (int) $request->input('limit', 10);

        // Filtrar por búsqueda
        if ($search) {
            $integrations = array_filter($integrations, function($item) use ($search) {
                $haystack = strtolower(
                    ($item['name'] ?? '') . ' ' .
                    ($item['identifier'] ?? '') . ' ' .
                    ($item['profile'] ?? '')
                );
                return strpos($haystack, strtolower($search)) !== false;
            });
        }

        // Ordenar
        $integrations = array_values($integrations); // reindexar
        usort($integrations, function($a, $b) use ($sort, $order) {
            $aVal = $a[$sort] ?? '';
            $bVal = $b[$sort] ?? '';
            if ($aVal == $bVal) return 0;
            if ($order === 'desc') {
                return $aVal < $bVal ? 1 : -1;
            } else {
                return $aVal > $bVal ? 1 : -1;
            }
        });

        $total = count($integrations);
        $rows = array_slice($integrations, $offset, $limit);

        // Marcar activos
        foreach ($rows as &$row) {
            $row['active'] = in_array($row['id'] ?? '', $activeIds);
        }

        return response()->json([
            'total' => $total,
            'rows' => array_values($rows),
        ]);
    }

    // Guarda la selección de integraciones activas
    public function saveIntegrations(Request $request)
    {
        $request->validate([
            'integrations' => 'nullable|array',
        ]);
        $ids = $request->input('integrations', []);

        \Log::info('Postik saveIntegrations - IDs recibidos', ['ids' => $ids]);

        // Validar que los IDs existan en la API de Postik y obtener name/picture
        $apiKey = DB::table('tbl_settings')->where('type', 'postik_api_key')->value('message');
        $endpoint = DB::table('tbl_settings')->where('type', 'postik_endpoint_url')->value('message');
        $validIntegrations = [];
        if ($apiKey && $endpoint) {
            $url = rtrim($endpoint, '/') . '/api/public/v1/integrations';
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Accept' => 'application/json',
            ])->get($url);
            if ($response->successful()) {
                $integrations = $response->json();
                // Filtrar solo los seleccionados y mapear id, name, picture
                foreach ($integrations as $item) {
                    if (in_array($item['id'] ?? '', $ids)) {
                        $validIntegrations[] = [
                            'id' => $item['id'] ?? '',
                            'name' => $item['name'] ?? '',
                            'picture' => $item['picture'] ?? '',
                        ];
                    }
                }
                \Log::info('Postik saveIntegrations - Integraciones válidas', ['validIntegrations' => $validIntegrations]);
            } else {
                \Log::warning('Postik saveIntegrations - No se pudo obtener integraciones de la API');
            }
        }
        // Si no hay conexión, no guardar nada
        if (empty($validIntegrations) && !empty($ids)) {
            \Log::warning('Postik saveIntegrations - Ninguna integración válida guardada');
            return response()->json(['success' => false, 'message' => 'No se pudieron validar las integraciones seleccionadas.']);
        }
        DB::table('tbl_settings')->updateOrInsert(
            ['type' => 'postik_integrations_active'],
            ['message' => json_encode($validIntegrations)]
        );
        \Log::info('Postik saveIntegrations - Guardado final', ['active' => $validIntegrations]);
        return response()->json(['success' => true, 'message' => 'Integraciones activas actualizadas.', 'active' => $validIntegrations]);
    }
}