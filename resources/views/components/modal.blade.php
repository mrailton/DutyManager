@props(['name', 'title', 'width' => 'max-w-md'])

<dialog {{ $attributes->class('modal') }} x-ref="{{ $name }}">
    <div class="modal-box {{ $width }}">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <h3 class="text-lg font-bold">{{ $title }}</h3>
        <div class="mt-4">
            {{ $slot }}
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
