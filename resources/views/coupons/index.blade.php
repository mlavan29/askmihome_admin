@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor restaurantTitle">{{trans('lang.coupon_plural')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item active">{{trans('lang.coupon_table')}}</li>

            </ol>

        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">
         <div id="data-table_processing" class="dataTables_processing panel panel-default"
                             style="display: none;">Processing...
         </div>

        <div class="row">

            <div class="col-12">

                <?php if ($id != '') { ?>
                    <div class="menu-tab">
                        <ul>
                            <li>
                                <a href="{{route('stores',$id)}}">{{trans('lang.tab_basic')}}</a>
                            </li>
                            <li>
                                <a href="{{route('stores.items',$id)}}">{{trans('lang.tab_items')}}</a>
                            </li>
                            <li>
                                <a href="{{route('stores.orders',$id)}}">{{trans('lang.tab_orders')}}</a>
                            </li>
                            <li class="active">
                                <a href="{{route('stores.coupons',$id)}}">{{trans('lang.tab_promos')}}</a>
                            <li>
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
                                            class="fa fa-list mr-2"></i>{{trans('lang.coupon_table')}}</a>
                            </li>
                            <?php if ($id != '') { ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! route('coupons.create') !!}/{{$id}}"><i
                                                class="fa fa-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                                </li>
                            <?php } else { ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! route('coupons.create') !!}"><i
                                                class="fa fa-plus mr-2"></i>{{trans('lang.coupon_create')}}</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="card-body">


                        <div class="table-responsive m-t-10">

                            <table id="couponTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">

                                <thead>

                                <tr>
                                <?php if (in_array('coupons.delete', json_decode(@session('user_permissions')))) { ?>
                          
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                                                               class="do_not_delete"
                                                                                               href="javascript:void(0)"><i
                                                        class="fa fa-trash"></i> {{trans('lang.all')}}</a></label>
                                    </th>
                                   <?php }?> 
                                    <th>{{trans('lang.coupon_code')}}</th>

                                    <th>{{trans('lang.coupon_discount')}}</th>

                                    <th>{{trans('lang.coupon_privacy')}}</th>

                                    <th>{{trans('lang.coupon_restaurant_id')}}</th>


                                    <th>{{trans('lang.coupon_expires_at')}}</th>


                                    <th>{{trans('lang.coupon_enabled')}}</th>

                                    <th>{{trans('lang.coupon_description')}}</th>


                                    <th>{{trans('lang.actions')}}</th>

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

