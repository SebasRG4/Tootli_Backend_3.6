@extends('layouts.admin.app')

@section('title', translate('messages.edit_customer'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-edit"></i>
                </span>
                <span>
                    {{translate('messages.edit_customer')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.customer.update', [$customer['id']])}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.first_name')}}</label>
                                <input type="text" name="f_name" value="{{$customer['f_name']}}" class="form-control"
                                    placeholder="{{translate('messages.first_name')}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.last_name')}}</label>
                                <input type="text" name="l_name" value="{{$customer['l_name']}}" class="form-control"
                                    placeholder="{{translate('messages.last_name')}}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.email')}}</label>
                                <input type="email" name="email" value="{{$customer['email']}}" class="form-control"
                                    placeholder="{{translate('messages.email')}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.phone')}}</label>
                                <input type="text" name="phone" value="{{$customer['phone']}}" class="form-control"
                                    placeholder="{{translate('messages.phone')}}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('messages.referral_code')}}</label>
                                <input type="text" name="ref_code" value="{{$customer['ref_code']}}" class="form-control"
                                    placeholder="{{translate('messages.referral_code')}}" required>
                            </div>
                            <small
                                class="text-info">{{translate('messages.this_code_will_be_displayed_in_the_app_for_this_user')}}</small>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end mt-3">
                        <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection