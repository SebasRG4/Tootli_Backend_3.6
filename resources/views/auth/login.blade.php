<!DOCTYPE html>
<?php
    $log_email_succ = session()->get('log_email_succ');
?>

<html dir="{{ $site_direction }}" lang="{{ $locale }}" class="{{ $site_direction === 'rtl'?'active':'' }}">
<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Title -->
    <title>{{translate('messages.login')}}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{asset('favicon.ico')}}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{asset('assets/admin')}}/css/vendor.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tio-bootstrap-bridge.css') }}?v=1.1">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/theme.minc619.css?v=1.0')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin')}}/css/toastr.css">

    <style>
        :root {
            --primary-green: #006837;
            --primary-green-hover: #00502a;
            --bg-page: #f4f6f5;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            margin: 0;
        }

        .new-admin-login-layout {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            justify-content: space-between;
        }

        /* Top Header */
        .admin-login-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 3rem;
            background-color: transparent;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none !important;
        }

        .admin-brand img {
            height: 38px;
            width: auto;
            object-fit: contain;
            border-radius: 8px;
        }

        .admin-brand-text {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary-green);
            letter-spacing: -0.02em;
        }

        .admin-header-badge {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* Main Form Container */
        .admin-login-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }

        .admin-login-card {
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }

        .admin-section-tag {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--primary-green);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            display: block;
        }

        .admin-login-title {
            font-size: 3.25rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0d1727;
            margin-bottom: 0.75rem;
            letter-spacing: -0.03em;
        }

        .admin-login-subtitle {
            font-size: 1.05rem;
            color: #64748b;
            font-weight: 400;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        /* Form Controls */
        .form-group-custom {
            margin-bottom: 1.25rem;
        }

        .custom-input-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
            display: block;
        }

        .custom-input-label .required-star {
            color: #ef4444;
            margin-left: 2px;
        }

        .input-pill-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-pill {
            width: 100%;
            height: 52px;
            border-radius: 50px !important;
            border: 1.5px solid #e2e8f0;
            background-color: #ffffff;
            padding: 0.75rem 3rem 0.75rem 1.35rem;
            font-size: 0.98rem;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .input-pill:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 4px rgba(0, 104, 55, 0.1);
            background-color: #ffffff;
        }

        .input-pill-icon {
            position: absolute;
            right: 1.25rem;
            color: #64748b;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }

        /* Checkbox & Forgot Link */
        .form-options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.25rem;
            margin-bottom: 1.75rem;
            font-size: 0.9rem;
        }

        .remember-checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #475569;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
            margin-bottom: 0;
        }

        .remember-checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1.5px solid #cbd5e1;
            accent-color: var(--primary-green);
            cursor: pointer;
        }

        .forgot-password-link {
            color: var(--primary-green);
            font-weight: 600;
            text-decoration: none !important;
            transition: color 0.2s ease;
            cursor: pointer;
        }

        .forgot-password-link:hover {
            color: var(--primary-green-hover);
            text-decoration: underline !important;
        }

        /* Submit Button */
        .btn-pill-submit {
            width: 100%;
            height: 52px;
            border-radius: 50px;
            background-color: var(--primary-green);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 700;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 10px 20px -5px rgba(0, 104, 55, 0.35);
        }

        .btn-pill-submit:hover {
            background-color: var(--primary-green-hover);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -5px rgba(0, 104, 55, 0.45);
            color: #ffffff;
        }

        .btn-pill-submit:active {
            transform: translateY(0);
        }

        /* Footer */
        .admin-login-footer {
            padding: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 500;
        }

        @media (max-width: 640px) {
            .admin-login-header {
                padding: 1rem 1.5rem;
            }

            .admin-login-title {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>
<div class="new-admin-login-layout">
    <!-- Top Header -->
    <header class="admin-login-header">
        <a href="#" class="admin-brand">
            @php($store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
            <img class="onerror-image" data-onerror-image="{{asset('assets/admin/img/favicon.png')}}"
                 src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $store_logo?->storage[0]?->value ?? 'public','favicon')}}" alt="Tootli">
            <span class="admin-brand-text">{{ \App\Models\BusinessSetting::where(['key' => 'business_name'])->first()?->value ?? 'Tootli' }}</span>
        </a>
        <span class="admin-header-badge">{{ translate(ucfirst($role ?? 'Admin')) }}</span>
    </header>

    <!-- Main Content -->
    <main class="admin-login-body">
        <div class="admin-login-card">
            <span class="admin-section-tag">{{ translate(strtoupper($role ?? 'admin')) }} ACCESS</span>
            <h1 class="admin-login-title">{{ translate('Log in') }}</h1>
            <p class="admin-login-subtitle">{{ translate('Use your admin account to access the dashboard.') }}</p>

            <form action="{{route('login_post')}}" method="post" id="form-id" class="js-validate">
                @csrf
                <input type="hidden" name="role" value="{{ $role ?? null }}">

                <!-- Email Input -->
                <div class="form-group-custom js-form-message">
                    <label class="custom-input-label" for="signinSrEmail">
                        {{translate('Email')}} <span class="required-star">*</span>
                    </label>
                    <div class="input-pill-wrapper">
                        <input type="email" class="input-pill" name="email" id="signinSrEmail"
                               tabindex="1" placeholder="admin@demo.com" value="{{ $email ?? '' }}" aria-label="admin@demo.com"
                               required data-msg="{{ translate('Please_enter_a_valid_email_address.') }}">
                        <span class="input-pill-icon"><i class="bi bi-key-fill"></i></span>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group-custom js-form-message">
                    <label class="custom-input-label" for="signupSrPassword">
                        {{translate('Password')}} <span class="required-star">*</span>
                    </label>
                    <div class="input-pill-wrapper">
                        <input type="password" class="input-pill js-toggle-password"
                               name="password" id="signupSrPassword" placeholder="••••••••" value="{{ $password ?? '' }}"
                               aria-label="••••••••" required
                               data-msg="{{translate('messages.invalid_password_warning')}}"
                               data-hs-toggle-password-options='{
                                   "target": "#changePassTarget",
                                   "defaultClass": "bi-eye-slash-fill",
                                   "showClass": "bi-eye-fill",
                                   "classChangeTarget": "#changePassIcon"
                               }'>
                        <button type="button" id="changePassTarget" class="input-pill-icon">
                            <i id="changePassIcon" class="bi bi-eye-slash-fill"></i>
                        </button>
                    </div>
                </div>

                <!-- Options Row -->
                <div class="form-options-row">
                    <label class="remember-checkbox-label" for="termsCheckbox">
                        <input type="checkbox" id="termsCheckbox" name="remember" {{ $password ? 'checked' : '' }}>
                        <span>{{translate('Remember me')}}</span>
                    </label>

                    <div id="forget-password" style="display: {{ $role == 'admin' ? '' : 'none' }};">
                        <span type="button" data-toggle="modal" class="forgot-password-link" data-target="#forgetPassModal">{{ translate('Forgot password?') }}</span>
                    </div>
                    <div id="forget-password1" style="display: {{ $role == 'vendor' ? '' : 'none' }};">
                        <span type="button" data-toggle="modal" class="forgot-password-link" data-target="#forgetPassModal1">{{ translate('messages.Forget Password') }}?</span>
                    </div>
                </div>

                <!-- Honeypot anti-bot field -->
                <div style="position: absolute; left: -9999px; opacity: 0; height: 0; width: 0; overflow: hidden;" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off" placeholder="Leave this field empty">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-pill-submit" id="signInBtn">
                    <span>{{translate('Log in')}}</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            @if(env('APP_MODE') == 'demo')
                @if (isset($role) && $role == 'admin')
                <div class="auto-fill-data-copy mt-4 p-3 bg-white rounded border">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <span class="d-block"><strong>Email</strong> : admin@admin.com</span>
                            <span class="d-block"><strong>Password</strong> : 12345678</span>
                        </div>
                        <div>
                            <button class="btn action-btn btn--primary m-0 copy_cred"><i class="tio-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                @if (isset($role) && $role == 'vendor')
                <div class="auto-fill-data-copy mt-4 p-3 bg-white rounded border">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <span class="d-block"><strong>Email</strong> : test.restaurant@gmail.com</span>
                            <span class="d-block"><strong>Password</strong> : 12345678</span>
                        </div>
                        <div>
                            <button class="btn action-btn btn--primary m-0 copy_cred2"><i class="tio-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="admin-login-footer">
        © {{ date('Y') }} {{ \App\Models\BusinessSetting::where(['key' => 'business_name'])->first()?->value ?? 'Tootli' }}. {{ translate('All rights reserved.') }}
    </footer>
