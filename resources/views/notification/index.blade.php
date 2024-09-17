@extends('layouts.app')

@section('content')

<div class="page-wrapper">


    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.notifications')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.notifications')}}</li>

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
                            <li class="nav-item">
                                <a class="nav-link" href="{!! url('notification/send') !!}"><i
                                            class="fa fa-plus mr-2"></i>{{trans('lang.create_notificaion')}}</a>
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
                                    <?php if (in_array('notification.delete', json_decode(@session('user_permissions')))) { ?>
                               
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                <i class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php }?>
                                    <th>{{trans('lang.subject')}}</th>

                                    <th>{{trans('lang.message')}}</th>

                                    <th>{{trans('lang.date_created')}}</th>

                                    <?php if (in_array('notification.delete', json_decode(@session('user_permissions')))) { ?>
                                    <th>{{trans('lang.actions')}}</th>
                                        <?php }?>
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

@endsection

@section('scripts')

<script type="text/javascript">
    var user_permissions = '<?php echo @session('user_permissions') ?>';

        user_permissions = JSON.parse(user_permissions);

        var checkDeletePermission = false;

        if ($.inArray('notification.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }

    var database = firebase.firestore();
    var offest = 1;
    var pagesize = 10;
    var end = null;
    var endarray = [];
    var start = null;
    var user_number = [];
    var refData = database.collection('notifications');
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
                start = snapshots.docs[snapshots.docs.length - 1];
                endarray.push(snapshots.docs[0]);
                if (snapshots.docs.length < pagesize) {
                    jQuery("#data-table_paginate").hide();
                }

            }

            $('#notificationTable').DataTable({
                order: [],
                columnDefs: [
                    {
                        targets:(checkDeletePermission==true) ? 3 : 2,
                        type: 'date',
                        render: function (data) {

                            return data;
                        }
                    },
                    {orderable: false, targets: (checkDeletePermission==true) ? [0, 4] : ''},
                ],
                order: (checkDeletePermission==true) ? ['3', 'desc'] : ['2','desc'],
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}"
                },
                responsive: true
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

                    database.collection('notifications').doc(dataId).delete().then(function () {

                        window.location.reload();
                    });

                });

            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    function buildHTML(snapshots) {

        if (snapshots.docs.length < pagesize) {
            jQuery("#data-table_paginate").hide();
        }
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
            if(checkDeletePermission){
            html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>';
            }    
            html = html + '<td>' + val.subject + '</td>';

            html = html + '<td>' + val.message + '</td>';

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
            if(checkDeletePermission){
            html = html + '<td class="action-btn"><a id="' + val.id + '" name="notifications-delete" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a></td>';
            }
            html = html + '</tr>';
            count = count + 1;
        });
        return html;
    }

    $(document).on("click", "a[name='notifications-delete']", function (e) {
        var id = this.id;
        database.collection('notifications').doc(id).delete().then(function () {
            window.location.reload();
        });
    });
</script>


@endsection
