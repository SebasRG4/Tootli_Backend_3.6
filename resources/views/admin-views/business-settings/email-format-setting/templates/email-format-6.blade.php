<?php
$company_name = App\Models\BusinessSetting::where('key', 'business_name')->first()->value;
?>
<table class="main-table">
    <tbody>
        <tr>
            <td class="main-table-td">
                <div class="text-center">
                <img class="mail-img-2 onerror-image" data-onerror-image="{{ asset('assets/admin/img/blank3.png') }}"

                src="{{ $data['icon_full_url'] ?? asset('assets/admin/img/blank3.png') }}"

                id="iconViewer" alt="">
                    <h2 id="mail-title" class="mt-2">{{ $data['title']?? 'Título principal o asunto del correo' }}</h2>
                    <div class="mb-2" id="mail-body">{!! $data['body']?? 'Hola sabrina,' !!}</div>
                </div>
                <table class="bg-section p-10 w-100 text-center">
                    <thead>
                        <tr>
                            <th>{{ 'SL' }}</th>
                            <th>{{ 'ID de transacción' }}</th>
                            <th>{{ 'Tiempo' }}</th>
                            <th>{{ 'Cantidad' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ '1' }}</td>
                            <td>{{ '23984357sd834' }}</td>
                            <td>{{ '23 julio 2023 3:34 am' }}</td>
                            <td>{{ '$500' }}</td>
                        </tr>
                    </tbody>
                </table>
                <span class="d-block text-center" class="email-template-06-span">
                    <a href="" class="cmn-btn" id="mail-button">{{ $data['button_name']??'View Request' }}</a>
                </span>
                <hr>
                <div class="mb-2" id="mail-footer">
                    {{ $data['footer_text'] ?? 'Comuníquese con nosotros para cualquier consulta, siempre estaremos encantados de ayudarle.' }}
                </div>
                <div>
                    {{ 'Gracias y saludos' }},
                </div>
                <div class="mb-4">
                    {{ $company_name }}
                </div>
            </td>
        </tr>
        <tr>
            <td>
            <span class="privacy">
                <a href="#" id="privacy-check" style="{{ (isset($data['privacy']) && $data['privacy'] == 1)?'':'display:none;' }}"><span class="dot"></span>{{ 'política de privacidad'}}</a>
                <a href="#" id="refund-check" style="{{ (isset($data['refund']) && $data['refund'] == 1)?'':'display:none;' }}"><span class="dot"></span>{{ 'Política de reembolso' }}</a>
                <a href="#" id="cancelation-check" style="{{ (isset($data['cancelation']) && $data['cancelation'] == 1)?'':'display:none;' }}"><span class="dot"></span>{{ 'Política de Cancelación' }}</a>
                <a href="#" id="contact-check" style="{{ (isset($data['contact']) && $data['contact'] == 1)?'':'display:none;' }}"><span class="dot"></span>{{ 'Contáctenos' }}</a>
            </span>
                <span class="social email-template-social-span">
                    <a href="" id="facebook-check" class="email-template-social-media" style="{{ (isset($data['facebook']) && $data['facebook'] == 1)?'':'display:none;' }}">
                        <img src="{{asset('assets/admin/img/img/facebook.png')}}" alt="">
                    </a>
                    <a href="" id="instagram-check" class="email-template-social-media" style="{{ (isset($data['instagram']) && $data['instagram'] == 1)?'':'display:none;' }}">
                        <img src="{{asset('assets/admin/img/img/instagram.png')}}" alt="">
                    </a>
                    <a href="" id="twitter-check" class="email-template-social-media" style="{{ (isset($data['twitter']) && $data['twitter'] == 1)?'':'display:none;' }}">
                        <img src="{{asset('assets/admin/img/img/twitter.png')}}" alt="">
                    </a>
                    <a href="" id="linkedin-check" class="email-template-social-media" style="{{ (isset($data['linkedin']) && $data['linkedin'] == 1)?'':'display:none;' }}">
                        <img src="{{asset('assets/admin/img/img/linkedin.png')}}" alt="">
                    </a>
                    <a href="" id="pinterest-check" class="email-template-social-media" style="{{ (isset($data['pinterest']) && $data['pinterest'] == 1)?'':'display:none;' }}">
                        <img src="{{asset('assets/admin/img/img/pinterest.png')}}" alt="">
                    </a>
                </span>
                <span class="copyright" id="mail-copyright">
                    {{ $data['copyright_text']?? 'Copyright 2023 6ammart. Todos los derechos reservados' }}
                </span>
            </td>
        </tr>
    </tbody>
</table>
<script src="{{asset('assets/admin')}}/js/view-pages/common.js"></script>