</div>

<!-- Modals -->
<div class="modal fade" id="forgetPassModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header justify-content-end">
        <span type="button" class="close-modal-icon" data-dismiss="modal">
            <i class="tio-clear"></i>
        </span>
      </div>
      <div class="modal-body">
        <div class="forget-pass-content">
            <img src="{{asset('assets/admin/img/send-mail.svg')}}" alt="">
            <h4>
                {{ translate('Send_Mail_to_Your_Email') }} ?
            </h4>
            <p>
                {{ translate('A mail will be send to your registered email') }} {{ isset($role) && $role == 'admin'  ? \App\Models\Admin::where('role_id',1)->first()?->masked_email : ''  }} {{ translate('with a  link to change passowrd') }}
            </p>
            <a class="btn btn-lg btn-block btn--primary mt-3" href="{{route('reset-password')}}">
                {{ translate('Send Mail') }}
            </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="forgetPassModal1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header justify-content-end">
        <span type="button" class="close-modal-icon" data-dismiss="modal">
            <i class="tio-clear"></i>
        </span>
      </div>
      <div class="modal-body">
        <div class="forget-pass-content">
            <img src="{{asset('assets/admin/img/send-mail.svg')}}" alt="">
            <h4>
                {{ translate('messages.Send_Mail_to_Your_Email') }} ?
            </h4>
            <form class="" action="{{ route('vendor-reset-password') }}" method="post">
                @csrf
                <input type="email" name="email" id="" class="form-control" placeholder="{{ translate('messages.plesae_enter_your_registerd_email') }}" required>
                <button type="submit" class="btn btn-lg btn-block btn--primary mt-3">{{ translate('messages.Send Mail') }}</button>
            </form>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="successMailModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header justify-content-end">
          <span type="button" class="close-modal-icon" data-dismiss="modal">
              <i class="tio-clear"></i>
          </span>
        </div>
        <div class="modal-body">
          <div class="forget-pass-content">
              <img src="{{asset('assets/admin/img/sent-mail.svg')}}" alt="">
              <h4>
                {{ translate('A mail has been sent to your registered email') }}!
              </h4>
              <p>
                {{ translate('Click the link in the mail description to change password') }}
              </p>
              <button class="btn btn-lg btn-block btn--primary mt-3" data-dismiss="modal">
                {{ translate('Got_It') }}
              </button>
          </div>
        </div>
      </div>
    </div>
