@extends('backend.layout')

@section('admin')

<main class="main-wrap">
    <div class="container my-5">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Section Titles</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                Add New
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Category Type</th>
                            <th>Section Title</th>
                            <th>Key</th>
                            <th>Value</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($titles as $title)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $title->category_type }}</td>
                            <td>{{ $title->section_title }}</td>
                            <td><code>{{ $title->key }}</code></td>
                            <td>{{ $title->value }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-warning editBtn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="{{ $title->id }}"
                                    data-category="{{ $title->category_type }}"
                                    data-sectiontitle="{{ $title->section_title }}"
                                    data-key="{{ $title->key }}"
                                    data-value="{{ $title->value }}">
                                    Edit
                                </button>

                                <form action="{{ route('section-titles.destroy', $title->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this title?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No section titles found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

{{-- ================= ADD MODAL ================= --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('section-titles.store') }}">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add Section Title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <label>Category Type</label>
                        <select name="category_type" class="form-select form-select-sm" required>
                            <option value="">Select Category</option>

                            @php
                                $renderOptions = function ($categories, $level = 0) use (&$renderOptions) {
                                    foreach ($categories as $category) {
                                        echo '<option value="'.$category->name.'">'
                                            . str_repeat('— ', $level)
                                            . e($category->name)
                                            . '</option>';

                                        if ($category->children && $category->children->count()) {
                                            $renderOptions($category->children, $level + 1);
                                        }
                                    }
                                };
                                $renderOptions(category_tree());
                            @endphp
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>Key</label>
                        <input type="text" name="key"
                               class="form-control"
                               placeholder="hero_title" required>
                    </div>
                    <div class="mb-2">
                        <label>Section Title</label>
                        <input type="text" name="section_title"
                               class="form-control"
                               placeholder="Section Title" required>
                    </div>

                    <div class="mb-2">
                        <label>Value</label>
                        <textarea name="value"
                                  class="form-control"
                                  rows="3" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm">
            @csrf
            @method('PUT')

            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Section Title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <label>Category Type</label>
                        <input type="text" name="category_type"
                               id="editCategory"
                               class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Section Title</label>
                        <input type="text" name="section_title"
                               id="editsectiontitle"
                               class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Key</label>
                        <input type="text" name="key"
                               id="editKey"
                               class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Value</label>
                        <textarea name="value"
                                  id="editValue"
                                  class="form-control"
                                  rows="3" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Update</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;

        document.getElementById('editCategory').value = this.dataset.category;
        document.getElementById('editsectiontitle').value = this.dataset.sectiontitle;
        document.getElementById('editKey').value = this.dataset.key;
        document.getElementById('editValue').value = this.dataset.value;

        document.getElementById('editForm').action =
            `/admin/section-titles/${id}`;
    });
});
</script>
@endpush
