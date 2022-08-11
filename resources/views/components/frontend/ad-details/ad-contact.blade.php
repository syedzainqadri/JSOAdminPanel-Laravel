@props(['ad'])

<div class="product-item__sidebar-item">
    @if ($ad->show_phone && $ad->phone !== null)
        <div class="card-number">
            <div class="number number--hide text--body-2">
                <span class="icon">
                    <x-svg.phone-icon width="32" height="32" />
                </span>
                {{ Str::limit($ad->phone, 8, ' XXXXXXXX') }}
            </div>
            <div class="number number--show text--body-2">
                <span class="icon">
                    <x-svg.phone-icon width="32" height="32" />
                </span>
                {{ $ad->phone }}
            </div>

            <span class="text--body-4 message">{{ __('reveal_phone_number') }}</span>
        </div>
    @endif

    @if (auth('user')->check() && auth('user')->user()->username !== $ad->customer->username)
        <form action="{{ route('frontend.message.store', $ad->customer->username) }}" method="POST"
            id="sendMessageForm">
            @csrf
            <input type="hidden" value="." name="body">
            <button type="submit" class="btn w-100">
                <span class="icon--left">
                    <x-svg.message-icon width="24" height="24" stroke="white" strokeWidth="1.6" />
                </span>
                {{ __('send_message') }}
            </button>
        </form>
        @if ($ad->whatsapp)
            <a href="https://wa.me/{{ $ad->whatsapp }}" class="btn w-100 mt-2 bg-success" target="_blank">
                <span class="icon--left">
                    <x-svg.whatsapp-icon />
                </span>
                {{ __('send_message_via_whatsapp') }}
            </a>
        @endif
        @if ($ad->customer->email)
            <a href="mailto:{{ $ad->customer->email }}" class="btn w-100 mt-2 bg-secondary">
                <span class="icon--left">
                    <x-svg.envelope-icon stroke="#ffffff" />
                </span>
                {{ __('send_message_via_email') }}
            </a>
        @endif
    @endif
    @if (!auth('user')->check())
        <a href="{{ route('users.login') }}" class="btn w-100 login_required">
            <span class="icon--left">
                <x-svg.message-icon width="24" height="24" stroke="white" strokeWidth="1.6" />
            </span>
            {{ __('send_message') }}
        </a>
        @if ($ad->whatsapp)
            <a href="https://wa.me/{{ $ad->whatsapp }}" class="btn w-100 mt-2 bg-success" target="_blank">
                <span class="icon--left">
                    <x-svg.whatsapp-icon />
                </span>
                {{ __('send_message_via_whatsapp') }}
            </a>
        @endif
        <a href="mailto:{{ $ad->customer->email }}" class="btn w-100 mt-2 bg-secondary">
            <span class="icon--left">
                <x-svg.envelope-icon stroke="#ffffff" />
            </span>
            {{ __('send_message_via_email') }}
        </a>
    @endif
</div>