</div>

<!-- JS Implementing Plugins -->
<script src="{{asset('assets/admin')}}/js/vendor.min.js"></script>
<!-- JS Front -->
<script src="{{asset('assets/admin')}}/js/theme.min.js"></script>
<script src="{{asset('assets/admin')}}/js/toastr.js"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        "use strict";
        @foreach($errors->all() as $error)
        toastr.error('{{translate($error)}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif
@if ($log_email_succ)
@php(session()->forget('log_email_succ'))
    <script>
        "use strict";
        $('#successMailModal').modal('show');
    </script>
@endif

<script>
    "use strict";
    $("#role-select").change(function() {
        var selectValue = $(this).val();
        if (selectValue == "admin") {
            $("#forget-password").show();
            $("#forget-password1").hide();
        } else if(selectValue == "vendor") {
            $("#forget-password").hide();
            $("#forget-password1").show();
        } else {
            $("#forget-password").hide();
            $("#forget-password1").hide();
        }
    });

    $(document).on('ready', function () {
        $('.js-toggle-password').each(function () {
            new HSTogglePassword(this).init()
        });

        $('.js-validate').each(function () {
            $.HSCore.components.HSValidation.init($(this));
        });
    });

    $(document).on('click','.reloadCaptcha', function(){
        $.ajax({
            url: "{{ route('reload-captcha') }}",
            type: "GET",
            dataType: 'json',
            beforeSend: function () {
                $('#loading').show()
                $('.capcha-spin').addClass('active')
            },
            success: function(data) {
                $('#reload-captcha').html(data.view);
            },
            complete: function () {
                $('#loading').hide()
                $('.capcha-spin').removeClass('active')
            }
        });
    });

    $(document).ready(function() {
        $('.onerror-image').on('error', function() {
            let img = $(this).data('onerror-image')
            $(this).attr('src', img);
        });
    });
</script>

@if(isset($recaptcha) && $recaptcha['status'] == 1)
    <script src="https://www.google.com/recaptcha/api.js?render={{$recaptcha['site_key']}}"></script>
    <script>
        $(document).ready(function() {
            $('#signInBtn').click(function (e) {
                if( $('#set_default_captcha_value').val() == 1){
                    $('#form-id').submit();
                    return true;
                }
                e.preventDefault();
                if (typeof grecaptcha === 'undefined') {
                    toastr.error('Invalid recaptcha key provided. Please check the recaptcha configuration.');
                    $('#reload-captcha').removeClass('d-none');
                    $('#set_default_captcha_value').val('1');
                    return;
                }
                grecaptcha.ready(function () {
                    grecaptcha.execute('{{$recaptcha['site_key']}}', {action: 'submit'}).then(function (token) {
                        $('#g-recaptcha-response').value = token;
                        $('#form-id').submit();
                    });
                });
                window.onerror = function (message) {
                    var errorMessage = 'An unexpected error occurred. Please check the recaptcha configuration';
                    if (message.includes('Invalid site key')) {
                        errorMessage = 'Invalid site key provided. Please check the recaptcha configuration.';
                    } else if (message.includes('not loaded in api.js')) {
                        errorMessage = 'reCAPTCHA API could not be loaded. Please check the recaptcha API configuration.';
                    }
                    $('#reload-captcha').removeClass('d-none');
                    $('#set_default_captcha_value').val('1');
                    toastr.error(errorMessage)
                    return true;
                };
            });
        });
    </script>
@endif

@if(env('APP_MODE')=='demo')
    <script>
        "use strict";
        $('.copy_cred').on('click', function () {
            $('#signinSrEmail').val('admin@admin.com');
            $('#signupSrPassword').val('12345678');
            toastr.success('Copied successfully!', 'Success!', {
                CloseButton: true,
                ProgressBar: true
            });
        })
        $('.copy_cred2').on('click', function () {
            $('#signinSrEmail').val('test.restaurant@gmail.com');
            $('#signupSrPassword').val('12345678');
            toastr.success('Copied successfully!', 'Success!', {
                CloseButton: true,
                ProgressBar: true
            });
        })
    </script>
@endif

<script>
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
</script>
</body>
</html>
