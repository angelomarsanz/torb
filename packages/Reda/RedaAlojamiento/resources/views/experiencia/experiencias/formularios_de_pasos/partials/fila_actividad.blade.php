@php
    $datosComplementarios = json_decode($actividad->precios_monedas_complementarios, true) ?? [];
    $readonly = $readonly ?? false;
    $disabled = $readonly ? 'disabled' : '';
@endphp
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
            </div>
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="row g-3">

                        <!-- 1. Nombre -->
                        <div class="col-md-12">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.nombre_del_producto_o_servicio') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                            <input type="text" name="actividades[{{ $actividad->id }}][nombre_actividad]"
                                value="{{ old('actividades.'.$actividad->id.'.nombre_actividad', $actividad->nombre_actividad) }}"
                                class="form-control" placeholder="" {{ $disabled }}
                            >
                            @error('actividades.'.$actividad->id.'.nombre_actividad')
                                <span class="text-danger small font-weight-700">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 2. Descripción -->
                        <div class="col-md-12">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.descripcion') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                            <textarea
                                name="actividades[{{ $actividad->id }}][descripcion_actividad]"
                                class="form-control @error('actividades.'.$actividad->id.'.descripcion_actividad') is-invalid @enderror"
                                rows="2"
                                placeholder="Haz una descripción del producto o servicio" {{ $disabled }}
                            >{{ old('actividades.'.$actividad->id.'.descripcion_actividad', $actividad->descripcion_actividad) }}</textarea>
                            @error('actividades.'.$actividad->id.'.descripcion_actividad')
                                <span class="text-danger small font-weight-700">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- 3. Tipo de producto o servicio -->
                        <div class="col-md-12">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.tipo_producto_o_servicio') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                            <select name="actividades[{{ $actividad->id }}][tipo_producto_servicio]" class="form-control" {{ $disabled }}>
                                <option value="" {{ is_null(old('actividades.'.$actividad->id.'.tipo_producto_servicio', $actividad->tipo_producto_servicio)) ? 'selected' : '' }} disabled>
                                    {{ __('reda-alojamiento::messages.general.seleccione_una_opcion') }}
                                </option>
                                <option value="producto" {{ old('actividades.'.$actividad->id.'.tipo_producto_servicio', $actividad->tipo_producto_servicio) == 'producto' ? 'selected' : '' }}>
                                    {{ __('reda-alojamiento::messages.general.producto') }}
                                </option>
                                <option value="servicio" {{ old('actividades.'.$actividad->id.'.tipo_producto_servicio', $actividad->tipo_producto_servicio) == 'servicio' ? 'selected' : '' }}>
                                    {{ __('reda-alojamiento::messages.general.servicio') }}
                                </option>
                            </select>
                            @error("actividades.{$actividad->id}.tipo_producto_servicio")
                                <div class="text-danger small font-weight-700 mt-1">
                                    <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- 4. Precio y Moneda General -->
                        <div class="col-md-7">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.precio') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fa fa-tag text-muted"></i></span>
                                <input type="number" step="0.01" name="actividades[{{ $actividad->id }}][precio]"
                                    value="{{ old('actividades.'.$actividad->id.'.precio', $actividad->precio) }}"
                                    class="form-control border-start-0 validar-precio" placeholder="0.00" {{ $disabled }}
                                >
                            </div>
                            @error('actividades.'.$actividad->id.'.precio')
                                <span class="text-danger small font-weight-700">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.moneda') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                            <select name="actividades[{{ $actividad->id }}][currency_id]" class="form-control" {{ $disabled }}>
                                <option value="" {{ is_null(old('actividades.'.$actividad->id.'.currency_id', $actividad->currency_id)) ? 'selected' : '' }} disabled>
                                    {{ __('reda-alojamiento::messages.general.seleccione_una_opcion') }}
                                </option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ old('actividades.'.$actividad->id.'.currency_id', $actividad->currency_id) == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->code }}
                                    </option>
                                @endforeach
                            </select>
                            @error("actividades.{$actividad->id}.currency_id")
                                <div class="text-danger small font-weight-700 mt-1">
                                    <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- 5. Precio en bolívares (Radio e Inputs) -->
                        <div class="col-md-12">
                            <label class="form-label small font-weight-700">{{ __('Precio en bolívares') }}</label>
                            <div class="d-flex flex-wrap gap-x-4 mb-2 mt-1">
                                <div class="form-check me-3">
                                    <input class="form-check-input radio-tipo-carga" type="radio" 
                                           name="actividades[{{ $actividad->id }}][tipo_carga_precio_local]" 
                                           id="manual-{{ $actividad->id }}" value="manual" 
                                           style="cursor: pointer; transform: scale(1.1);" {{ $disabled }}
                                           {{ old('actividades.'.$actividad->id.'.tipo_carga_precio_local', $actividad->tipo_carga_precio_local) == 'manual' ? 'checked' : '' }}>
                                    <label class="form-check-label small font-weight-600" for="manual-{{ $actividad->id }}" 
                                           style="cursor: pointer; margin-left: 10px;">
                                        {{ __('Carga manual') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input radio-tipo-carga" type="radio" 
                                           name="actividades[{{ $actividad->id }}][tipo_carga_precio_local]" 
                                           id="automatico-{{ $actividad->id }}" value="automatico_bcv" 
                                           style="cursor: pointer; transform: scale(1.1);" {{ $disabled }}
                                           {{ old('actividades.'.$actividad->id.'.tipo_carga_precio_local', $actividad->tipo_carga_precio_local) == 'automatico_bcv' || is_null(old('actividades.'.$actividad->id.'.tipo_carga_precio_local', $actividad->tipo_carga_precio_local)) ? 'checked' : '' }}>
                                    <label class="form-check-label small font-weight-600" for="automatico-{{ $actividad->id }}" 
                                           style="cursor: pointer; margin-left: 10px;">
                                        {{ __('Cálculo automático BCV') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 div-precio-bolivares {{ old('actividades.'.$actividad->id.'.tipo_carga_precio_local', $actividad->tipo_carga_precio_local) == 'manual' ? '' : 'd-none' }}">
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <label class="form-label small font-weight-700">{{ __('Precio en bolívares:') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                                    <input type="number" step="0.01" name="actividades[{{ $actividad->id }}][precio_pago_bolivares]"
                                        value="{{ old('actividades.'.$actividad->id.'.precio_pago_bolivares', $datosComplementarios['precio_pago_bolivares'] ?? '') }}"
                                        class="form-control input-precio-bolivares" placeholder="0.00" {{ $disabled }}
                                    >
                                    @error('actividades.'.$actividad->id.'.precio_pago_bolivares')
                                        <span class="text-danger small font-weight-700">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.moneda') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                                    <select name="actividades[{{ $actividad->id }}][moneda_pago_bolivares]" class="form-control select-moneda-complementaria" {{ $disabled }}>
                                        <option value="" {{ is_null(old('actividades.'.$actividad->id.'.moneda_pago_bolivares', $datosComplementarios['moneda_pago_bolivares'] ?? null)) ? 'selected' : '' }} disabled>
                                            {{ __('reda-alojamiento::messages.general.seleccione_una_opcion') }}
                                        </option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" {{ old('actividades.'.$actividad->id.'.moneda_pago_bolivares', $datosComplementarios['moneda_pago_bolivares'] ?? null) == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("actividades.{$actividad->id}.moneda_pago_bolivares")
                                        <div class="text-danger small font-weight-700 mt-1">
                                            <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 6. Precio y Moneda de Promoción -->
                        <div class="col-md-12">
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <label class="form-label small font-weight-700">{{ __('Precio promoción:') }}</label>
                                    <input type="number" step="0.01" name="actividades[{{ $actividad->id }}][precio_promocion]"
                                        value="{{ old('actividades.'.$actividad->id.'.precio_promocion', $datosComplementarios['precio_promocion'] ?? '') }}"
                                        class="form-control" placeholder="0.00" {{ $disabled }}
                                    >
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small font-weight-700">{{ __('Moneda promoción') }}</label>
                                    <select name="actividades[{{ $actividad->id }}][moneda_precio_promocion]" class="form-control" {{ $disabled }}>
                                        <option value="" {{ is_null(old('actividades.'.$actividad->id.'.moneda_precio_promocion', $datosComplementarios['moneda_precio_promocion'] ?? null)) ? 'selected' : '' }}>
                                            {{ __('reda-alojamiento::messages.general.seleccione_una_opcion') }}
                                        </option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" {{ (old('actividades.'.$actividad->id.'.moneda_precio_promocion', $datosComplementarios['moneda_precio_promocion'] ?? null)) == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 7. Disponibilidad -->
                        <div class="col-md-6">
                            <label class="form-label small font-weight-700">{{ __('reda-alojamiento::messages.general.disponibilidad') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                            <select name="actividades[{{ $actividad->id }}][disponibilidad]" class="form-control" {{ $disabled }}>
                                <option value="" {{ is_null(old('actividades.'.$actividad->id.'.disponibilidad', $actividad->disponibilidad)) ? 'selected' : '' }} disabled>
                                    {{ __('reda-alojamiento::messages.general.seleccione_una_opcion') }}
                                </option>
                                <option value="1" {{ old('actividades.'.$actividad->id.'.disponibilidad', $actividad->disponibilidad) == '1' ? 'selected' : '' }}>
                                    {{ __('reda-alojamiento::messages.general.disponible') }}
                                </option>
                                <option value="0" {{ old('actividades.'.$actividad->id.'.disponibilidad', $actividad->disponibilidad) == '0' ? 'selected' : '' }}>
                                    {{ __('reda-alojamiento::messages.general.no_disponible') }}
                                </option>
                            </select>
                            @error("actividades.{$actividad->id}.disponibilidad")
                                <div class="text-danger small font-weight-700 mt-1">
                                    <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- 8. Estatus Producto/Servicio -->
                        <div class="col-md-6">
                            <label class="form-label small font-weight-700">{{ __('Estatus') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                            <select name="actividades[{{ $actividad->id }}][estatus_producto_servicio]" class="form-control" {{ $disabled }}>
                                <option value="" {{ is_null(old('actividades.'.$actividad->id.'.estatus_producto_servicio', $actividad->estatus_producto_servicio)) ? 'selected' : '' }} disabled>
                                    {{ __('reda-alojamiento::messages.general.seleccione_una_opcion') }}
                                </option>
                                <option value="activo" {{ old('actividades.'.$actividad->id.'.estatus_producto_servicio', $actividad->estatus_producto_servicio) == 'activo' || is_null($actividad->estatus_producto_servicio) ? 'selected' : '' }}>
                                    {{ __('Activo') }}
                                </option>
                                <option value="inactivo" {{ old('actividades.'.$actividad->id.'.estatus_producto_servicio', $actividad->estatus_producto_servicio) == 'inactivo' ? 'selected' : '' }}>
                                    {{ __('Inactivo') }}
                                </option>
                            </select>
                            @error("actividades.{$actividad->id}.estatus_producto_servicio")
                                <div class="text-danger small font-weight-700 mt-1">
                                    <i class="fa fa-exclamation-triangle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="col-lg-4 d-flex flex-column align-items-center justify-content-center border-start-lg ps-lg-4">
                    <label class="form-label small font-weight-700 w-100 text-center mb-2">{{ __('reda-alojamiento::messages.general.imagen_de_la_actividad') }} @if(!$readonly)<span class="text-danger">*</span>@endif</label>
                    <div class="actividad-foto-card-container {{ !$actividad->foto_actividad ? 'no-image' : '' }}" id="foto-container-{{ $actividad->id }}">
                        @if($actividad->foto_actividad)
                            <img src="{{ asset('public/images/actividades_experiencias/'.$actividad->foto_actividad) }}"
                                 class="img-fluid rounded-3 shadow-sm" alt="Foto">
                            @if(!$readonly)
                            <label class="edit-photo-overlay-outline" for="file-{{ $actividad->id }}" title="Cambiar imagen">
                                <i class="fa fa-pencil-alt"></i>
                            </label>
                            @endif
                        @else
                            <label class="upload-placeholder" @if(!$readonly) for="file-{{ $actividad->id }}" @endif>
                                <i class="fa fa-image fa-2x mb-2 text-muted"></i>
                                <span class="small text-muted">{{ __('reda-alojamiento::messages.general.subir_foto') }}</span>
                            </label>
                        @endif
                        @if(!$readonly)
                        <input id="file-{{ $actividad->id }}" type="file" name="actividades[{{ $actividad->id }}][foto_actividad]"
                               data-id="{{ $actividad->id }}" class="upload_photos" accept="image/*" style="display:none;">
                        @endif
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
