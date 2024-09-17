@extends('layouts.app')


@section('content')
<div class="page-wrapper">


    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.store_payment_plural')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.store_payment_plural')}}</li>
            </ol>
        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">
        <div id="data-table_processing" class="dataTables_processing panel panel-default"
                                 style="display: none;">{{ trans('lang.processing')}}
        </div>

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        <div class="table-responsive m-t-10">


                            <table id="paymentTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">

                                <thead>

                                <tr>
                                    <th>{{ trans('lang.restaurant')}}</th>
                                    <th>{{ trans('lang.total_amount')}}</th>
                                    <th>{{trans('lang.paid_amount')}}</th>
                                    <th>{{trans('lang.remaining_amount')}}</th>
                                </tr>

                                </thead>

                                <tbody id="append_list1">


                                </tbody>

                            </table>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@section('scripts')

<script>

    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var ref = database.collection('vendors').orderBy('title');

    var append_list = '';

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    var refCurrency = database.collection('currencies').where('isActive', '==', true);
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;

        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });


    $(document).ready(function () {

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();

        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';
        ref.get().then(async function (snapshots) {
            html = '';

            html = await buildHTML(snapshots);
            jQuery("#data-table_processing").hide();
            if (html != '') {
                append_list.innerHTML = html;
                start = snapshots.docs[snapshots.docs.length - 1];
                endarray.push(snapshots.docs[0]);
                if (snapshots.docs.length < pagesize) {
                    jQuery("#data-table_paginate").hide();
                }
            }

            $('#paymentTable').DataTable({
                order: [['0', 'asc']],

                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}"
                },
                responsive: true
            });

        });

    });

    async function buildHTML(snapshots) {
        var html = '';

        await Promise.all(snapshots.docs.map(async (listval) => {

            var val = listval.data();

            var getData = await getPaymentListData(val);
            html += getData;
        }));

        return html;
    }

    async function getPaymentListData(val) {
        var html = '';

        html = html + '<tr>';
        newdate = '';
        var id = val.id;
        var route1 = '{{route("stores.view",":id")}}';
        route1 = route1.replace(':id', id);

        html = html + '<td data-url="' + route1 + '" class="redirecttopage ">' + val.title + '</td>';

        var data = await remainingPrice(val.id);

        var total_class = 'text-green';
        var paid_price_val_class = 'text-red';
        var remaining_val_class = 'text-green';

        if (currencyAtRight) {

            if (data.total < 0) {
                total_class = 'text-red';

                total = Math.abs(data.total);
                data.total = '(-' + parseFloat(total).toFixed(decimal_degits) + "" + currentCurrency + ')';

            } else {
                data.total = parseFloat(data.total).toFixed(decimal_degits) + "" + currentCurrency;

            }

            
            paid_price_val = Math.abs(data.paid_price_val);
            data.paid_price_val = '(' + parseFloat(paid_price_val).toFixed(decimal_degits) + "" + currentCurrency + ')';
            


            if (data.remaining_val < 0) {
                remaining_val_class = 'text-red';
                remaining_val = Math.abs(data.remaining_val);
                data.remaining_val = '(-' + parseFloat(remaining_val).toFixed(decimal_degits) + "" + currentCurrency + ')';
            } else {
                data.remaining_val = parseFloat(data.remaining_val).toFixed(decimal_degits) + "" + currentCurrency;

            }
        } else {

            if (data.total < 0) {
                total_class = 'text-red';

                total = Math.abs(data.total);
                data.total = '(-' + currentCurrency + "" + parseFloat(total).toFixed(decimal_degits) + ')';

            } else {
                data.total = currentCurrency + "" + parseFloat(data.total).toFixed(decimal_degits);

            }

            paid_price_val = Math.abs(data.paid_price_val);
            data.paid_price_val = '(' + currentCurrency + "" + parseFloat(paid_price_val).toFixed(decimal_degits) + ')';
            

            if (data.remaining_val < 0) {
                remaining_val_class = 'text-red';

                remaining_val = Math.abs(data.remaining_val);

                data.remaining_val = '(-' + currentCurrency + "" + parseFloat(remaining_val).toFixed(decimal_degits) + ')';

            } else {
                data.remaining_val = currentCurrency + "" + parseFloat(data.remaining_val).toFixed(decimal_degits);

            }


        }


        html = html + '<td class="' + total_class + '">' + data.total + '</td>';
        html = html + '<td class="' + paid_price_val_class + '">' + data.paid_price_val + '</td>';
        html = html + '<td class="' + remaining_val_class + '">' + data.remaining_val + '</td>';
        html = html + '</tr>';

        return html;
    }

    async function remainingPrice(vendorID) {

        var data = {};

        var paid_price = 0;

        var total_price = 0;

        var remaining = 0;

        var adminCommission = 0;

        await database.collection('payouts').where('vendorID', '==', vendorID).where('paymentStatus', '==', 'Success').get().then(async function (payoutSnapshots) {

            payoutSnapshots.docs.forEach((payout) => {

                var payoutData = payout.data();

                paid_price = parseFloat(paid_price) + parseFloat(payoutData.amount);

            });

            await database.collection('users').where('vendorID', '==', vendorID).get().then(async function (vendorSnapshots) {
                var vendor = [];
                var wallet_amount = 0;
                if (vendorSnapshots.docs.length) {
                    vendor = vendorSnapshots.docs[0].data();

                    if (isNaN(vendor.wallet_amount) || vendor.wallet_amount == undefined || vendor.wallet_amount == "") {
                        wallet_amount = 0;
                    } else {
                        wallet_amount = vendor.wallet_amount;
                    }

                }

                var remaining = wallet_amount;

                total_price = wallet_amount + paid_price;

                if (Number.isNaN(paid_price)) {
                    paid_price = 0;
                }

                if (Number.isNaN(total_price)) {
                    total_price = 0;
                }

                if (Number.isNaN(remaining)) {
                    remaining = 0;
                }

                // jQuery(".total_" + vendorID).html(total_price_val);
                //
                // jQuery(".name_" + vendorID).html('(' + paid_price_val + ')');
                //
                // jQuery(".remaining_" + vendorID).html(remaining_val);

                data = {
                    'total': total_price,
                    'paid_price_val': paid_price,
                    'remaining_val': remaining,
                };
            });

        });

        return data;

    }

    async function remainingPriceOLD(vendorID) {
        var paid_price = 0;
        var total_price = 0;
        var remaining = 0;
        var adminCommission = 0;

        await database.collection('payouts').where('vendorID', '==', vendorID).get().then(async function (payoutSnapshots) {

            payoutSnapshots.docs.forEach((payout) => {
                var payoutData = payout.data();
                paid_price = parseFloat(paid_price) + parseFloat(payoutData.amount);
            })

            await database.collection('restaurant_orders').where('vendor.id', '==', vendorID).where("status", "in", ["Order Completed"]).get().then(async function (orderSnapshots) {

                orderSnapshots.docs.forEach((order) => {
                    var orderData = order.data();
                    var mainproductTotal = 0;
                    orderData.products.forEach((product) => {
                        if (product.price && product.quantity != 0) {

                            var extras_price = 0;
                            if (product.extras_price != undefined) {
                                extras_price = parseFloat(product.extras_price) * parseInt(product.quantity);
                            }
                            var productTotal = parseFloat(product.price) * parseInt(product.quantity) + extras_price;
                            total_price = total_price + productTotal;
                            mainproductTotal = mainproductTotal + productTotal;
                        }


                    })

                    tax = 0;
                    if (orderData.hasOwnProperty('taxSetting')) {
                        if (orderData.taxSetting.type && orderData.taxSetting.tax) {
                            if (orderData.taxSetting.type == "percent") {
                                tax = (orderData.taxSetting.tax * mainproductTotal) / 100;
                            } else {
                                tax = orderData.taxSetting.tax;
                            }
                        }
                    }

                    if (!isNaN(tax)) {
                        mainproductTotal = parseFloat(mainproductTotal) + parseFloat(tax);
                    }

                    if (orderData.adminCommission != undefined && orderData.adminCommissionType != undefined && orderData.adminCommission > 0 && mainproductTotal > 0) {
                        var commission = 0;
                        if (orderData.adminCommissionType == "Percent") {
                            commission = (mainproductTotal * parseFloat(orderData.adminCommission)) / 100;

                        } else {
                            commission = parseFloat(orderData.adminCommission);
                        }

                        adminCommission = commission + adminCommission;
                    } else if (orderData.adminCommission != undefined && orderData.adminCommission > 0 && mainproductTotal > 0) {
                        var commission = parseFloat(orderData.adminCommission);
                        adminCommission = commission + adminCommission;
                    }

                })
                total_price = total_price - adminCommission; //deduct admin commission
                remaining = total_price - paid_price;
                if (Number.isNaN(paid_price)) {
                    paid_price = 0;
                }
                if (Number.isNaN(total_price)) {
                    total_price = 0;
                }
                if (Number.isNaN(remaining)) {
                    remaining = 0;
                }
                if (currencyAtRight) {

                    total_price_val = parseFloat(total_price).toFixed(decimal_degits) + "" + currentCurrency;

                    paid_price_val = parseFloat(paid_price).toFixed(decimal_degits) + "" + currentCurrency;

                    remaining_val = parseFloat(remaining).toFixed(decimal_degits) + "" + currentCurrency;

                } else {

                    total_price_val = currentCurrency + "" + parseFloat(total_price).toFixed(decimal_degits);

                    paid_price_val = currentCurrency + "" + parseFloat(paid_price).toFixed(decimal_degits);

                    remaining_val = currentCurrency + "" + parseFloat(remaining).toFixed(decimal_degits);

                }


                jQuery(".total_" + vendorID).html(total_price_val);
                jQuery(".name_" + vendorID).html(paid_price_val);
                jQuery(".remaining_" + vendorID).html(remaining_val);
                jQuery("#data-table_processing").hide();
            });
        });
        return remaining;
    }

</script>

@endsection
