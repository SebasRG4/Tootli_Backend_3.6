<div class="modal fade" id="getInformationModal" tabindex="-1" aria-labelledby="getInformationModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered max-w-655px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-2 px-2 d-flex justify-content-end">
                <button type="button" class="close border-0 btn-circle bg-section2 shadow-none" data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 px-sm-5 pt-0">
                <div class="swiper instruction-carousel pb-3">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <img width="80" class="mb-3"
                                    src="{{ asset('assets/back-end/img/modal/instruction.png') }}"
                                    loading="lazy" alt="">
                                <div>
                                    <h3 class="lh-md mb-3 text-capitalize text-start">
                                        {{ 'guía paso a paso' }}
                                    </h3>
                                    <ol class="d-flex flex-column px-4 gap-2 mb-4">
                                        <li> {{ 'Abra el administrador de publicidad o la plataforma que desea integrar (por ejemplo, metaanuncios, anuncios de Snapchat, Google Analytics).' }}
                                        </li>
                                        <li> {{ 'Localice y copie los identificadores de seguimiento necesarios desde sus respectivas configuraciones.' }}
                                        </li>
                                        <li> {{ 'Encienda el interruptor de la plataforma que desea activar.' }}
                                        </li>
                                        <li> {{ 'pegue el código en el cuadro de entrada y haga clic en enviar.' }}
                                        </li>
                                        <li> {{ 'Si ya no desea realizar un seguimiento de los análisis de una plataforma, desactive la opción en cualquier momento.' }}
                                        </li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
