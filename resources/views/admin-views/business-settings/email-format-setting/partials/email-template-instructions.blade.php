<div class="modal fade" id="instructions">
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
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/1.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Seleccionar tema'}}</h5>
                                <p>
                                    {{ 'Elija un tema de plantilla de correo electrónico relacionado con el propósito para el cual está creando el correo electrónico.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/5.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Elija logotipo'}}</h5>
                                <p>
                                    {{'Cargue el logotipo de su empresa en formato 1:1. Esto se mostrará encima del título principal del correo electrónico.'}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/2.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Escribe un título'}}</h5>
                                <p>
                                    {{'Asigne a su correo electrónico un "Título atractivo" para ayudar al lector a comprenderlo fácilmente.'}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/3.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Escribe un mensaje en el cuerpo del correo electrónico.'}}</h5>
                            </div>
                            <p>
                                {{ 'puede agregar su mensaje usando marcadores de posición para incluir contenido dinámico. A continuación se muestran algunos ejemplos de marcadores de posición que puede utilizar:' }}
                            </p>
                            <ul>
                                <li>
                                    {userName}: {{ 'el nombre del usuario.' }}
                                </li>
                                <li>
                                    {deliveryManName}: {{ 'el nombre del repartidor.' }}
                                </li>
                                <li>
                                    {storeName}: {{ 'el nombre de la tienda.' }}
                                </li>
                                <li>
                                    {orderId}: {{ 'la identificación del pedido.' }}
                                </li>
                                <li>
                                    {transactionId}: {{ 'la identificación de la transacción.' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/4.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Agregar botón y enlace'}}</h5>
                                <p>
                                    {{'Especifique el texto y la URL del botón que desea incluir en su correo electrónico.'}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/5.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Cambie la imagen del banner si es necesario'}}</h5>
                                <p>
                                    {{'Elija la imagen de banner relevante para el tema de correo electrónico que utiliza para este correo.'}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/6.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Agregar contenido al pie de página del correo electrónico'}}</h5>
                                <p>
                                    {{'Escriba texto en la sección de pie de página del correo electrónico y elija enlaces de páginas importantes y enlaces de redes sociales.'}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/7.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Crear un aviso de derechos de autor'}}</h5>
                                <p>
                                    {{'Incluya un aviso de derechos de autor en la parte inferior de su correo electrónico para proteger su contenido.'}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="mb-20">
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/email-templates/8.png')}}" alt="" class="mb-20">
                                <h5 class="modal-title">{{'Guardar y publicar'}}</h5>
                                <p>
                                    {{'Una vez que haya configurado todos los elementos de su plantilla de correo electrónico, guárdela y publíquela para usarla.'}}
                                </p>
                                <button class="btn btn--primary w-100 mw-300px" data-dismiss="modal" type="button">{{'Entendido'}}</button>
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
