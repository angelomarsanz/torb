<div class="modal fade" id="modal-mediacion-reda" tabindex="-1" role="dialog" aria-labelledby="modalMediacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-mediacion-reda" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="booking_id" id="reda-booking-id">
                <input type="hidden" name="anfitrion_id" id="reda-anfitrion-id">
                <input type="hidden" name="turista_id" id="reda-turista-id">
                <input type="hidden" name="id_usuario_inicial" value="{{ Auth::id() }}">

                <div class="modal-header">
                    <h5 class="modal-title reda-mediation-title" id="modalMediacionLabel">{{ __('Solicitar Mediación') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prioridad" class="reda-mediation-label">{{ __('Prioridad') }} <span class="text-danger">*</span></label>
                            <select name="prioridad" id="prioridad" class="form-control" required>
                                <option value="">{{ __('Seleccione una opción') }}</option>
                                <option value="Baja">{{ __('Baja') }}</option>
                                <option value="Media">{{ __('Media') }}</option>
                                <option value="Alta">{{ __('Alta') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="motivo" class="reda-mediation-label">{{ __('Motivo') }} <span class="text-danger">*</span></label>
                            <input type="text" name="motivo" id="motivo" class="form-control" placeholder="{{ __('Ej: Problema con el pago, Daños, etc.') }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="descripcion" class="reda-mediation-label">{{ __('Descripción') }} <span class="text-danger">*</span></label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="4" placeholder="{{ __('Explique detalladamente su situación...') }}" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label for="documentos" class="reda-mediation-label">{{ __('Adjuntar Evidencias (Opcional)') }}</label>
                            <div class="custom-file">
                                <input type="file" name="documentos[]" id="documentos" class="custom-file-input" multiple>
                                <label class="custom-file-label" for="documentos">{{ __('Elegir archivos') }}</label>
                            </div>
                            <small class="text-muted">{{ __('Puede seleccionar varios archivos (Imágenes, PDF).') }}</small>
                            <div id="file-list-preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer reda-mediation-footer">
                    <button type="button" class="btn btn-secondary px-4 font-weight-700" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-700" id="btn-enviar-mediacion">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        {{ __('Enviar Solicitud') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
