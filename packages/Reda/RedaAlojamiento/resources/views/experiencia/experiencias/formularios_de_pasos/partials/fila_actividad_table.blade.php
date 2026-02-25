<tr id="fila-actividad-{{ $actividad->id }}">
    <td class="vertical-align-top pt-3">
        <input type="number" 
            name="actividades[{{ $actividad->id }}][orden_actividad]" 
            value="{{ old('actividades.'.$actividad->id.'.orden_actividad', $actividad->orden_actividad) }}" 
            class="form-control text-center @error('actividades.'.$actividad->id.'.orden_actividad') is-invalid @enderror" 
            min="1"
            required>
        @error('actividades.'.$actividad->id.'.orden_actividad')
            <span class="text-danger small font-weight-700">Debe ser un número válido mayor a cero.</span>
        @enderror
    </td>
    <td class="vertical-align-top pt-3">
        <textarea name="actividades[{{ $actividad->id }}][descripcion_actividad]" 
                class="form-control @error('actividades.'.$actividad->id.'.descripcion_actividad') is-invalid @enderror" 
                placeholder="Describe la actividad..."
                rows="2"
                required>{{ old('actividades.'.$actividad->id.'.descripcion_actividad', $actividad->descripcion_actividad) }}</textarea>
        @error('actividades.'.$actividad->id.'.descripcion_actividad')
            <span class="text-danger small font-weight-700">Este campo es obligatorio.</span>
        @enderror
    </td>
    <td>
        <div class="actividad-foto-container {{ !$actividad->foto_actividad ? 'placeholder-height' : '' }}">
            @if($actividad->foto_actividad)
                <img src="{{ asset('public/images/actividades_experiencias/' . $actividad->foto_actividad) }}" alt="Foto">

                <label class="edit-photo-overlay-outline" for="file-{{ $actividad->id }}" title="Cambiar imagen">
                    <i class="fa fa-pencil-alt"></i>
                </label>

                <input id="file-{{ $actividad->id }}" type="file" name="actividades[{{ $actividad->id }}][foto_actividad]" data-id="{{ $actividad->id }}" class="upload_photos" accept="image/*" style="display:none;">
            @else
                <label class="custom-file-upload m-0" for="file-{{ $actividad->id }}">
                    <i class="fa fa-cloud-upload"></i> Cargar foto
                    <input id="file-{{ $actividad->id }}" type="file" name="actividades[{{ $actividad->id }}][foto_actividad]" data-id="{{ $actividad->id }}" class="upload_photos" accept="image/*" style="display:none;">
                </label>
            @endif
        </div>
        @error("foto_actividad_id_" . $actividad->id)
            <div class="text-danger mt-2" style="font-size: 13px; font-weight: 700;">
                <i class="fa fa-exclamation-triangle"></i> {{ $message }}
            </div>
        @enderror
    </td>
    <td class="text-center">
        <button type="button" 
            class="btn-delete-actividad-simple" 
            data-id="{{ $actividad->id }}" 
            data-url="{{ route('reda.experiencias.actividades.delete', $actividad->id) }}"
            title="Eliminar actividad">
            <i class="fa fa-trash-alt"></i>
        </button>
    </td>
</tr>