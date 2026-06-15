<div class="modal fade" id="cropModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title font-weight-700 text-dark">{{ __('reda-alojamiento::messages.general.recortar_imagen') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="img-container bg-dark d-flex align-items-center justify-content-center" style="min-height: 400px; max-height: 600px; overflow: hidden;">
                    <img id="image-to-crop" src="" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-between p-3">
                <input type="hidden" id="crop_photo_id" value="">
                <button type="button" class="btn btn-outline-secondary px-4" style="border-radius: 20px; font-weight: 600;" data-dismiss="modal">
                    {{ __('reda-alojamiento::messages.general.cancelar') }}
                </button>
                <button type="button" class="btn btn-success px-4" id="crop-and-upload" data-origen="fotos-experiencias" style="border-radius: 20px; font-weight: 600; background-color: #28a745; border: none;">
                    <i class="fa fa-crop mr-2"></i> {{ __('reda-alojamiento::messages.general.guardar_cambios') }}
                </button>
            </div>
        </div>
    </div>
</div>
