
@extends('layouts.main')

@section('title')
    {{ __('postik_configuration') }}
@endsection

@section('content')
 <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('manage') . ' ' . __('postik_configuration') }}</h1>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item text-dark">
                            <a href="{{ route('home') }}" class="text-dark"><i class="fas fa-home mr-1"></i>{{ __('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active"><i class="nav-icon fas fa-map-marker mr-1"></i>{{ __('postik_configuration') }}</li>
                    </ol>
                </div>

            </div>
        </div>
    </section>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>{{ __('postik_configuration') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form id="create_form" action="{{ route('postik.configuration.save') }}" role="form" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="endpoint_url">__{{ __('endpoint_url_postik') }}</label>
                            <input type="url" class="form-control" id="endpoint_url" name="endpoint_url" value="{{ old('endpoint_url', $endpoint) }}" required placeholder="https://admin-news.nextream.net">
                        </div>
                        <div class="form-group mb-3">
                            <label for="api_key">__{{ __('api_key_postik') }}</label>
                            <input type="text" class="form-control" id="api_key" name="api_key" value="{{ old('api_key', $apiKey) }}" required placeholder="API Key de Postik">
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('save_configuration') }}</button>
                    </form>
                    <div id="config-message"></div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Guardar configuración por AJAX
    document.getElementById('postik-config-form').addEventListener('submit', function(e) {
        e.preventDefault();
        let form = this;
        let data = new FormData(form);
        fetch("{{ route('postik.configuration.save') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: data
        })
        .then(response => response.json())
        .then(json => {
            let msg = document.getElementById('config-message');
            if(json.success) {
                msg.innerHTML = '<div class="alert alert-success">' + json.message + '</div>';
            } else {
                msg.innerHTML = '<div class="alert alert-danger">' + (json.message || 'Error al guardar configuración') + '</div>';
            }
        })
        .catch(() => {
            document.getElementById('config-message').innerHTML = '<div class="alert alert-danger">Error de red</div>';
        });
    });
});
</script>
@endpush
                </div>
            </div>
            
            <div class="card card-secondary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('integrations_list') }}</h4>
                </div>
                <div class="card-body">
                     <input type="checkbox" id="select-all-integrations" title="{{ __('select_all') }}">
                    <form id="postik-integrations-form">
                        @csrf
                        <div class="table-responsive">
                            <table aria-describedby="postikdesc" id="postik-integrations-table"
                                data-toggle="table"
                                data-url="{{ route('postik.integrations.list') }}"
                                data-side-pagination="server"
                                data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]"
                                data-search="true"
                                data-show-columns="true"
                                data-show-refresh="true"
                                data-mobile-responsive="true"
                                data-buttons-class="primary"
                                data-trim-on-search="false"
                                data-sort-name="id"
                                data-sort-order="desc">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">{{ __('ID') }}</th>
                                        <th data-field="picture" data-formatter="avatarFormatter">{{ __('avatar') }}</th>
                                        <th data-field="name" data-sortable="true">{{ __('name') }}</th>
                                        <th data-field="identifier" data-sortable="true">{{ __('identifier') }}</th>
                                        <th data-field="profile">{{ __('profile') }}</th>
                                        <th data-field="active" data-formatter="activeFormatter">
                                            {{ __('active') }}
                                        </th>
                                    </tr>

                                </thead>
                            </table>
                        </div>
                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-primary">{{ __('save_active_integrations') }}</button>
                        </div>
                    </form>
                    <div id="integrations-message"></div>
<script>
function avatarFormatter(value, row) {
    if (value) {
        return '<img src="' + value + '" class="rounded-circle" width="32" height="32">';
    }
    return '<span class="badge bg-secondary">N/A</span>';
}

function activeFormatter(value, row, index) {
    let checked = value ? 'checked' : '';
    return `<input type="checkbox" class="integration-active" value="${row.id}" ${checked}>`;
}

document.addEventListener('DOMContentLoaded', function() {
    // Refrescar tabla solo si existe el botón
    var refreshBtn = document.getElementById('refresh-integrations');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            $('#postik-integrations-table').bootstrapTable('refresh');
        });
    }

    // Guardar integraciones activas por AJAX
    document.getElementById('postik-integrations-form').addEventListener('submit', function(e) {
        e.preventDefault();
        let checked = document.querySelectorAll('.integration-active:checked');
        let ids = Array.from(checked).map(cb => cb.value);
        let token = document.querySelector('input[name="_token"]').value;
        let btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> {{ __('saving') }}';
        let msg = document.getElementById('integrations-message');
        msg.innerHTML = '';
        fetch("{{ route('postik.integrations.save') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ integrations: ids })
        })
        .then(r => r.json())
        .then(json => {
            if(json.success || json.message) {
                showSuccessToast(json.message || 'Integraciones guardadas');
            } else {
                showErrorToast(json.message || 'Error al guardar integraciones');
            }
            $('#postik-integrations-table').bootstrapTable('refresh');
        })
        .catch(() => {
            showErrorToast('Error de red');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '{{ __('save_active_integrations') }}';
        });
    });
});

// Seleccionar/deseleccionar todos los checkboxes de integraciones
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('select-all-integrations');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.integration-active');
            checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
        });
        // Si se cambia cualquier checkbox individual, actualizar el estado del select-all
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('integration-active')) {
                let all = document.querySelectorAll('.integration-active');
                let checked = document.querySelectorAll('.integration-active:checked');
                selectAll.checked = all.length > 0 && checked.length === all.length;
            }
        });
    }
});
</script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
