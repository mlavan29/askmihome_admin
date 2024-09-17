@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.driver_plural')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item active">{{trans('lang.driver_table')}}</li>

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

                <div class="card">

                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! route('drivers') !!}"><i
                                            class="fa fa-list mr-2"></i>{{trans('lang.driver_table')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{!! route('drivers.create') !!}"><i
                                            class="fa fa-plus mr-2"></i>{{trans('lang.drivers_create')}}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive m-t-10">

                            <table id="driverTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">

                                <thead>

                                <tr>
                                <?php if (in_array('drivers.delete', json_decode(@session('user_permissions')))) { ?>

                                    <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active"
                                        ><a id="deleteAll" class="do_not_delete"
                                            href="javascript:void(0)"><i
                                                        class="fa fa-trash"></i> {{trans('lang.all')}}</a></label>
                                    </th>
                                    <?php } ?>

                                    <th>{{trans('lang.extra_image')}}</th>

                                    <th>{{trans('lang.user_name')}}</th>
                                    <th>{{trans('lang.email')}}</th>
                                    <th>{{trans('lang.date')}}</th>

                                    <th>{{trans('lang.driver_available')}}</th>

                                    {{--<th>{{trans('lang.order_transactions')}}</th>--}}

                                    <th>{{trans('lang.wallet_history')}}</th>
                                    <th>{{trans('lang.dashboard_total_orders')}}</th>


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

    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var ref = database.collection('users').where("role", "==", "driver").orderBy('createdAt', 'desc');
    var alldriver = database.collection('users').where("role", "==", "driver");
    var append_list = '';


    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');

    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    });
    var user_permissions = '<?php echo @session('user_permissions') ?>';

user_permissions = JSON.parse(user_permissions);

var checkDeletePermission = false;