<script type="text/javascript">
    var user_permissions = '<?php echo @session('user_permissions') ?>';

    user_permissions = JSON.parse(user_permissions);

    var checkDeletePermission = false;

    if ($.inArray('coupons.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }

    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];

    var getId = '{{$id}}';
    <?php if ($id != '') { ?>
    var ref = database.collection('coupons').where('resturant_id', '==', '<?php echo $id; ?>');
    const getStoreName = getStoreNameFunction('<?php echo $id; ?>');

    <?php } else { ?>
    var ref = database.collection('coupons');
    <?php } ?>

    ref = ref.orderBy('expiresAt', 'desc');
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
            var html = '';

            html = await buildHTML(snapshots);
            if (html != '') {
                append_list.innerHTML = html;
                start = snapshots.docs[snapshots.docs.length - 1];
                endarray.push(snapshots.docs[0]);
                if (snapshots.docs.length < pagesize) {
                    jQuery("#data-table_paginate").hide();
                }

            }

            $('#couponTable').DataTable({
                order: [],
                 columnDefs: [
                   {
                       targets: (checkDeletePermission==true) ? 5 : 4,
                      type: 'date',
                      render: function (data) {

                          return data;
                     }
                 },
                 { orderable: false, targets: (checkDeletePermission==true) ? [0, 3,6] : [2,5] },
                ],
                order: (checkDeletePermission==true) ? ['5', 'desc'] : [4,'desc'],

                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}"
                },
                responsive: true
            });
            jQuery("#data-table_processing").hide();

        });

    });

    async function getStoreNameFunction(resturant_id) {
        var vendorName = '';
        await database.collection('vendors').where('id', '==', resturant_id).get().then(async function (snapshots) {
            if(!snapshots.empty){
            var vendorData = snapshots.docs[0].data();

            vendorName = vendorData.title;
            $('.restaurantTitle').html('{{trans("lang.coupon_plural")}} - ' + vendorName);

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

    async function buildHTML(snapshots) {
        var html = '';
        await Promise.all(snapshots.docs.map(async (listval) => {
            var datas = listval.data();
            var getData = await getListData(datas);
            html += getData;
        }));
        return html;
    }

    async function getListData(val) {
        var html = '';
        html = html + '<tr>';
        newdate = '';
        if (currencyAtRight) {
            if (val.discountType == 'Percent' || val.discountType == 'Percentage') {
                discount_price = val.discount + "%";
            } else {
                discount_price = parseFloat(val.discount).toFixed(decimal_degits) + "" + currentCurrency;
            }
        } else {
            if (val.discountType == 'Percent' || val.discountType == 'Percentage') {
                discount_price = val.discount + "%";
            } else {
                discount_price = currentCurrency + "" + parseFloat(val.discount).toFixed(decimal_degits);
            }
        }
        var id = val.id;

        var route1 = '{{route("coupons.edit",":id")}}';
        route1 = route1.replace(':id', id);
        <?php if ($id != '') { ?>
        route1 = route1 + '?eid={{$id}}';
        <?php } ?>

        var route_view = '{{route("stores.view",":id")}}';
        route_view = route_view.replace(':id', val.resturant_id);
        if(checkDeletePermission){
        html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + id + '" ></label></td>';
        }
        html = html + '<td  data-url="' + route1 + '"  class="redirecttopage">' + val.code + '</td>';
        html = html + '<td>' + discount_price + '</td>';

        if (val.hasOwnProperty('isPublic') && val.isPublic) {
            html = html + '<td class="success"><span class="badge badge-success py-2 px-3">{{trans("lang.public")}}</sapn></td>';
        } else {
            html = html + '<td class="danger"><span class="badge badge-danger py-2 px-3">{{trans("lang.private")}}</sapn></td>';
        }
        var store = await getrestaurantName(val.resturant_id);
        if(store!='' && store!=undefined){
         html = html + '<td  data-url="' + route_view + '" class="redirecttopage storeName_' + val.resturant_id + '" >' + store + '</td>';

        }else{
            html = html + '<td></td>';
        }


        var date = '';
        var time = '';
        if (val.hasOwnProperty("expiresAt")) {

            try {
                date = val.expiresAt.toDate().toDateString();
                time = val.expiresAt.toDate().toLocaleTimeString('en-US');
            } catch (err) {

            }
            html = html + '<td class="dt-time">' + date + ' ' + time + '</td>';
        } else {
            html = html + '<td></td>';
        }
        if (val.isEnabled) {
            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isEnabled"><span class="slider round"></span></label></td>';
        } else {
            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isEnabled"><span class="slider round"></span></label></td>';
        }

        html = html + '<td>' + val.description + '</td>';

        html = html + '<td class="action-btn"><a href="' + route1 + '"><i class="fa fa-edit"></i></a>';
        if(checkDeletePermission){
        html=html+'<a id="' + val.id + '" name="coupon_delete_btn" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        }
        html=html+'</td>';

        html = html + '</tr>';
        return html;

    }


    $(document).on("click", "input[name='isEnabled']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('coupons').doc(id).update({'isEnabled': true}).then(function (result) {

            });
        } else {
            database.collection('coupons').doc(id).update({'isEnabled': false}).then(function (result) {

            });
        }

    });


    async function getrestaurantName(resturant_id) {
        var title = '';
        if (resturant_id) {
            await database.collection('vendors').where("id", "==", resturant_id).get().then(async function (snapshots) {
                if (snapshots.docs.length > 0) {
                    var data = snapshots.docs[0].data();
                    title = data.title;
                    //$('.storeName_' + resturant_id).html(data.title);
                }
            });
        }

        return title;
    }


    $("#is_active").click(function () {

        $("#couponTable .is_open").prop('checked', $(this).prop('checked'));
    });


    $("#deleteAll").click(function () {
        if ($('#couponTable .is_open:checked').length) {

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#couponTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');

                    database.collection('coupons').doc(dataId).delete().then(function () {

                        window.location.reload();
                    });

                });

            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    $(document).on("click", "a[name='coupon_delete_btn']", function (e) {

        var id = this.id;
        database.collection('coupons').doc(id).delete().then(function () {

            window.location = "{{! url()->current() }}";
        });

    });

</script>

@endsection
