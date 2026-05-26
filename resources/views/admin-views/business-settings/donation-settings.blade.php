@extends('layouts.admin.app')

@section('title', translate('Donation Settings'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/setting.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('Donation Settings')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        @php($donation_button_status=\App\Models\BusinessSetting::where(['key'=>'donation_button_status'])->first())
        @php($donation_button_status=$donation_button_status?$donation_button_status->value:0)

        @php($donation_button_image=\App\Models\BusinessSetting::where(['key'=>'donation_button_image'])->first())
        @php($donation_button_image=$donation_button_image?$donation_button_image->value:'')

        <form action="{{route('admin.business-settings.donation-settings')}}" method="post"
        enctype="multipart/form-data">
        @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="donation_button_status">{{translate('Donation Button Status')}}</label>
                                <select name="donation_button_status" id="donation_button_status" class="form-control">
                                    <option value="1" {{$donation_button_status == 1 ? 'selected' : ''}}>{{translate('Active')}}</option>
                                    <option value="0" {{$donation_button_status == 0 ? 'selected' : ''}}>{{translate('Inactive')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{translate('Donation Button Image')}}</label>
                                <div class="custom-file">
                                    <input type="file" name="donation_button_image" id="donationFile" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="donationFile">{{translate('Choose File')}}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <center id="donation-image-viewer-section">
                                    <img class="upload-img-view onerror-image" id="donationViewer"
                                        data-onerror-image="{{asset('assets/admin/img/160x160/img2.jpg')}}"
                                        src="{{ \App\CentralLogics\Helpers::get_full_url('business', $donation_button_image, 'public', 'favicon') }}"
                                        alt=""/>
                                </center>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{translate('messages.submit')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
    <script>
        function readURL(input, viewer_id) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#'+viewer_id).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#donationFile").change(function () {
            readURL(this, 'donationViewer');
        });
    </script>
@endpush
