@extends('layouts.app')

@section('content')
    <div class="page-wrapper">
        <!-- <div class="row page-titles"> -->
        <div class="card">
            <div class="payment-top-tab mt-3 mb-3">
                <ul class="nav nav-tabs card-header-tabs align-items-end">
                    <li class="nav-item">
                        <a class="nav-link  stripe_active_label" href="{!! url('settings/payment/stripe') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_stripe')}}<span
                                    class="badge ml-2"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active cod_active_label" href="{!! url('settings/payment/cod') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_cod_short')}}<span
                                    class="badge ml-2"></span>
                        </a>
                    </li>
                   {{-- <li class="nav-item">
                        <a class="nav-link apple_pay_active_label" href="{!! url('settings/payment/applepay') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_apple_pay')}}<span
                                    class="badge ml-2"></span>
                        </a>
                    </li> --}}
                    <li class="nav-item">
                        <a class="nav-link razorpay_active_label" href="{!! url('settings/payment/razorpay') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_razorpay')}}<span
                                    class="badge ml-2"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link paypal_active_label" href="{!! url('settings/payment/paypal') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paypal')}}<span
                                    class="badge ml-2"></span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link paytm_active_label" href="{!! url('settings/payment/paytm') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paytm')}}<span
                                    class="badge ml-2"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link wallet_active_label" href="{!! url('settings/payment/wallet') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_wallet')}}<span
                                    class="badge ml-2"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link payfast_active_label" href="{!! url('settings/payment/payfast') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.payfast')}}<span
                                    class="badge ml-2"></span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link paystack_active_label" href="{!! url('settings/payment/paystack') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.app_setting_paystack_lable')}}<span
                                    class="badge ml-2"></span></a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link flutterWave_active_label" href="{!! url('settings/payment/flutterwave') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.flutterWave')}}<span
                                    class="badge ml-2"></span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mercadopago_active_label" 
                        href="{!! url('settings/payment/mercadopago') !!}"><i
                                    class="fa fa-envelope-o mr-2"></i>{{trans('lang.mercadopago')}}<span
                                    class="badge ml-2"></span></a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div id="data-table_processing" class="dataTables_processing panel panel-default"
                     style="display: none;">Processing...
                </div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend><i class="mr-3 fa fa-money"></i>{{trans('lang.app_setting_cod_short')}}</legend>
                            <div class="form-check width-100">
                                <input type="checkbox" class=" enable_cod" id="enable_cod">
                                <label class="col-3 control-label"
                                       for="enable_cod">{{trans('lang.app_setting_enable_cod')}}</label>
                                <div class="form-text text-muted">
                                    {!! trans('lang.app_settings_enable_cod_help') !!}
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary save_cod_btn"><i
                            class="fa fa-save"></i> {{trans('lang.save')}}</button>
                <a href="{{url('/dashboard')}}" class="btn btn-default"><i
                            class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
            </div>

        </div>
    </div>

@endsection

@section('scripts')

    <script>
        var database = firebase.firestore();
        var stripeData = database.collection('settings').doc('stripeSettings');
        var ref = database.collection('settings').doc('CODSettings');
        var applePayData = database.collection('settings').doc('applePay');
        var razorpayData = database.collection('settings').doc('razorpaySettings');
        var paypalData = database.collection('settings').doc('paypalSettings');
        var paytmData = database.collection('settings').doc('PaytmSettings');
        var walletData = database.collection('settings').doc('walletSettings');
        var payFastSettings = database.collection('settings').doc('payFastSettings');

        var payStackSettings = database.collection('settings').doc('payStack');
        var flutterWaveSettings = database.collection('settings').doc('flutterWave');
        var MercadopagoSettings = database.collection('settings').doc('MercadoPago');


        $(document).ready(function () {
            jQuery("#data-table_processing").show();
            ref.get().then(async function (snapshots) {
                var cod = snapshots.data();

                if (cod == undefined) {
                    database.collection('settings').doc('CODSettings').set({}).then(function (result) {
                        location.reload();
                    });
                }

                try {
                    if (cod.isEnabled) {
                        $(".enable_cod").prop('checked', true);
                        jQuery(".cod_active_label span").addClass('badge-success');
                        jQuery(".cod_active_label span").text('Active');
                    }

                    stripeData.get().then(async function (stripeSnapshots) {
                        var stripe = stripeSnapshots.data();
                        if (stripe.isEnabled) {
                            jQuery(".stripe_active_label span").addClass('badge-success');
                            jQuery(".stripe_active_label span").text('Active');
                        }
                    })

                    applePayData.get().then(async function (applePaySnapshots) {
                        var applePay = applePaySnapshots.data();
                        if (applePay.isEnabled) {
                            jQuery(".apple_pay_active_label span").addClass('badge-success');
                            jQuery(".apple_pay_active_label span").text('Active');
                        }
                    })

                    razorpayData.get().then(async function (razorpaySnapshots) {
                        var razorPay = razorpaySnapshots.data();
                        if (razorPay.isEnabled) {
                            jQuery(".razorpay_active_label span").addClass('badge-success');
                            jQuery(".razorpay_active_label span").text('Active');
                        }
                    })

                    paypalData.get().then(async function (paypalSnapshots) {
                        var paypal = paypalSnapshots.data();
                        if (paypal.isEnabled) {
                            jQuery(".paypal_active_label span").addClass('badge-success');
                            jQuery(".paypal_active_label span").text('Active');
                        }
                    })
                    paytmData.get().then(async function (codSnapshots) {
                        var paytm = codSnapshots.data();
                        if (paytm.isEnabled) {
                            jQuery(".paytm_active_label span").addClass('badge-success');
                            jQuery(".paytm_active_label span").text('Active');
                        }
                    })

                    walletData.get().then(async function (walletSnapshots) {
                        var wallet = walletSnapshots.data();
                        if (wallet.isEnabled) {
                            jQuery(".wallet_active_label span").addClass('badge-success');
                            jQuery(".wallet_active_label span").text('Active');
                        }
                    })

                    payFastSettings.get().then(async function (payFastSnapshots) {
                        var payFast = payFastSnapshots.data();
                        if (payFast.isEnable) {
                            jQuery(".payfast_active_label span").addClass('badge-success');
                            jQuery(".payfast_active_label span").text('Active');
                        }
                    })

                    payStackSettings.get().then(async function (payStackSnapshots) {
                        var payStack = payStackSnapshots.data();
                        if (payStack.isEnable) {
                            jQuery(".paystack_active_label span").addClass('badge-success');
                            jQuery(".paystack_active_label span").text('Active');
                        }
                    })


                    flutterWaveSettings.get().then(async function (flutterWaveSnapshots) {
                        var flutterWave = flutterWaveSnapshots.data();
                        if (flutterWave.isEnable) {
                            jQuery(".flutterWave_active_label span").addClass('badge-success');
                            jQuery(".flutterWave_active_label span").text('Active');
                        }
                    })
                    MercadopagoSettings.get().then(async function (mercadopagoSnapshots) {
                        var mercadopago = mercadopagoSnapshots.data();
                        if (mercadopago.isEnabled) {
                            jQuery(".mercadopago_active_label span").addClass('badge-success');
                            jQuery(".mercadopago_active_label span").text('Active');
                        }

                    })
                } catch (error) {

                }

                jQuery("#data-table_processing").hide();
            })

            $(".save_cod_btn").click(function () {

                var isCODEnabled = $(".enable_cod").is(":checked");
                database.collection('settings').doc("CODSettings").update({'isEnabled': isCODEnabled}).then(function (result) {

                    window.location.href = '{{ url("settings/payment/cod")}}';

                });

            })

        })

    </script>

@endsection