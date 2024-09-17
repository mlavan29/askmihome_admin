@extends('layouts.app')

@section('content')

<div class="page-wrapper">


    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">{{trans('lang.category_plural')}}</h3>

        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.category_plural')}}</li>
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

                <div class="card">

                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                            class="fa fa-list mr-2"></i>{{trans('lang.category_table')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{!! route('categories.create') !!}"><i
                                            class="fa fa-plus mr-2"></i>{{trans('lang.category_create')}}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">


                        <div class="table-responsive m-t-10">


                            <table id="categoriesTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">

                                <thead>

                                <tr>
                                <?php if (in_array('categories.delete', json_decode(@session('user_permissions')))) { ?>

                                    <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                        class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>
                                                        <?php } ?>
                                    <th>{{trans('lang.category_image')}}</th>

                                    <th>{{trans('lang.faq_category_name')}}</th>
                                    <th>{{trans('lang.food_plural')}}</th>
                                    <th> {{trans('lang.item_publish')}}</th>
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
    var ref = database.collection('vendor_categories').orderBy('title');;
    var append_list = '';
    var placeholderImage = '';
    var user_permissions = '<?php echo @session('user_permissions') ?>';

    user_permissions = JSON.parse(user_permissions);

    var checkDeletePermission = false;

    if ($.inArray('categories.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }
    $(document).ready(function () {

        var inx = parseInt(offest) * parseInt(pagesize);
        jQuery("#data-table_processing").show();

        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })

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



            $('#categoriesTable').DataTable({
                order: (checkDeletePermission==true) ? [2, "asc"] : [1,"asc"],
                columnDefs: [
                    {
                        orderable: false,
                         targets: (checkDeletePermission == true) ? [0, 1, 4, 5] : [0, 1, 3, 4],

                        },
                   {
                    targets: (checkDeletePermission == true) ? [3] : [2],

                    type: "html-num-fmt",
                    },
                ],
                "language": {
                    "zeroRecords": "{{trans("lang.no_record_found")}}",
                    "emptyTable": "{{trans("lang.no_record_found")}}"
                },
                responsive: true
            });


                      

        });
    });

  async  function buildHTML(snapshots) {
        var html = '';
        await Promise.all(snapshots.docs.map(async (listval) => {
            var val = listval.data();

            var getData = await getListData(val);
            html += getData;
        }));
        return html;
    }
    
    async function getListData(val){
            var html = '';
            html = html + '<tr>';
            newdate = '';

            var id = val.id;
            var route1 = '{{route("categories.edit",":id")}}';
            route1 = route1.replace(':id', id);
            if (checkDeletePermission) {

            html = html + '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label"\n' +
                'for="is_open_' + id + '" ></label></td>';
            }
            if (val.photo == '') {
                html = html + '<td><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image"></td>';
            } else {
                html = html + '<td><img class="rounded" style="width:50px" src="' + val.photo + '" alt="image"></td>';
            }

            html = html + '<td data-url="' + route1 + '" class="redirecttopage"><a href="' + route1 + '">' + val.title + '</a></td>';
            productCount=await getProductTotal(val.id);
            var categoryId = val.id;
            var url = '{{url("items?categoryID=id")}}';
            url = url.replace("id", categoryId);
            html = html + '<td data-order="'+productCount+'"><a class="product_' + val.id + '" href="' + url + '">'+productCount+'</a></td>';
            if (val.publish) {
                html = html + '<td><label class="switch"><input type="checkbox" checked id="' + val.id + '" name="publish"><span class="slider round"></span></label></td>';
            } else {
                html = html + '<td><label class="switch"><input type="checkbox" id="' + val.id + '" name="publish"><span class="slider round"></span></label></td>';
            }
            html = html + '<td class="action-btn"><a href="' + route1 + '"><i class="fa fa-edit"></i></a>';
            if (checkDeletePermission) {

            html = html + '<a id="' + val.id + '" name="category-delete" class="do_not_delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
            }
            html = html + '</td>';
            html = html + '</tr>';
            return html;

    }
    async function getProductTotal(id, section_id) {


        var vendor_products = database.collection('vendor_products').where('categoryID', '==', id);
        var Product_total=0;
        if (section_id) {
            vendor_products = vendor_products.where('section_id', '==', section_id)
        }
        await vendor_products.get().then(async function (productSnapshots) {
             Product_total = productSnapshots.docs.length;
              return Product_total;
            //jQuery(".product_" + id).html(Product_total);
        });
        return Product_total;
    }

    $(document).on("click", "a[name='category-delete']", function (e) {
        var id = this.id;
        database.collection('vendor_categories').doc(id).delete().then(function (result) {
            window.location.href = '{{ route("categories")}}';
        });
    });


    $("#is_active").click(function () {
        $("#categoriesTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {

        if ($('#categoriesTable .is_open:checked').length) {

            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                $('#categoriesTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');
                    database.collection('vendor_categories').doc(dataId).delete().then(function () {
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

    $(document).on("click", "input[name='publish']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        if (ischeck) {
            database.collection('vendor_categories').doc(id).update({'publish': true}).then(function (result) {

            });
        } else {
            database.collection('vendor_categories').doc(id).update({'publish': false}).then(function (result) {

            });
        }

    });

</script>

@endsection
