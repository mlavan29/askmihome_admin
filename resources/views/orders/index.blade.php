@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor orderTitle">{{trans('lang.order_plural')}} </h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.order_plural')}}</li>
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

                <div class="menu-tab vendorMenuTab">
                    <ul>
                        <li>
                            <a href="{{route('stores.view',$id)}}">{{trans('lang.tab_basic')}}</a>
                        </li>
                        <li>
                            <a href="{{route('stores.items',$id)}}">{{trans('lang.tab_items')}}</a>
                        </li>
                        <li class="active">
                            <a href="{{route('stores.orders',$id)}}">{{trans('lang.tab_orders')}}</a>
                        </li>
                        <li>
                            <a href="{{route('stores.coupons',$id)}}">{{trans('lang.tab_promos')}}</a>
                        </li>
                        <li>
                            <a href="{{route('stores.payout',$id)}}">{{trans('lang.tab_payouts')}}</a>
                        </li>
                        <li id="restaurant_wallet"></li>

                    </ul>
                </div>

                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive m-t-10">
                            <table id="orderTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                <?php if (in_array('orders.delete', json_decode(@session('user_permissions')))) { ?>

                                    <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                <i class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                                <?php } ?>

                                    <th>{{trans('lang.order_id')}}</th>
                                    @if ($id == '')

                                    <th>{{trans('lang.restaurant')}}</th>
                                    @endif
                                    @if (isset($_GET['userId']))
                                    <th class="driverClass">{{trans('lang.driver_plural')}}</th>

                                    @elseif (isset($_GET['driverId']))
                                    <th>{{trans('lang.order_user_id')}}</th>

                                    @else
                                    <th class="driverClass">{{trans('lang.driver_plural')}}</th>
                                    <th>{{trans('lang.order_user_id')}}</th>

                                    @endif

                                    <th>{{trans('lang.date')}}</th>
                                    <th>{{trans('lang.restaurants_payout_amount')}}</th>
                                    <th>{{trans('lang.order_type')}}</th>
                                    <th>{{trans('lang.order_order_status_id')}}</th>
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

@endsection

@section('scripts')

