@extends('backend.layout')

@section('admin')

<main class="main-wrap">
<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Site Settings</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
            Add New Setting
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3">
        @foreach($settings as $group => $items)
            <li class="nav-item">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-{{ $group }}">
                    {{ ucfirst($group) }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach($settings as $group => $items)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
             id="tab-{{ $group }}">

            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="20%">Key</th>
                                <th>Value</th>
                                <th width="10%">Type</th>
                                <th width="140">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($items as $setting)
                            <tr>
                                <td><code>{{ $setting->key }}</code></td>
                                <td>
                                    @if($setting->type === 'image' && $setting->value)
                                        <img src="{{ asset('storage/'.$setting->value) }}"
                                             style="max-height:40px">
                                    @else
                                        {{ Str::limit($setting->value, 80) }}
                                    @endif
                                </td>
                                <td>{{ $setting->type }}</td>
                                <td>
                                    <button
                                        class="btn btn-sm btn-warning editBtn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editSettingModal"
                                        data-id="{{ $setting->id }}"
                                        data-group="{{ $setting->group }}"
                                        data-key="{{ $setting->key }}"
                                        data-type="{{ $setting->type }}"
                                        data-value="{{ $setting->value }}">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('site-settings.destroy', $setting->id) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this setting?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        @endforeach
    </div>

</div>
</main>

{{-- ================= ADD MODAL ================= --}}
<div class="modal fade" id="addSettingModal" tabindex="-1">
<div class="modal-dialog">
<form method="POST" action="{{ route('site-settings.store') }}" enctype="multipart/form-data">
@csrf
<div class="modal-content">

    <div class="modal-header">
        <h5>Add Site Setting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <div class="mb-2">
            <label>Group</label>
            <input type="text" name="group" class="form-control" placeholder="seo / general / branding" required>
        </div>

        <div class="mb-2">
            <label>Key</label>
            <input type="text" name="key" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Type</label>
            <select name="type" class="form-select" id="addType">
                <option value="text">Text</option>
                <option value="textarea">Textarea</option>
                <option value="image">Image</option>
                <option value="url">URL</option>
                <option value="email">Email</option>
            </select>
        </div>

        <div class="mb-2" id="addValueWrap">
            <label>Value</label>
            <input type="text" name="value" class="form-control">
        </div>
    </div>

    <div class="modal-footer">
        <button class="btn btn-primary">Save</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    </div>

</div>
</form>
</div>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div class="modal fade" id="editSettingModal" tabindex="-1">
<div class="modal-dialog">
<form method="POST" id="editForm" enctype="multipart/form-data">
@csrf
@method('PUT')
<div class="modal-content">

    <div class="modal-header">
        <h5>Edit Site Setting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <div class="mb-2">
            <label>Group</label>
            <input type="text" name="group" id="editGroup" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Key</label>
            <input type="text" name="key" id="editKey" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Type</label>
            <select name="type" id="editType" class="form-select">
                <option value="text">Text</option>
                <option value="textarea">Textarea</option>
                <option value="image">Image</option>
                <option value="url">URL</option>
                <option value="email">Email</option>
            </select>
        </div>

        <div class="mb-2" id="editValueWrap">
            <label>Value</label>
            <input type="text" name="value" id="editValue" class="form-control">
        </div>
    </div>

    <div class="modal-footer">
        <button class="btn btn-primary">Update</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    </div>

</div>
</form>
</div>
</div>

@endsection

@push('scripts')
<script>
function renderValueInput(type, wrap, value = '') {
    if (type === 'textarea') {
        wrap.innerHTML = `<label>Value</label><textarea name="value" class="form-control" rows="3">${value}</textarea>`;
    } else if (type === 'image') {
        wrap.innerHTML = `<label>Image</label><input type="file" name="value" class="form-control">`;
    } else {
        wrap.innerHTML = `<label>Value</label><input type="text" name="value" class="form-control" value="${value}">`;
    }
}

// Add modal type switch
document.getElementById('addType').addEventListener('change', function () {
    renderValueInput(this.value, document.getElementById('addValueWrap'));
});

// Edit modal populate
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('editGroup').value = this.dataset.group;
        document.getElementById('editKey').value = this.dataset.key;
        document.getElementById('editType').value = this.dataset.type;

        renderValueInput(
            this.dataset.type,
            document.getElementById('editValueWrap'),
            this.dataset.value ?? ''
        );

        document.getElementById('editForm').action =
            `/admin/site-settings/${this.dataset.id}`;
    });
});
</script>
@endpush
