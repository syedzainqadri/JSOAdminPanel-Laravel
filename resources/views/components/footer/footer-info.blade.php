<div class="col-xl-3">
    <div class="footer__brand-logo">
        @if ($logotype === 'dark')
            <img style=" height: 48px;width: 182px;" src="{{ $settings->white_logo_url }}" alt="logo-brand" />
        @else
            <img style="height: 48px;width: 182px;" src="{{ $settings->logo_image_url }}" alt="logo-brand" />
        @endif
    </div>
    <div class="footer__loc-info">
        <p class="text--body-3">
            {{ $cms->contact_address }}
        </p>
        <p class="text--body-3 phone">
            {{ __('phone') }}: {{ $cms->contact_number }}
        </p>
        <p class="text--body-3 email text-lowercase">
            {{ __('mail') }}: {{ $cms->contact_email }}
        </p>
    </div>
</div>