<script type="text/javascript">

    var database = firebase.firestore();
    var vendor_id = '<?php echo $id; ?>';
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var append_list = '';
    var redData = ref;
    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    $('.vendorMenuTab').hide();
    var refCurrency = database.collection('currencies').where('isActive', '==', true);
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;
        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    var order_status = jQuery('#order_status').val();
    var search = jQuery("#search").val();

    var refData = database.collection('restaurant_orders');

    var ref = '';

    var user_permissions = '<?php echo @session('user_permissions') ?>';

    user_permissions = JSON.parse(user_permissions);

    var checkDeletePermission = false;

    if ($.inArray('orders.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    var checkPrintPermission = false;

if ($.inArray('vendors.orderprint', user_permissions) >= 0) {
    checkPrintPermission = true;
}

    $(document.body).on('change', '#order_status', function () {
        order_status = jQuery(this).val();
    });

    $(document.body).on('keyup', '#search', function () {
        search = jQuery(this).val();
    });

    var getId = '<?php echo $id;?>';

    var userID = '<?php if (isset($_GET['userId'])) {
        echo $_GET['userId'];
    } else {
        echo '';
    }?>';
    var driverID = '<?php if (isset($_GET['driverId'])) {
        echo $_GET['driverId'];
    } else {
        echo '';
    } ?>';
    var orderStatus = '<?php if (isset($_GET['status'])) {
            echo $_GET['status'];
        } else {
            echo '';
        } ?>';



    if (userID) {

        const getUserName = getUserNameFunction(userID);

        if ((order_status == 'All' || order_status != '') && search != '') {
            ref = refData.where('authorID', '==', userID);
        } else {
            ref = refData.orderBy('createdAt', 'desc').where('authorID', '==', userID);
        }

    } else if (driverID) {

        const getUserName = getUserNameFunction(driverID);

        if ((order_status == 'All' || order_status != '') && search != '') {
            ref = refData.where('driverID', '==', driverID);
        } else {
            ref = refData.orderBy('createdAt', 'desc').where('driverID', '==', driverID);
        }

    }else if(orderStatus){
            if(orderStatus=='order-placed'){
                ref = refData.orderBy('createdAt', 'desc').where('status', '==', 'Order Placed');
            }
            else if(orderStatus=='order-confirmed'){
                ref = refData.orderBy('createdAt', 'desc').where('status', 'in', ['Order Accepted','Driver Accepted']);
            }
            else if(orderStatus=='order-shipped'){
             ref = refData.orderBy('createdAt', 'desc').where('status', 'in', ['Order Shipped','In Transit']);
            }
            else if(orderStatus=='order-completed'){
             ref = refData.orderBy('createdAt', 'desc').where('status', '==', 'Order Completed');
            }
            else if(orderStatus=='order-canceled'){
             ref = refData.orderBy('createdAt', 'desc').where('status', '==', 'Order Rejected');
            }
            else if(orderStatus=='order-failed'){
             ref = refData.orderBy('createdAt', 'desc').where('status', '==', 'Driver Rejected');
            }
            else if(orderStatus=='order-pending'){
             ref = refData.orderBy('createdAt', 'desc').where('status', '==', 'Driver Pending');
            }else{

                ref = refData.orderBy('createdAt', 'desc');
            }






    } 
    else if (getId != '') {

        $('.vendorMenuTab').show();

        const getStoreName = getStoreNameFunction(getId);

        if ((order_status == 'All' || order_status != '') && search != '') {
            ref = refData.where('vendorID', '==', getId);
        } else {
            ref = refData.orderBy('createdAt', 'desc').where('vendorID', '==', getId);
        }

    } else {

        if ((order_status == 'All' || order_status != '') && search != '') {

            ref = refData;
        } else {
            ref = refData.orderBy('createdAt', 'desc');
        }
    }

    $(document).ready(function () {
        

        jQuery('#search').hide();

        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        $(document.body).on('change', '#selected_search', function () {

            if (jQuery(this).val() == 'status') {
                jQuery('#order_status').show();
                jQuery('#search').hide();
            } else {

                jQuery('#order_status').hide();
                jQuery('#search').show();

            }
        });

        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();

        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';

        ref.get().then(async function (snapshots) {

            var html = '';
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
            if (getId != '' || driverID || userID) {
                $('#orderTable').DataTable({
                    order: [],
                    columnDefs: [
                        {
                            targets: (checkDeletePermission == true) ? 4 : 3,

                            type: 'date',
                            render: function (data) {

                                return data;
                            }
                        },
                        {
                            orderable: false,
                             targets: (checkDeletePermission == true) ? [0, 7, 8] : [0,6, 7]

                            },
                    ],
                    order: (checkDeletePermission==true) ? [4, "desc"] : [3,"desc"],
                    "language": {
                        "zeroRecords": "{{trans('lang.no_record_found')}}",
                        "emptyTable": "{{trans('lang.no_record_found')}}"
                    },
                    responsive: true
                });

            } else {
                $('#orderTable').DataTable({
                    order: [],
                    columnDefs: [
                        {
                            targets: (checkDeletePermission == true) ? 5 : 4,

                            type: 'date',
                            render: function (data) {

                                return data;
                            }
                        },
                        {
                            orderable: false,
                             targets: (checkDeletePermission == true) ? [0, 8, 9] : [0,7,8]

                            },
                    ],
                    order: (checkDeletePermission==true) ? [5, "desc"] : [4,"desc"],
                    "language": {
                        "zeroRecords": "{{trans("lang.no_record_found")}}",
                        "emptyTable": "{{trans("lang.no_record_found")}}"
                    },
                    responsive: true
                });

            }

        });

    });


    async function buildHTML(snapshots) {
        var html = '';
        await Promise.all(snapshots.docs.map(async (listval) => {
            var val = listval.data();

            let result = user_number.filter(obj => {
                return obj.id == val.author;
            })

            if (result.length > 0) {
                val.phoneNumber = result[0].phoneNumber;
                val.isActive = result[0].isActive;

            } else {
                val.phoneNumber = '';
                val.isActive = false;
            }

            var getData = await getListData(val);
            html += getData;
        }));
        return html;
    }

    async function getListData(val) {
        var html = '';

        html = html + '<tr>';
        newdate = '';
        var id = val.id;
        var vendorID = val.vendorID;

        var user_id = val.authorID;
        var route1 = '{{route("orders.edit",":id")}}';
        route1 = route1.replace(':id', id);

        var printRoute = '{{route("vendors.orderprint",":id")}}';
        printRoute = printRoute.replace(':id', id);

        <?php if($id != ''){ ?>
        route1 = route1 + '?eid={{$id}}';
        printRoute = printRoute + '?eid={{$id}}';
        <?php }?>

        var route_view = '{{route("stores.view",":id")}}';
        route_view = route_view.replace(':id', vendorID);

        var customer_view = '{{route("users.edit",":id")}}';
        customer_view = customer_view.replace(':id', user_id);
        if (checkDeletePermission) {
        html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + id + '" ></label></td>';
        }

        html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.id + '</td>';

        if (userID) {

            var title = '';
            if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                title = val.vendor.title;
            }

            html = html + '<td  data-url="' + route_view + '" class="redirecttopage">' + title + '</td>';

            if (val.hasOwnProperty("driver") && val.driver != null) {
                var driverId = val.driver.id;
                var diverRoute = '{{route("drivers.edit",":id")}}';
                diverRoute = diverRoute.replace(':id', driverId);
                html = html + '<td  data-url="' + diverRoute + '" class="redirecttopage">' + val.driver.firstName + ' ' + val.driver.lastName + '</td>';

            } else {
                html = html + '<td></td>';
            }

        } else if (driverID) {

            if (val.hasOwnProperty("author") && val.author != null) {
                var driverId = val.author.id;

                html = html + '<td  data-url="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</td>';

            } else {
                html = html + '<td></td>';
            }
            var title = '';
            if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                title = val.vendor.title;
            }
            html = html + '<td  data-url="' + route_view + '" class="redirecttopage">' + title + '</td>';

        } else if (getId != '') {

            if (val.hasOwnProperty("driver") && val.driver != null) {
                var driverId = val.driver.id;
                var diverRoute = '{{route("drivers.edit",":id")}}';
                diverRoute = diverRoute.replace(':id', driverId);
                html = html + '<td  data-url="' + diverRoute + '" class="redirecttopage">' + val.driver.firstName + ' ' + val.driver.lastName + '</td>';

            } else {
                html = html + '<td></td>';

            }


            if (val.hasOwnProperty("author") && val.author != null) {
                var driverId = val.author.id;

                html = html + '<td  data-url="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</td>';

            } else {
                html = html + '<td></td>';
            }

        } else {
            var title = '';
            if (val.hasOwnProperty('vendor') && val.vendor.title != undefined) {
                title = val.vendor.title;
            }

            html = html + '<td  data-url="' + route_view + '" class="redirecttopage">' + title + '</td>';

            if (val.hasOwnProperty("driver") && val.driver != null) {
                var driverId = val.driver.id;
                var diverRoute = '{{route("drivers.edit",":id")}}';
                diverRoute = diverRoute.replace(':id', driverId);
                html = html + '<td  data-url="' + diverRoute + '" class="redirecttopage">' + val.driver.firstName + ' ' + val.driver.lastName + '</td>';

            } else {
                html = html + '<td></td>';
            }


            if (val.hasOwnProperty("author") && val.author != null) {
                var driverId = val.author.id;

                html = html + '<td  data-url="' + customer_view + '" class="redirecttopage">' + val.author.firstName + ' ' + val.author.lastName + '</td>';

            } else {
                html = html + '<td></td>';
            }

        }


        var date = '';
        var time = '';
        if (val.hasOwnProperty("createdAt")) {

            try {
                date = val.createdAt.toDate().toDateString();
                time = val.createdAt.toDate().toLocaleTimeString('en-US');
            } catch (err) {

            }
            html = html + '<td class="dt-time">' + date + ' ' + time + '</td>';
        } else {
            html = html + '<td></td>';
        }
        var price = 0;


        var price = await buildHTMLProductstotal(val);

        html = html + '<td class="text-green">' + price + '</td>';
        if (val.hasOwnProperty('takeAway') && val.takeAway) {
            html = html + '<td>{{trans("lang.order_takeaway")}}</td>';
        } else {
            html = html + '<td>{{trans("lang.order_delivery")}}</td>';
        }


        if (val.status == 'Order Placed') {
            html = html + '<td class="order_placed"><span>' + val.status + '</span></td>';

        } else if (val.status == 'Order Accepted') {
            html = html + '<td class="order_accepted"><span>' + val.status + '</span></td>';

        } else if (val.status == 'Order Rejected') {
            html = html + '<td class="order_rejected"><span>' + val.status + '</span></td>';

        } else if (val.status == 'Driver Pending') {
            html = html + '<td class="driver_pending"><span>' + val.status + '</span></td>';

        } else if (val.status == 'Driver Rejected') {
            html = html + '<td class="driver_rejected"><span>' + val.status + '</span></td>';

        } else if (val.status == 'Order Shipped') {
            html = html + '<td class="order_shipped"><span>' + val.status + '</span></td>';

        } else if (val.status == 'In Transit') {
            html = html + '<td class="in_transit"><span>' + val.status + '</span></td>';

        } else if (val.status == 'Order Completed') {
            html = html + '<td class="order_completed"><span>' + val.status + '</span></td>';

        }else{
            html = html + '<td class="order_completed"><span>' + val.status + '</span></td>';

        }
        html = html + '<td class="action-btn">';
        if (checkPrintPermission) {
        html = html + '<a href="' + printRoute + '"><i class="fa fa-print" style="font-size:20px;"></i></a>';
        }
        html = html + '<a href="' + route1 + '"><i class="fa fa-edit"></i></a>';
        if (checkDeletePermission) {

        html = html + '<a id="' + val.id + '" class="do_not_delete" name="order-delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        }
        html = html + '</td>';
        html = html + '</tr>';

        return html;
    }

    $("#is_active").click(function () {
        $("#orderTable .is_open").prop('checked', $(this).prop('checked'));

    });

    $("#deleteAll").click(function () {
        if ($('#orderTable .is_open:checked').length) {

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#orderTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');

                    database.collection('restaurant_orders').doc(dataId).delete().then(function () {

                        setTimeout(function () {
                            window.location.reload();
                        }, 7000);

                    });

                });

            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    $(document).on("click", "a[name='order-delete']", function (e) {
        var id = this.id;
        database.collection('restaurant_orders').doc(id).delete().then(function (result) {
            window.location.href = '{{ url()->current() }}';
        });
    });


    async function getStoreNameFunction(vendorId) {
        var vendorName = '';
        await database.collection('vendors').where('id', '==', vendorId).get().then(async function (snapshots) {
            if(!snapshots.empty){
            var vendorData = snapshots.docs[0].data();

            vendorName = vendorData.title;
            $('.orderTitle').html('{{trans("lang.order_plural")}} - ' + vendorName);

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

    async function getUserNameFunction(userId) {
        var userName = '';
        await database.collection('users').where('id', '==', userId).get().then(async function (snapshots) {
            var user = snapshots.docs[0].data();

            userName = user.firstName + ' ' + user.lastName;
            $('.orderTitle').html('{{trans("lang.order_plural")}} - ' + userName);
        });

        return userName;

    }

    function buildHTMLProductstotal(snapshotsProducts) {

        var adminCommission = snapshotsProducts.adminCommission;
        var discount = snapshotsProducts.discount;
        var couponCode = snapshotsProducts.couponCode;
        var extras = snapshotsProducts.extras;
        var extras_price = snapshotsProducts.extras_price;
        var rejectedByDrivers = snapshotsProducts.rejectedByDrivers;
        var takeAway = snapshotsProducts.takeAway;
        var tip_amount = snapshotsProducts.tip_amount;
        var status = snapshotsProducts.status;
        var products = snapshotsProducts.products;
        var deliveryCharge = snapshotsProducts.deliveryCharge;
        var totalProductPrice = 0;
        var total_price = 0;
        var specialDiscount = snapshotsProducts.specialDiscount;

        var intRegex = /^\d+$/;
        var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

        if (products) {

            products.forEach((product) => {

                var val = product;
                if (val.price) {
                    price_item = parseFloat(val.price).toFixed(2);

                    extras_price_item = 0;
                    if (val.extras_price && !isNaN(extras_price_item) && !isNaN(val.quantity)) {
                        extras_price_item = (parseFloat(val.extras_price) * parseInt(val.quantity)).toFixed(2);
                    }
                    if (!isNaN(price_item) && !isNaN(val.quantity)) {
                        totalProductPrice = parseFloat(price_item) * parseInt(val.quantity);
                    }
                    var extras_price = 0;
                    if (parseFloat(extras_price_item) != NaN && val.extras_price != undefined) {
                        extras_price = extras_price_item;
                    }
                    totalProductPrice = parseFloat(extras_price) + parseFloat(totalProductPrice);
                    totalProductPrice = parseFloat(totalProductPrice).toFixed(2);
                    if (!isNaN(totalProductPrice)) {
                        total_price += parseFloat(totalProductPrice);
                    }


                }

            });
        }

        if (intRegex.test(discount) || floatRegex.test(discount)) {

            discount = parseFloat(discount).toFixed(decimal_degits);
            total_price -= parseFloat(discount);

            if (currencyAtRight) {
                discount_val = discount + "" + currentCurrency;
            } else {
                discount_val = currentCurrency + "" + discount;
            }

        }
        var special_discount = 0;
        if (specialDiscount != undefined) {
            special_discount = parseFloat(specialDiscount.special_discount).toFixed(2);

            total_price = total_price - special_discount;
        }
        var total_item_price = total_price;
        var tax = 0;
        taxlabel = '';
        taxlabeltype = '';

        if (snapshotsProducts.hasOwnProperty('taxSetting')) {
            var total_tax_amount = 0;
            for (var i = 0; i < snapshotsProducts.taxSetting.length; i++) {
                var data = snapshotsProducts.taxSetting[i];

                if (data.type && data.tax) {
                    if (data.type == "percentage") {
                        tax = (data.tax * total_price) / 100;
                        taxlabeltype = "%";
                    } else {
                        tax = data.tax;
                        taxlabeltype = "fix";
                    }
                    taxlabel = data.title;
                }
                total_tax_amount += parseFloat(tax);
            }
            total_price = parseFloat(total_price) + parseFloat(total_tax_amount);
        }


        if ((intRegex.test(deliveryCharge) || floatRegex.test(deliveryCharge)) && !isNaN(deliveryCharge)) {

            deliveryCharge = parseFloat(deliveryCharge).toFixed(decimal_degits);
            total_price += parseFloat(deliveryCharge);

            if (currencyAtRight) {
                deliveryCharge_val = deliveryCharge + "" + currentCurrency;
            } else {
                deliveryCharge_val = currentCurrency + "" + deliveryCharge;
            }
        }

        if (intRegex.test(tip_amount) || floatRegex.test(tip_amount) && !isNaN(tip_amount)) {

            tip_amount = parseFloat(tip_amount).toFixed(decimal_degits);
            total_price += parseFloat(tip_amount);
            total_price = parseFloat(total_price).toFixed(decimal_degits);
        }
        if (currencyAtRight) {
            var total_price_val = parseFloat(total_price).toFixed(decimal_degits) + "" + currentCurrency;
        } else {
            var total_price_val = currentCurrency + "" + parseFloat(total_price).toFixed(decimal_degits);
        }
        return total_price_val;
    }

</script>

@endsection
