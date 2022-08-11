<div class="">
    <form action="{{ route('frontend.seller.review') }}" method="post">
        @csrf
        <div id="rateYo">
        </div>
        @error('stars')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
        <input type="hidden" name="stars" id="rating">
        <div class="mt-3 input-field--textarea">
            <textarea name="comment" placeholder="" id="description" class="@error('comment') border-danger @enderror"></textarea>

            @error('comment')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit" class="btn">
            <span class="ml-2">{{ __('publish_review') }}</span>
            <svg width="24" height="24" class="ml-1" viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M3.75 12H20.25" stroke="white" stroke-width="1.6" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M13.5 5.25L20.25 12L13.5 18.75" stroke="white" stroke-width="1.6" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>
    </form>
</div>

@push('component_style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
    <style>
        .mb-24 {
            margin-bottom: 24px;
        }
    </style>
@endpush

@push('component_script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#rateYo").rateYo({
                starWidth: '30px',
                fullStar: true,
                mormalFill: 'yellow',
                ratedFill: 'orange',
                onSet: function(rating, rateYoInstance) {
                    $('#rating').val(rating);
                }
            });
        });
    </script>
    <script>
        setTimeout(() => {
            $('.jq-ry-normal-group').addClass('d-flex');
            $('.jq-ry-normal-group').addClass('gap-1');
        }, 1000);
    </script>
@endpush
