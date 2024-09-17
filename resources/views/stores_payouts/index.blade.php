@extends('layouts.app')


@section('content')
<div class="page-wrapper">


    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor restaurantTitle">{{trans('lang.restaurants_payout_plural')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.restaurants_payout_plural')}}</li>
            </ol>
        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">
        <div id="data-table_processing" class="dataTables_processing panel panel-default"
                             style="display: none;">{{trans('lang.processing')}}
         </div>

        <div class="row">

            <div class="col-12">
                <?php if ($id != '') { ?>
                    <div class="menu-tab">
                        <ul>
                            <li>
                                <a href="{{route('stores.view',$id)}}">{{trans('lang.tab_basic')}}</a>
                            </li>
                            <li>
                                <a href="{{route('stores.items',$id)}}">{{trans('lang.tab_items')}}</a>
                            </li>
                            <li>
                                <a href="{{route('stores.orders',$id)}}">{{trans('lang.tab_orders')}}</a>
                            </li>
                            <li>
                                <a href="{{route('stores.coupons',$id)}}">{{trans('lang.tab_promos')}}</a>
                            <li class="active">
                                <a href="{{route('stores.payout',$id)}}">{{trans('lang.tab_payouts')}}</a>
                            </li>
                            <li id="restaurant_wallet"></li>

                        </ul>
                    </div>
                <?php } ?>
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                            class="fa fa-list mr-2"></i>{{trans('lang.restaurants_payout_table')}}</a>
                            </li>

                            <?php if ($id != '') { ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! route('storesPayouts.create') !!}/{{$id}}"><i
                                                class="fa fa-plus mr-2"></i>{{trans('lang.restaurants_payout_create')}}</a>
                                </li>
                            <?php } else { ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! route('storesPayouts.create') !!}"><i
                                                class="fa fa-plus mr-2"></i>{{trans('lang.restaurants_payout_create')}}</a>
                                </li>
                            <?php } ?>


                        </ul>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive m-t-10">


                            <table id="restaurantPayoutTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">

                                <thead>

                                <tr>
                                    <?php if ($id == '') { ?>
                                        <th>{{ trans('lang.restaurant')}}</th>
                                    <?php } ?>
                                    <th>{{trans('lang.paid_amount')}}</th>
                                    <th>{{trans('lang.date')}}</th>
                                    <th>{{trans('lang.restaurants_payout_note')}}</th>
                                    <th>Admin {{trans('lang.restaurants_payout_note')}}</th>
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

    var intRegex = /^\d+$/;
    var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

    var getId = '{{$id}}';
    <?php if($id != ''){ ?>
    var refData = database.collection('payouts').where('vendorID', '==', '<?php echo $id; ?>').where('paymentStatus', '==', 'Success');
    var ref = refData.orderBy('paidDate', 'desc');

    const getStoreName = getStoreNameFunction('<?php echo $id; ?>');

    <?php }else{ ?>
    var refData = database.collection('payouts').where('paymentStatus', '==', 'Success');
    var ref = refData.orderBy('paidDate', 'desc');
    <?php } ?>

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

    var append_list = '';

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

            html = buildHTML(snapshots);

            if (html != '') {
                append_list.innerHTML = html;
                start = snapshots.docs[snapshots.docs.length - 1];
                endarray.push(snapshots.docs[0]);
                if (snapshots.docs.length < pagesize) {
                    jQuery("#data-table_paginate").hide();
                }

            }

            if (getId != '') {
                $('#restaurantPayoutTable').DataTable({
                    order: [],
                    columnDefs: [
                        {
                            targets: 1,
                            type: 'date',
                            render: function (data) {

                                return data;
                            }
                        },

                    ],
                    order: [['1', 'desc']],
                    "language": {
                        "zeroRecords": "{{trans("lang.no_record_found")}}",
                        "emptyTable": "{{trans("lang.no_record_found")}}"
                    },
                    responsive: true
                });
            } else {
                $('#restaurantPayoutTable').DataTable({
                    order: [],
                    columnDefs: [
                        {
                            targets: 2,
                            type: 'date',
                            render: function (data) {

                                return data;
                            }
                        },
                        {orderable: false, targets: [0]},

                    ],
                    order: [['2', 'desc']],
                    "language": {
                        "zeroRecords": "{{trans("lang.no_record_found")}}",
                        "emptyTable": "{{trans("lang.no_record_found")}}"
                    },
                    responsive: true
                });
            }

            if (snapshots.docs.length < pagesize) {
                jQuery("#data-table_paginate").hide();
            }

            jQuery("#data-table_processing").hide();
        });

    });

    async function getStoreNameFunction(vendorId) {
        var vendorName = '';
        await database.collection('vendors').where('id', '==', vendorId).get().then(async function (snapshots) {
            if(!snapshots.empty){
            var vendorData = snapshots.docs[0].data();

            vendorName = vendorData.title;
            $('.restaurantTitle').html('{{trans("lang.restaurants_payout_plural")}} - ' + vendorName);

            if (vendorData.dine_in_active == true) {
                $(".dine_in_future").show();
            }
            walletRoute = "{{route('users.walletstransaction',':id')}}";
            walletRoute = walletRoute.replace(":id", vendorData.author);
            $('#restaurant_wallet').append('<a href="' + walletRoute + '">{{trans("lang.wallet_transaction")}}</a>');

        }
        });

        return vendorName;

    }

    function buildHTML(snapshots) {
        var html = '';
        var alldata = [];
        var number = [];

        snapshots.docs.forEach((listval) => {
            var datas = listval.data();
            datas.id = listval.id;
            alldata.push(datas);
        });

        var count = 0;
        alldata.forEach((listval) => {

            var val = listval;
            var price_val = '';
            var price = val.amount;

            if (intRegex.test(price) || floatRegex.test(price)) {

                price = parseFloat(price).toFixed(2);
            } else {
                price = 0;
            }

            if (currencyAtRight) {
                price_val = parseFloat(price).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                price_val = currentCurrency + "" + parseFloat(price).toFixed(decimal_degits);
            }
            html = html + '<tr>';
            <?php if($id == ''){ ?>
            const restaurant = payoutRestaurant(val.vendorID);
            html = html + '<td class="restaurant_' + val.vendorID + ' redirecttopage" ></td>';
            <?php } ?>
            html = html + '<td class="text-red">(' + price_val + ')</td>';
            var date = val.paidDate.toDate().toDateString();
            var time = val.paidDate.toDate().toLocaleTimeString('en-US');
            html = html + '<td class="dt-time">' + date + ' ' + time + '</td>';

            if (val.note != undefined && val.note != '') {
                html = html + '<td>' + val.note + '</td>';
            } else {
                html = html + '<td></td>';
            }
            if (val.adminNote != undefined && val.adminNote != '') {
                html = html + '<td>' + val.adminNote + '</td>';
            } else {
                html = html + '<td></td>';
            }

            html = html + '</tr>';
        });
        return html;
    }

    async function payoutRestaurant(restaurant) {
        var payoutRestaurant = '';
        var route = '{{route("stores.view",":id")}}';
        route = route.replace(':id', restaurant);
        await database.collection('vendors').where("id", "==", restaurant).get().then(async function (snapshotss) {

            if (snapshotss.docs[0]) {
                var restaurant_data = snapshotss.docs[0].data();
                payoutRestaurant = restaurant_data.title;
                // jQuery(".restaurant_"+restaurant).html('<a href="'+route+'">'+payoutRestaurant+'</a>');
                jQuery(".restaurant_" + restaurant).attr("data-url", route).html(payoutRestaurant);
            } else {
                jQuery(".restaurant_" + restaurant).attr("data-url", route).html('');
            }
        });
        return payoutRestaurant;
    }

</script>

@endsection
