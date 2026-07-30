<div class="modal fade" id="how-it-works">
    <div class="modal-dialog status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <div class="single-item-slider owl-carousel">
                    <div class="item">
                        <div class="max-349 mx-auto mb-20 text-center">
                            <img src="{{asset('assets/admin/img/landing-how.png')}}" alt="" class="mb-20">
                            <h5 class="modal-title">{{'¡Aviso!'}}</h5>
                            <p>
                                {{'Si desea deshabilitar o desactivar alguna sección, déjela vacía, ¡no realice ningún cambio allí!'}}
                            </p>
                        </div>
                    </div>
                    <div class="item">
                        <div class="max-349 mx-auto mb-20 text-center">
                            <img src="{{asset('assets/admin/img/notice-2.png')}}" alt="" class="mb-20">
                            <h5 class="modal-title">{{'Si desea cambiar el idioma'}}</h5>
                            <p>
                                {{'¡Cambie el idioma en la barra de pestañas e ingrese sus datos nuevamente!'}}
                            </p>
                        </div>
                    </div>
                    <div class="item">
                        <div class="max-349 mx-auto mb-20 text-center">
                            <img src="{{asset('assets/admin/img/notice-3.png')}}" alt="" class="mb-20">
                            <h5 class="modal-title">{{'¡Veamos los cambios!'}}</h5>
                            <p>
                                {{'¡Visite la página de inicio para ver los cambios que realizó en la opción de configuración!'}}
                            </p>
                            <div class="btn-wrap">
                                <a href="{{ route('home') }}" class="btn btn--primary w-100">{{ 'Visita ahora' }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <div class="slide-counter"></div>
                </div>
            </div>
        </div>
    </div>
</div>