if ($.inArray('drivers.delete', user_permissions) >= 0) {
    checkDeletePermission = true;
}
    $(document).ready(function () {

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


            $('#driverTable').DataTable({
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
                        targets: (checkDeletePermission == true) ? [0, 1, 5, 6, 7,8] : [0, 1, 4, 5, 6,7]

                    },
                ],
              
                    order: (checkDeletePermission==true) ? [4, "desc"] : [3,"desc"],
                
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}"
                },
                responsive: true
            });
        });


        alldriver.get().then(async function (snapshotsdriver) {

            snapshotsdriver.docs.forEach((listval) => {
                database.collection('restaurant_orders').where('driverID', '==', listval.id).where("status", "in", ["Order Completed"]).get().then(async function (orderSnapshots) {
                    var count_order_complete = orderSnapshots.docs.length;
                    database.collection('users').doc(listval.id).update({'orderCompleted': count_order_complete}).then(function (result) {

                    });

                });

            });
        });

    });


    async function buildHTML(snapshots) {
        var html = '';

        await Promise.all(snapshots.docs.map(async (listval) => {

            var val = listval.data();

            var getData = await getDriverListData(val);
            html += getData;
        }));

        return html;
    }

    async function getDriverListData(val) {
        var html = '';

        html = html + '<tr>';
        newdate = '';
        var id = val.id;

        var route1 = '{{route("drivers.view",":id")}}';
        route1 = route1.replace(':id', id);
        var route2 = '{{route("drivers.edit",":id")}}';
        route2 = route2.replace(':id', id);
        if (checkDeletePermission) {
        html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
            'for="is_open_' + id + '" ></label></td>';
        }
        if (val.profilePictureURL) {
            html = html + '<td><img class="rounded" style="width:50px" src="' + val.profilePictureURL + '" alt="image"></td>';

        } else {
            html = html + '<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"></td>';

        }

        html = html + '<td data-url="' + route1 + '" class="redirecttopage">' + val.firstName + ' ' + val.lastName + '</td>';
        html = html + '<td>  ' + val.email + '</td>';

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

        if (val.isActive) {
            html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>';
        } else {
            html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="isActive"><span class="slider round"></span></label></td>';
        }


        var trroute1 = '{{route("order_transactions.index",":id")}}';
        trroute1 = trroute1.replace(':id', 'driverId=' + id);

        //html = html + '<td><a href="' + trroute1 + '">{{trans("lang.order_transactions")}}</a></td>';
        var trroute2 = '{{route("orders",":id")}}';
        trroute2 = trroute2.replace(':id', 'driverId=' + id);

        var payoutRequests = '{{route("payoutRequests.drivers.view",":id")}}';
        payoutRequests = payoutRequests.replace(':id', id);

        html = html + '<td><a href="' + payoutRequests + '">{{trans("lang.wallet_history")}}</a></td>';


        const driver = await orderDetails(val.id);
        html = html + '<td><a href="' + trroute2 + '">' + driver + '</a></td>';


        var driverView = '{{route("drivers.view",":id")}}';
        driverView = driverView.replace(':id', id);
        html = html + '<td class="action-btn"><a href="' + driverView + '"><i class="fa fa-eye"></i></a><a href="' + route2 + '"><i class="fa fa-edit"></i></a>';
        if (checkDeletePermission) {

        html=html+'<a id="' + val.id + '" name="driver-delete" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        
        }
        html = html+'</td>';
        html = html + '</tr>';
        return html;
    }

    async function orderDetails(driver) {
        var count_order_complete = 0;
        await database.collection('restaurant_orders').where('driverID', '==', driver).get().then(async function (orderSnapshots) {
            count_order_complete = orderSnapshots.docs.length;
        });
        return count_order_complete;
    }

    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('users').doc(id).update({'isActive': true}).then(function (result) {
            });
        } else {
            database.collection('users').doc(id).update({'isActive': false}).then(function (result) {
            });
        }
    });

    $("#is_active").click(function () {
        $("#driverTable .is_open").prop('checked', $(this).prop('checked'));

    });

    $("#deleteAll").click(function () {
        if ($('#driverTable .is_open:checked').length) {

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {

                jQuery("#data-table_processing").show();

                $('#driverTable .is_open:checked').each(function () {

                    var dataId = $(this).attr('dataId');

                    database.collection('users').doc(dataId).delete().then(function () {

                        const getStoreName = deleteDriverData(dataId);

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

    async function deleteDriverData(driverId) {
        await database.collection('order_transactions').where('driverId', '==', driverId).get().then(async function (snapshotsOrderTransacation) {
            if (snapshotsOrderTransacation.docs.length > 0) {
                snapshotsOrderTransacation.docs.forEach((temData) => {
                    var item_data = temData.data();

                    database.collection('order_transactions').doc(item_data.id).delete().then(function () {

                    });
                });
            }

        });

        await database.collection('driver_payouts').where('driverID', '==', driverId).get().then(async function (snapshotsItem) {

            if (snapshotsItem.docs.length > 0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data = temData.data();

                    database.collection('driver_payouts').doc(item_data.id).delete().then(function () {

                    });
                });
            }

        });
    }

    $(document.body).on('click', '.redirecttopage', function () {
        var url = $(this).attr('data-url');
        window.location.href = url;
    });

    $(document).on("click", "a[name='driver-delete']", function (e) {
        var id = this.id;
        database.collection('users').doc(id).delete().then(function () {
            deleteDriverData(id).then(function () {
                setTimeout(function () {
                    window.location.reload();
                }, 9000);
                //window.location.reload();
            });
        });


    });

    async function deleteDriverData(driverId) {

        await database.collection('order_transactions').where('driverId', '==', driverId).get().then(async function (snapshotsOrderTransacation) {
            if (snapshotsOrderTransacation.docs.length > 0) {
                snapshotsOrderTransacation.docs.forEach((temData) => {
                    var item_data = temData.data();

                    database.collection('order_transactions').doc(item_data.id).delete().then(function () {

                    });
                });
            }

        });

        await database.collection('driver_payouts').where('driverID', '==', driverId).get().then(async function (snapshotsItem) {

            if (snapshotsItem.docs.length > 0) {
                snapshotsItem.docs.forEach((temData) => {
                    var item_data = temData.data();

                    database.collection('driver_payouts').doc(item_data.id).delete().then(function () {

                    });
                });
            }

        });


    }

</script>

@endsection
