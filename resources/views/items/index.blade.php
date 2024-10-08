@extends('layouts.app')

@section('content')

<div class="page-wrapper">


    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor restaurantTitle">{{trans('lang.item_plural')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.item_plural')}}</li>
            </ol>

        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">
        <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">Processing...
        </div>

        <div class="row">

            <div class="col-12">


                <?php if ($id != '') { ?>
                    <div class="menu-tab">
                        <ul>
                            <li>
                                <a href="{{route('stores',$id)}}">{{trans('lang.tab_basic')}}</a>
                            </li>
                            <li class="active">
                                <a href="{{route('stores.items',$id)}}">{{trans('lang.tab_items')}}</a>
                            </li>
                            <li>
                                <a href="{{route('stores.orders',$id)}}">{{trans('lang.tab_orders')}}</a>
                            </li>
                            <li>
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
                                <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.item_table')}}</a>
                            </li>
                            <?php if ($id != '') { ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! route('items.create') !!}/{{$id}}"><i class="fa fa-plus mr-2"></i>{{trans('lang.item_create')}}</a>
                                </li>
                            <?php } else { ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="{!! route('items.create') !!}"><i class="fa fa-plus mr-2"></i>{{trans('lang.item_create')}}</a>
                                </li>
                            <?php } ?>

                        </ul>
                    </div>
                    <div class="card-body">


                        <div class="table-responsive m-t-10">


                            <table id="itemTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">

                                <thead>

                                    <tr>
                                        <?php if (in_array('items.delete', json_decode(@session('user_permissions')))) { ?>

                                            <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                                    <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                        <?php } ?>
                                        <th>{{trans('lang.item_image')}}</th>
                                        <th>{{trans('lang.item_name')}}</th>
                                        <th>{{trans('lang.item_price')}}</th>
                                        <?php if ($id == '') { ?>
                                            <th>{{trans('lang.item_restaurant_id')}}</th>
                                        <?php } ?>

                                        <th>{{trans('lang.item_category_id')}}</th>
                                        <th>{{trans('lang.item_publish')}}</th>
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
    const urlParams = new URLSearchParams(location.search);
    for (const [key, value] of urlParams) {
        if (key == 'categoryID') {
            var categoryID = value;
        } else {
            var categoryID = '';
        }

    }
    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;
    var id = "<?php echo $id; ?>";
    if (categoryID != '' && categoryID != undefined) {
        var ref = database.collection('vendor_products').where('categoryID', '==', categoryID);
    } else {
        <?php if ($id != '') { ?>
            var ref = database.collection('vendor_products').where('vendorID', '==', '<?php echo $id; ?>');
            const getStoreName = getStoreNameFunction('<?php echo $id; ?>');

        <?php } else { ?>
            var ref = database.collection('vendor_products');
        <?php } ?>
    }
    ref = ref.orderBy('name');

    var refCurrency = database.collection('currencies').where('isActive', '==', true);
    var append_list = '';

    refCurrency.get().then(async function(snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;

        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function(snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    var user_permissions = '<?php echo @session('user_permissions') ?>';

    user_permissions = JSON.parse(user_permissions);

    var checkDeletePermission = false;

    if ($.inArray('items.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    $(document).ready(function() {
        $('#category_search_dropdown').hide();

        $(document.body).on('click', '.redirecttopage', function() {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });

        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();

        append_list = document.getElementById('append_list1');
        append_list.innerHTML = '';
        ref.get().then(async function(snapshots) {
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

            <?php if ($id == '') { ?>

                $('#itemTable').DataTable({
                    order: (checkDeletePermission==true) ? [2, "asc"] : [5,"asc"],
                    columnDefs: [

                        {
                            orderable: false,
                            targets: (checkDeletePermission == true) ? [0, 1, 6, 7] : [0, 5, 6],

                        },
                        {
                            targets: (checkDeletePermission == true) ? 3 : 2,
                            type: "html-num-fmt",
                        },

                    ],
                    "language": {
                        "zeroRecords": "{{trans('lang.no_record_found ')}}",
                        "emptyTable": "{{trans('lang.no_record_found ')}}"
                    },
                    responsive: true
                });
            <?php
            } else { ?>

                $('#itemTable').DataTable({
                    order: [],
                    columnDefs: [{
                            orderable: false,
                            targets: (checkDeletePermission == true) ? [0, 1, 4, 5, 6] : [0, 3, 4, 5],

                        },
                        {
                            targets: (checkDeletePermission == true) ? 3 : 2,
                            type: "html-num-fmt",
                        },

                    ],
                    "language": {
                        "zeroRecords": "{{trans(' lang.no_record_found ')}}",
                        "emptyTable": "{{trans('lang.no_record_found ')}}"
                    },
                    responsive: true
                });
            <?php } ?>


            jQuery("#data-table_processing").hide();
        });

    });


    async function buildHTML(snapshots) {
        var html = '';
        await Promise.all(snapshots.docs.map(async (listval) => {
            var val = listval.data();

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
        var route1 = '{{route("items.edit",":id")}}';
        route1 = route1.replace(':id', id);

        <?php if ($id != '') { ?>

            route1 = route1 + '?eid={{$id}}';

        <?php } ?>
        if (checkDeletePermission) {

        html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + id + '" ></label></td>';
        }
        if (val.photos != '') {
            html = html + '<td><img class="rounded" style="width:50px" src="' + val.photo + '" alt="image"></td>';

        } else if (val.photo != '') {
            html = html + '<td><img class="rounded" style="width:50px" src="' + val.photo + '" alt="image"></td>';
        } else {
            html = html + '<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"></td>';
        }
        html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.name + '</td>';


        if (val.hasOwnProperty('disPrice') && val.disPrice != '' && val.disPrice != '0') {
            if (currencyAtRight) {
                html = html + '<td class="text-green" data-html="true" data-order="' + val.disPrice + '">' + parseFloat(val.disPrice).toFixed(decimal_degits) + '' + currentCurrency + '  <s>' + parseFloat(val.price).toFixed(decimal_degits) + '' + currentCurrency + '</s></td>';

            } else {
                html = html + '<td class="text-green" data-html="true" data-order="' + val.disPrice + '">' + '' + currentCurrency + parseFloat(val.disPrice).toFixed(decimal_degits) + '  <s>' + currentCurrency + '' + parseFloat(val.price).toFixed(decimal_degits) + '</s></td>';

            }

        } else {

            if (currencyAtRight) {
                html = html + '<td class="text-green" data-html="true" data-order="' + val.price + '">' + parseFloat(val.price).toFixed(decimal_degits) + '' + currentCurrency + '</td>';
            } else {
                html = html + '<td class="text-green" data-html="true" data-order="' + val.price + '">' + currentCurrency + '' + parseFloat(val.price).toFixed(decimal_degits) + '</td>';
            }
        }

        <?php if ($id == '') { ?>
            const restaurant = await productRestaurant(val.vendorID);
            var restaurantroute = '{{route("stores.view",":id")}}';
            restaurantroute = restaurantroute.replace(':id', val.vendorID);

            html = html + '<td><a href="' + restaurantroute + '">' + restaurant + '</a></td>';
        <?php } ?>


        const category = await productCategory(val.categoryID);
        var caregoryroute = '{{route("categories.edit",":id")}}';
        caregoryroute = caregoryroute.replace(':id', val.categoryID);

        html = html + '<td><a href="' + caregoryroute + '">' + category + '</a></td>';
        if (val.publish) {
            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="publish"><span class="slider round"></span></label></td>';
        } else {
            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="publish"><span class="slider round"></span></label></td>';
        }
        html = html + '<td class="action-btn"><a href="' + route1 + '" class="link-td"><i class="fa fa-edit"></i></a>';
        if (checkDeletePermission) {

        html = html + '<a id="' + val.id + '" name="item-delete" href="javascript:void(0)" class="link-td do_not_delete"><i class="fa fa-trash"></i></a>';
        }
        html = html + '</td>';
        html = html + '</tr>';

        return html;
    }

    $(document).on("click", "input[name='publish']", function(e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('vendor_products').doc(id).update({
                'publish': true
            }).then(function(result) {

            });
        } else {
            database.collection('vendor_products').doc(id).update({
                'publish': false
            }).then(function(result) {

            });
        }

    });

    async function productRestaurant(restaurant) {
        var productRestaurant = '';
        await database.collection('vendors').where("id", "==", restaurant).get().then(async function(snapshotss) {


            if (snapshotss.docs[0]) {
                var restaurant_data = snapshotss.docs[0].data();
                productRestaurant = restaurant_data.title;

            }
        });
        return productRestaurant;
    }

    async function getStoreNameFunction(vendorId) {
        var vendorName = '';
        await database.collection('vendors').where('id', '==', vendorId).get().then(async function(snapshots) {
            if (!snapshots.empty) {
                var vendorData = snapshots.docs[0].data();

                vendorName = vendorData.title;
                $('.restaurantTitle').html('{{trans("lang.item_plural")}} - ' + vendorName);

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

    async function productCategory(category) {
        var productCategory = '';
        await database.collection('vendor_categories').where("id", "==", category).get().then(async function(snapshotss) {

            if (snapshotss.docs[0]) {
                var category_data = snapshotss.docs[0].data();
                productCategory = category_data.title;
            }
        });
        return productCategory;
    }

    $(document).on("click", "a[name='item-delete']", function(e) {
        var id = this.id;
        database.collection('vendor_products').doc(id).delete().then(function(result) {
            window.location.href = '{{ url()->current() }}';
        });
    });

    $(document.body).on('change', '#selected_search', function() {

        if (jQuery(this).val() == 'category') {

            var ref_category = database.collection('vendor_categories');

            ref_category.get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data = listval.data();
                    $('#category_search_dropdown').append($("<option></option").attr("value", data.id).text(data.title));

                });

            });
            jQuery('#search').hide();
            jQuery('#category_search_dropdown').show();
        } else {
            jQuery('#search').show();
            jQuery('#category_search_dropdown').hide();

        }
    });

    $("#is_active").click(function() {
        $("#itemTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function() {
        if ($('#itemTable .is_open:checked').length) {

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#itemTable .is_open:checked').each(function() {
                    var dataId = $(this).attr('dataId');

                    database.collection('vendor_products').doc(dataId).delete().then(function() {
                        setTimeout(function() {
                            window.location.reload();
                        }, 7000);

                    });
                });
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
</script>


@endsection