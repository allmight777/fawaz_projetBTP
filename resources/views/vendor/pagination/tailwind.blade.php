{{-- Vue de pagination unique pour tout le site (remplace la vue Tailwind par défaut de
     Laravel). Suit automatiquement la couleur d'accent de l'espace via --accent, définie
     dans chaque layout. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="fawaz-pagination">
        <ul class="fawaz-pagination-list">
            {{-- Lien précédent --}}
            @if ($paginator->onFirstPage())
                <li class="fawaz-page-item disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span class="fawaz-page-link"><i class="fas fa-chevron-left"></i></span>
                </li>
            @else
                <li class="fawaz-page-item">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="fawaz-page-link" aria-label="{{ __('pagination.previous') }}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Numéros de page --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="fawaz-page-item disabled"><span class="fawaz-page-link fawaz-page-dots">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="fawaz-page-item active" aria-current="page">
                                <span class="fawaz-page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="fawaz-page-item">
                                <a href="{{ $url }}" class="fawaz-page-link" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Lien suivant --}}
            @if ($paginator->hasMorePages())
                <li class="fawaz-page-item">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="fawaz-page-link" aria-label="{{ __('pagination.next') }}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="fawaz-page-item disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span class="fawaz-page-link"><i class="fas fa-chevron-right"></i></span>
                </li>
            @endif
        </ul>
    </nav>

    @once
        <style>
            .fawaz-pagination { display: flex; justify-content: center; margin-top: 8px; }
            .fawaz-pagination-list {
                display: flex;
                align-items: center;
                gap: 4px;
                list-style: none;
                padding: 0;
                margin: 0;
                flex-wrap: wrap;
            }
            .fawaz-page-item { list-style: none; }
            .fawaz-page-link {
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 34px;
                height: 34px;
                padding: 0 8px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 500;
                color: #4b5563;
                text-decoration: none;
                border: 1px solid transparent;
                transition: all 0.2s ease;
            }
            .fawaz-page-link i { font-size: 12px; line-height: 1; }
            .fawaz-page-item:not(.disabled):not(.active) a.fawaz-page-link:hover {
                background: var(--accent-bg, #f3f4f6);
                color: var(--accent, #2563eb);
                border-color: var(--accent-bg, #f3f4f6);
            }
            .fawaz-page-item.active .fawaz-page-link {
                background: var(--accent, #2563eb);
                color: #ffffff;
                font-weight: 700;
            }
            .fawaz-page-item.disabled .fawaz-page-link {
                color: #c1c5cc;
                cursor: not-allowed;
            }
            .fawaz-page-dots { border: none; cursor: default; }
        </style>
    @endonce
@endif
