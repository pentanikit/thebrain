@forelse ($categories as $category)
    <tr>
        <td class="text-center">
            @if($category->level > 1)
                {{-- Child / subcategory indicator --}}
                <i class="material-icons md-subdirectory_arrow_right text-muted"></i>
            @else
                {{-- Main category checkbox --}}
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="{{ $category->id }}">
                </div>
            @endif
        </td>

        <td>{{ $counter }}</td>

        <td>
            <b>
                {!! str_repeat('— ', max(0, $category->level - 1)) !!}
                {{ $category->name }}
            </b>
        </td>

        <td>
            @if($category->thumbnail)
                <img src="{{ asset('storage') . '/' . $category->thumbnail }}"
                     width="60" height="60" alt="{{ $category->name }}">
            @else
                <span class="text-muted small">No image</span>
            @endif
        </td>

        <td>/{{ $category->slug }}</td>

        <td>{{ $category->sort_order }}</td>

        <td class="text-end">
            <div class="dropdown">
                <a class="btn btn-light rounded btn-sm font-sm"
                   href="#"
                   data-bs-toggle="dropdown">
                    <i class="material-icons md-more_horiz"></i>
                </a>
                <div class="dropdown-menu">
                    {{-- Edit link later if you want --}}
                    {{-- <a class="dropdown-item" href="#">Edit info</a> --}}

                    <form action="{{ route('deletecategory', $category->id) }}"
                          method="POST"
                          onsubmit="return confirm('Delete this category?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </td>
    </tr>
    <?php $counter++; ?>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted">
            No Categories Found
        </td>
    </tr>
@endforelse
