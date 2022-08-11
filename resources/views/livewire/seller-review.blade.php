<div>
    @if ($total != 0)
        @foreach ($reviews as $review)
            <div class="review-wrap d-flex">
                <div class="profile-img">
                    <img src="{{ $review->user->image_url }}" alt="Seller Profile" class="seller-image">
                </div>
                <div class="review-info">
                    @if ($review->stars)
                        <div class="rating d-flex">
                            <div class="star-rating">
                                @for ($i = 0; $i < $review->stars; $i++)
                                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.8446 14.901L14.7849 17.3974C15.2886 17.7165 15.9139 17.2419 15.7644 16.654L14.626 12.1756C14.5939 12.0509 14.5977 11.9197 14.637 11.797C14.6762 11.6743 14.7492 11.5652 14.8477 11.4822L18.3811 8.54132C18.8453 8.1549 18.6057 7.38439 18.0092 7.34567L13.3949 7.0462C13.2706 7.03732 13.1514 6.99332 13.0511 6.91931C12.9509 6.84531 12.8737 6.74435 12.8286 6.62819L11.1076 2.29436C11.0609 2.17106 10.9777 2.06492 10.8692 1.99002C10.7606 1.91511 10.6319 1.875 10.5 1.875C10.3681 1.875 10.2394 1.91511 10.1309 1.99002C10.0223 2.06492 9.93914 2.17106 9.89236 2.29436L8.1714 6.62819C8.12631 6.74435 8.04914 6.84531 7.9489 6.91931C7.84865 6.99332 7.72944 7.03732 7.60515 7.0462L2.99078 7.34567C2.39429 7.38439 2.15466 8.1549 2.61894 8.54132L6.15232 11.4822C6.25079 11.5652 6.32383 11.6743 6.36305 11.797C6.40226 11.9197 6.40606 12.0509 6.374 12.1756L5.31824 16.3288C5.13889 17.0343 5.88929 17.6038 6.49369 17.2209L10.1554 14.901C10.2584 14.8354 10.3779 14.8006 10.5 14.8006C10.6221 14.8006 10.7416 14.8354 10.8446 14.901Z"
                                            fill="#FFBF00" />
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    @endif
                    <div class="seller-details d-flex">
                        <h2 class="name">{{ $review->user->name }}</h2>
                    </div>
                    <p class="review-text">{{ $review->comment }}</p>
                </div>
            </div>
        @endforeach
    @endif
    @if ($reviews->count() > 0)
        <div class="load-more">
            @if ($loadbutton && $total >= 5)
                @if (count($reviews) >= $total)
                    <div class="text-center">{{ __('no_more_comments_found') }}</div>
                @else
                    @if ($loading)
                        <button wire:loading class="btn btn--bg">
                            {{ __('loading') }}
                            <span class="icon--right">
                                <x-svg.sync-icon />
                            </span>
                        </button>
                    @else
                        <button wire:click="loadmore" wire:loading.remove class="btn btn-load-more">
                            <span>
                                <x-svg.sync-icon />
                            </span>
                            <span>{{ __('load_more') }}</span>
                        </button>
                    @endif
                @endif
            @endif
        </div>
    @endif
</div>
