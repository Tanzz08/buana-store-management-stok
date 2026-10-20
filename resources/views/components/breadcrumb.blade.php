{{--
    Komponen breadcrumb navigasi.

    Props:
        $links — array dari ['label' => string, 'url' => string|null]
                 Elemen terakhir adalah halaman saat ini (tanpa link, diberi aria-current="page")

    Contoh penggunaan:
        <x-breadcrumb :links="[
            ['label' => 'Beranda', 'url' => route('admin.dashboard')],
            ['label' => 'Produk', 'url' => route('admin.products.index')],
            ['label' => 'Tambah Produk', 'url' => null],
        ]" />
--}}
@props(['links' => []])

@if (count($links) > 0)
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            @foreach ($links as $index => $link)
                @php $isLast = $index === count($links) - 1; @endphp

                <li class="breadcrumb__item">
                    @if (!$isLast)
                        {{-- Intermediate items: clickable links --}}
                        @if (!empty($link['url']))
                            <a
                                href="{{ $link['url'] }}"
                                class="breadcrumb__link"
                            >{{ $link['label'] }}</a>
                        @else
                            <span class="breadcrumb__current">{{ $link['label'] }}</span>
                        @endif

                        {{-- Separator --}}
                        <svg class="breadcrumb__separator" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    @else
                        {{-- Last item: current page, no link --}}
                        <span
                            class="breadcrumb__current"
                            aria-current="page"
                        >{{ $link['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
