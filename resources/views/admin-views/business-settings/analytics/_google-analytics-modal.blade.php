<div class="modal fade" id="modalForGoogleAnalytics" tabindex="-1" aria-labelledby="modalForGoogleAnalytics"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered max-w-655">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                <button type="button" class="close border-0 btn-circle bg-section2 shadow-none" data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-20 py-0 mb-30">
                <div class="swiper instruction-carousel pb-3">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="swiper-slide">
                                <div class="">
                                    <div class="d-flex justify-content-center mb-5">
                                        <img height="60"
                                            src="{{ asset('assets/admin/img/svg/google.svg') }}"
                                            loading="lazy" alt="">
                                    </div>
                                    <div class="text-dark mb-3">
                                        <h3 class="lh-base">
                                            {{ 'cómo obtener la identificación de medición de Google Analytics' }}
                                        </h3>
                                        <p class="opacity-75">
                                            {{ 'Para encontrar su ID de medición de Google Analytics, inicie sesión en su cuenta de Google Analytics.' }}
                                            {{ 'vaya a administrador y luego a flujos de datos.' }}
                                            {{ 'seleccione su flujo de datos web y se mostrará su identificación de medición.' }}
                                            {{ 'cópialo.' }}
                                        </p>
                                    </div>

                                    <div class="text-dark mb-3">
                                        <h3 class="lh-base">
                                            {{ 'dónde utilizar la identificación de medición de Google Analytics' }}
                                        </h3>
                                        <p class="opacity-75">
                                            {{ 'abra la función de herramientas de marketing en su panel de administración y siga los pasos:' }}
                                        </p>
                                        <ol class="d-flex flex-column gap-2 opacity-75">
                                            <li>
                                                {{ 'navegue hasta la sección de identificación de medición de Google Analytics en herramientas de marketing.' }}
                                            </li>
                                            <li>
                                                {{ 'encienda el botón de alternancia.' }}
                                            </li>
                                            <li>
                                                {{ 'pegue su ID de medición de Google Analytics en el cuadro de entrada y haga clic en enviar.' }}
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
</div>
