@extends('layouts.app')

@section('content')

<div class="page-wrapper">


    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.dynamic_notification')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>

                <li class="breadcrumb-item">{{trans('lang.dynamic_notification')}}</li>

            </ol>

        </div>

        <div>

        </div>

    </div>


    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                            class="fa fa-list mr-2"></i>{{trans('lang.notificaions_table')}}</a>
                            </li>

                        </ul>
                    </div>
                    <div class="card-body">

                    <div class="table-responsive m-t-10">


                        <table id="notificationTable"
                               class="display nowrap table table-hover table-striped table-bordered table table-striped"
                               cellspacing="0" width="100%">

                            <thead>

                            <tr>

                                <th>{{trans('lang.type')}}</th>
                                <th>{{trans('lang.subject')}}</th>
                                <th>{{trans('lang.message')}}</th>
                                <th>{{trans('lang.actions')}}</th>

                            </tr>

                            </thead>

                            <tbody id="append_restaurants">


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

    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var refData = database.collection('dynamic_notification');
    var ref = refData.orderBy('createdAt', 'desc');
    var append_list = '';


    $(document).ready(function () {


        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();

        append_list = document.getElementById('append_restaurants');
        append_list.innerHTML = '';
        ref.get().then(async function (snapshots) {
            html = '';
            html = await buildHTML(snapshots);
            jQuery("#data-table_processing").hide();
            if (html != '') {
                append_list.innerHTML = html;
                $('[data-toggle="tooltip"]').tooltip();

            }

            $('#notificationTable').DataTable({
                order: [],
                columnDefs: [
                    {orderable: false, targets: [3]},
                ],
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}"
                },
                //responsive: true
            });
        });

    })
    $("#is_active").click(function () {
        $("#notificationTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#notificationTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#notificationTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');

                    database.collection('dynamic_notification').doc(dataId).delete().then(function () {

                        window.location.reload();
                    });

                });

            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });


    function buildHTML(snapshots) {

        var html = '';
        var number = [];
        var count = 0;
        snapshots.docs.forEach(async (listval) => {
            var listval = listval.data();

            var val = listval;
            val.id = listval.id;
            html = html + '<tr>';
            newdate = '';
            var id = val.id;
            route1 = '{{route("dynamic-notification.save",":id")}}'
            route1 = route1.replace(":id", id);

            if (val.type == "restaurant_rejected") {

                type = "{{trans('lang.order_rejected_by_restaurant')}}";
                title = "{{trans('lang.order_reject_notification')}}";
            } else if (val.type == "restaurant_accepted") {
                type = "{{trans('lang.order_accepted_by_restaurant')}}";
                title = "{{trans('lang.order_accept_notification')}}";
            } else if (val.type == "takeaway_completed") {
                type = "{{trans('lang.takeaway_order_completed')}}";
                title = "{{trans('lang.takeaway_order_complete_notification')}}";
            } else if (val.type == "driver_completed") {
                type = "{{trans('lang.driver_completed_order')}}";
                title = "{{trans('lang.order_complete_notification')}}";

            } else if (val.type == "driver_accepted") {
                type = "{{trans('lang.driver_accepted_order')}}";
                title = "{{trans('lang.driver_accept_order_notification')}}";
            } else if (val.type == "dinein_canceled") {
                type = "{{trans('lang.dine_order_book_canceled')}}";
                title = "{{trans('lang.dinein_cancel_notification')}}";
            } else if (val.type == "dinein_accepted") {
                type = "{{trans('lang.dine_order_book_accepted')}}";
                title = "{{trans('lang.dinein_accept_notification')}}";
            } else if (val.type == "order_placed") {
                type = "{{trans('lang.new_order_place')}}";
                title = "{{trans('lang.order_placed_notification')}}";
            } else if (val.type == "dinein_placed") {
                type = "{{trans('lang.new_dine_booking')}}";
                title = "{{trans('lang.dinein_order_place_notification')}}";

            } else if (val.type == "schedule_order") {
                type = "{{trans('lang.shedule_order')}}";
                title = "{{trans('lang.schedule_order_notification')}}";
            } else if (val.type == "payment_received") {
                type = "{{trans('lang.pament_received')}}";
                title = "{{trans('lang.payment_receive_notification')}}";
            }

            html = html + '<td>' + type + '</td>';
            html = html + '<td>' + val.subject + '</td>';

            html = html + '<td>' + val.message + '</td>';

            html = html + '<td class="action-btn"><i class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip" title="' + title + '" aria-describedby="tippy-3"></i><a href="' + route1 + '"><i class="fa fa-edit"></i></a></td>';

            html = html + '</tr>';
            count = count + 1;
        });
        return html;
    }



    $(document).on("click", "a[name='notifications-delete']", function (e) {
        var id = this.id;
        database.collection('dynamic_notification').doc(id).delete().then(function () {
            window.location.reload();
        });
    });
</script>


@endsection