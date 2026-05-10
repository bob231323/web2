{{-- Usage: <x-pet-card :pet="$pet" :index="$loop->index" /> --}}
@props(['pet', 'index' => 0])

<div class="pet-card" data-pet-id="{{ $pet->id }}" style="animation-delay: {{ $index * 0.07 }}s">
    <div class="pet-card-image">
        @if (!empty($pet->image_path))
            <img
                src="{{ asset('storage/' . $pet->image_path) }}"
                alt="{{ $pet->name }}"
                loading="lazy"
                onerror="this.parentElement.innerHTML='<span class=pet-type-initial>{{ strtoupper(substr($pet->type, 0, 1)) }}</span>'"
            >
        @else
            <span class="pet-type-initial">{{ strtoupper(substr($pet->type, 0, 1)) }}</span>
        @endif
    </div>
    <div class="pet-card-body">
        <span class="pet-card-type">{{ $pet->type ?? 'other' }}</span>
        <div class="pet-card-name">{{ $pet->name }}</div>
        <div class="pet-card-meta">
            @if (!empty($pet->breed)){{ $pet->breed }} · @endif
            @if ($pet->age !== null){{ (int) $pet->age }} yr{{ $pet->age == 1 ? '' : 's' }}@endif
        </div>
        <div class="pet-card-desc">{{ $pet->description ?? '' }}</div>
        <div class="pet-card-actions">
            <button class="btn-edit"
                onclick='openEditModal({{ json_encode(["id"=>$pet->id,"name"=>$pet->name,"type"=>$pet->type,"breed"=>$pet->breed,"age"=>$pet->age,"description"=>$pet->description]) }})'>
                Edit
            </button>
            <button class="btn-delete"
                onclick="openConfirm({{ $pet->id }}, '{{ route('pets.destroy', $pet->id) }}')">
                Delete
            </button>
        </div>
    </div>
</div>