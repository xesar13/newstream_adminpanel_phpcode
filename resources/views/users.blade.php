@extends('layouts.main')

@section('title')
    {{ __('user') . ' ' . __('list') }}
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item text-dark">
                            <a href="{{ route('home') }}" class="text-dark"><i class="fas fa-home mr-1"></i>{{ __('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active"><i class="nav-icon fas fa-user mr-1"></i>{{ __('user') . ' ' . __('list') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        @can('user-list')
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('user') . ' ' . __('list') }} </h3>
                        </div>
                        <div class="card-body">
                            <table aria-describedby="mydesc" id='table' data-toggle="table" data-url="{{ route('usersList') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true"
                                data-show-refresh="true" data-toolbar="#toolbar" data-mobile-responsive="true" data-buttons-class="primary" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th scope="col" data-field="id" data-sortable="true">{{ __('id') }}</th>
                                        <th scope="col" data-field="profile">{{ __('profile') }}</th>
                                        <th scope="col" data-field="name" data-sortable="true">{{ __('name') }}</th>
                                        <th scope="col" data-field="email" data-sortable="true">{{ __('email') }}</th>
                                        <th scope="col" data-field="type">{{ __('type') }}</th>
                                        <th scope="col" data-field="mobile">{{ __('mobile') }}</th>
                                        <th scope="col" data-field="status1">{{ __('status') }}</th>
                                        <th scope="col" data-field="date" data-sortable="true">{{ __('created_at') }}</th>
                                        <th scope="col" data-field="role">{{__('create_and_manage') . ' ' . __('news') }}</th>
                                        @canany(['user-edit'])
                                        <th scope="col" data-field="operate" data-events="actionEvents">{{ __('operate') }}</th>
                                        @endcanany
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        <div class="modal fade" id="editDataModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ __('edit') . ' ' . __('user') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="update_form" action="{{ url('app_users') }}" role="form" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type='hidden' name="edit_id" id="edit_id" value='' />
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-12 col-sm-12">
                                    <div class="form-check">
                                        <input id="edit_role" name="edit_role" value="1" type="checkbox" class="form-check-input">
                                        <label class="form-check-label user-role">{{ __('allow_manage_news') }}</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-12 col-sm-12">
                                    <label>{{ __('status') }}</label><br>
                                    <div class="btn-group">
                                        <label class="btn btn-success" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input class="mr-1" type="radio" name="edit_status" value="1">{{ __('active') }}
                                        </label>
                                        <label class="btn btn-danger" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input class="mr-1" type="radio" name="edit_status" value="0">{{ __('deactive') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('submit') }}</button>
                        </div>
                    </form>
                </div>
                </div>
            </div>
    </section>
@endsection

@section('script')
    <script type="text/javascript">
        window.actionEvents = {
            'click .edit-data': function(e, value, row, index) {
                $('#edit_id').val(row.id);
                if (row.role_id) {
                    $("input[name=edit_role]").prop("checked", true);
                } else {
                    $("input[name=edit_role]").prop("checked", false);
                }
                if (row.status == 0) {
                    $("input[name=edit_status][value=0]").prop("checked", true);
                } else {
                    $("input[name=edit_status][value=1]").prop('checked', true);
                }
            }
        };

        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                limit: p.limit,
                offset: p.offset,
                search: p.search,
            };
        }
    </script>
@endsection
