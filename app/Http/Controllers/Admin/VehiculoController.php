<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    // Tipos y combustibles para los selects
    private array $tiposVehiculo = ['Sedán','Pick-up','SUV','Van','Camión','Moto','Bus'];
    private array $tiposCombustible = ['Gasolina','Diesel','Eléctrico','Híbrido','GLP'];

    // ── GET /admin/vehiculos ──────────────────────────────────────────────────
    public function index(Request $request)
    {
        $statusFiltro = $request->query('status', '');
        $page         = (int) $request->query('page', 1);

        $query = Vehicle::latest();

        if ($statusFiltro) {
            $query->where('status', $statusFiltro);
        }

        $paginado = $query->paginate(10, ['*'], 'page', $page);

        return view('admin.vehiculos.index', [
            'vehiculos'   => $paginado->items(),
            'paginado'    => [
                'current_page' => $paginado->currentPage(),
                'last_page'    => $paginado->lastPage(),
                'total'        => $paginado->total(),
            ],
            'statusFiltro' => $statusFiltro,
            'statuses'     => Vehicle::STATUSES,
        ]);
    }

    // ── GET /admin/vehiculos/crear ────────────────────────────────────────────
    public function create()
    {
        return view('admin.vehiculos.create', [
            'tiposVehiculo'    => $this->tiposVehiculo,
            'tiposCombustible' => $this->tiposCombustible,
            'statuses'         => Vehicle::STATUSES,
        ]);
    }

    // ── POST /admin/vehiculos ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'plate'        => 'required|string|max:20|unique:vehicles,plate',
            'brand'        => 'required|string|max:100',
            'model'        => 'required|string|max:100',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_type' => 'required|string|max:50',
            'capacity'     => 'required|integer|min:1',
            'fuel_type'    => 'required|string|max:30',
            'mileage'      => 'nullable|integer|min:0',
            'status'       => 'nullable|string|in:' . implode(',', Vehicle::STATUSES),
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'plate.required'    => 'La placa es obligatoria.',
            'plate.unique'      => 'Esta placa ya está registrada.',
            'brand.required'    => 'La marca es obligatoria.',
            'model.required'    => 'El modelo es obligatorio.',
            'year.required'     => 'El año es obligatorio.',
            'year.min'          => 'El año mínimo es 1900.',
            'vehicle_type.required' => 'El tipo de vehículo es obligatorio.',
            'capacity.required' => 'La capacidad es obligatoria.',
            'capacity.min'      => 'La capacidad mínima es 1.',
            'fuel_type.required'=> 'El tipo de combustible es obligatorio.',
            'image.image'       => 'El archivo debe ser una imagen.',
            'image.mimes'       => 'Formatos aceptados: jpg, jpeg, png, webp.',
            'image.max'         => 'La imagen no debe superar 2MB.',
        ]);

        $data = $request->only([
            'plate','brand','model','year','vehicle_type',
            'capacity','fuel_type','mileage','status',
        ]);

        // Convertir placa a mayúsculas
        $data['plate']   = strtoupper($data['plate']);
        $data['status']  = $data['status'] ?? Vehicle::STATUS_AVAILABLE;
        $data['mileage'] = $data['mileage'] ?? 0;

        // Subir imagen si existe
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('vehicles', 'public');
        }

        Vehicle::create($data);

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Vehículo registrado exitosamente.');
    }

    // ── GET /admin/vehiculos/{id}/editar ──────────────────────────────────────
    public function edit(int $id)
    {
        $vehiculo = Vehicle::find($id);

        if (!$vehiculo) {
            return redirect()->route('admin.vehiculos')
                ->with('error', 'Vehículo no encontrado.');
        }

        return view('admin.vehiculos.edit', [
            'vehiculo'         => $vehiculo->toArray(),
            'tiposVehiculo'    => $this->tiposVehiculo,
            'tiposCombustible' => $this->tiposCombustible,
            'statuses'         => Vehicle::STATUSES,
        ]);
    }

    // ── PUT /admin/vehiculos/{id} ─────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $vehiculo = Vehicle::find($id);

        if (!$vehiculo) {
            return redirect()->route('admin.vehiculos')
                ->with('error', 'Vehículo no encontrado.');
        }

        $request->validate([
            'plate'        => ['required','string','max:20', Rule::unique('vehicles','plate')->ignore($id)],
            'brand'        => 'required|string|max:100',
            'model'        => 'required|string|max:100',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_type' => 'required|string|max:50',
            'capacity'     => 'required|integer|min:1',
            'fuel_type'    => 'required|string|max:30',
            'status'       => 'required|string|in:' . implode(',', Vehicle::STATUSES),
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'plate.required'    => 'La placa es obligatoria.',
            'plate.unique'      => 'Esta placa ya está en uso por otro vehículo.',
            'brand.required'    => 'La marca es obligatoria.',
            'model.required'    => 'El modelo es obligatorio.',
            'year.required'     => 'El año es obligatorio.',
            'vehicle_type.required' => 'El tipo de vehículo es obligatorio.',
            'capacity.required' => 'La capacidad es obligatoria.',
            'fuel_type.required'=> 'El tipo de combustible es obligatorio.',
            'status.required'   => 'El estado es obligatorio.',
            'image.image'       => 'El archivo debe ser una imagen.',
            'image.mimes'       => 'Formatos aceptados: jpg, jpeg, png, webp.',
            'image.max'         => 'La imagen no debe superar 2MB.',
        ]);

        $data = $request->only([
            'plate','brand','model','year','vehicle_type',
            'capacity','fuel_type','status',
        ]);

        $data['plate'] = strtoupper($data['plate']);

        // Reemplazar imagen si se sube una nueva
        if ($request->hasFile('image')) {
            if ($vehiculo->image_path) {
                Storage::disk('public')->delete($vehiculo->image_path);
            }
            $data['image_path'] = $request->file('image')->store('vehicles', 'public');
        }

        $vehiculo->update($data);

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Vehículo actualizado exitosamente.');
    }

    // ── DELETE /admin/vehiculos/{id} ── Borrado lógico ────────────────────────
    public function destroy(int $id)
    {
        $vehiculo = Vehicle::find($id);

        if (!$vehiculo) {
            return back()->with('error', 'Vehículo no encontrado.');
        }

        // Bloquear si tiene solicitudes activas
        $tieneActivas = $vehiculo->tripRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($tieneActivas) {
            return back()->with('error',
                'No se puede eliminar: el vehículo tiene solicitudes pendientes o aprobadas.');
        }

        // Bloquear si tiene mantenimiento abierto
        if ($vehiculo->openMaintenances()->exists()) {
            return back()->with('error',
                'No se puede eliminar: el vehículo tiene un mantenimiento abierto.');
        }

        $vehiculo->delete();

        return redirect()->route('admin.vehiculos')
            ->with('success', 'Vehículo eliminado correctamente (borrado lógico).');
    }
}