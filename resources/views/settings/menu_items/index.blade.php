@extends('layouts.app')


@section('content')

<div class="page-wrapper">


    <div class="row page-titles">


        <div class="col-md-5 align-self-center">


            <h3 class="text-themecolor">{{trans('lang.menu_items')}}</h3>


        </div>


        <div class="col-md-7 align-self-center">


            <ol class="breadcrumb">


                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>


                <li class="breadcrumb-item">{{trans('lang.menu_items')}}</li>


                <li class="breadcrumb-item active">{{trans('lang.menu_items_table')}}</li>


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

                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                            class="fa fa-list mr-2"></i>{{trans('lang.menu_items_table')}}</a>

                            </li>

                            <li class="nav-item">

                                <a class="nav-link" href="{!! route('setting.banners.create') !!}"><i
                                            class="fa fa-plus mr-2"></i>{{trans('lang.menu_items_create')}}</a>

                            </li>


                        </ul>

                    </div>

                    <div class="card-body">


                        <div class="table-responsive m-t-10">

                            <table id="bannerItemsTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">


                                <thead>


                                <tr>
                                 <?php if (in_array('setting.banners.delete', json_decode(@session('user_permissions')))) { ?>
                                   
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)">
                                                <i class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php }?>
                                    <th>{{trans('lang.photo')}}</th>
                                    <th>{{trans('lang.title')}}</th>
                                    <th>{{trans('lang.banner_position')}}</th>
                                    <th>{{trans('lang.publish')}}</th>
                                    <th>{{trans('lang.actions')}}</th>


                                </tr>


                                </thead>


                                <tbody id="append_vendors">

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

        if ($.inArray('setting.banners.delete', user_permissions) >= 0) {
            checkDeletePermission = true;
        }


    var database = firebase.firestore();

    var offest = 1;

    var pagesize = 10;

    var end = null;

    var endarray = [];

    var start = null;

    var user_number = [];

    var refData = database.collection('menu_items').orderBy('title');

    var append_list = '';

    var placeholderImage = '';

    var placeholder = database.collection('settings').doc('placeHolderImage');


    placeholder.get().then(async function (snapshotsimage) {

        var placeholderImageData = snapshotsimage.data();

        placeholderImage = placeholderImageData.image;

    })


    $(document).ready(function () {


        var inx = parseInt(offest) * parseInt(pagesize);

        jQuery("#data-table_processing").show();


        append_list = document.getElementById('append_vendors');

        append_list.innerHTML = '';

        refData.get().then(async function (snapshots) {

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


            $('#bannerItemsTable').DataTable({
                order: (checkDeletePermission==true) ? ['2', 'asc'] : ['1','asc'],
                columnDefs: [

                    {orderable: false, targets: (checkDeletePermission==true) ? [0, 1, 4, 5] : [0,3,4]},
                ],
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}"
                },
                responsive: true
            });

        });


    })


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

            var route1 = '{{route("setting.banners.edit",":id")}}';

            route1 = route1.replace(':id', id);
            if(checkDeletePermission){
            html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '" style="width:30px;"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>';
            }
            if (val.photo != '') {

                html = html + '<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + val.photo + '" alt="image"></td>';

            } else {

                html = html + '<td><img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image"></td>';

            }

            html = html + '<td><a href="' + route1 + '">' + val.title + '</a></td>';
            html = html + '<td>' + val.position + '</td>';


            if (val.is_publish) {
                html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="publish"><span class="slider round"></span></label></td>';
            } else {
                html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="publish"><span class="slider round"></span></label></td>';
            }

            html = html + '<td class="vendor-action-btn"><a href="' + route1 + '"><i class="fa fa-edit"></i></a>';
            if(checkDeletePermission){
            html=html+'<a id="' + val.id + '" name="vendor-delete" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
            }
            html=html+'</td>';


            html = html + '</tr>';

            count = count + 1;

        });

        return html;

    }

    /* toggal publish action code start*/
    $(document).on("click", "input[name='publish']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('menu_items').doc(id).update({'is_publish': true}).then(function (result) {

            });
        } else {
            database.collection('menu_items').doc(id).update({'is_publish': false}).then(function (result) {

            });
        }

    });

    /*toggal publish action code end*/

    $("#is_active").click(function () {
        $("#bannerItemsTable .is_open").prop('checked', $(this).prop('checked'));

    });

    $("#deleteAll").click(function () {
        if ($('#bannerItemsTable .is_open:checked').length) {
            if (confirm('Are You Sure want to Delete Selected Data ?')) {
                jQuery("#data-table_processing").show();
                $('#bannerItemsTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');

                    database.collection('menu_items').doc(dataId).delete().then(function () {
                        window.location.reload();

                    });

                });

            }
        } else {
            alert('Please Select Any One Record .');
        }
    });


    $(document).on("click", "a[name='vendor-delete']", function (e) {
        var id = this.id;
        database.collection('menu_items').doc(id).delete().then(function () {
            window.location.reload();
        });
    });


</script>

@endsection
