<div class="col-12 mb-4 fila-actividad-container" id="fila-actividad-{{ $actividad->id }}">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-orange text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                        {{ $actividad->orden_actividad ?? '!' }}
                    </span>
                    <h5 class="card-title mb-0 font-weight-700 text-dark">{{ __('reda-alojamiento::messages.general.detalle_del_producto_o_servicio') }}</h5>
                </div>
                <button 
                    type="button" class="btn btn-outline-danger btn-sm border-0 btn-delete-actividad-simple" 
                    data-id="{{ $actividad->id }}" 
                    data-url="{{ route('reda.experiencias.actividades.delete', $actividad->id) }}"
                    title="Eliminar producto y/o servicio"
                >
                    <i class="fa fa-trash-alt"></i>
                </button>                
            </div>
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.nro') }} <span class="text-danger">*</label>
                            <input 
                                type="number" 
                                name="actividades[{{ $actividad->id }}][orden_actividad]" 
                                value="{{ old('actividades.'.$actividad->id.'.orden_actividad', $actividad->orden_actividad) }}" 
                                class="form-control text-center @error('actividades.'.$actividad->id.'.orden_actividad') is-invalid @enderror" 
                                min="1" 
                                required
                            >
                            @error('actividades.'.$actividad->id.'.orden_actividad')
                                <span class="text-danger small font-weight-700">{{ __('reda-alojamiento::messages.general.debe_ser_un_numero_valido_mayor_a_cero') }}</span>
                            @enderror
                        </div>
                        <div class="col-md-9">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.nombre_del_producto_o_servicio') }} <span class="text-danger">*</label>
                            <input type="text" name="actividades[{{ $actividad->id }}][nombre_actividad]" 
                                value="{{ old('actividades.'.$actividad->id.'.nombre_actividad', $actividad->nombre_actividad) }}" 
                                class="form-control" placeholder="" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.descripcion') }} <span class="text-danger">*</label>
                            <textarea 
                                name="actividades[{{ $actividad->id }}][descripcion_actividad]" 
                                class="form-control @error('actividades.'.$actividad->id.'.descripcion_actividad') is-invalid @enderror" 
                                rows="2" 
                                placeholder="Haz una descripción del producto o servicio"
                                required>{{ old('actividades.'.$actividad->id.'.descripcion_actividad', $actividad->descripcion_actividad) }}</textarea>
                            @error('actividades.'.$actividad->id.'.descripcion_actividad')
                                <span class="text-danger small font-weight-700">Este campo es obligatorio.</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.precio') }} <span class="text-danger">*</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fa fa-tag text-muted"></i></span>
                                <input type="number" step="0.01" name="actividades[{{ $actividad->id }}][precio]" 
                                    value="{{ old('actividades.'.$actividad->id.'.precio', $actividad->precio) }}" 
                                    class="form-control border-start-0 validar-precio" placeholder="0.00"> 
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.moneda') }} <span class="text-danger">*</label>
                            <select name="actividades[{{ $actividad->id }}][currency_id]" class="form-select form-control">
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ (old('actividades.'.$actividad->id.'.currency_id', $actividad->currency_id) == $currency->id) ? 'selected' : '' }}>
                                        {{ $currency->code }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.disponibilidad') }} <span class="text-danger">*</label>
                            <select name="actividades[{{ $actividad->id }}][disponibilidad]" class="form-select form-control">
                                <option value="1" {{ old('actividades.'.$actividad->id.'.disponibilidad', $actividad->disponibilidad) == '1' ? 'selected' : '' }}>Disponible</option>
                                <option value="0" {{ old('actividades.'.$actividad->id.'.disponibilidad', $actividad->disponibilidad) == '0' ? 'selected' : '' }}>No Disponible</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 d-flex flex-column align-items-center justify-content-center border-start-lg ps-lg-4">
                    <label class="form-label small font-weight-700 w-100 text-center mb-2">{{ __('reda-alojamiento::messages.general.imagen_de_la_actividad') }} <span class="text-danger">*</label>
                    <div class="actividad-foto-card-container {{ !$actividad->foto_actividad ? 'no-image' : '' }}" id="foto-container-{{ $actividad->id }}">
                        @if($actividad->foto_actividad)
                            <img src="{{ asset('public/images/actividades_experiencias/'.$actividad->foto_actividad) }}" 
                                 class="img-fluid rounded-3 shadow-sm" alt="Foto">
                            <label class="edit-photo-overlay-outline" for="file-{{ $actividad->id }}" title="Cambiar imagen">
                                <i class="fa fa-pencil-alt"></i>
                            </label>
                        @else
                            <label class="upload-placeholder" for="file-{{ $actividad->id }}">
                                <i class="fa fa-image fa-2x mb-2 text-muted"></i>
                                <span class="small text-muted">{{ __('reda-alojamiento::messages.general.subir_foto') }}</span>
                            </label>
                        @endif
                        <input id="file-{{ $actividad->id }}" type="file" name="actividades[{{ $actividad->id }}][foto_actividad]" 
                               data-id="{{ $actividad->id }}" class="upload_photos" accept="image/*" style="display:none;">
                    </div>
                    @error("foto_actividad_id_" . $actividad->id)
                        <div class="text-danger mt-2" style="font-size: 13px; font-weight: 700;">
                            <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>